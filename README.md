<h1 align="center">The Eufony DBAL Package</h1>

<p align="center">
    <a href="https://packagist.org/packages/eufony/dbal">
        <img alt="Packagist Downloads" src="https://img.shields.io/packagist/dt/eufony/dbal?label=Packagist%20Downloads">
    </a>
    <a href="https://github.com/eufony/dbal">
        <img alt="GitHub Stars" src="https://img.shields.io/github/stars/eufony/dbal?label=GitHub%20Stars">
    </a>
    <a href="https://github.com/eufony/dbal/issues">
        <img alt="Issues" src="https://img.shields.io/github/issues/eufony/dbal/open?label=Issues">
    </a>
    <br>
    <a href="https://github.com/eufony/dbal#license">
        <img alt="License" src="https://img.shields.io/github/license/eufony/dbal?label=License">
    </a>
    <a href="https://github.com/eufony/dbal#contributing">
        <img alt="Community Built" src="https://img.shields.io/badge/Made%20with-%E2%9D%A4-red">
    </a>
</p>

*eufony/dbal provides an abstraction layer over SQL to ease development using relational database management systems and
to prevent lock-in to a specific SQL flavor.*

*eufony/dbal* is a PHP library that handles interfacing with the relational database of your choice. It uses an
expressive syntax that is then translated on-the-fly to the appropriate syntax for your SQL flavor. Using it, you'll
enjoy all the benefits of plain SQL, plus:

- [PSR-3](https://www.php-fig.org/psr/psr-3/) compatible logging of database events.
- Transparent caching of query results using any [PSR-6](https://www.php-fig.org/psr/psr-6/)
  or [PSR-16](https://www.php-fig.org/psr/psr-16/) compliant caching implementation.
- Easy protection against SQL injection attacks using prepared statements.
- No-fuss serialization / unserialization of any valid PHP type.

Interested? [Here's how to get started.](#getting-started)

## FAQ

### Why not write my own SQL queries directly?

The problem with plain old SQL is that "SQL" unfortunately does not refer to a single thing. Instead, it comes in
different "flavors", each of which have slight variations on their syntax and supported features. Nobody writes "SQL"
queries. You always have to target a specific flavor.

*"Okay, what if I only use SQL functions and syntax that are part of a universal SQL standard?"*

While there is such a thing as an SQL "standard" adopted by both [ANSI](https://ansi.org/) and [ISO](https://iso.org/),
you'll quickly face two problems when trying to write queries that comply with it:

1. Some very basic functionality, such as that of the [MySQL](https://mysql.com/) `LIMIT` and `OFFSET` keywords, are
   inexplicably missing from the standard instruction set. In such situations, you're forced to either rewrite your
   query in hacky ways that try to accomplish the same result, or give up and only target a specific flavor, which
   might (will) cause portability problems later on.

2. Different SQL flavors comply with the standard to a wildly varying degree.
   While [PostgreSQL](https://postgresql.org/) and [SQLite](https://sqlite.org/) are *mostly* compliant (at least, with
   the core standard, disregarding the various extensions), you'll still come across small differences that break things
   in very subtle ways that might otherwise go unnoticed. Trying to take these into account will add significant
   overhead to development and will generally make it unmotivating to work on the backend infrastructure.

*eufony/dbal* takes care of these problem for you. As an abstraction layer, you, as a developer, don't have to worry at
all about the syntax that comes out the other end. If you want to migrate flavors, you only need to switch to any of the
ready-made [driver implementations](https://packagist.org/providers/eufony/dbal-driver-implemtation); or, if one doesn't
exist, [contribute](#contributing) and [make your own](docs/Supporting_other_Databases.md). Additionally, you'll get to
enjoy some creature comforts when interacting with your database from PHP; such as transparent logging, caching, and
conversion between PHP and SQL data types.

### Why not use a more well-established, mature project?

*~~Because trusting your critical infrastructure to untested, unfunded, emerging projects is fun and exciting!~~*

On a more serious note, *eufony/dbal* was started as a sister project to [*eufony/orm*](https://github.com/eufony/orm),
an [Object Relational Mapping](https://en.wikipedia.org/wiki/Object-relational_mapping) library that aims to rethink the
disadvantages of relational database models with an inventive pragmatic approach. It was concluded during early
development that supporting multiple SQL flavors [was impractical](#why-not-write-my-own-sql-queries-directly) without
an abstraction layer on top of SQL.

As such, the project was split into two, with *eufony/orm* providing the "top" and *eufony/dbal* providing the "bottom"
halves, in much the same way as other popular projects such as the [Doctrine ORM](https://github.com/doctrine/orm).
Unlike the Doctrine project, however, *eufony/dbal* also tries to create an expressive syntax for using its query
builders, as they are something that the end-user (you) can reasonably be expected to interact with.

## Getting started

### Installation

*eufony/dbal* is released as a [Packagist](https://packagist.org/) package and can be easily installed
via [Composer](https://getcomposer.org/) with:

    composer require "eufony/dbal:^0.1.0@beta"

> **Note:** This package ***does not have any stable releases*** yet (not even a v0.x pre-release) and is currently in
> the ***beta stage***. As such, to install it you either need to reduce the minimum stability in your `composer.json`
> to `beta` (not recommended), or override it for this package only using `@beta` in the dependency definition (such as
> in the command above).

### Basic Usage

*For a more detailed documentation, see [here](docs).*

*eufony/dbal* is a "zero-configuration" library, making it blazingly fast to get started. Just define a new database
connection and (optionally) give it a name, like so:

```php
$driver = /* ... */;
$database = new Connection($driver, key: "default");
```

The driver can be any implementation of the driver interface. Out of the box, *eufony/dbal* supports PostgreSQL, MySQL,
and SQLite:

```php
// PostgreSQL
$postgres = new PostgreSQLDriver($dsn, $user, $password);

// MySQL
$mysql = new MySQLDriver($dsn, $user, $password);

// SQLite
$sqlite = new SQLiteDriver($path); // $path can also be `:memory:` for an ephemeral database
```

All three drivers use the PHP PDO extension under the hood.

Once your connection is activated, you can immediately start building and sending queries to the database:

```php
// Define the query
$query = Select::from("users");

// You can also extend the query using loops, conditional logic, etc.
if ($fetch_ids_only) {
    $query = $query->fields(["id"]);
}

// Generate the query string and send it to the database for execution
$users = $query->execute();
```

You can find a list of example queries [here](docs/Queries.md).

## Contributing

Found a bug or a missing feature? Report it over at the [issue tracker](https://github.com/eufony/dbal/issues).

## License

This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General Public
License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more
details.

You should have received a copy of the GNU Lesser General Public License along with this program. If not,
see <https://www.gnu.org/licenses/>.
