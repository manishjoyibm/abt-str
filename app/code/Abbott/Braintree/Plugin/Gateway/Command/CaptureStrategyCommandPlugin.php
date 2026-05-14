<?php


namespace Abbott\Braintree\Plugin\Gateway\Command;

use Braintree\Transaction;
use \PayPal\Braintree\Gateway\Command\CaptureStrategyCommand as OriginalCaptureStrategyCommand;
use PayPal\Braintree\Gateway\Helper\SubjectReader;
use PayPal\Braintree\Model\Adapter\BraintreeAdapter;
use PayPal\Braintree\Model\Adapter\BraintreeSearchAdapter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\ObjectManager\TMapFactory;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;

/**
 * Class CaptureStrategyCommandPlugin
 * @package Abbott\Braintree\Plugin\Gateway\Command
 */
class CaptureStrategyCommandPlugin
{

    /**
     * @var TransactionRepositoryInterface
     */
    private $repository;
    /**
     * @var FilterBuilder
     */
    private $filterBuilder;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var SubjectReader
     */
    private $subjectReader;
    /**
     * @var BraintreeAdapter
     */
    private $braintreeAdapter;
    /**
     * @var BraintreeSearchAdapter
     */
    private $braintreeSearchAdapter;

    /**
     * @var \Magento\Framework\ObjectManager\TMap
     */
    private $vaultHandlers;

    /**
     * CaptureStrategyCommandPlugin constructor.
     * @param TransactionRepositoryInterface $repository
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SubjectReader $subjectReader
     * @param BraintreeAdapter $braintreeAdapter
     * @param BraintreeSearchAdapter $braintreeSearchAdapter
     * @param TMapFactory $tmapFactory
     * @param array $vaultHandlers
     */
    public function __construct(
        TransactionRepositoryInterface $repository,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SubjectReader $subjectReader,
        BraintreeAdapter $braintreeAdapter,
        BraintreeSearchAdapter $braintreeSearchAdapter,
        TMapFactory $tmapFactory,
        $vaultHandlers = []
    ) {

        $this->repository = $repository;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->subjectReader = $subjectReader;
        $this->braintreeAdapter = $braintreeAdapter;
        $this->braintreeSearchAdapter = $braintreeSearchAdapter;
        $this->vaultHandlers = $tmapFactory->create(
            [
                'array' => $vaultHandlers,
                'type' => HandlerInterface::class
            ]
        );
    }

    /**
     * @param OriginalCaptureStrategyCommand $subject
     * @param array $commandSubject
     * @return array
     */
    public function beforeExecute(OriginalCaptureStrategyCommand $subject, array $commandSubject)
    {
        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = $this->subjectReader->readPayment($commandSubject);

        /** @var OrderPaymentInterface $paymentInfo */
        $paymentInfo = $paymentDO->getPayment();
        $existsCapture = $this->isExistsCaptureTransaction($paymentInfo);
        if ($existsCapture || $this->isExpiredAuthorization($paymentInfo)) {
            $extensionAttributes = $paymentInfo->getExtensionAttributes();
            $paymentToken = $extensionAttributes->getVaultPaymentToken();
            if (!$paymentToken) {
                $this->retrieveVaultToken($commandSubject);
            }
        }
    }

    /**
     * Check if capture transaction already exists
     *
     * @param OrderPaymentInterface $payment
     * @return bool
     */
    private function isExistsCaptureTransaction(OrderPaymentInterface $payment): bool
    {
        $this->searchCriteriaBuilder->addFilters(
            [
                $this->filterBuilder
                    ->setField('payment_id')
                    ->setValue($payment->getId())
                    ->create(),
            ]
        );

        $this->searchCriteriaBuilder->addFilters(
            [
                $this->filterBuilder
                    ->setField('txn_type')
                    ->setValue(TransactionInterface::TYPE_CAPTURE)
                    ->create(),
            ]
        );

        $searchCriteria = $this->searchCriteriaBuilder->create();

        $count = $this->repository->getList($searchCriteria)->getTotalCount();
        return (boolean) $count;
    }

    /**
     * @param OrderPaymentInterface $payment
     * @return boolean
     */
    private function isExpiredAuthorization(OrderPaymentInterface $payment): bool
    {
        $collection = $this->braintreeAdapter->search(
            [
                $this->braintreeSearchAdapter->id()->is($payment->getLastTransId()),
                $this->braintreeSearchAdapter->status()->is(Transaction::AUTHORIZATION_EXPIRED)
            ]
        );

        return $collection->maximumCount() > 0;
    }

    /**
     * @param OrderPaymentInterface $payment
     * @return boolean
     */
    private function retrieveVaultToken(array $commandSubject)
    {
        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = $this->subjectReader->readPayment($commandSubject);

        /** @var OrderPaymentInterface $payment */
        $payment = $paymentDO->getPayment();
        $collection = $this->braintreeAdapter->search(
            [
                $this->braintreeSearchAdapter->id()->is($payment->getLastTransId()),
            ]
        );

        if ($collection->maximumCount() > 0) {
            $transaction =  new \stdClass();
            $transaction->transaction = $collection->firstItem();
            if (isset($this->vaultHandlers[$payment->getMethod()])) {
                /** @var HandlerInterface $vaultHandler */
                $vaultHandler = $this->vaultHandlers[$payment->getMethod()];
                $vaultHandler->handle($commandSubject, ["object" => $transaction]);
            }
        }
    }

}
