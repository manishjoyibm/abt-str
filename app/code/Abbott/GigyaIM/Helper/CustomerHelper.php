<?php

namespace Abbott\GigyaIM\Helper;

use Exception;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Abbott\GigyaIM\Api\SsmCartRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\QuoteGraphQl\Model\Cart\CreateEmptyCartForGuest;
use Abbott\GigyaIM\Api\Data\SsmCartInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Quote\Model\Quote;

class CustomerHelper extends AbstractHelper
{

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var FilterGroupBuilder
     */
    protected $filterGroupBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepo;

    /**
     * @var SsmCartRepositoryInterface
     */
    protected $ssmCartRepo;

    /**
     * @var GetCartForUser
     */
    protected $getCartForUser;

    /**
     * @var CreateEmptyCartForGuest
     */
    protected $createEmptyCartForGuest;

    /**
     * @var StoreManager
     */
    protected $storeManager;

    /**
     * @var SsmCartInterface
     */
    protected $ssmCartInterface;

    /**
     * @var CartManagementInterface
     */
    protected $cartManagement;

    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    protected $maskedQuoteIdToQuoteId;

    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var SortOrderBuilder
     */
    protected $sortOrderBuilder;

    /**
     * @var Quote
     */
    protected $quote;

    /**
     * @param Context $context
     * @param FilterBuilder $filter
     * @param FilterGroupBuilder $filterGroup
     * @param SearchCriteriaBuilder $search
     * @param CustomerRepositoryInterface $customerInterface
     * @param SsmCartRepositoryInterface $ssmInterface
     * @param GetCartForUser $getCartForUser
     * @param CreateEmptyCartForGuest $createEmptyCartForGuest
     * @param StoreManagerInterface $storeManager
     * @param SsmCartInterface $ssmCartInterface
     * @param CartManagementInterface $cartManagement
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     * @param CartRepositoryInterface $cartRepository
     * @param SortOrderBuilder $sortOrderBuilder
     * @param Quote $quote
     */
    public function __construct(
        Context $context,
        FilterBuilder $filter,
        FilterGroupBuilder $filterGroup,
        SearchCriteriaBuilder $search,
        CustomerRepositoryInterface $customerInterface,
        SsmCartRepositoryInterface $ssmInterface,
        GetCartForUser $getCartForUser,
        CreateEmptyCartForGuest $createEmptyCartForGuest,
        StoreManagerInterface $storeManager,
        SsmCartInterface $ssmCartInterface,
        CartManagementInterface $cartManagement,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        CartRepositoryInterface $cartRepository,
        SortOrderBuilder $sortOrderBuilder,
        Quote $quote
    ) {
        parent::__construct($context);
        $this->filterBuilder = $filter;
        $this->filterGroupBuilder = $filterGroup;
        $this->searchCriteriaBuilder = $search;
        $this->customerRepo = $customerInterface;
        $this->ssmCartRepo = $ssmInterface;
        $this->getCartForUser = $getCartForUser;
        $this->createEmptyCartForGuest = $createEmptyCartForGuest;
        $this->storeManager = $storeManager;
        $this->ssmCartInterface = $ssmCartInterface;
        $this->cartManagement = $cartManagement;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->cartRepository = $cartRepository;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->quote = $quote;
    }

    /**
     * Find Gigya Customer
     *
     * @param string $uid
     * @param string $email
     * @param integer $websiteId
     * @return void
     */
    public function findGigyaCustomer($uid, $email, $websiteId = 1)
    {
        $customer = false;
        try {
            $customer = $this->customerRepo->get($email, $websiteId);
            if ($customer) {
                $gigyaId = $customer->getCustomAttribute('gigya_uid') ?
                    $customer->getCustomAttribute('gigya_uid')->getValue()
                    : null;
                if (!empty($gigyaId) && $gigyaId != $uid) {
                    $customer = false;
                }
            }
        } catch (NoSuchEntityException $exp) {
            $this->_logger->info($exp);
            $customer = false;
        } catch (LocalizedException $exp) {
            $this->_logger->critical($exp);
            $customer = false;
        }
        return $customer;
    }

    /**
     * @param $cognitoId
     * @param $email
     * @param int $websiteId
     * @return false|CustomerInterface
     */
    public function findCognitoCustomer($cognitoId, $email, int $websiteId = 1): false|CustomerInterface
    {
        $customer = false;
        try {
            $customer = $this->customerRepo->get($email, $websiteId);
            if ($customer) {
                $magentoCognitoId = $customer->getCustomAttribute('cognito_id') ?
                    $customer->getCustomAttribute('cognito_id')->getValue()
                    : null;
                if (!empty($magentoCognitoId) && $magentoCognitoId != $cognitoId) {
                    $customer = false;
                }
            }
        } catch (NoSuchEntityException $exp) {
            $this->_logger->info($exp);
            $customer = false;
        } catch (LocalizedException $exp) {
            $this->_logger->critical($exp);
            $customer = false;
        }
        return $customer;
    }

