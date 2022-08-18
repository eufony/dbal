<?php
/*
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

namespace Eufony\DBAL\Log;

use Eufony\DBAL\Connection;
use Eufony\DBAL\Query\Builder\Insert;
use Eufony\Log\Utils\LoggerTrait;
use Psr\Log\AbstractLogger;
use Psr\Log\NullLogger;

/**
 * Provides a logging implementation for logging into the database.
 *
 * The messages are logged into the `__log` table in the database connection;
 * along with the log level, current timestamp, and, if one occurred, the
 * exception.
 */
class DatabaseLogger extends AbstractLogger
{
    use LoggerTrait;

    /**
     * The database connection to log into.
     *
     * @var \Eufony\DBAL\Connection $database
     */
    public Connection $database;

    /**
     * Class constructor.
     * Creates a new logger that logs into the given database.
     *
     * @param \Eufony\DBAL\Connection $database
     */
    public function __construct(Connection $database)
    {
        $this->database = $database;
    }

    /**
     * Combined getter / setter for database connection to be logged into.
     *
     * Returns the current database connection.
     * If `$database` is set, sets the new connection and returns the previous
     * instance.
     *
     * @param \Eufony\DBAL\Connection|null $database
     * @return \Eufony\DBAL\Connection
     */
    public function database(?Connection $database = null): Connection
    {
        $prev = $this->database;
        $this->database = $database ?? $this->database;
        return $prev;
    }

    /**
     * @inheritDoc
     */
    public function log($level, $message, array $context = []): void
    {
        [$level, $message, $context] = $this->psr3_validateParams($level, $message, $context);
        $message = $this->psr3_interpolateMessage($message, $context);

        // Temporarily turn off logging (creates an infinite loop otherwise)
        $logger = $this->database->logger(new NullLogger());

        // TODO: Ensure log table exists.
        /*
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
                "type" => "varchar",
                "length" => 9,
                "nullable" => false,
            ],
            "message" => [
                "type" => "text",
            ],
            "exception" => [
                "type" => "text",
                "default" => null,
            ],
        ];

        Create::table("__log", idempotent: true)->fields($fields)->execute();
        */

        // Execute log query
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
