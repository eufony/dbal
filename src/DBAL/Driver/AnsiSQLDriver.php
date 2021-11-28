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

namespace Eufony\ORM\DBAL\Driver;

use Eufony\ORM\BadMethodCallException;
use Eufony\ORM\DBAL\Query\Create;
use Eufony\ORM\DBAL\Query\Delete;
use Eufony\ORM\DBAL\Query\Drop;
use Eufony\ORM\DBAL\Query\Insert;
use Eufony\ORM\DBAL\Query\Keyword\Ex;
use Eufony\ORM\DBAL\Query\Query;
use Eufony\ORM\DBAL\Query\Select;
use Eufony\ORM\DBAL\Query\Update;
use Eufony\ORM\InvalidArgumentException;

/**
 * Provides a database driver implementation for database engines that more or
 * less comply with the ANSI SQL standards.
 * However, database engines usually never comply with the complete standards,
 * which might lead to some unpredictability and instability in obscure ways.
 * Refer to the "SQL compliance" Wikipedia page for a comparison between
 * different SQL engines.
 *
 * @see https://en.wikipedia.org/wiki/SQL_compliance
 */
class AnsiSQLDriver extends AbstractPDODriver {

    /** @inheritdoc */
    protected function select(Select $query): string {
        // Get query props
        $table = $query['table'];
        $alias = $query['alias'] ?? null;
        $fields = $query['fields'] ?? null;

        $sql = "SELECT ";

        // Build fields
        if (isset($fields)) {
            $select_fields = [];

            foreach ($fields as $field) {
                if (preg_match_all("/^([a-zA-Z]+)\((\w+|\*)\)$/", $field, $matches) === 1) {
                    $function = $matches[1][0];
                    $field = $matches[2][0];

                    $function = strtoupper($function);
                    $field = $this->fieldQuote($field);

                    $select_fields[] = "$function($field)";
                } else {
                    $select_fields[] = $this->fieldQuote($field);
                }
            }

            $sql .= implode(", ", $select_fields);
        } else {
            $sql .= "*";
        }

        // Build FROM
        $table = $this->fieldQuote($table);
        $sql .= " FROM $table";

        if (isset($alias)) {
            $sql .= " AS $alias";
        }

        // Build clauses
        $sql .= $this->join($query) ?? "";
        $sql .= $this->where($query) ?? "";
        $sql .= $this->order($query) ?? "";
        $sql .= $this->limit($query) ?? "";

        // Return result
        return $sql;
    }

    /** @inheritdoc */
    protected function insert(Insert $query): string {
        // Get query props
        $table = $query['table'];
        $values = $query['values'] ?? null;

        // Ensure values property is set
        if (!isset($values)) {
            throw new InvalidArgumentException("Insert values not set");
        }

        $table = $this->fieldQuote($table);
        $sql = "INSERT INTO $table";

        // Build values
        $fields = implode(", ", array_map(fn($key) => $this->fieldQuote($key), array_keys($values)));
        $values = implode(", ", array_values($values));
        $sql .= " ($fields) VALUES ($values)";

        // Return result
        return $sql;
    }

    /** @inheritdoc */
    protected function update(Update $query): string {
        // Get query props
        $table = $query['table'];
        $values = $query['values'] ?? null;

        // Ensure values property is set
        if (!isset($values)) {
            throw new InvalidArgumentException("Update values not set");
        }

        $table = $this->fieldQuote($table);
        $sql = "UPDATE $table";

        // Build values
        $fields = array_map(fn($field) => $this->fieldQuote($field), array_keys($values));
        $values = array_values($values);
        $values = implode(", ", array_map(fn($field, $value) => "$field = $value", $fields, $values));
        $sql .= " SET $values";

        // Build clauses
        $sql .= $this->where($query) ?? "";

        // Return result
        return $sql;
    }

    /** @inheritdoc */
    protected function delete(Delete $query): string {
        // Get query props
        $table = $query['table'];

        $table = $this->fieldQuote($table);
        $sql = "DELETE FROM $table";

        // Build clauses
        $sql .= $this->where($query) ?? "";

        // Return result
        return $sql;
    }

    /** @inheritdoc */
    protected function create(Create $query): string {
        // Get query props

        $sql = "CREATE TABLE ";

        // Return result
        return $sql;
    }

    /** @inheritdoc */
    protected function drop(Drop $query): string {
        // Get query props
        $table = $query['table'];

        $table = $this->fieldQuote($table);
        $sql = "DROP TABLE $table";

        // Return result
        return $sql;
    }

