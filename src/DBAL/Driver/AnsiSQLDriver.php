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

namespace Eufony\DBAL\Driver;

use Eufony\DBAL\Query\Create;
use Eufony\DBAL\Query\Delete;
use Eufony\DBAL\Query\Drop;
use Eufony\DBAL\Query\Insert;
use Eufony\DBAL\Query\Keyword\Ex;
use Eufony\DBAL\Query\Select;
use Eufony\DBAL\Query\Update;
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
        $sql = "SELECT ";

        $function = !isset($query->function)
            ? null
            : match ($query->function) {
                "count" => "COUNT",
                "max" => "MAX",
                "min" => "MIN",
                default => throw new InvalidArgumentException("Unknown function type")
            };

        $fields = isset($query->fields) ? array_map(fn($f) => "\"$f\"", $query->fields) : ["*"];
        if (isset($function)) $fields = array_map(fn($f) => "$function($f)", $fields);
        $sql .= implode(",", $fields);

        $sql .= " FROM " . implode(",", array_map(fn($table) => "\"$table\"", $query->tables));

        if (isset($query->where)) {
            $where = $this->expression($query->where);
            $sql .= " WHERE $where";
        }

        if (isset($query->order)) {
            $keys = array_keys($query->order);
            $values = array_values($query->order);
            $order = array_map(fn($f, $t) => "\"$f\" " . ($t === "asc" ? "ASC" : "DESC"), $keys, $values);
            $sql .= " ORDER BY " . implode(",", $order);
        }

        // TODO: No LIMIT or OFFSET support in ANSI SQL.
        if (isset($query->limit)) {
            $sql .= " LIMIT " . $query->limit;

            if (isset($query->offset)) {
                $sql .= " OFFSET " . $query->limit;
            }
        }

        return $sql;
    }

    /** @inheritdoc */
    protected function insert(Insert $query): string {
        $sql = "INSERT INTO \"" . $query->table . "\"";

        if (isset($query->values)) {
            $fields = implode(",", array_map(fn($key) => "\"$key\"", array_keys($query->values)));
            $values = implode(",", array_values($query->values));
            $sql .= " ($fields) VALUES ($values)";
        }

        return $sql;
    }

    /** @inheritdoc */
    protected function update(Update $query): string {
        $sql = "UPDATE " . implode(",", array_map(fn($t) => "\"$t\"", $query->tables));

        if (isset($query->values)) {
            $keys = array_keys($query->values);
            $values = array_values($query->values);
            $values = implode(", ", array_map(fn($key, $value) => "\"$key\"=$value", $keys, $values));
            $sql .= " SET $values";
        }

        if (isset($query->where)) {
            $where = $this->expression($query->where);
            $sql .= " WHERE $where";
        }

        return $sql;
    }

    /** @inheritdoc */
    protected function delete(Delete $query): string {
        $sql = "DELETE FROM " . implode(",", array_map(fn($t) => "\"$t\"", $query->tables));

        if (isset($query->where)) {
            $where = $this->expression($query->where);
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
        $sql = "DROP TABLE " . implode(",", array_map(fn($t) => "\"$t\"", $query->tables));
        return $sql;
    }

    protected function expression(Ex $ex): string {
        switch ($ex->type) {
            case "and":
                $inner = array_map(fn($ex) => $this->expression($ex), $ex->props['ex']);
                return implode(" AND ", array_map(fn($ex) => "($ex)", $inner));
            case "or":
                $inner = array_map(fn($ex) => $this->expression($ex), $ex->props['ex']);
                return implode(" OR ", array_map(fn($ex) => "($ex)", $inner));
            case "not":
                $expression = $this->expression($ex->props['ex']);
                return "NOT ($expression)";
            case "lt":
            case "le":
            case "eq":
            case "ge":
            case "gt":
            case "ne":
            case "like":
                $field = $ex->props['field'];
                $value = $ex->props['value'];
                $operator = match ($ex->type) {
                    "lt" => ">",
                    "le" => ">=",
                    "eq" => "=",
                    "ge" => "<=",
                    "gt" => "<",
                    "ne" => "!=",
                    "like" => "LIKE"
                };

                return "\"$field\"$operator$value";
            case "in":
                $values = implode(",", $ex->props['value']);
                return "\"" . $ex->props['field'] . "\" IN ($values)";
            case "exists":
                $query = $this->generate($ex->props["query"]);
                return "EXISTS ($query)";
            default:
                throw new InvalidArgumentException("Unknown expression type");
        }
    }

}