    /**
     * Delete SsmCart
     *
     * @param $email
     * @param $websiteId
     * @return void
     * @throws Exception
     */
    public function deleteSsmCart($email, $websiteId): void
    {
        $ssmCart = $this->fetchSsmCart($email, $websiteId);
        foreach ($ssmCart as $ssm) {
            $ssm->delete();
        }
    }

    /**
     * SetCart function
     *
     * @param $email
     * @param $websiteId
     * @param $guestCart
     * @return mixed|string
     */
    public function setCart($email, $websiteId, $guestCart = null): mixed
    {
        try {
            $maskedQuoteId = "";
            $ssmCart = $this->fetchSsmCart($email, $websiteId);
            $cart = false;
            foreach ($ssmCart as $ssm) {
                $cart = $ssm;
            }
            if ($cart) {
                $maskedQuoteId = $this->getCart($cart->getMaskedCartId());
                if ($maskedQuoteId != $cart->getMaskedCartId()) {
                    $cart->setMaskedCartId($maskedQuoteId)->save();
                }
            } elseif ($guestCart) {
                $maskedQuoteId = $this->getCart($guestCart);
                $cartData = $this->ssmCartInterface;
                $cartData->setEmail($email)->setWebsiteId($websiteId)->setMaskedCartId($maskedQuoteId);
                $this->ssmCartRepo->save($cartData);
            } else {
                $maskedQuoteId = $this->getCart("");
                $cartData = $this->ssmCartInterface;
                $cartData->setEmail($email)->setWebsiteId($websiteId)->setMaskedCartId($maskedQuoteId);
                $this->ssmCartRepo->save($cartData);
            }

            // Check the mutation for merge carts
            if ($guestCart && $guestCart != $maskedQuoteId) {
                try {
                    $storeId = $this->storeManager->getStore()->getId();
                    $newCart = $this->getCartForUser->execute($maskedQuoteId, 0, $storeId);
                    $guestCart = $this->getCartForUser->execute($guestCart, 0, $storeId);
                    $newCart->merge($guestCart);
                    $guestCart->setIsActive(false);
                    $this->cartRepository->save($newCart);
                    $this->cartRepository->save($guestCart);
                } catch (GraphQlNoSuchEntityException $e) {
                    $this->_logger->critical($e);
                } catch (Exception $e) {
                    $this->_logger->critical($e);
                }
            }
        } catch (Exception $e) {
            $this->_logger->critical($e);
        }
        return $maskedQuoteId;
    }

    /**
     * GetCart function
     *
     * @param $maskedQuoteId
     * @return mixed|string
     * @throws NoSuchEntityException
     * @throws GraphQlAuthorizationException
     * @throws GraphQlInputException
     */
    protected function getCart($maskedQuoteId): mixed
    {
        try {
            $this->getCartForUser->execute($maskedQuoteId, 0, $this->storeManager->getStore()->getId());
        } catch (GraphQlNoSuchEntityException $e) {
            $maskedQuoteId = "";
        }
        if (empty($maskedQuoteId)) {
            $maskedQuoteId = $this->createEmptyCartForGuest->execute(null);
        }
        return $maskedQuoteId;
    }

    /**
     * Fetch SsmCart
     *
     * @param $email
     * @param $websiteId
     * @return SsmCartInterface[]
     * @throws LocalizedException
     */
    protected function fetchSsmCart($email, $websiteId): array
    {
        $filterGroups = [];
        $ssmEmailFilter = $this->filterBuilder
            ->setField(SsmCartInterface::EMAIL)
            ->setConditionType('eq')
            ->setValue($email)
            ->create();
        $filterGroups[] = $this->filterGroupBuilder->addFilter($ssmEmailFilter)->create();

        $ssmWebsiteFilter = $this->filterBuilder
            ->setField(SsmCartInterface::WEBSITE_ID)
            ->setConditionType('eq')
            ->setValue($websiteId)
            ->create();
        $filterGroups[] = $this->filterGroupBuilder->addFilter($ssmWebsiteFilter)->create();

        $sortOrder = $this->sortOrderBuilder->setField(SsmCartInterface::EMAIL)->setDirection('DESC')->create();

        $searchSsmCartCriteria = $this->searchCriteriaBuilder->create()
            ->setSortOrders([$sortOrder])->setFilterGroups($filterGroups);
        return $this->ssmCartRepo->getList($searchSsmCartCriteria)->getItems();
    }
}
