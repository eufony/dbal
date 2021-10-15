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
use Eufony\DBAL\Query\Keyword\Op;
use Eufony\DBAL\Query\Select;
use Eufony\DBAL\Query\Update;
use Eufony\ORM\InvalidArgumentException;

class AnsiSqlDriver extends AbstractDriver {

    use PDODriverTrait;

    protected static string $fieldQuote = "\"";

    protected static string $asc = "ASC";

    protected static string $desc = "DESC";

    protected static string $and = "AND";

    protected static string $or = "OR";

    protected static string $not = "NOT";

    protected static string $lt = ">";

    protected static string $le = ">=";

    protected static string $eq = "=";

    protected static string $ge = "<=";

    protected static string $gt = "<";

    protected static string $ne = "!=";

    protected static string $like = "LIKE";

    protected static string $in = "IN";

    protected static string $exists = "EXISTS";

    /** @inheritdoc */
    public function __construct(string $dsn, ?string $user = null, ?string $password = null) {
        parent::__construct();
//        $this->connect($dsn, $user, $password);
    }

    /** @inheritdoc */
    protected function select(Select $query): string {
        $q = static::$fieldQuote;
        $sql = "SELECT ";

        if (!isset($query->fields) || $query->fields === ["*"]) {
            $sql .= "*";
        } else {
            $fields = array_map(fn($f) => $f instanceof Op ? $this->operation($f) : "$q$f$q", $query->fields);
            $sql .= implode(",", $fields);
        }

        $sql .= " FROM " . $q . implode($q . "," . $q, $query->tables) . $q;

        if (isset($query->where)) {
            $where = $this->expression($query->where);
            $sql .= " WHERE $where";
        }

        if (isset($query->order)) {
            $keys = array_keys($query->order);
            $values = array_values($query->order);
            $order = array_map(fn($f, $t) => "$q$f$q " . ($t === "asc" ? static::$asc : static::$desc), $keys, $values);
            $sql .= " ORDER BY " . implode(",", $order);
        }

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
        $q = static::$fieldQuote;
        $sql = "INSERT INTO " . $q . $query->table . $q;

        if (isset($query->values)) {
            $values = implode(",", $query->values);
            $sql .= " VALUES ($values)";
        }

        return $sql;
    }

    /** @inheritdoc */
    protected function update(Update $query): string {
        $q = static::$fieldQuote;
        $sql = "UPDATE " . implode(",", array_map(fn($t) => "$q$t$q", $query->tables));

        if (isset($query->values)) {
            $keys = array_keys($query->values);
            $values = array_values($query->values);
            $values = implode(", ", array_map(fn($key, $value) => "$key=$value", $keys, $values));
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
        $q = static::$fieldQuote;
        $sql = "DELETE FROM " . implode(",", array_map(fn($t) => "$q$t$q", $query->tables));

        if (isset($query->where)) {
            $where = $this->expression($query->where);
            $sql .= " WHERE $where";
        }

        return $sql;
    }

    /** @inheritdoc */
    protected function create(Create $query): string {
    }

    /** @inheritdoc */
    protected function drop(Drop $query): string {
    }

    protected function expression(Ex $ex): string {
        $q = static::$fieldQuote;

        switch ($ex->type) {
            case "and":
                $inner = array_map(fn($ex) => $this->expression($ex), $ex->props['ex']);
                return "(" . implode(") " . static::$and . " (", $inner) . ")";
            case "or":
                $inner = array_map(fn($ex) => $this->expression($ex), $ex->props['ex']);
                return "(" . implode(") " . static::$or . " (", $inner) . ")";
            case "not":
                return static::$not . " (" . $this->expression($ex->props['ex']) . ")";
            case "lt":
            case "le":
            case "eq":
            case "ge":
            case "gt":
            case "ne":
            case "like":
                $type = $ex->type;
                return $q . $ex->props['field'] . $q . static::$$type . $ex->props['value'];
            case "in":
                $values = implode(",", $ex->props['value']);
                return $q . $ex->props['field'] . $q . " " . static::$in . " ($values)";
            case "exists":
                return static::$exists . " (" . $this->generate($ex->props["query"]) . ")";
            default:
                throw new InvalidArgumentException("Unknown expression type");
        }
    }

    protected function operation(Op $op): string {
        $q = static::$fieldQuote;

        $operation = match ($op->type) {
            "min" => "MIN",
            "max" => "MAX",
            "avg" => "AVG",
            "count" => "COUNT",
            default => throw new InvalidArgumentException("Unknown operation type")
        };

        $field = $op->field === "*" ? $op->field : $q . $op->field . $q;

        return "$operation($field)";
    }

}
