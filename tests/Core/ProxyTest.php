<?php

namespace FabienM\TransactionManager\Core;

use FabienM\TransactionManager\TestTrait;
use Psr\Log\LoggerInterface;

/**
 * Class ProxyTest
 * @author Fabien Meurillon <fabien@meurillon.org>
 */
class ProxyTest extends \PHPUnit_Framework_TestCase
{
    use TestTrait;

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getTransactionManagerMock()
    {
        return $this->getMockBuilder('\FabienM\TransactionManager\Core\TransactionManagerInterface')->getMock();
    }

    public function testCall()
    {
        $transactionManager = $this->getTransactionManagerMock();
        $transactionManager->expects($this->once())->method('start');
        $transactionManager->expects($this->once())->method('commit');
        $transactionManager->expects($this->never())->method('rollback');
        $service = new DummyService();
        $proxy = new Proxy($service, ['dummy' => false], $transactionManager, true, $this->getLogger());

        $proxy->dummy($this->getLogger());
    }

    /**
     * @expectedException \Exception
     */
    public function testError()
    {
        $transactionManager = $this->getTransactionManagerMock();
        $transactionManager->expects($this->once())->method('start');
        $transactionManager->expects($this->never())->method('commit');
        $transactionManager->expects($this->once())->method('rollback');
        $service = new DummyService();
        $proxy = new Proxy($service, ['dummyException' => false], $transactionManager, true, $this->getLogger());

        $proxy->dummyException();
    }

    public function testNonTransactional()
    {
        $transactionManager = $this->getTransactionManagerMock();
        $transactionManager->expects($this->never())->method('start');
        $transactionManager->expects($this->never())->method('commit');
        $transactionManager->expects($this->never())->method('rollback');
        $service = new DummyService();
        $proxy = new Proxy($service, [], $transactionManager, true, $this->getLogger());

        $proxy->dummy($this->getLogger());
    }

    /**
     * @param DummyService $dummyService
     * @dataProvider proxyProvider
     */
    public function testProxyInheritance(DummyService $dummyService)
    {
        $dummyService->dummy($this->getLogger());
    }

    public function proxyProvider()
    {
        $transactionManager = $this->getTransactionManagerMock();
        return [[new Proxy($service, ['dummy' => false], $transactionManager, true, $this->getLogger())]];
    }
}
