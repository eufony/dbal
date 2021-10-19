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

namespace Eufony\DBAL\Query\Clause;

use Eufony\DBAL\Query\Keyword\Ex;

trait WhereClauseTrait {

    protected Ex $where;

    public function where(Ex $expression): static {
        $this->where = $this->extractContext($expression);
        return $this;
    }

    private function extractContext(Ex $ex): Ex {
        switch ($ex['type']) {
            case "and":
            case "or":
                $ex['props']['ex'] = array_map(fn($ex) => $this->extractContext($ex), $ex['props']['ex']);
                return $ex;
            case "not":
                $ex['props']['ex'] = $this->extractContext($ex['props']['ex']);
                return $ex;
            case "lt":
            case "le":
            case "eq":
            case "ge":
            case "gt":
            case "ne":
            case "like":
            case "in":
                $placeholder = hash("md5", uniqid(more_entropy: true));
                $this->context[$placeholder] = $ex['props']['value'];
                $ex['props']['value'] = ":" . $placeholder;
                return $ex;
            default:
                return $ex;
        }
    }

}
