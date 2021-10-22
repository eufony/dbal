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

use Eufony\ORM\DBAL\Query\Create;
use Eufony\ORM\DBAL\Query\Delete;
use Eufony\ORM\DBAL\Query\Drop;
use Eufony\ORM\DBAL\Query\Insert;
use Eufony\ORM\DBAL\Query\Keyword\Ex;
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
        $table = $query['table'];
        $fields = $query['fields'] ?? null;
        $function = $query['function'] ?? null;
        $joins = $query['joins'] ?? null;
        $limit = $query['limit'] ?? null;
        $offset = $query['offset'] ?? null;
        $order = $query['order'] ?? null;
        $where = $query['where'] ?? null;

        $sql = "SELECT ";

        if (isset($function)) {
            $function = match ($function) {
                "count" => "COUNT",
                "max" => "MAX",
                "min" => "MIN",
                default => throw new InvalidArgumentException("Unknown function type")
            };
        }

        $fields = isset($fields) ? array_map(fn($field) => "\"$field\"", $fields) : ["*"];
        if (isset($function)) $fields = array_map(fn($field) => "$function($field)", $fields);
        $sql .= implode(",", $fields);

        if (isset($joins)) {
            $tables = [$table];
            $aliases = [null];
            $join_string = "";

            foreach ($joins as $join) {
                $type = $join['type'];
                $primary_table = $join['primary_table'];
                $primary_field = $join['primary_field'];
                $foreign_table = $join['foreign_table'];
                $foreign_field = $join['foreign_field'];

                if (in_array($foreign_table, $tables)) {
                    $count = array_count_values($tables)[$foreign_table] + 1;
                    $alias = $foreign_table . $count;
                } else {
                    $alias = null;
                }

                $tables[] = $foreign_table;
                $aliases[] = $alias;

                $type = match ($type) {
                    "inner" => "INNER"
                };
                $join_table = $alias === null ? $foreign_table : $alias;

                $join_string .= " $type JOIN $join_table";
                $join_string .= " ON $primary_table.$primary_field = $join_table.$foreign_field";
            }

            $from = array_map(fn($table, $alias) => $alias === null ? "$table" : "$table AS $alias", $tables, $aliases);
            $from = implode(", ", $from);
            $sql .= " FROM $from";

            $sql .= $join_string;
        } else {
            $sql .= " FROM \"$table\"";
        }

        if (isset($where)) {
            $where = $this->expression($where);
            $sql .= " WHERE $where";
        }

        if (isset($order)) {
            $order = array_map(
                fn($field, $type) => "\"$field\" " . ($type === "asc" ? "ASC" : "DESC"),
                array_keys($order),
                array_values($order)
            );
            $sql .= " ORDER BY " . implode(", ", $order);
        }

        // TODO: No LIMIT or OFFSET support in ANSI SQL.
        if (isset($limit)) {
            $sql .= " LIMIT " . $limit;

            if (isset($offset)) {
                $sql .= " OFFSET " . $offset;
            }
        }

        return $sql;
    }

    /** @inheritdoc */
    protected function insert(Insert $query): string {
        $table = $query['table'];
        $values = $query['values'] ?? null;

        if (!isset($values)) {
            throw new InvalidArgumentException("Insert values not set");
        }

        $sql = "INSERT INTO \"$table\"";

        $fields = implode(", ", array_map(fn($key) => "\"$key\"", array_keys($values)));
        $values = implode(", ", array_values($query['values']));
        $sql .= " ($fields) VALUES ($values)";

        return $sql;
    }

    /** @inheritdoc */
    protected function update(Update $query): string {
        $table = $query['table'];
        $values = $query['values'] ?? null;
        $where = $query['where'] ?? null;

        if (!isset($values)) {
            throw new InvalidArgumentException("Update values not set");
        }

        $sql = "UPDATE \"$table\"";

        $values = array_map(fn($key, $value) => "\"$key\" = $value", array_keys($values), array_values($values));
        $values = implode(", ", $values);
        $sql .= " SET $values";

        if (isset($where)) {
            $where = $this->expression($where);
            $sql .= " WHERE $where";
        }

        return $sql;
    }

    /** @inheritdoc */
    protected function delete(Delete $query): string {
        $table = $query['table'];
        $where = $query['where'] ?? null;

        $sql = "DELETE FROM \"$table\"";

        if (isset($where)) {
            $where = $this->expression($query['where']);
            $sql .= " WHERE $where";
        }

        return $sql;
    }

    /** @inheritdoc */
    protected function create(Create $query): string {
        $sql = "CREATE TABLE ";
        return $sql;
    }

    /** @inheritdoc */
    protected function drop(Drop $query): string {
        $tables = $query['tables'];

        $sql = "DROP TABLE " . implode(", ", array_map(fn($table) => "\"$table\"", $tables));

        return $sql;
    }

    protected function expression(Ex $ex): string {
        switch ($ex->type()) {
            case "and":
                $inner = array_map(fn($ex) => $this->expression($ex), $ex->props()['ex']);
                return implode(" AND ", array_map(fn($ex) => "($ex)", $inner));
            case "or":
                $inner = array_map(fn($ex) => $this->expression($ex), $ex->props()['ex']);
                return implode(" OR ", array_map(fn($ex) => "($ex)", $inner));
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
                $operator = match ($ex->type()) {
                    "lt" => ">",
                    "le" => ">=",
                    "eq" => "=",
                    "ge" => "<=",
                    "gt" => "<",
                    "ne" => "!=",
                    "like" => "LIKE"
                };

                return "\"$field\" $operator $value";
            case "in":
                $field = $ex->props()['field'];
                $values = $ex->props()['value'];
                $values = implode(", ", $values);
                return "\"$field\" IN ($values)";
            case "exists":
                $inner = $this->generate($ex->props()["query"]);
                return "EXISTS ($inner)";
            default:
                throw new InvalidArgumentException("Unknown expression type");
        }
    }

}
