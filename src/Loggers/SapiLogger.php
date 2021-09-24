<?php
/*
 * The Eufony ORM Package
 * Copyright (c) 2021 Alpin Gencer
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace Eufony\ORM\Loggers;

/**
 * Provides a logging implementation for logging to the error log.
 * The messages will be sent to the SAPI logging handler directly.
 */
class SapiLogger extends \Psr\Log\AbstractLogger {

    use LoggerTrait;

    /** @inheritdoc */
    public function log($level, $message, array $context = []) {
        [$level, $message, $context] = $this->validateParams($level, $message, $context);
        if (!$this->compareLevels($level, $this->minLevel, $this->maxLevel)) return;

        // Send log level and message to the SAPI logging handler (the error log)
        error_log("$level: $message", 4);
    }

}
