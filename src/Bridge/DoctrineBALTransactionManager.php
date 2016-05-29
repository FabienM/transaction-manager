<?php

namespace FabienM\TransactionManager\Bridge;

use Doctrine\DBAL\Connection;
use FabienM\TransactionManager\Core\AbstractTransactionManager;
use Psr\Log\LoggerInterface;

class DoctrineBALTransactionManager extends AbstractTransactionManager
{
    /** @var Connection */
    private $connection;

    /**
     * DoctrineBALTransactionManager constructor.
     * @param Connection $connection
     * @param bool $nestWithSavepoints
     * @param LoggerInterface $logger
     */
    public function __construct(Connection $connection, $nestWithSavepoints, LoggerInterface $logger = null)
    {
        parent::__construct(false, $logger);
        $this->connection = $connection;
        $this->connection->setNestTransactionsWithSavepoints($nestWithSavepoints);
    }
    protected function doCommit()
    {
        $this->connection->commit();
    }

    /**
     * @param string $savepoint
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function doRollback($savepoint = null)
    {
        $this->connection->rollBack();
    }

    protected function doStart()
    {
        $this->connection->beginTransaction();
    }
}
