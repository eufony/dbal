<?php
/*
 * The Eufony ORM
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

namespace Eufony\ORM\Tests\Unit\DBAL\Driver;

use BadMethodCallException;
use Eufony\ORM\DBAL\Driver\DriverInterface;
use Eufony\ORM\DBAL\Query\Builder\Delete;
use Eufony\ORM\DBAL\Query\Builder\Drop;
use Eufony\ORM\DBAL\Query\Builder\Insert;
use Eufony\ORM\DBAL\Query\Builder\Query;
use Eufony\ORM\DBAL\Query\Builder\Select;
use Eufony\ORM\DBAL\Query\Builder\Update;
use Eufony\ORM\DBAL\Query\Expr;
use Exception;
use PHPUnit\Framework\TestCase;

/**
 * Provides an abstract database driver implementation tester.
 */
abstract class AbstractDriverTest extends TestCase
{
    /**
     * The database driver implementation to test.
     *
     * @var \Eufony\ORM\DBAL\Driver\DriverInterface $driver
     */
    protected DriverInterface $driver;

    /**
     * Returns a new instance of a database driver implementation to test.
     *
     * @return \Eufony\ORM\DBAL\Driver\DriverInterface
     */
    abstract public function getDriver(): DriverInterface;

    /**
     * Returns an array of query strings.
     *
     * The query strings correspond to each of the query builders returned by
     * `queryBuilders()`.
     *
     * @return string[]
     */
    abstract public function queryStrings(): array;

    public function validData(): array
    {
        return [
            "string" => [
                "empty_string" => "",
                "string" => "foo",
                "long_string" => str_repeat("a", 1024 * 1024),
            ],
            "int" => [
                "zero" => 0,
                "big_int_negative" => PHP_INT_MIN,
                "big_int_positive" => PHP_INT_MAX,
            ],
            "float" => [
                "float_zero" => 0.0,
                "small_float" => PHP_FLOAT_MIN,
                "big_float" => PHP_FLOAT_MAX,
            ],
            "bool" => [
                "true" => true,
                "false" => false,
            ],
            "null" => [
                "null" => null,
            ],
            "array" => [
                "array" => ["foo", "bar", "baz"],
                "associative_array" => ["foo" => "bar"],
                "nested_array" => [["foo" => "bar"], ["foo" => "baz"]],
            ],
            "object" => [
                "serializable" => new Exception("Serialized exception"),
            ]
        ];
    }

    public function selectBuilders(): array
    {
        $queries = [
            Select::from("test"),
            Select::from("test")->fields("foo"),
            Select::from("test")->fields("foo", "bar"),
        ];

        foreach ($this->groupByClauseParams() as $params) {
            $query = clone $queries[0];
            $queries[] = $query->groupBy(...$params);
        }

        foreach ($this->joinClauseParams() as $join_chains) {
            foreach (["innerJoin", "leftJoin"] as $join_type) {
                $query = clone $queries[0];

                foreach ($join_chains as $params) {
                    $query = call_user_func_array([$query, $join_type], $params);
                }

                $queries[] = $query;
            }
        }

        foreach ($this->limitClauseParams() as $params) {
            $query = clone $queries[0];
            $queries[] = $query->limit(...$params);
        }

        foreach ($this->orderByClauseParams() as $params) {
            $query = clone $queries[0];
            $queries[] = $query->orderBy(...$params);
        }

        foreach ($this->whereClauseParams() as $params) {
            $query = clone $queries[0];
            $queries[] = $query->where(...$params);
        }

        return $queries;
    }

    public function insertBuilders(): array
    {
        $queries = [];
        $base_query = Insert::into("test");

        foreach ($this->valuesClauseParams() as $params) {
            $query = clone $base_query;
            $queries[] = $query->values(...$params);
        }

        return $queries;
    }

    public function updateBuilders(): array
    {
        $queries = [];
        $base_query = Update::table("test");

        foreach ($this->valuesClauseParams() as $params) {
            $query = clone $base_query;
            $queries[] = $query->values(...$params);
        }

        foreach ($this->whereClauseParams() as $params) {
            $query = clone $queries[0];
            $queries[] = $query->where(...$params);
        }

        return $queries;
    }

    public function deleteBuilders(): array
    {
        $queries = [Delete::from("test")];

        foreach ($this->whereClauseParams() as $params) {
            $query = clone $queries[0];
            $queries[] = $query->where(...$params);
        }

        return $queries;
    }

    public function createBuilders(): array
    {
        return [
        ];
    }

    public function dropBuilders(): array
    {
        return [
            Drop::table("test"),
        ];
    }

    public function groupByClauseParams(): array
    {
        return [
            ["foo"],
            ["foo", "bar", "baz"],
        ];
    }

    public function joinClauseParams(): array
    {
        return [
            [
                ["b", "on" => Expr::same("a.id", "b.a_id")]
            ],
            [
                ["b", "on" => Expr::same("a.b_id", "b.id")]
            ],
            [
                ["b", "on" => Expr::same("a.b_id", "b.id")],
                ["c", "on" => Expr::same("b.c_id", "c.id")]
            ],
            [
                ["b", "b1", "on" => Expr::same("a.b1_id", "b1.id")],
                ["b", "b2", "on" => Expr::same("a.b2_id", "b2.id")],
            ],
            [
                ["a", "a2", "on" => Expr::same("a.a2_id", "a2.id")],
            ],
        ];
    }

    public function limitClauseParams(): array
    {
        return [
            [5],
            [2, 7],
        ];
    }

    public function orderByClauseParams(): array
    {
        return [
            ["foo"],
            [["foo" => "asc"]],
            [["foo" => "desc"]],
            [["foo", "bar"]],
            [["foo", "bar" => "desc"]],
            [["foo" => "desc", "bar" => "desc"]],
        ];
    }

