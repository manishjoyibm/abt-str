<?php

namespace Abbott\GigyaIM\Test\Unit\Model;

use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Abbott\GigyaIM\Api\Data\SsmCartInterface;
use Abbott\GigyaIM\Api\Data\SsmCartInterfaceFactory;
use Abbott\GigyaIM\Api\Data\SsmCartSearchResultsInterface;
use Abbott\GigyaIM\Api\Data\SsmCartSearchResultsInterfaceFactory;

class SsmCartRepositoryTest extends \PHPUnit\Framework\TestCase
{
    public $ssmfactory;
    public $ssmCart;
    public $ssmCartColl;
    public $collectionFactoryMock;
    /**
     * @var (\Abbott\GigyaIM\Api\Data\SsmCartSearchResultsInterfaceFactory & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $ssmSearchFactory;
    public $searchResultsFactoryMock;
    public $resource;
    /**
     * @var (\Abbott\GigyaIM\Api\Data\SsmCartInterface & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $ssmInterface;
    public $repo;
    public function setUp(): void
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $className = \Abbott\GigyaIM\Model\SsmCartRepository::class;
        //$arguments = $objectManagerHelper->getConstructArguments($className);
        $this->ssmfactory =  $this->getMockBuilder(\Abbott\GigyaIM\Model\SsmCartFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->ssmCart =  $this->getMockBuilder(\Abbott\GigyaIM\Model\SsmCart::class)
            ->disableOriginalConstructor()->getMock();
        $this->ssmCartColl =  $this->getMockBuilder(\Abbott\GigyaIM\Model\ResourceModel\SsmCart\CollectionFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->collectionFactoryMock = $this->createMock(\Abbott\GigyaIM\Model\ResourceModel\SsmCart\CollectionFactory::class);
        $this->ssmSearchFactory =  $this->getMockBuilder(\Abbott\GigyaIM\Api\Data\SsmCartSearchResultsInterfaceFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->searchResultsFactoryMock = $this->createMock(SsmCartSearchResultsInterfaceFactory::class);
        $this->resource =  $this->getMockBuilder(\Abbott\GigyaIM\Model\ResourceModel\SsmCart::class)
            ->disableOriginalConstructor()->getMock();
        $this->ssmfactory->expects($this->any())
            ->method("create")
            ->willReturn($this->ssmCart);

        $this->ssmInterface = $this->getMockBuilder(\Abbott\GigyaIM\Api\Data\SsmCartInterface::class)
            ->disableOriginalConstructor()->getMock();

        $this->ssmCart->expects($this->any())
            ->method("getResource")
            ->willReturn($this->resource);
        
        $arguments['factory'] = $this->ssmfactory;
        $arguments['ssmCartCollFactory'] = $this->collectionFactoryMock;
        $arguments['ssmSearchResultFactory'] = $this->searchResultsFactoryMock;
        $arguments['resource'] = $this->resource;
        $this->repo = $objectManagerHelper->getObject($className, $arguments);
    }

    public function testByGetId()
    {
        $id = 1;

        $this->resource
            ->method('load')
            ->with($this->ssmCart, $id)
            ->willReturn($this->ssmCart);

        $this->ssmCart->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($id));

        $this->resource->expects($this->any())
            ->method('load')
            ->with($this->ssmCart, $id)
            ->willReturn($this->ssmCart);
        
        $cart = $this->repo->getById($id);
        $this->assertEquals($this->ssmCart, $cart);
    }

    public function ttestByGetIdExp()
    {
        $id = 2;

        $this->resource
            ->method('load')
            ->with($this->ssmCart, $id)
            ->willReturn($this->ssmCart);

        $this->ssmCart->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(false));

        $this->resource->expects($this->any())
            ->method('load')
            ->with($this->ssmCart, $id)
            ->willReturn($this->ssmCart);

        $cart = $this->repo->getById($id);
        //$exp = static::throwException(new \Magento\Framework\Exception\NoSuchEntityException(__("Unable to find ssm shopping cart with ID").$id));
        $this->assertEquals($exp, $cart);
    }

    public function testSave()
    {
        $this->resource->expects($this->once())
            ->method('save')
            ->with($this->ssmCart)
            ->willReturnSelf();
        $this->repo->save($this->ssmCart);
    }

    public function testDelete()
    {
        $this->ssmCart->expects($this->any())
        ->method('delete')
        ->willReturnSelf();

        $this->resource->expects($this->once())
            ->method('delete')
            ->with($this->ssmCart)
            ->willReturnSelf();
        
        $this->repo->delete($this->ssmCart);
    }

    public function testDeleteById()
    {
        $id = 1;

        $this->resource
            ->method('load')
            ->with($this->ssmCart, $id)
            ->willReturn($this->ssmCart);

        $this->ssmCart->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($id));
            
        $this->ssmCart->expects($this->any())
            ->method('delete')
            ->willReturnSelf();

        $this->repo->deleteById($id);
    }

    public function testGetByEmail()
    {
        $email = "abc@abc.com";
        $websiteId = 1;

        $this->resource
            ->method('getByEmailAndWebsite')
            ->with($email, $websiteId)
            ->willReturn($this->ssmCart);

        $this->repo->getByEmail($email, $websiteId);
    }

    /**
     * Test getList method
     */
    public function testGetList()
    {
        $email = "abc@abc.com";
        $websiteId = 1;

        $filterName = 'email';
        $filterValue = $email;
        $collectionSize = 5;
        $scCurrPage = 1;
        $scPageSize = 3;

        $searchCriteriaMock = $this->createMock(SearchCriteriaInterface::class);
        $searchResultsMock = $this->createMock(SsmCartSearchResultsInterface::class);
        $searchResultsMock->expects($this->atLeastOnce())
            ->method('setSearchCriteria')
            ->with($searchCriteriaMock)
            ->willReturnSelf();
        $this->searchResultsFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($searchResultsMock);

        /*$collection =  $this->getMockBuilder(\Abbott\GigyaIM\Model\ResourceModel\SsmCart\Collection::class)
            ->disableOriginalConstructor()->getMock();
        $collection->method('load')->willReturn([$this->ssmCart]);
        $this->ssmCartColl->method('create')
            ->willReturn($collection); */

        $collectionMock = $this->createPartialMock(
            \Abbott\GigyaIM\Model\ResourceModel\SsmCart\Collection::class,
            ['addFieldToFilter', 'getSize', 'addOrder', 'setCurPage', 'setPageSize', 'getIterator', 'load']
        );
        $this->collectionFactoryMock
            ->method('create')
            ->willReturn($collectionMock);

        $filterGroupMock = $this->createPartialMock(FilterGroup::class, ['getFilters']);
        $filterMock = $this->createPartialMock(
            Filter::class,
            ['getConditionType', 'getField', 'getValue']
        );

        $searchCriteriaMock->expects($this->once())
            ->method('getFilterGroups')
            ->willReturn([$filterGroupMock]);
        $filterGroupMock->expects($this->once())
            ->method('getFilters')
            ->willReturn([$filterMock]);
        $filterMock->expects($this->once())
            ->method('getConditionType')
            ->willReturn(false);
        $filterMock->expects($this->atLeastOnce())
            ->method('getField')
            ->willReturn($filterName);
        $filterMock->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturn($filterValue);
        $collectionMock->expects($this->any())
            ->method('addFieldToFilter')
            ->with($filterName, ['eq' => $filterValue]);
        $collectionMock
            ->expects($this->once())
            ->method('getSize')
            ->willReturn($collectionSize);
        $searchResultsMock->expects($this->once())
            ->method('setTotalCount')
            ->with($collectionSize);

        $searchCriteriaMock->expects($this->once())
            ->method('getCurrentPage')
            ->willReturn($scCurrPage);
        $collectionMock->expects($this->once())
            ->method('setCurPage')
            ->with($scCurrPage)
            ->willReturnSelf();
        $searchCriteriaMock->expects($this->once())
            ->method('getPageSize')
            ->willReturn($scPageSize);
        $collectionMock->expects($this->once())
            ->method('setPageSize')
            ->with($scPageSize)
            ->willReturn($collectionMock);

        $sortOrderMock = $this->createPartialMock(SortOrder::class, ['getField', 'getDirection']);
        $searchCriteriaMock->expects($this->atLeastOnce())
            ->method('getSortOrders')
            ->willReturn([$sortOrderMock]);
        $sortOrderMock->expects($this->any())
            ->method('getField')
            ->willReturn($filterName);
        $collectionMock->expects($this->once())
            ->method('addOrder')
            ->with($filterName, SortOrder::SORT_ASC);
        $sortOrderMock->expects($this->once())
            ->method('getDirection')
            ->willReturn(SortOrder::SORT_ASC);
        $collectionMock->expects($this->any())
            ->method('load')
            ->willReturn([$this->ssmCart]);

        /*$ssmCartModelMock = $this->createPartialMock(SsmCart::class, ['getData']);
        $ssmCartModelMock->expects($this->once())
            ->method('getData')
            ->willReturn([
                'id' => 1,
            ]);
        $collectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$ssmCartModelMock]));


        $ssmCartMock = $this->getMockForAbstractClass(SsmCartInterface::class);
        $this->ssmCartFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($ssmCartMock);
        $searchResultsMock->expects($this->once())
            ->method('setItems')
            ->with([$ssmCartMock])
            ->willReturnSelf();
        */

        $this->repo->getList($searchCriteriaMock);

        //$this->assertSame($searchResultsMock, $this->profileRepository->getList($searchCriteriaMock));
    }

    public function ttestGetList()
    {
        $email = "abc@abc.com";
        $websiteId = 1;

        $searchCriteria = $this->getMockForAbstractClass(
            \Magento\Framework\Api\SearchCriteriaInterface::class,
            [],
            '',
            false
        );
        $collection =  $this->getMockBuilder(\Abbott\GigyaIM\Model\ResourceModel\SsmCart\Collection::class)
            ->disableOriginalConstructor()->getMock();
        $collection->method('load')->willReturn([$this->ssmCart]);
        $this->ssmCartColl->method('create')
            ->willReturn($collection);

        $this->repo->getList($searchCriteriaMock);
    }
}
