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

namespace Eufony\DBAL\Log;

use Eufony\DBAL\Database;
use Eufony\DBAL\QueryException;
use Psr\Log\AbstractLogger;
use Psr\Log\NullLogger;

/**
 * Provides a logging implementation for logging into a database directly.
 * The messages are logged into the `__log` table in the database; along with
 * the log level, current timestamp, and, if one occurred, the exception.
 */
class DatabaseLogger extends AbstractLogger {

    use LoggerTrait;

    /**
     * The database connection to log into.
     *
     * @var Database $database
     */
    public Database $database;

    /**
     * Class constructor.
     * Creates a new logger that logs into the given database.
     *
     * @param Database $database
     */
    public function __construct(Database $database) {
        $this->database = $database;
    }

    /** @inheritdoc */
    public function log($level, $message, array $context = []) {
        [$level, $message, $context] = $this->validateParams($level, $message, $context);
        if (!$this->compareLevels($level, $this->minLevel, $this->maxLevel)) return;
        $message = $this->interpolate($message, $context);

        // TODO: Use query builders for this.

        // Temporarily turn off logging (creates an infinite loop otherwise)
        $logger = $this->database->logger(new NullLogger());

        // Ensure log table exists
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

            $this->database->query($sql);
        } catch (QueryException) {
            // table already exists, silently ignore
        }

        // Fetch ID from table
        $result = $this->database->query("SELECT \"id\" FROM \"__log\" ORDER BY \"id\" DESC");
        $id = !empty($result) ? $result[0]['id'] + 1 : 1;

        $values = [
            "id" => $id,
            "time" => date("Y-m-d H:i:s"),
            "level" => $level,
            "message" => $message
        ];

        // If an exception was passed, add it to the values array
        if (array_key_exists("exception", $context)) $values['exception'] = $context['exception'];

        // Build and execute query from values array
        $fields = implode(",", array_map(fn($key) => "\"$key\"", array_keys($values)));
        $placeholders = implode(",", array_map(fn($key) => ":$key", array_keys($values)));
        $sql = "INSERT INTO \"__log\" ($fields) VALUES ($placeholders)";

        $this->database->query($sql, $values);

        // Restore previous logger
        $this->database->logger($logger);
    }

}
