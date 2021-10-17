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
 * Tasks include managing database connections as well as logging, caching, and
 * inflection interfaces.
 */
class ORM {

    /**
     * Stores active instances of database connections.
     *
     * @var array<\Eufony\DBAL\Connection> $connections
     */
    private static array $connections = [];

    /**
     * An implementation of `\Eufony\ORM\Inflection\InflectionInterface`.
     * Defaults to an instance of `\Eufony\ORM\Inflection\DoctrineInflector`.
     *
     * @var \Eufony\ORM\Inflection\InflectorInterface $inflector
     */
    private static InflectorInterface $inflector;

    /**
     * Returns an active instance of a database connection.
     * If no key is specified, the `default` connection is returned.
     *
     * @param string $key
     * @return \Eufony\DBAL\Connection
     */
    public static function connection(string $key = "default"): Connection {
        // Ensure connection exists
        if (!array_key_exists($key, static::$connections)) {
            throw new InvalidArgumentException("Unknown database connection '$key'");
        }

        return static::$connections[$key];
    }

    /**
     * Returns all active instances of database connections.
     *
     * @return array<\Eufony\DBAL\Connection>
     */
    public static function connections(): array {
        return static::$connections;
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
     * Initializes database connections from the given drivers.
     * If only a single driver is supplied, it will be treated as the `default`
     * connection.
     * If an array of drivers is supplied, each key-value pair will be treated
     * as a separate connection to be established.
     * The keys can later be used as a reference to the active connections
     * using the `ORM::connection()` method.
     *
     * By default, sets up a `\Eufony\ORM\Inflection\DoctrineInflector` for
     * inflection.
     *
     * Throws a `\Eufony\ORM\InvalidArgumentException` if an attempt to
     * establish a duplicate connection occurs.
     *
     * @param \Eufony\DBAL\Driver\DriverInterface|array $drivers
     *
     * @see \Eufony\ORM\ORM::connection()
     */
    public static function init(DriverInterface|array $drivers): void {
        // If a single driver is passed, it will be treated as the default driver
        if (!is_array($drivers)) {
            $drivers = ["default" => $drivers];
        }

        // Initialize database connections with given drivers
        foreach ($drivers as $key => $driver) {
            // Ensure connection doesn't exist
            if (isset(static::$connections[$key])) {
                throw new InvalidArgumentException("Database connection '$key' is already initialized");
            }

            static::$connections[$key] = new Connection($driver);
        }

        // Initialize inflector
        static::$inflector ??= new DoctrineInflector();
    }

    /**
     * Class constructor.
     * Constructor is private, class cannot be instantiated.
     * All methods of this class must be called statically.
     */
    private function __construct() {
    }

}
