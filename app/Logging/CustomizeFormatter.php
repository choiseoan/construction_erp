<?php

namespace App\Logging;

use Monolog\Formatter\LineFormatter;

class CustomizeFormatter
{
    /**
     * Customize the given logger instance.
     *
     * @param  \Illuminate\Log\Logger  $logger
     * @return void
     */
    public function __invoke($logger)
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter(new LineFormatter(
                "[%datetime%] [%ip%] [%id%] %method% %url% %channel%.%level_name% : %message% %context% %extra%\n",
                "Y-m-d H:i:s",
                true,
                true
            ));
        }
    }
}