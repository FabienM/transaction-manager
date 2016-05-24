<?php

namespace FabienM\TransactionManager\Core;

/**
 * Proxy class to manage transactional methods
 * @author Fabien Meurillon <fabien@meurillon.org>
 */
class Proxy
{
    /**
     * @var object
     */
    private $service;

    /**
     * @var bool[string]
     */
    private $transactionalMethods;

    /**
     * @var TransactionManagerInterface
     */
    private $transactionManager;

    /** @var bool */
    private $rollbackOnError;

    /**
     * Proxy constructor.
     * @param object $service
     * @param bool $transactionalMethods
     * @param TransactionManagerInterface $transactionManager
     * @param bool $rollbackOnError
     */
    public function __construct(
        $service,
        $transactionalMethods,
        TransactionManagerInterface $transactionManager,
        $rollbackOnError
    ) {
        $this->service = $service;
        $this->transactionalMethods = $transactionalMethods;
        $this->transactionManager = $transactionManager;
        $this->rollbackOnError = $rollbackOnError;
    }

    public function __call($name, $arguments)
    {
        if (!array_key_exists($name, $this->transactionalMethods)) {
            return call_user_func_array(array($this->service, $name), $arguments);
        }
        $this->transactionManager->start($this->transactionalMethods[$name]);
        try {
            return call_user_func_array(array($this->service, $name), $arguments);
        } catch (\Exception $e) {
            if ($this->rollbackOnError) {
                $this->transactionManager->rollback();
            }
        }
        $this->transactionManager->commit();
    }
}
