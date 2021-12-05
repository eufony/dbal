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

namespace Eufony\ORM\DBAL\Driver;

use Eufony\ORM\DBAL\Query\Builder\Create;
use Eufony\ORM\DBAL\Query\Builder\Delete;
use Eufony\ORM\DBAL\Query\Builder\Drop;
use Eufony\ORM\DBAL\Query\Builder\Insert;
use Eufony\ORM\DBAL\Query\Builder\Query;
use Eufony\ORM\DBAL\Query\Builder\Select;
use Eufony\ORM\DBAL\Query\Builder\Update;
use Eufony\ORM\DBAL\Query\Expr;
use Eufony\ORM\QueryException;
use InvalidArgumentException;

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
 * In such cases, a `\Eufony\ORM\UnsupportedException` will be thrown.
 */
class AnsiSQLDriver extends AbstractPDODriver
{
    /**
     * @inheritDoc
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
            $fields = array_map(fn($field) => $this->quoteField($field), $fields);
            $sql .= implode(", ", $fields);
        } else {
            $sql .= "*";
        }

        // Build FROM
        $table = $this->quoteField($table);
        $sql .= " FROM $table";

        if (isset($alias)) {
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
     * @inheritDoc
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
     * @inheritDoc
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
     * @inheritDoc
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
     * @inheritDoc
     */
    protected function generateCreate(Create $query): string
    {
        // TODO: Implement Create query builder.
    }

    /**
     * @inheritDoc
     */
    protected function generateDrop(Drop $query): string
    {
        // Get query props
        $table = $query['table'];

        $table = $this->quoteField($table);
        $sql = "DROP TABLE $table";

        // Return result
        return $sql;
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
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
     * @inheritDoc
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
            throw new QueryException("No OFFSET clause support in ANSI SQL");
        }

        // Return result
        return $clause;
    }

    /**
     * @inheritDoc
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
     * {@inheritDoc}
     *
     * **Note**: This method is unused by SQL drivers.
     */
    protected function generateValuesClause(Query $query): string
    {
        return "";
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
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
                $inner = array_map(fn($expr) => $this->generateExpression($expr), $expr->props()['expr']);
                $function = strtoupper($expr->type());
                return implode(" $function ", array_map(fn($ex) => "($ex)", $inner));
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
                $real_value = $expr->context()[trim($value, ":")];

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

                if ($expr->type() === "eq" && $real_value === null) {
                    return "$field IS NULL";
                } elseif ($expr->type() === "ne" && $real_value === null) {
                    return "$field IS NOT NULL";
                }

                if (in_array($expr, ["lt", "le", "ge", "gt"]) && is_string($value)) {
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
     * Wraps identifiers in a field string with quotes.
     *
     * Also works with table-field pairs separated by a period (`"table"."field"`).
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
