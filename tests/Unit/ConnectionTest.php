<?php
/*
 * The Eufony DBAL Package
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

namespace Eufony\DBAL\Tests\Unit;

use Eufony\DBAL\Connection;
use Eufony\DBAL\Driver\DriverInterface;
use Eufony\DBAL\QueryException;
use Eufony\DBAL\TransactionException;
use InvalidArgumentException;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * Unit tests for `Eufony\DBAL\Connection`.
 */
class ConnectionTest extends TestCase
{
    /**
     * The `Connection` object to test.
     *
     * @var \Eufony\DBAL\Connection
     */
    protected Connection $connection;

    /**
     * The internal mock `DriverInterface` object used to test the database
     * connection.
     *
     * @var \Eufony\DBAL\Driver\DriverInterface $internalDriver
     */
    protected DriverInterface $internalDriver;

    /**
     * The internal mock PSR-3 logger used to test the database connection.
     *
     * @var \Psr\Log\LoggerInterface $internalLogger
     */
    protected LoggerInterface $internalLogger;

    /**
     * The internal mock PSR-16 cache used to test the database connection.
     *
     * @var \Psr\SimpleCache\CacheInterface $internalCache
     */
    protected CacheInterface $internalCache;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->internalDriver = Mockery::mock(DriverInterface::class);
        $this->internalLogger = Mockery::mock(LoggerInterface::class);
        $this->internalCache = Mockery::mock(CacheInterface::class);
        $this->connection = new Connection($this->internalDriver);
        $this->connection->logger($this->internalLogger);
        $this->connection->cache($this->internalCache);

        $this->internalLogger->allows(
            [
                "debug" => "string",
                "info" => "string",
                "notice" => "string",
                "warning" => "string",
                "error" => "string",
                "critical" => "string",
                "alert" => "string",
                "emergency" => "string",
            ]
        );
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testGetStaticInstance()
    {
        $this->assertSame($this->connection, Connection::get());
    }

    public function testGetInternalDriver()
    {
        $this->assertSame($this->internalDriver, $this->connection->driver());
    }

    public function testGetInternalLogger()
    {
        $this->assertSame($this->internalLogger, $this->connection->logger());
    }

    public function testGetInternalCache()
    {
        $this->assertSame($this->internalCache, $this->connection->cache());
    }

    public function testQueryReadNoCache()
    {
        $query = "SELECT * FROM \"test\" WHERE \"id\"=:id AND \"foo\"=:foo";
        $context = ["id" => 0, "foo" => "bar"];

        $result = [["id" => 0, "foo" => "bar"]];

        $this->internalDriver
            ->expects("execute")
            ->withArgs([$query, $context])
            ->andReturns($result);

        $this->assertEquals($result, $this->connection->query($query, $context, ttl: null));
    }

    /**
     * @depends testQueryReadNoCache
     */
    public function testQueryRead()
    {
        $query = "SELECT * FROM \"test\" WHERE \"id\"=:id AND \"foo\"=:foo";
        $context = ["id" => 0, "foo" => "bar"];

        $result = [["id" => 0, "foo" => "bar"]];
        $cacheKeyRegex = "/[a-zA-Z0-9_.]+/";

        $this->internalCache
            ->expects("get")
            ->withArgs(fn($key) => preg_match($cacheKeyRegex, $key) === 1)
            ->andReturns(null);

        $this->internalDriver
            ->expects("execute")
            ->withArgs([$query, $context])
            ->andReturns($result);

        $this->internalCache
            ->expects("set")
            ->withArgs(fn($key, $value, $ttl) => preg_match($cacheKeyRegex, $key) && $value === $result && $ttl === 1);

        $this->assertEquals($result, $this->connection->query($query, $context));

        $this->internalCache
            ->expects("get")
            ->withArgs(fn($key) => preg_match($cacheKeyRegex, $key) === 1)
            ->andReturns($result);

        $this->assertEquals($result, $this->connection->query($query, $context));
    }

    public function testQueryWrite()
    {
        $query = "INSERT INTO \"test\" (\"id\",\"foo\") VALUES (:id,:foo)";
        $context = ["id" => 0, "foo" => "bar"];

        $result = [];

        $this->internalDriver
            ->expects("execute")
            ->withArgs([$query, $context])
            ->andReturns($result);

        $this->internalCache->expects("clear");

        $this->assertEquals($result, $this->connection->query($query, $context));
    }

    /**
     * @depends testQueryWrite
     */
    public function testQueryFail()
    {
        $query = "INSERT INTO \"test\" (\"id\",\"foo\") VALUES (:id,:foo)";
        $context = ["id" => 0, "foo" => "bar"];

        $this->internalDriver
            ->expects("execute")
            ->withArgs([$query, $context])
            ->andThrows(new QueryException());

        $this->expectException(QueryException::class);
        $this->connection->query($query, $context);
    }

    /**
     * @depends testQueryWrite
     */
    public function testQueryInvalidContext()
    {
        $query = "INSERT INTO \"test\" (\"id\",\"foo\") VALUES (:id,:foo)";
        $context = [0, "bar"];

        $this->internalDriver
            ->expects("execute")
            ->withArgs([$query, $context])
            ->andThrows(new InvalidArgumentException());

        $this->expectException(InvalidArgumentException::class);
        $this->connection->query($query, $context);
    }

    public function testTransactional()
    {
        $this->internalDriver->expects("inTransaction")->andReturns(false);
        $this->internalDriver->expects("beginTransaction");
        $this->internalDriver->expects("commit");
        $function_called = false;

        $this->connection->transactional(function () use (&$function_called) {
            $function_called = true;
        });

        $this->assertTrue($function_called);
    }

    /**
     * @depends testTransactional
     */
    public function testTransactionalNested()
    {
        $this->internalDriver->expects("inTransaction")->andReturns(false);
        $this->internalDriver->expects("beginTransaction");
        $this->internalDriver->expects("commit");
        $this->expectNotToPerformAssertions();

        $this->connection->transactional(function () {
            $this->internalDriver->expects("inTransaction")->andReturns(true);

            $this->connection->transactional(function () {
            });
        });
    }

    /**
     * @depends testTransactional
     */
    public function testTransactionalWithException()
    {
        $this->internalDriver->expects("inTransaction")->andReturns(false);
        $this->internalDriver->expects("beginTransaction");
        $this->internalDriver->expects("rollback");
        $this->expectException(TransactionException::class);

        $this->connection->transactional(function () {
            throw new QueryException();
        });
    }
}
