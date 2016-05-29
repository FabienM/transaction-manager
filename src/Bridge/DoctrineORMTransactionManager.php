<?php

namespace FabienM\TransactionManager\Bridge;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * This class is a Transaction Manager for Doctrine ORM.
 *
 * @author Fabien Meurillon <fabien@meurillon.org>
 */
class DoctrineORMTransactionManager extends DoctrineBALTransactionManager
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var bool */
    private $clearOnClose;

    /**
     * DoctrineTransactionManager constructor.
     * @param EntityManagerInterface $entityManager
     * @param bool $nestWithSavepoints
     * @param bool $clearOnClose true if entityManager should be cleared after transaction ends
     * @param LoggerInterface $logger
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        $nestWithSavepoints,
        $clearOnClose,
        LoggerInterface $logger = null
    ) {
        parent::__construct($entityManager->getConnection(), $nestWithSavepoints, $logger);
        $this->entityManager = $entityManager;
        $this->clearOnClose = $clearOnClose;
    }

    public function commit()
    {
        $this->entityManager->flush();
        $this->logger->debug("EntityManager flushed");
        parent::commit();
        if ($this->clearOnClose) {
            $this->clear();
        }
    }

    public function rollback()
    {
        parent::rollback();
        if ($this->clearOnClose) {
            $this->clear();
        }
    }

    private function clear()
    {
        $this->entityManager->clear();
        $this->logger->debug("EntityManager cleared");
    }
}
