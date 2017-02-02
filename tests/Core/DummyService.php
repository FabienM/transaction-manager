<?php

namespace FabienM\TransactionManager\Core;

use Psr\Log\LoggerInterface;

class DummyService
{
    public function dummy(LoggerInterface $logger)
    {
        $logger->debug("Hello World");
        return;
    }

    public function dummyException() {
        throw new \Exception("dummy");
    }
}
