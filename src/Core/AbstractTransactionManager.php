<?php

namespace FabienM\TransactionManager\Core;

use Psr\Log\LoggerInterface;

abstract class AbstractTransactionManager implements TransactionManagerInterface
{
    /** @var LoggerInterface */
    protected $logger;

    /** @var bool */
    protected $readOnly;

    /**
     * AbstractTransactionManager constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
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
        $this->doRollback();
    }

    public function start($readOnly)
    {
        if ($this->logger !== null) {
            $this->logger->debug("Attempt to commit a readonly transaction. Rollbacking.");
        }
        $this->readOnly = $readOnly;
        $this->doStart();
    }

    abstract protected function doCommit();

    abstract protected function doRollback();

    abstract protected function doStart();
}
