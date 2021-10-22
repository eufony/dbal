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

namespace Eufony\ORM\Log;

use Eufony\ORM\DBAL\Connection;
use Eufony\ORM\DBAL\Query\Create;
use Eufony\ORM\DBAL\Query\Insert;
use Eufony\ORM\Schema\Schema;
use Psr\Log\AbstractLogger;
use Psr\Log\NullLogger;

/**
 * Provides a logging implementation for logging into a database directly.
 * The messages are logged into the `__log` table in the database connection;
 * along with the log level, current timestamp, and, if one occurred, the
 * exception.
 */
class DatabaseLogger extends AbstractLogger {

    use LoggerTrait;

    /**
     * The database connection to log into.
     *
     * @var \Eufony\ORM\DBAL\Connection $database
     */
    public Connection $database;

    /**
     * Class constructor.
     * Creates a new logger that logs into the given database.
     *
     * @param \Eufony\ORM\DBAL\Connection $database
     */
    public function __construct(Connection $database) {
        $this->database = $database;
    }

    /**
     * Returns the database connection to be logged into.
     * If `$database` is set, sets the new connection and returns the previous
     * instance.
     *
     * @param \Eufony\ORM\DBAL\Connection|null $database
     * @return \Eufony\ORM\DBAL\Connection
     */
    public function database(?Connection $database = null): Connection {
        $prev = $this->database;
        $this->database = $database ?? $this->database;
        return $prev;
    }

    /** @inheritdoc */
    public function log($level, $message, array $context = []): void {
        [$level, $message, $context] = $this->validateParams($level, $message, $context);
        if (!$this->compareLevels($level, $this->minLevel, $this->maxLevel)) return;
        $message = $this->interpolate($message, $context);

        // Temporarily turn off logging (creates an infinite loop otherwise)
        $logger = $this->database->logger(new NullLogger());

        // Ensure log table exists
        if (!Schema::tableExists("__log")) {
            $fields = [
                "id" => [
                    "type" => "int",
                    "nullable" => "false",
                    "primary_key" => "true",
                    "auto_increment" => true,
                ],
                "time" => [
                    "type" => "datetime",
                    "nullable" => false,
                ],
                "level" => [
                    "type" => "string(9)",
                    "nullable" => false,
                ],
                "message" => [
                    "type" => "string",
                ],
                "exception" => [
                    "type" => "string",
                    "default" => null,
                ],
            ];

            Create::table("__log")->fields($fields)->execute();
        }

        $values = [
            "time" => date("Y-m-d H:i:s"),
            "level" => $level,
            "message" => $message,
            "exception" => $context['exception'] ?? null,
        ];

        Insert::into("__log")->values($values)->execute();

        // Restore previous logger
        $this->database->logger($logger);
    }

}
