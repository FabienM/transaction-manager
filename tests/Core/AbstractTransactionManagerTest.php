<?php

namespace FabienM\TransactionManager\Core;

use FabienM\TransactionManager\TestTrait;

/**
 * Class AbstractTransactionManagerTest
 * @author Fabien Meurillon <fabien@meurillon.org>
 */
class AbstractTransactionManagerTest extends \PHPUnit_Framework_TestCase
{
    use TestTrait;

    protected function getTransactionManagerMockBuilder()
    {
        return $this->getMockBuilder('\FabienM\TransactionManager\Core\AbstractTransactionManager')
            ->setConstructorArgs([true, $this->getLogger()]);
    }

    /**
     * @expectedException \FabienM\TransactionManager\Core\Exception\TransactionManagerException
     */
    public function testCommitWithoutTransaction()
    {
        $transactionManager = $this->getTransactionManagerMockBuilder()->getMockForAbstractClass();
        $transactionManager->commit();
    }

    /**
     * @expectedException \FabienM\TransactionManager\Core\Exception\TransactionManagerException
     */
    public function testRollbackWithoutTransaction()
    {
        $transactionManager = $this->getTransactionManagerMockBuilder()->getMockForAbstractClass();
        $transactionManager->rollback();
    }

    public function testSimpleCommitTransaction()
    {
        $transactionManager = $this->getTransactionManagerMockBuilder()->getMockForAbstractClass();
        $transactionManager->start(false);
        $transactionManager->commit();
    }

    public function testSimpleRollbackTransaction()
    {
        $transactionManager = $this->getTransactionManagerMockBuilder()->getMockForAbstractClass();
        $transactionManager->start(false);
        $transactionManager->rollback();
    }

    public function testComplexTransactionWithSavepoints()
    {
        $transactionManager = $this->getTransactionManagerMockBuilder()
            ->setMethods(['doSavepoint', 'doCommit', 'doRollback', 'doStart'])
            ->getMockForAbstractClass();

        $transactionManager->expects($this->exactly(3))->method('doSavepoint');
        $transactionManager->expects($this->once())->method('doCommit');
        $transactionManager->expects($this->exactly(2))->method('doRollback')->withConsecutive(
            $this->anything(),
            $this->anything()
        );

        $this->runComplexTransaction($transactionManager);
    }

    public function testComplexTransactionWithoutSavepoints()
    {
        $transactionManager = $this->getTransactionManagerMockBuilder()
            ->setConstructorArgs([false, $this->getLogger()])
            ->getMockForAbstractClass();

        $transactionManager->expects($this->never())->method('doSavepoint');
        $transactionManager->expects($this->once())->method('doCommit');
        $transactionManager->expects($this->never())->method('doRollback');

        $this->runComplexTransaction($transactionManager);
    }

    private function runComplexTransaction(TransactionManagerInterface $transactionManager)
    {
        $transactionManager->start(false);
        $transactionManager->start(true);
        $transactionManager->start(false);
        $transactionManager->start(false);
        $transactionManager->commit();
        $transactionManager->rollback();
        $transactionManager->commit();
        $transactionManager->commit();
    }

    /**
     * @expectedException \FabienM\TransactionManager\Core\Exception\UnsupportedMethodException
     */
    public function testNotSupportedSavepoint()
    {
        $transactionManager = $this->getTransactionManagerMockBuilder()
            ->setConstructorArgs([true, $this->getLogger()])
            ->getMockForAbstractClass();
        $transactionManager->start(false);
        $transactionManager->start(false);
    }
}
