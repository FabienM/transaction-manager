<?php

namespace FabienM\TransactionManager\Core;

use FabienM\TransactionManager\Core\Exception\UnsupportedMethodException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

abstract class AbstractTransactionManager implements TransactionManagerInterface
{
    /** @var bool */
    protected $nestWithSavepoints;

    /** @var LoggerInterface */
    protected $logger;

    /** @var bool */
    protected $readOnly;

    /** @var int */
    protected $transactionDepth;

    /**
     * AbstractTransactionManager constructor.
     * @param LoggerInterface $logger
     * @param bool $nestWithSavepoints
     */
    public function __construct($nestWithSavepoints, LoggerInterface $logger = null)
    {
        $this->logger = ($logger !== null ? $logger : new NullLogger());
        $this->nestWithSavepoints = $nestWithSavepoints;
    }

    public function commit()
    {
        if ($this->readOnly) {
            if ($this->logger !== null) {
                $this->logger->debug("Attempt to commit a readonly transaction. Rollbacking.");
            }
            return $this->rollback();
        }
        $this->doCommit();
    }

    public function rollback()
    {
        if (!$this->nestWithSavepoints || $this->transactionDepth === 0) {
            $this->doRollback();
            return;
        }
        $this->doRollback($this->getCurrentSavepoint());
        $this->transactionDepth--;
    }

    public function start($readOnly)
    {
        $this->readOnly = $readOnly;
        if (!$this->nestWithSavepoints) {
            $this->doStart();
            return;
        }
        if ($this->nestWithSavepoints && ++$this->transactionDepth > 0) {
            $this->doSavepoint($this->getCurrentSavepoint());
            return;
        }
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
        return sprintf("TM_SAVEPOINT_%d", $this->transactionDepth);
    }
}
