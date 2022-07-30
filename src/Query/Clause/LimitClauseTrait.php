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

namespace Eufony\DBAL\Query\Clause;

/**
 * Provides properties and methods for query builders that support the `LIMIT` and
 * `OFFSET` clauses.
 */
trait LimitClauseTrait
{
    /**
     * The maximum number of rows that will be returned by the query.
     *
     * @var int $limit
     */
    protected int $limit;

    /**
     * The position of the row the result set should start at.
     *
     * @var int $offset
     */
    protected int $offset;

    /**
     * Sets the number of rows the query result should be limited to.
     *
     * If only one parameter is passed, up to the specified number of rows will be
     * returned.
     * If both parameters are passed, the rows ranging from the first parameter up
     * to the last parameter (both inclusive) will be returned.
     *
     * Usually, this method should be used in conjunction with the `orderBy()`
     * method.
     *
     * @param int $x
     * @param int|null $y
     * @return $this
     */
    public function limit(int $x, ?int $y = null): static
    {
        if ($y === null) {
            $this->limit = $x;
        } else {
            $this->limit = $y - $x;
            $this->offset = $x;
        }

        return $this;
    }
}
