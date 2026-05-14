<?php
declare(strict_types=1);

namespace Abbott\AdultSignature\Controller\Adultsignature;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;

/**
 * Controller to mark adult signature as accepted on the current quote.
 *
 * @category  Abbott
 * @package   Abbott_AdultSignature
 */
class Accept extends Action implements HttpPostActionInterface, CsrfAwareActionInterface
{
    /** @var CheckoutSession */
    private CheckoutSession $checkoutSession;

    /** @var JsonFactory */
    private JsonFactory $resultJsonFactory;

    /**
     * @param Context $context Action context
     * @param CheckoutSession $checkoutSession Checkout session
     * @param JsonFactory $resultJsonFactory JSON result factory
     */
    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Execute controller: set acceptance flag and persist the quote.
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $quote = $this->checkoutSession->getQuote();

        if ($quote && (int)$quote->getData('adult_signature_required') === 1) {
            $quote->setData('adult_signature_accepted', 1);
            $quote->collectTotals()->save();
        }
        return $this->resultJsonFactory->create()->setData(['ok' => true]);
    }

    /**
     * Create CSRF validation exception (none in this case to bypass form key).
     *
     * @param RequestInterface $request Request
     * @return InvalidRequestException|null
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * Validate request for CSRF (return true to allow without form key).
     *
     * @param RequestInterface $request Request
     * @return bool
     */
    public function validateForCsrf(RequestInterface $request): bool
    {
        return true;
    }
}
