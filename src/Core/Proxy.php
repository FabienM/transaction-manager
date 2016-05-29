<?php

namespace FabienM\TransactionManager\Core;

use Psr\Log\LoggerInterface;

/**
 * Proxy class to manage transactional methods in a nested service.
 *
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

    /** @var LoggerInterface */
    private $logger = null;

    /**
     * Proxy constructor.
     * @param object $service
     * @param bool $transactionalMethods
     * @param TransactionManagerInterface $transactionManager
     * @param bool $rollbackOnError
     * @param LoggerInterface $logger
     */
    public function __construct(
        $service,
        $transactionalMethods,
        TransactionManagerInterface $transactionManager,
        $rollbackOnError,
        LoggerInterface $logger = null
    ) {
        $this->service = $service;
        $this->transactionalMethods = $transactionalMethods;
        $this->transactionManager = $transactionManager;
        $this->rollbackOnError = $rollbackOnError;
        $this->logger = $logger;
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
            $this->handleException($e);
        }
        $this->transactionManager->commit();
    }

    /**
     * @param \Exception $exception
     * @throws \Exception
     */
    protected function handleException(\Exception $exception)
    {
        if ($this->rollbackOnError) {
            if ($this->logger !== null) {
                $this->logger->info(
                    "Caught {exception} and rollbackOnError is enabled. Rolling back.",
                    array('exception', get_class($exception))
                );
                $this->transactionManager->rollback();
            }
        }
        throw $exception;
    }
}
