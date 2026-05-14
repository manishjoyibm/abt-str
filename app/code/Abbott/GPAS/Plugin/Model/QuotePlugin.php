<?php


namespace Abbott\GPAS\Plugin\Model;


use Abbott\GPAS\Api\QrCodeRepositoryInterface;
use Abbott\GPAS\Helper\Data;
use Abbott\GPAS\Model\Attribute\Customer\QrCodeId;
use Abbott\GPAS\Model\Attribute\Product\IsGpas;
use Abbott\GPAS\Model\Cookie\QrCode;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;

class QuotePlugin
{

    /**
     * @var QrCode
     */
    private $qrCodeCookie;
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;
    /**
     * @var QrCodeRepositoryInterface
     */
    private $qrCodeRepository;
    /**
     * @var Data
     */
    private $helper;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * QuotePlugin constructor.
     * @param QrCode $qrCodeCookie
     * @param CustomerRepositoryInterface $customerRepository
     * @param QrCodeRepositoryInterface $qrCodeRepository
     * @param Data $helper
     */
    public function __construct(
        QrCode $qrCodeCookie,
        CustomerRepositoryInterface $customerRepository,
        QrCodeRepositoryInterface $qrCodeRepository,
        Data $helper, StoreManagerInterface $storeManager
    )
    {
        $this->qrCodeCookie = $qrCodeCookie;
        $this->customerRepository = $customerRepository;
        $this->qrCodeRepository = $qrCodeRepository;
        $this->helper = $helper;
        $this->storeManager = $storeManager;
    }

    /**
     * @param \Magento\Quote\Model\Quote $subject
     * @param \Magento\Catalog\Model\Product $product
     * @param null $request
     * @param string $processMode
     * @return array
     * @throws LocalizedException
     */
    public function beforeAddProduct(
        \Magento\Quote\Model\Quote $subject,
        \Magento\Catalog\Model\Product $product,
        $request = null,
        $processMode = \Magento\Catalog\Model\Product\Type\AbstractType::PROCESS_MODE_FULL)
    {
        if ($product->getData(IsGpas::ATTRIBUTE_CODE)) {
            if($this->helper->isEnabled($this->storeManager->getStore()->getId())) {
                try {
                    $qrCode = null;
                    if ($customerId = $subject->getCustomerId()) {
                        $customer = $this->customerRepository->getById($customerId);
                        if ($code = $customer->getCustomAttribute(QrCodeId::ATTRIBUTE_CODE)) {
                            $qrCode = $this->qrCodeRepository->getById($code->getValue());
                        }
                    } else {
                        if ($code = $this->qrCodeCookie->get()) {
                            $qrCode = $this->qrCodeRepository->getByCode($code);
                        }
                    }
                    if(!$qrCode || $qrCode->getIsRedeemed()) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('We cannot add this product to cart.')
                        );
                    }
                } catch (\Exception $e) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('We cannot add this product to cart.')
                    );
                }
            }
        }

        return [$product, $request, $processMode];
    }
}