    public function valuesClauseParams(): array
    {
        $valid_data = $this->validData();
        $data = [];

        foreach ($valid_data as $var_type => $test_cases) {
            if ($var_type === "array") {
                continue;
            }

            foreach ($test_cases as $test_name => $test_value) {
                $data[] = [[$test_name => $test_value]];
            }
        }

        return $data;
    }

    public function whereClauseParams(): array
    {
        $valid_data = $this->validData();
        $data = [
            [Expr::true()],
            [Expr::not(Expr::true())],
            [Expr::and(Expr::true(), Expr::true(), Expr::true())],
            [Expr::or(Expr::true(), Expr::true(), Expr::true())],
            [Expr::same("primary", "foreign")],
            [Expr::like("neither", "foo")],
            [Expr::like("left", "%foo")],
            [Expr::like("right", "foo%")],
            [Expr::like("both", "%foo%")],
            [Expr::exists(Select::from("test"))],
        ];

        foreach (["lt", "le", "ge", "gt"] as $expr) {
            foreach ($valid_data as $var_type => $test_cases) {
                if (!in_array($var_type, ["int", "float"])) {
                    continue;
                }

                foreach ($test_cases as $test_name => $test_value) {
                    $data[] = [Expr::$expr($test_name, $test_value)];
                }
            }
        }

        foreach (["eq", "ne"] as $expr) {
            foreach ($valid_data as $var_type => $test_cases) {
                if ($var_type === "array") {
                    continue;
                }

                foreach ($test_cases as $test_name => $test_value) {
                    $data[] = [Expr::$expr($test_name, $test_value)];
                }
            }
        }

        foreach ($valid_data as $var_type => $test_cases) {
            if ($var_type !== "array") {
                continue;
            }

            foreach ($test_cases as $test_name => $test_value) {
                if ($test_name === "nested_array") {
                    continue;
                }

                $data[] = [Expr::in($test_name, $test_value)];
            }
        }

        return $data;
    }

    /**
     * Returns an array of valid query builders.
     *
     * The query builders correspond to each of the query strings returned by
     * `queryStrings()`.
     *
     * @return Query[]
     */
    public function queryBuilders(): array
    {
        return array_merge(
            $this->selectBuilders(),
            $this->insertBuilders(),
            $this->updateBuilders(),
            $this->deleteBuilders(),
            $this->createBuilders(),
            $this->dropBuilders(),
        );
    }

    /**
     * Returns an array of invalid query builders.
     *
     * Attempting to generate a query from these query builders should result in an
     * exception.
     *
     * @return Query[]
     */
    public function invalidQueryBuilders(): array
    {
        return [
            Select::from("test")->fields(),

            Select::from("test")->leftJoin("a")->leftJoin("b"),

            Select::from("test")->limit(7, 2),

            Select::from("test")->orderBy(["foo" => "invalid"]),

            Insert::into("test"),

            Insert::into("test")->values([]),

            Insert::into("test")->values(["foo"]),

            Update::table("test"),

            Update::table("test")->values([]),

            Update::table("test")->values(["foo"]),
        ];
    }

    /**
     * Data provider for testing the generation of query strings.
     *
     * Returns a query builder and the expected query string for each data set.
     * The query string may contain curly braces (`{}`) as a shorthand for
     * randomized named placeholders, as well as additional indentation to make the
     * query more human-readable.
     *
     * @return mixed[][]
     */
    public function data_expectedQueryStrings(): array
    {
        return array_map(fn($query, $string) => [$query, $string], $this->queryBuilders(), $this->queryStrings());
    }

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->driver = $this->getDriver();
    }

    /**
     * @dataProvider data_expectedQueryStrings
     */
    public function test_generate(Query $query, string $expected)
    {
        $expected = preg_replace(["/\n/", "/ +/"], ["", " "], $expected);
        $expected = preg_quote($expected);
        $expected = str_replace("\?", ":\w{32}", $expected);
        $this->assertMatchesRegularExpression("/^$expected$/", $this->driver->generate($query));
    }

    /**
     * @depends test_generate
     */
    public function test_execute()
    {
    }

    public function test_inTransaction()
    {
        $this->assertFalse($this->driver->inTransaction());

        $this->driver->beginTransaction();
        $this->assertTrue($this->driver->inTransaction());
        $this->driver->commit();
        $this->assertFalse($this->driver->inTransaction());

        $this->driver->beginTransaction();
        $this->assertTrue($this->driver->inTransaction());
        $this->driver->rollback();
        $this->assertFalse($this->driver->inTransaction());
    }

    /**
     * @depends test_inTransaction
     */
    public function test_beginTransaction_withActiveTransaction()
    {
        $this->assertFalse($this->driver->inTransaction());
        $this->driver->beginTransaction();
        $this->assertTrue($this->driver->inTransaction());
        $this->expectException(BadMethodCallException::class);
        $this->driver->beginTransaction();
    }

    /**
     * @depends test_inTransaction
     */
    public function test_commit()
    {
    }

    /**
     * @depends test_inTransaction
     */
    public function test_commit_withoutActiveTransaction()
    {
        $this->assertFalse($this->driver->inTransaction());
        $this->expectException(BadMethodCallException::class);
        $this->driver->commit();
    }

    /**
     * @depends test_inTransaction
     */
    public function test_rollback()
    {
    }

    /**
     * @depends test_inTransaction
     */
    public function test_rollback_withoutActiveTransaction()
    {
        $this->assertFalse($this->driver->inTransaction());
        $this->expectException(BadMethodCallException::class);
        $this->driver->rollback();
    }
}
