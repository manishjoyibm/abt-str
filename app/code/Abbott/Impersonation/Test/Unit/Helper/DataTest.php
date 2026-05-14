<?php

namespace Abbott\Impersonation\Test\Unit\Helper;

use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\HTTP\Client\CurlFactory;

/**
 * Class DataTest
 */
class DataTest extends \PHPUnit\Framework\TestCase
{
    public $helper;
    /**
     * @var (\Abbott\Impersonation\Helper\Data & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $helperMock;
    /**
     * @var (\Magento\Framework\HTTP\Client\CurlFactory & \PHPUnit\Framework\MockObject\MockObject)
     */
    public $curlFactoryMock;
    /**
     * @var \Magento\Framework\HTTP\Client\Curl\
     */
    protected $curl;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface\
     */
    protected $scopeConfig;
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder\
     */
    protected $transportBuilder;
    /**
     * @var \Abbott\AwsLambda\Helper\Data\
     */
    protected $awslambdahelper;
    /**
     * @var \Abbott\AwsLambda\Logger\Log\
     */
    protected $log;
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface\
     */
    protected $customerRepository;
    /**
     * @var \Magento\Framework\Filesystem\DirectoryList\
     */
    protected $dirList;
    /**
     * @var \Magento\Framework\Filesystem\Driver\File\
     */
    protected $file;
    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->curl = $this->createMock(\Magento\Framework\HTTP\Client\Curl::class);

        $this->scopeConfig = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);

        $this->transportBuilder = $this->createMock(\Magento\Framework\Mail\Template\TransportBuilder::class);

        $this->awslambdahelper = $this->createMock(\Abbott\AwsLambda\Helper\Data::class, ['getAccessKey']);

        $this->log = $this->createMock(\Abbott\AwsLambda\Logger\Log::class);

        $this->customerRepository = $this->createMock(\Magento\Customer\Api\CustomerRepositoryInterface::class);

        $this->dirList = $this->createMock(\Magento\Framework\Filesystem\DirectoryList::class);

        $this->file = $this->createMock(\Magento\Framework\Filesystem\Driver\File::class);

        $this->helper = new \Abbott\Impersonation\Helper\Data($this->curl, $this->scopeConfig, $this->transportBuilder, $this->awslambdahelper, $this->log, $this->customerRepository, $this->dirList, $this->file);

        $this->helperMock = $this->getMockBuilder(\Abbott\Impersonation\Helper\Data::class)
        ->disableOriginalConstructor()->getMock();

        $this->curlFactoryMock = $this->createMock(CurlFactory::class);
    }
    
    /**
     * @return null
     */
    public function testGetCurlResponseTrue()
    {
        $returnval = null;
        $params = '';
        $url = 'http::test.com';

        $this->testGetAttributeValue();
        $this->awslambdahelper->method('getAccessKey')->willReturn('123456');
        //************ Curl Start */

        $curl = $this
        ->getMockBuilder(Curl::class)
        ->setMethods(['addHeader','setOption','post'])
        ->disableOriginalConstructor()
        ->getMock();
        
        $curl->expects($this->any())
        ->method('addHeader')
        ->will($this->returnValue($curl));
        
        $curl->expects($this->any())
        ->method('setOption')
        ->will($this->returnValue($curl));

        $curl->expects($this->any())
        ->method('post')->with($url, $params)
        ->will($this->returnValue($curl));

        //************ Curl End */

        $this->assertSame($returnval, $this->helper->getCurlResponse('url', 1));
    }

    /**
     * @return bool
     */
    public function testGetCurlResponseFalse()
    {
        $returnval = false;
        $this->testGetAttributeValue();
        $this->assertSame($returnval, $this->helper->getCurlResponse('url', 1));
    }

    /**
     * @return bool
     */
    public function testGetAttributeValue()
    {
        $returnval = '123456789';
        $customerData = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->getMockForAbstractClass();

        $this->customerRepository
            ->expects($this->any())
            ->method('getById')
            ->with(1)
            ->will($this->returnValue($customerData));
           
        $attributeMock = $this->getMockBuilder(\Magento\Framework\Api\AbstractExtensibleObject::class)
        ->setMethods(['getValue'])
        ->disableOriginalConstructor()
        ->getMockForAbstractClass();

        $customerData->method('getCustomAttribute')->with('gigya_uid')->willReturn($attributeMock);
       
        $attributeMock->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturn($returnval);
         
        $this->assertSame($returnval, $this->helper->getAttributeValue(1));
    }

    public function testnotGetAttributeValue()
    {
        $returnval = false;
        $customerData = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->getMockForAbstractClass();

        $this->customerRepository
            ->expects($this->any())
            ->method('getById')
            ->with(1)
            ->will($this->returnValue($customerData));

        $customerData->method('getCustomAttribute')->with('gigya_uid')->willReturn($returnval);

        $this->assertSame($returnval, $this->helper->getAttributeValue(1));
    }
}