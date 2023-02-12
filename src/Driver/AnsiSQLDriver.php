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

namespace Eufony\DBAL\Driver;

use Eufony\DBAL\Query\Builder\Alter;
use Eufony\DBAL\Query\Builder\Create;
use Eufony\DBAL\Query\Builder\Delete;
use Eufony\DBAL\Query\Builder\Drop;
use Eufony\DBAL\Query\Builder\Insert;
use Eufony\DBAL\Query\Builder\Query;
use Eufony\DBAL\Query\Builder\Select;
use Eufony\DBAL\Query\Builder\Update;
use Eufony\DBAL\Query\Expr;
use Eufony\DBAL\UnsupportedException;
use Generator;
use InvalidArgumentException;
use ReflectionClass;

/**
 * Provides a database driver implementation that strictly complies with the
 * ANSI SQL standards.
 *
 * This is meant to be used for SQL engines that don't have an officially
 * supported driver yet.
 * However, SQL vendors usually never completely comply with the ANSI SQL
 * standards, which might lead to some unpredictability and instability in
 * obscure ways.
 *
 * **Note**: The ANSI SQL standard does not have full support of all features
 * that are provided by the Eufony DBAL, which makes it impossible to implement
 * support for some queries.
 * In such cases, a `\Eufony\DBAL\UnsupportedException` will be thrown.
 */
class AnsiSQLDriver extends AbstractDriver
{
    /**
     * @inheritDoc
     */
    public function query(Query $query): Generator
    {
        $query_string = $this->generate($query);
        yield $query_string;
        yield $this->execute($query_string, $query->context());
    }

    public function generate(Query $query): string
    {
        $short_name = (new ReflectionClass(get_class($query)))->getShortName();
        $method_name = "generate" . ucfirst($short_name);
        return $this->$method_name($query);
    }

    /**
     * Generates the full SQL string of a `Select` query builder.
     *
     * @param \Eufony\DBAL\Query\Builder\Select $query
     * @return string
     */
    protected function generateSelect(Select $query): string
    {
        // Get query props
        $table = $query['table'];
        $alias = $query['alias'] ?? null;
        $fields = $query['fields'] ?? null;

        $sql = "SELECT ";

        // Build fields
        if (isset($fields)) {
            $field_strings = [];

            foreach ($fields as $field => $field_alias) {
                if ($field === $field_alias) {
                    $field_strings[] = $this->quoteField($field);
                } else {
                    $field_strings[] = $this->quoteField($field) . " AS " . $this->quoteField($field_alias);
                }
            }

            $sql .= implode(", ", $field_strings);
        } else {
            $sql .= "*";
        }

        // Build FROM
        $table = $this->quoteField($table);
        $sql .= " FROM $table";

        if (isset($alias)) {
            $alias = $this->quoteField($alias);
            $sql .= " AS $alias";
        }

        // Build clauses
        $sql .= $this->generateJoinClause($query);
        $sql .= $this->generateWhereClause($query);
        $sql .= $this->generateGroupByClause($query);
        $sql .= $this->generateOrderByClause($query);
        $sql .= $this->generateLimitClause($query);

        // Return result
        return $sql;
    }

    /**
     * Generates the full SQL string of an `Insert` query builder.
     *
     * @param \Eufony\DBAL\Query\Builder\Insert $query
     * @return string
     */
    protected function generateInsert(Insert $query): string
    {
        // Get query props
        $table = $query['table'];
        $values = $query['values'] ?? null;

        // Ensure values property is set
        if (!isset($values)) {
            throw new InvalidArgumentException("Insert values not set");
        }

        $table = $this->quoteField($table);
        $sql = "INSERT INTO $table";

        // Build values
        $fields = implode(", ", array_map(fn($key) => $this->quoteField($key), array_keys($values)));
        $values = implode(", ", array_values($values));
        $sql .= " ($fields) VALUES ($values)";

        // Return result
        return $sql;
    }

