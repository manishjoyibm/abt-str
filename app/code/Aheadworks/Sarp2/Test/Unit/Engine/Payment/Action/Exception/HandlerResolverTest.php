<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Sarp2\Test\Unit\Engine\Payment\Action\Exception;

use Aheadworks\Sarp2\Engine\Payment\Action\Exception\HandlerInterface;
use Aheadworks\Sarp2\Engine\Payment\Action\Exception\HandlerResolver;
use Aheadworks\Sarp2\Engine\Payment\Action\Exception\Resolver\ResolveStrategyInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp2\Engine\Payment\Action\Exception\HandlerResolver
 */
class HandlerResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var HandlerResolver
     */
    private $resolver;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->resolver = $objectManager->getObject(HandlerResolver::class);
    }

    /**
     * @param HandlerInterface|\PHPUnit_Framework_MockObject_MockObject|null $expectedResult
     * @dataProvider getHandlerDataProvider
     */
    public function testGetHandler($expectedResult)
    {
        $paymentMethod = 'braintree';
        $exception = new \Exception('Gateway error.');

        $strategyMock = $this->createMock(ResolveStrategyInterface::class);

        $strategyMock->expects($this->once())
            ->method('resolve')
            ->with($exception)
            ->willReturn($expectedResult);

        $class = new \ReflectionClass($this->resolver);

        $strategyPoolProperty = $class->getProperty('strategyPool');
        $strategyPoolProperty->setAccessible(true);
        $strategyPoolProperty->setValue($this->resolver, [$paymentMethod => $strategyMock]);

        $this->assertEquals(
            $expectedResult,
            $this->resolver->getHandler($exception, $paymentMethod)
        );
    }

    public function testGetHandlerNonHandledPaymentMethod()
    {
        $class = new \ReflectionClass($this->resolver);

        $strategyPoolProperty = $class->getProperty('strategyPool');
        $strategyPoolProperty->setAccessible(true);
        $strategyPoolProperty->setValue(
            $this->resolver,
            ['braintree' => $this->createMock(ResolveStrategyInterface::class)]
        );

        $this->assertNull($this->resolver->getHandler(new \Exception('Gateway error.'), 'paypal'));
    }

    /**
     * @return array
     */
    public function getHandlerDataProvider()
    {
        return [
            [$this->createMock(HandlerInterface::class)],
            [null]
        ];
    }
}
