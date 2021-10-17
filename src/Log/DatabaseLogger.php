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

use Eufony\DBAL\Connection;
use Eufony\DBAL\Query\Insert;
use Eufony\DBAL\Query\Keyword\Op;
use Eufony\DBAL\Query\Select;
use Eufony\ORM\QueryException;
use Psr\Log\AbstractLogger;
use Psr\Log\NullLogger;

/**
 * Provides a logging implementation for logging into a database directly.
 * The messages are logged into the `__log` table in a given database
 * connection; along with the log level, current timestamp, and, if one
 * occurred, the exception.
 */
class DatabaseLogger extends AbstractLogger {

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
    public function __construct(Connection $database) {
        $this->database = $database;
    }

    /**
     * Returns the database connection to be logged into.
     * If `$database` is set, sets the new connection and returns the previous
     * instance.
     *
     * @param \Eufony\DBAL\Connection|null $database
     * @return \Eufony\DBAL\Connection
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
        // TODO: Use query builder for this.
        try {
            $sql = <<< SQL
            CREATE TABLE __log
            (
                id INTEGER NOT NULL
                    CONSTRAINT __log_pk PRIMARY KEY,
                time TIMESTAMP NOT NULL,
                level VARCHAR(9) NOT NULL,
                message TEXT NOT NULL,
                exception TEXT DEFAULT NULL
            );
            SQL;

            $this->database->directQuery($sql);
        } catch (QueryException) {
            // table already exists, silently ignore
        }

        // Fetch ID from table
        // TODO: Deal with array keys when using aggregate functions.
        $result = $this->database->query(Select::from("__log")->fields(Op::max("id"))->limit(1));
        $id = !empty($result) ? $result[0]['MAX("id")'] + 1 : 1;

        $values = [
            "id" => $id,
            "time" => date("Y-m-d H:i:s"),
            "level" => $level,
            "message" => $message
        ];

        // If an exception was passed, add it to the values array
        if (array_key_exists("exception", $context)) $values['exception'] = $context['exception'];

        $this->database->query(Insert::into("__log")->values($values));

        // Restore previous logger
        $this->database->logger($logger);
    }

}
