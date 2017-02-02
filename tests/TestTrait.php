<?php

namespace FabienM\TransactionManager;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;
use Psr\Log\LoggerInterface;

trait TestTrait
{
    /** @var LoggerInterface */
    private $logger;

    public function getLogger()
    {
        if ($this->logger !== null) {
            return $this->logger;
        }

        $this->logger = new Logger("test", [new StreamHandler("php://stderr")], [new PsrLogMessageProcessor()]);
        return $this->logger;
    }
}