    /**
     * Builds the and `JOIN` clause of a query.
     * Returns the fully generated string if the query has set joins, null
     * otherwise.
     *
     * @param \Eufony\ORM\DBAL\Query\Query $query
     * @return string|null
     */
    protected function join(Query $query): string|null {
        // Get query props
        $joins = $query['joins'] ?? null;

        // Return null if joins not set
        if (!isset($joins)) {
            return null;
        }

        $clause = "";

        foreach ($joins as $join) {
            // Get join props
            $type = $join['type'];
            $join_table = $join['table'];
            $alias = $join['alias'] ?? null;
            $on = $join['on'] ?? null;

            // Ensure ON predicate is set
            if (!isset($on)) {
                throw new BadMethodCallException("No ON predicate on join");
            }

            // Build JOIN
            $type = strtoupper($type);
            $join_table = $this->fieldQuote($join_table);
            $clause .= " $type JOIN $join_table";

            if ($alias !== null) {
                $clause .= " AS $alias";
            }

            // Build ON
            $on = $this->expression($on);
            $clause .= " ON $on";
        }

        // Return result
        return $clause;
    }

    /**
     * Builds the `LIMIT` clause of a query.
     * Returns the fully generated string if the query has a set limit, null
     * otherwise.
     *
     *
     * @param \Eufony\ORM\DBAL\Query\Query $query
     * @return string|null
     */
    protected function limit(Query $query): string|null {
        // Get query props
        $limit = $query['limit'] ?? null;
        $offset = $query['offset'] ?? null;

        // TODO: No LIMIT or OFFSET support in ANSI SQL.

        // Return null if limit not set
        if (!isset($limit)) {
            return null;
        }

        // Build limit
        $clause = " LIMIT $limit";

        // Return limit if offset not set
        if (!isset($offset)) {
            return $clause;
        }

        // Build offset
        $clause .= " OFFSET $offset";

        // Return result
        return $clause;
    }

    /**
     * Builds the `ORDER BY` clause of a query.
     * Returns the fully generated string if the query has a set order, null
     * otherwise.
     *
     * @param \Eufony\ORM\DBAL\Query\Query $query
     * @return string|null
     */
    protected function order(Query $query): string|null {
        // Get query props
        $order = $query['order'] ?? null;

        // Return null if order not set
        if (!isset($order)) {
            return null;
        }

        // Build order
        $fields = array_map(fn($field) => $this->fieldQuote($field), array_keys($order));
        $types = array_map(fn($type) => strtoupper($type), array_values($order));
        $order = implode(", ", array_map(fn($field, $type) => "$field $type", $fields, $types));

        // Return result
        return " ORDER BY $order";
    }

    /**
     * Builds the `WHERE` clause of a query.
     * Returns the fully generated string if the query has a set where
     * condition, null otherwise.
     *
     * @param \Eufony\ORM\DBAL\Query\Query $query
     * @return string|null
     */
    protected function where(Query $query): string|null {
        // Get query props
        $where = $query['where'] ?? null;

        // Return null if where condition not set
        if (!isset($where)) {
            return null;
        }

        // Build where condition
        $where = $this->expression($where);

        // Return result
        return " WHERE $where";
    }

    /**
     * Wraps identifiers in a field string with quotes.
     * Also works with table-field pairs separated with a period (ex.
     * `"foo"."bar"`).
     *
     * @param string $field
     * @return string
     */
    protected function fieldQuote(string $field): string {
        return implode(".", array_map(fn($part) => $part === "*" ? $part : "\"$part\"", explode(".", $field)));
    }

    /**
     * Recursively builds an expression.
     * Returns the fully generated string.
     *
     * @param \Eufony\ORM\DBAL\Query\Keyword\Ex $ex
     * @return string
     */
    protected function expression(Ex $ex): string {
        switch ($ex->type()) {
            case "and":
            case "or":
                $inner = array_map(fn($ex) => $this->expression($ex), $ex->props()['ex']);
                $function = strtoupper($ex->type());
                return implode(" $function ", array_map(fn($ex) => "($ex)", $inner));
            case "not":
                $inner = $this->expression($ex->props()['ex']);
                return "NOT ($inner)";
            case "lt":
            case "le":
            case "eq":
            case "ge":
            case "gt":
            case "ne":
            case "like":
                $field = $ex->props()['field'];
                $value = $ex->props()['value'];
                $literal = $ex->props()['literal'];
                $operator = match ($ex->type()) {
                    "lt" => "<",
                    "le" => "<=",
                    "eq" => "=",
                    "ge" => ">=",
                    "gt" => ">",
                    "ne" => "!=",
                    "like" => "LIKE"
                };

                $field = $this->fieldQuote($field);

                if (!$literal) {
                    $value = $this->fieldQuote($value);
                }

                return "$field $operator $value";
            case "in":
                $field = $ex->props()['field'];
                $values = $ex->props()['value'];
                $values = implode(", ", $values);

                $field = $this->fieldQuote($field);

                return "$field IN ($values)";
            case "exists":
                $inner = $this->generate($ex->props()["query"]);
                return "EXISTS ($inner)";
            default:
                throw new InvalidArgumentException("Unknown expression type");
        }
    }

}