    /**
     * Generates the full SQL string of an `Update` query builder.
     *
     * @param \Eufony\DBAL\Query\Builder\Update $query
     * @return string
     */
    protected function generateUpdate(Update $query): string
    {
        // Get query props
        $table = $query['table'];
        $values = $query['values'] ?? null;

        // Ensure values property is set
        if (!isset($values)) {
            throw new InvalidArgumentException("Update values not set");
        }

        $table = $this->quoteField($table);
        $sql = "UPDATE $table";

        // Build values
        $fields = array_map(fn($field) => $this->quoteField($field), array_keys($values));
        $values = array_values($values);
        $values = implode(", ", array_map(fn($field, $value) => "$field = $value", $fields, $values));
        $sql .= " SET $values";

        // Build clauses
        $sql .= $this->generateWhereClause($query);

        // Return result
        return $sql;
    }

    /**
     * Generates the full SQL string of a `Delete` query builder.
     *
     * @param \Eufony\DBAL\Query\Builder\Delete $query
     * @return string
     */
    protected function generateDelete(Delete $query): string
    {
        // Get query props
        $table = $query['table'];

        $table = $this->quoteField($table);
        $sql = "DELETE FROM $table";

        // Build clauses
        $sql .= $this->generateWhereClause($query);

        // Return result
        return $sql;
    }

    /**
     * Generates the full SQL string of a `Create` query builder.
     *
     * @param \Eufony\DBAL\Query\Builder\Create $query
     * @return string
     */
    protected function generateCreate(Create $query): string
    {
        // TODO: Implement Create query builder.
        return ";";
    }

    /**
     * Generates the full SQL string of an `Alter` query builder.
     *
     * @param \Eufony\DBAL\Query\Builder\Alter $query
     * @return string
     */
    protected function generateAlter(Alter $query): string
    {
        // TODO: Implement Alter query builder.
        return ";";
    }

    /**
     * Generates the full SQL string of a `Drop` query builder.
     *
     * @param \Eufony\DBAL\Query\Builder\Drop $query
     * @return string
     */
    protected function generateDrop(Drop $query): string
    {
        // TODO: Implement Drop query builder.
        return ";";
    }

    /**
     * Generates the SQL string of a query builder's `GROUP BY` clause.
     *
     * @param \Eufony\DBAL\Query\Builder\Query $query
     * @return string
     */
    protected function generateGroupByClause(Query $query): string
    {
        // Get query props
        $groupBy = $query['groupBy'] ?? null;
        $having = $query['having'] ?? null;

        // Return empty string if no group by fields set
        if (!isset($groupBy)) {
            return "";
        }

        $groupBy = implode(", ", array_map(fn($part) => $this->quoteField($part), $groupBy));
        $clause = " GROUP BY $groupBy";

        if (isset($having)) {
            $having = $this->generateExpression($having);
            $clause .= " HAVING $having";
        }

        return $clause;
    }

    /**
     * Generates the SQL string of a query builder's `JOIN` clauses.
     *
     * @param \Eufony\DBAL\Query\Builder\Query $query
     * @return string
     */
    protected function generateJoinClause(Query $query): string
    {
        // Get query props
        $joins = $query['joins'] ?? null;

        // Return empty string if no joins set
        if (!isset($joins)) {
            return "";
        }

        $clause = "";

        foreach ($joins as $join) {
            // Get join props
            $type = $join['type'];
            $join_table = $join['table'];
            $alias = $join['alias'] ?? null;
            $on = $join['on'];

            // Build JOIN
            $type = strtoupper($type);
            $join_table = $this->quoteField($join_table);
            $clause .= " $type JOIN $join_table";

            if ($alias !== null) {
                $alias = $this->quoteField($alias);
                $clause .= " AS $alias";
            }

            // Build ON
            $on = $this->generateExpression($on);
            $clause .= " ON $on";
        }

        // Return result
        return $clause;
    }

    /**
     * Generates the SQL string of a query builder's `LIMIT` clause.
     *
     * @param \Eufony\DBAL\Query\Builder\Query $query
     * @return string
     */
    protected function generateLimitClause(Query $query): string
    {
        // Get query props
        $limit = $query['limit'] ?? null;
        $offset = $query['offset'] ?? null;

        // Return empty string if no limit set
        if (!isset($limit)) {
            return "";
        }

        // Build limit
        $clause = " FETCH FIRST $limit ROWS ONLY";

        // Cannot build offset: No support in ANSI SQL
        if (isset($offset)) {
            throw new UnsupportedException("No OFFSET clause support in ANSI SQL");
        }

        // Return result
        return $clause;
    }

