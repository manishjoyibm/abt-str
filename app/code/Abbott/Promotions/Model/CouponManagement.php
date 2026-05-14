<?php

namespace Abbott\Promotions\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use \Magento\Quote\Api\CouponManagementInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Abbott\CartRuleMessage\Helper\Data as dataHelper;
use Abbott\ShoppingCart\Helper\Data as shippingMessageDataHelper;

class CouponManagement extends \Magento\Quote\Model\CouponManagement implements
    \Abbott\Promotions\Api\AdditionalCouponMessageInterface
{
    /**
     * @var DataHelper
     */
    protected $dataHelper;

    /**
     * @var ShippingMessageDataHelper
     */
    protected $shippingMessageDataHelper;

    /**
     * Quote repository.
     *
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    protected $saleRule;

    protected $coupon;

    protected $json;

    protected $messageData;

    /**
     * Constructs a coupon read service object.
     *
     * @param CartRepositoryInterface $quoteRepository Quote repository.
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        \Magento\SalesRule\Model\Rule $saleRule,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Abbott\Promotions\Api\Data\MessageDataInterface $messageData,
        \Magento\SalesRule\Model\Coupon $coupon,
        DataHelper $dataHelper,
        ShippingMessageDataHelper $shippingMessageDataHelper
    ) {
        $this->saleRule = $saleRule;
        $this->json = $json;
        $this->messageData = $messageData;
        $this->coupon = $coupon;
        $this->dataHelper = $dataHelper;
        $this->shippingMessageDataHelper = $shippingMessageDataHelper;
        parent::__construct($quoteRepository);
    }

    /**
     * Set function
     *
     * @param $cartId
     * @param $couponCode
     * @return true
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function set($cartId, $couponCode)
    {
        /** @var  \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        $customErrorMessage = null;
        if (!$quote->getItemsCount()) {
            throw new NoSuchEntityException(__('The "%1" Cart doesn\'t contain products.', $cartId));
        }
        if (!$quote->getStoreId()) {
            throw new NoSuchEntityException(__('Cart isn\'t assigned to correct store'));
        }
        $quote->getShippingAddress()->setCollectShippingRates(true);
        $ruleId = $this->coupon->loadByCode($couponCode)->getRuleId();
        if ($ruleId) {
            $rule = $this->saleRule->load($ruleId);
            if ($rule->getEnableErrorMessage() && $rule->getCustomErrorMessage()) {
                $customErrorMessage = $rule->getCustomErrorMessage();
            }
        }
        try {
            $quote->setCouponCode($couponCode);
            $this->quoteRepository->save($quote->collectTotals());
        } catch (LocalizedException $e) {
            throw new CouldNotSaveException(__('The coupon code couldn\'t be applied: ' . $e->getMessage()), $e);
        } catch (\Exception $e) {
            if ($customErrorMessage) {
                throw new CouldNotSaveException(__($customErrorMessage));
            }
            throw new CouldNotSaveException(
                __("The coupon code couldn't be applied. Verify the coupon code and try again."),
                $e
            );
        }
        if ($quote->getCouponCode() != $couponCode) {
            if ($customErrorMessage) {
                throw new CouldNotSaveException(__($customErrorMessage));
            }
            throw new NoSuchEntityException(__("The coupon code isn't valid. Verify the code and try again."));
        }
        return true;
    }

    /**
     * Adds a coupon by code to a specified cart.
     *
     * @param int $cartId
     * @param string $couponCode
     * @return \Abbott\Promotions\Api\Data\MessageDataInterface
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function setCoupon($cartId, $couponCode)
    {
        $message = null;
        $response = $this->set($cartId, $couponCode);
        if ($response) {
            $ruleId = $this->coupon->loadByCode($couponCode)->getRuleId();
            if ($ruleId) {
                $rule = $this->saleRule->load($ruleId);
                if ($this->dataHelper->getEnable()) {
                    if ($rule->getCheckoutMessage()) {
                        $message = $rule->getCheckoutMessage();
                    } else {
                        $message = null;
                    }
                }
            }
        }
        $shippingMessageDetails = $this->shippingMessageDataHelper->getShippingDetails();
        $shippingMessage = [
            "message" => $shippingMessageDetails[0]['message'],
            "bar_color" => $shippingMessageDetails[0]['color'],
            "bar_width" => $shippingMessageDetails[0]['percentage']
        ];
        $this->messageData->setMessage($message);
        $this->messageData->setResult($response);
        $this->messageData->setShippingMessage($shippingMessage);
        return $this->messageData;
    }
}
