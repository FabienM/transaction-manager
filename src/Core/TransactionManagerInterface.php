<?php

namespace FabienM\TransactionManager\Core;

use FabienM\TransactionManager\Core\Exception\UnsupportedMethodException;

interface TransactionManagerInterface
{
    /**
     * Commit the current transaction.
     * @throws UnsupportedMethodException
     */
    public function commit();

    /**
     * Rollback the current transaction.
     * @throws UnsupportedMethodException
     */
    public function rollback();

    /**
     * Start a new transaction.
     * @param bool $readOnly mark this transaction as read only
     * @throws UnsupportedMethodException
     */
    public function start($readOnly);
}
