<?php

namespace FabienM\TransactionManager\Core;

use FabienM\TransactionManager\Core\Exception\UnsupportedMethodException;

interface TransactionManagerInterface
{
    /**
     * Commit the current transaction.
     *
     * If the transaction has been started with `readOnly` turned on, a rollback MUST be performed
     *
     * @throws UnsupportedMethodException
     */
    public function commit();

    /**
     * Rollback the current transaction.
     *
     * @throws UnsupportedMethodException
     */
    public function rollback();

    /**
     * Start a new transaction.
     *
     * If a transaction has already been started and the underlying DBMS supports savepoint, a savepoint SHOULD be
     * created instead.
     *
     * @param bool $readOnly mark this transaction as read only
     * @throws UnsupportedMethodException
     */
    public function start($readOnly);
}
