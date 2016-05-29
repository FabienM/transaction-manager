<?php

namespace FabienM\TransactionManager\Bridge;

use FabienM\TransactionManager\Core\AbstractTransactionManager;
use Psr\Log\LoggerInterface;

class PDOTransactionManager extends AbstractTransactionManager
{
    /** @var \PDO */
    private $connection;

    /**
     * DoctrineBALTransactionManager constructor.
     * @param \PDO $connection
     * @param LoggerInterface $logger
     */
    public function __construct(\PDO $connection, LoggerInterface $logger = null)
    {
        parent::__construct($logger);
        $this->connection = $connection;
    }

    protected function doCommit()
    {
        $this->connection->commit();
    }

    protected function doRollback($savepoint = null)
    {
        if ($savepoint !== null) {
            $this->connection->exec(sprintf("ROLLBACK TO %s", $savepoint));
        }
        $this->connection->rollBack();
    }

    protected function doStart()
    {
        $this->connection->beginTransaction();
    }

    protected function doSavepoint($savepoint)
    {
        $this->connection->exec(sprintf("SAVEPOINT %s", $savepoint));
    }
}