    /**
     * Generates the SQL string of a query builder's `ORDER BY` clause.
     *
     * @param \Eufony\DBAL\Query\Builder\Query $query
     * @return string
     */
    protected function generateOrderByClause(Query $query): string
    {
        // Get query props
        $order = $query['order'] ?? null;

        // Return empty string if no order set
        if (!isset($order)) {
            return "";
        }

        // Build order
        $fields = array_map(fn($field) => $this->quoteField($field), array_keys($order));
        $types = array_map(fn($type) => strtoupper($type), array_values($order));
        $order = implode(", ", array_map(fn($field, $type) => "$field $type", $fields, $types));

        // Return result
        return " ORDER BY $order";
    }

    /**
     * Generates the SQL string of a query builder's `VALUES` clause.
     *
     * This function is currently a no-op, as the `VALUES` clauses are handled by
     * `generateInsert()` and `generateUpdate()`, respectively.
     *
     * @param \Eufony\DBAL\Query\Builder\Query $query
     * @return string
     */
    protected function generateValuesClause(Query $query): string
    {
        return "";
    }

    /**
     * Generates the SQL string of a query builder's `WHERE` clause.
     *
     * @param \Eufony\DBAL\Query\Builder\Query $query
     * @return string
     */
    protected function generateWhereClause(Query $query): string
    {
        // Get query props
        $where = $query['where'] ?? null;

        // Return empty string if no where condition set
        if (!isset($where)) {
            return "";
        }

        // Build where condition
        $where = $this->generateExpression($where);

        // Return result
        return " WHERE $where";
    }

    /**
     * Generates the SQL string of an expression.
     *
     * @param \Eufony\DBAL\Query\Expr $expr
     * @return string
     */
    protected function generateExpression(Expr $expr): string
    {
        switch ($expr->type()) {
            case "true":
                return "1 = 1";
            case "not":
                $inner = $this->generateExpression($expr->props()['expr']);
                return "NOT ($inner)";
            case "and":
            case "or":
                $inner = array_map(fn($expr) => "(" . $this->generateExpression($expr) . ")", $expr->props()['expr']);
                $function = strtoupper($expr->type());
                return implode(" $function ", $inner);
            case "same":
                $primary = $expr->props()['primary'];
                $foreign = $expr->props()['foreign'];

                $primary = $this->quoteField($primary);
                $foreign = $this->quoteField($foreign);

                return "$primary = $foreign";
            case "lt":
            case "le":
            case "eq":
            case "ge":
            case "gt":
            case "ne":
            case "like":
                $field = $expr->props()['field'];
                $value = $expr->props()['value'];
                $value_is_placeholder = str_starts_with($value, ":");

                $operator = match ($expr->type()) {
                    "lt" => "<",
                    "le" => "<=",
                    "eq" => "=",
                    "ge" => ">=",
                    "gt" => ">",
                    "ne" => "<>",
                    "like" => "LIKE"
                };

                $field = $this->quoteField($field);

                if (in_array($expr->type(), ["eq", "ne"])) {
                    $real_value = $expr->context()[trim($value, ":")];
                    if ($real_value === null) {
                        return "$field IS " . ($expr->type() === "eq" ? "" : "NOT ") . "NULL";
                    }
                }

                if (in_array($expr->type(), ["lt", "le", "ge", "gt"]) && !$value_is_placeholder) {
                    $value = $this->quoteField($value);
                }

                return "$field $operator $value";
            case "in":
                $field = $expr->props()['field'];
                $values = $expr->props()['values'];

                $field = $this->quoteField($field);
                $values = implode(", ", $values);

                return "$field IN ($values)";
            case "exists":
                $query = $expr->props()['query'];
                $query = $this->generate($query);
                return "EXISTS ($query)";
            default:
                throw new InvalidArgumentException("Unknown expression type");
        }
    }

    /**
     * Properly quotes fields names such that they are not treated as literals in
     * SQL.
     *
     * @param string $field
     * @return string
     */
    protected function quoteField(string $field): string
    {
        $parts = explode(".", $field);
        $parts = array_map(fn($part) => $part === "*" ? $part : "\"$part\"", $parts);
        return implode(".", $parts);
    }
}
