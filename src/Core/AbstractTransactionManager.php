<?php

namespace FabienM\TransactionManager\Core;

use FabienM\TransactionManager\Core\Exception\TransactionManagerException;
use FabienM\TransactionManager\Core\Exception\UnsupportedMethodException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class AbstractTransactionManager
 *
 * @author Fabien Meurillon <fabien@meurillon.org>
 */
abstract class AbstractTransactionManager implements TransactionManagerInterface
{
    /** @var bool */
    protected $nestWithSavepoints;

    /** @var LoggerInterface */
    protected $logger;

    /** @var bool[] */
    protected $transactionDepths;

    /**
     * AbstractTransactionManager constructor.
     * @param LoggerInterface $logger
     * @param bool $nestWithSavepoints
     */
    public function __construct($nestWithSavepoints, LoggerInterface $logger = null)
    {
        $this->logger = ($logger !== null ? $logger : new NullLogger());
        $this->nestWithSavepoints = $nestWithSavepoints;
        $this->transactionDepths = [];
    }

    public function commit()
    {
        $this->checkTransaction();
        $readOnly = array_pop($this->transactionDepths);
        if ($readOnly) {
            $this->logger->debug("Attempt to commit a readonly transaction. Rolling back.");
            $this->transactionDepths[] = $readOnly;
            return $this->rollback();
        }
        if (count($this->transactionDepths) > 0) {
            return;
        }
        $this->doCommit();
        $this->logger->debug("Transaction committed");
    }

    public function rollback()
    {
        $this->checkTransaction();
        array_pop($this->transactionDepths);
        if (count($this->transactionDepths) === 0) {
            $this->doRollback();
            $this->logger->debug("Transaction rollbacked");
            return;
        }
        if (!$this->nestWithSavepoints) {
            $this->logger->debug("Rollback aborted because it's a nested transaction");
            return;
        }
        $savepoint = $this->getCurrentSavepoint();
        $this->doRollback($savepoint);
        $this->logger->debug("Transaction rollbacked to savepoint {savepoint}", ['savepoint' => $savepoint]);
    }

    public function start($readOnly)
    {
        if ($this->nestWithSavepoints && count($this->transactionDepths) > 0) {
            $this->doSavepoint($this->getCurrentSavepoint());
            $this->logger->debug("Savepoint {savepoint} created", ['savepoint' => $this->getCurrentSavepoint()]);
        }
        if (count($this->transactionDepths) === 0) {
            $this->doStart();
            $this->logger->debug("New transaction started");
        }
        $this->transactionDepths[] = $readOnly;
        return;
    }

    abstract protected function doCommit();

    /**
     * Rollback to the given savepoint or the whole transaction if null given
     *
     * @param string $savepoint the targeted savepoint
     */
    abstract protected function doRollback($savepoint = null);

    abstract protected function doStart();

    /**
     * Create a savepoint with the given name
     *
     * @param string $savepoint Savepoint name
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function doSavepoint($savepoint)
    {
        throw new UnsupportedMethodException("Savepoints are not supported by this transaction manager");
    }

    /**
     * @return string
     */
    private function getCurrentSavepoint()
    {
        return sprintf("TM_SAVEPOINT_%d", count($this->transactionDepths));
    }

    private function checkTransaction()
    {
        if (count($this->transactionDepths) === 0) {
            throw new TransactionManagerException("No transaction started, nothing to rollback or commit");
        }
    }
}
