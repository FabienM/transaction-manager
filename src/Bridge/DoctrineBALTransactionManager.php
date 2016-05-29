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
     */
    public function __construct(Connection $connection, LoggerInterface $logger = null)
    {
        parent::__construct($logger);
        $this->connection = $connection;
    }
    protected function doCommit()
    {
        $this->connection->commit();
    }

    protected function doRollback()
    {
        $this->connection->rollBack();
    }

    protected function doStart()
    {
        $this->connection->beginTransaction();
    }
}
