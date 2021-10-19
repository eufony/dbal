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

namespace Eufony\ORM;

use Eufony\DBAL\Connection;
use Eufony\DBAL\Driver\DriverInterface;
use Eufony\ORM\Inflection\DoctrineInflector;
use Eufony\ORM\Inflection\InflectorInterface;

/**
 * Manages and oversees the state of the Eufony ORM as a whole.
 * Tasks include managing database connections as inflection interfaces.
 */
class ORM {

    /**
     * Stores the active instance of the database connection.
     *
     * @var \Eufony\DBAL\Connection $connection
     */
    private static Connection $connection;

    /**
     * An implementation of `InflectionInterface`.
     * Defaults to an instance of `\Eufony\ORM\Inflection\DoctrineInflector`.
     *
     * @var \Eufony\ORM\Inflection\InflectorInterface $inflector
     */
    private static InflectorInterface $inflector;

    /**
     * Returns the active instance of the database connection.
     *
     * @return \Eufony\DBAL\Connection
     */
    public static function connection(): Connection {
        // Ensure connection exists
        if (!isset(static::$connection)) {
            throw new BadMethodCallException("No active database connection");
        }

        return static::$connection;
    }

    /**
     * Returns the current inflector.
     * If `$inflector` is set, sets the new inflector and returns the previous
     * instance.
     *
     * @param \Eufony\ORM\Inflection\InflectorInterface|null $inflector
     * @return \Eufony\ORM\Inflection\InflectorInterface
     */
    public static function inflector(?InflectorInterface $inflector = null): InflectorInterface {
        $prev = static::$inflector;
        static::$inflector = $inflector ?? static::$inflector;
        return $prev;
    }

    /**
     * Initializes the database connection from the given driver.
     *
     * By default, sets up a `\Eufony\ORM\Inflection\DoctrineInflector` for
     * inflection.
     *
     * Throws a `\Eufony\ORM\InvalidArgumentException` if an attempt to
     * establish a duplicate connection occurs.
     *
     * @param \Eufony\DBAL\Driver\DriverInterface $driver
     */
    public static function init(DriverInterface $driver): void {
        // Ensure connection doesn't exist
        if (isset(static::$connection)) {
            throw new BadMethodCallException("Database connection is already active");
        }

        // Initialize database connection
        static::$connection = new Connection($driver);

        // Initialize inflector
        static::$inflector = new DoctrineInflector();
    }

    /**
     * Class constructor.
     * Constructor is private, class cannot be instantiated.
     * All methods of this class must be called statically.
     */
    private function __construct() {
    }

}
