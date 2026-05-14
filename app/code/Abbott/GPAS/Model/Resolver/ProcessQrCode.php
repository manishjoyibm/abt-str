<?php


namespace Abbott\GPAS\Model\Resolver;

use Abbott\GPAS\Api\QrCodeManagerInterface;
use Abbott\GPAS\Exception\UsedCodeException;
use Abbott\GPAS\Logger\Logger;
use Abbott\GPAS\Model\Cookie\QrCode;
use Abbott\GPAS\Model\GetCustomerIp;
use GraphQL\Error\UserError;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class ProcessQrCode implements ResolverInterface
{
    /**
     * @var QrCodeManagerInterface
     */
    protected $qrCodeManager;
    /**
     * @var QrCode
     */
    private $qrCodeCookie;
    /**
     * @var GetCustomerIp
     */
    private $customerIp;
    /**
     * @var Logger
     */
    private $logger;

    /**
     * ProcessQrCode constructor.
     *
     * @param QrCodeManagerInterface $qrCodeManager
     * @param QrCode $qrCodeCookie
     * @param GetCustomerIp $customerIp
     * @param Logger $logger
     */
    public function __construct(
        QrCodeManagerInterface $qrCodeManager,
        QrCode $qrCodeCookie,
        GetCustomerIp $customerIp,
        Logger $logger
    ) {
        $this->qrCodeManager = $qrCodeManager;
        $this->qrCodeCookie = $qrCodeCookie;
        $this->customerIp = $customerIp;
        $this->logger = $logger;
    }

    /**
     * Resolve function
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return string[]
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        /** @var \Magento\GraphQl\Model\Query\ContextInterface $context */
        $code = $args['input']['code'] ?? null;
        $lat = $args['input']['lat'] ?? null;
        $long = $args['input']['long'] ?? null;

        if (!$code) {
            throw new GraphQlNoSuchEntityException(__("Invalid QR code value"));
        }
        try {
            $response = $this->qrCodeManager->processInit(
                $code,
                $this->customerIp->getCurrentIp(),
                $context->getUserId(),
                $lat,
                $long
            );

            if ($context->getUserId() == 0) {
                $this->qrCodeCookie->set($response->getCode());
            }
        } catch (LocalizedException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        } catch (UsedCodeException $e) {
            throw new UserError(__($e->getMessage()));
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new GraphQlInputException(__("We were not able to process to code"));
        }
        return $response;
    }
}
