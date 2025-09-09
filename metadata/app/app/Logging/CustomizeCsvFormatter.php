<?php

namespace App\Logging;

use Monolog\Formatter\LineFormatter;

class CustomizeCsvFormatter
{
    public function __invoke($logger): void
    {
        $format     = "%datetime%,%level_name%,%channel%,%message%\n";
        $dateFormat = "Y-m-d\\TH:i:s.vP"; // ISO-8601 w/ millis

        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter(new LineFormatter(
                $format,
                $dateFormat,
                true,   // allowInlineLineBreaks
                true    // ignoreEmptyContextAndExtra
            ));
        }
    }
}
