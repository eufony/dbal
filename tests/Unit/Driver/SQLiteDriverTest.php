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

namespace Eufony\DBAL\Tests\Unit\Driver;

use Eufony\DBAL\Driver\DriverInterface;
use Eufony\DBAL\Driver\SQLiteDriver;

class SQLiteDriverTest extends AbstractDriverTest
{
    /**
     * @inheritDoc
     */
    public function getDriver(): DriverInterface
    {
        return new SQLiteDriver(":memory:");
    }

    /**
     * @inheritDoc
     */
    public function queryStrings(): array
    {
        return [
            <<< SQL
                SELECT * FROM "test"
                SQL,
            <<< SQL
                SELECT * FROM "foo" AS "bar"
                SQL,
            <<< SQL
                SELECT "foo" FROM "test"
                SQL,
            <<< SQL
                SELECT "foo", "bar" AS "baz" FROM "test"
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    GROUP BY "foo"
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    GROUP BY "foo", "bar", "baz"
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    INNER JOIN "b" ON "a"."id" = "b"."a_id"
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    LEFT JOIN "b" ON "a"."id" = "b"."a_id"
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    INNER JOIN "b" ON "a"."b_id" = "b"."id"
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    LEFT JOIN "b" ON "a"."b_id" = "b"."id"
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    INNER JOIN "b" ON "a"."b_id" = "b"."id"
                    INNER JOIN "c" ON "b"."c_id" = "c"."id"
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    LEFT JOIN "b" ON "a"."b_id" = "b"."id"
                    LEFT JOIN "c" ON "b"."c_id" = "c"."id"
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    INNER JOIN "b" AS "b1" ON "a"."b1_id" = "b1"."id"
                    INNER JOIN "b" AS "b2" ON "a"."b2_id" = "b2"."id"
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    LEFT JOIN "b" AS "b1" ON "a"."b1_id" = "b1"."id"
                    LEFT JOIN "b" AS "b2" ON "a"."b2_id" = "b2"."id"
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    INNER JOIN "a" AS "a2" ON "a"."a2_id" = "a2"."id"
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    LEFT JOIN "a" AS "a2" ON "a"."a2_id" = "a2"."id"
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    LIMIT 5
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    LIMIT 6 OFFSET 1
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    ORDER BY "foo" ASC
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    ORDER BY "foo" ASC
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    ORDER BY "foo" DESC
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    ORDER BY "foo" ASC, "bar" ASC
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    ORDER BY "foo" ASC, "bar" DESC
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    ORDER BY "foo" DESC, "bar" DESC
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE 1 = 1
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE NOT (1 = 1)
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE (1 = 1) AND (1 = 1) AND (1 = 1)
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE (1 = 1) OR (1 = 1) OR (1 = 1)
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "primary" = "foreign"
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "neither" LIKE ?
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "left" LIKE ?
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "right" LIKE ?
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "both" LIKE ?
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE EXISTS (SELECT * FROM "test")
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "zero" < ?
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "big_int_negative" < ?
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "big_int_positive" < ?
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "float_zero" < ?
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "small_float" < ?
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "big_float" < ?
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "primary" < "foreign"
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "zero" <= ?
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "big_int_negative" <= ?
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "big_int_positive" <= ?
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "float_zero" <= ?
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "small_float" <= ?
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "big_float" <= ?
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "primary" <= "foreign"
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "zero" >= ?
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "big_int_negative" >= ?
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "big_int_positive" >= ?
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "float_zero" >= ?
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "small_float" >= ?
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "big_float" >= ?
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "primary" >= "foreign"
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "zero" > ?
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "big_int_negative" > ?
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "big_int_positive" > ?
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "float_zero" > ?
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "small_float" > ?
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "big_float" > ?
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "primary" > "foreign"
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "empty_string" = ?
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "string" = ?
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "long_string" = ?
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "zero" = ?
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "big_int_negative" = ?
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "big_int_positive" = ?
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "float_zero" = ?
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "small_float" = ?
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "big_float" = ?
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "true" = ?
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "false" = ?
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "null" IS NULL
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "serializable" = ?
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "empty_string" <> ?
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "string" <> ?
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "long_string" <> ?
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "zero" <> ?
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "big_int_negative" <> ?
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "big_int_positive" <> ?
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "float_zero" <> ?
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "small_float" <> ?
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "big_float" <> ?
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "true" <> ?
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "false" <> ?
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "null" IS NOT NULL
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "serializable" <> ?
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "array" IN (?, ?, ?)
                SQL,
            <<< SQL
                SELECT * FROM "test"
                    WHERE "associative_array" IN (?)
                SQL,
            <<< SQL
                INSERT INTO "test" ("empty_string") VALUES (?)
                SQL,
            <<< SQL
                INSERT INTO "test" ("string") VALUES (?)
                SQL,
            <<< SQL
                INSERT INTO "test" ("long_string") VALUES (?)
                SQL,
            <<< SQL
                INSERT INTO "test" ("zero") VALUES (?)
                SQL,
            <<< SQL
                INSERT INTO "test" ("big_int_negative") VALUES (?)
                SQL,
            <<< SQL
                INSERT INTO "test" ("big_int_positive") VALUES (?)
                SQL,
            <<< SQL
                INSERT INTO "test" ("float_zero") VALUES (?)
                SQL,
            <<< SQL
                INSERT INTO "test" ("small_float") VALUES (?)
                SQL,
            <<< SQL
                INSERT INTO "test" ("big_float") VALUES (?)
                SQL,
            <<< SQL
                INSERT INTO "test" ("true") VALUES (?)
                SQL,
            <<< SQL
                INSERT INTO "test" ("false") VALUES (?)
                SQL,
            <<< SQL
                INSERT INTO "test" ("null") VALUES (?)
                SQL,
            <<< SQL
                INSERT INTO "test" ("serializable") VALUES (?)
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                SQL,
            <<< SQL
                UPDATE "test" SET "string" = ?
                SQL,
            <<< SQL
                UPDATE "test" SET "long_string" = ?
                SQL,
            <<< SQL
                UPDATE "test" SET "zero" = ?
                SQL,
            <<< SQL
                UPDATE "test" SET "big_int_negative" = ?
                SQL,
            <<< SQL
                UPDATE "test" SET "big_int_positive" = ?
                SQL,
            <<< SQL
                UPDATE "test" SET "float_zero" = ?
                SQL,
            <<< SQL
                UPDATE "test" SET "small_float" = ?
                SQL,
            <<< SQL
                UPDATE "test" SET "big_float" = ?
                SQL,
            <<< SQL
                UPDATE "test" SET "true" = ?
                SQL,
            <<< SQL
                UPDATE "test" SET "false" = ?
                SQL,
            <<< SQL
                UPDATE "test" SET "null" = ?
                SQL,
            <<< SQL
                UPDATE "test" SET "serializable" = ?
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE 1 = 1
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE NOT (1 = 1)
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE (1 = 1) AND (1 = 1) AND (1 = 1)
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE (1 = 1) OR (1 = 1) OR (1 = 1)
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "primary" = "foreign"
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "neither" LIKE ?
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "left" LIKE ?
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "right" LIKE ?
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "both" LIKE ?
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE EXISTS (SELECT * FROM "test")
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "zero" < ?
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "big_int_negative" < ?
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "big_int_positive" < ?
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "float_zero" < ?
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "small_float" < ?
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "big_float" < ?
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "primary" < "foreign"
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "zero" <= ?
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "big_int_negative" <= ?
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "big_int_positive" <= ?
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "float_zero" <= ?
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "small_float" <= ?
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "big_float" <= ?
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "primary" <= "foreign"
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "zero" >= ?
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "big_int_negative" >= ?
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "big_int_positive" >= ?
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "float_zero" >= ?
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "small_float" >= ?
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "big_float" >= ?
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "primary" >= "foreign"
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "zero" > ?
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "big_int_negative" > ?
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "big_int_positive" > ?
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "float_zero" > ?
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "small_float" > ?
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "big_float" > ?
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "primary" > "foreign"
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "empty_string" = ?
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "string" = ?
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "long_string" = ?
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "zero" = ?
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "big_int_negative" = ?
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "big_int_positive" = ?
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "float_zero" = ?
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "small_float" = ?
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "big_float" = ?
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "true" = ?
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "false" = ?
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "null" IS NULL
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "serializable" = ?
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "empty_string" <> ?
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "string" <> ?
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "long_string" <> ?
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "zero" <> ?
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "big_int_negative" <> ?
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "big_int_positive" <> ?
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "float_zero" <> ?
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "small_float" <> ?
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "big_float" <> ?
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "true" <> ?
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "false" <> ?
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "null" IS NOT NULL
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "serializable" <> ?
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "array" IN (?, ?, ?)
                SQL,
            <<< SQL
                UPDATE "test" SET "empty_string" = ?
                    WHERE "associative_array" IN (?)
                SQL,
            <<< SQL
                DELETE FROM "test"
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE 1 = 1
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE NOT (1 = 1)
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE (1 = 1) AND (1 = 1) AND (1 = 1)
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE (1 = 1) OR (1 = 1) OR (1 = 1)
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "primary" = "foreign"
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "neither" LIKE ?
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "left" LIKE ?
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "right" LIKE ?
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "both" LIKE ?
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE EXISTS (SELECT * FROM "test")
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "zero" < ?
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "big_int_negative" < ?
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "big_int_positive" < ?
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "float_zero" < ?
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "small_float" < ?
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "big_float" < ?
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "primary" < "foreign"
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "zero" <= ?
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "big_int_negative" <= ?
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "big_int_positive" <= ?
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "float_zero" <= ?
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "small_float" <= ?
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "big_float" <= ?
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "primary" <= "foreign"
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "zero" >= ?
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "big_int_negative" >= ?
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "big_int_positive" >= ?
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "float_zero" >= ?
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "small_float" >= ?
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "big_float" >= ?
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "primary" >= "foreign"
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "zero" > ?
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "big_int_negative" > ?
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "big_int_positive" > ?
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "float_zero" > ?
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "small_float" > ?
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "big_float" > ?
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "primary" > "foreign"
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "empty_string" = ?
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "string" = ?
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "long_string" = ?
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "zero" = ?
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "big_int_negative" = ?
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "big_int_positive" = ?
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "float_zero" = ?
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "small_float" = ?
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "big_float" = ?
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "true" = ?
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "false" = ?
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "null" IS NULL
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "serializable" = ?
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "empty_string" <> ?
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "string" <> ?
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "long_string" <> ?
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "zero" <> ?
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "big_int_negative" <> ?
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "big_int_positive" <> ?
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "float_zero" <> ?
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "small_float" <> ?
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "big_float" <> ?
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "true" <> ?
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "false" <> ?
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "null" IS NOT NULL
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "serializable" <> ?
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "array" IN (?, ?, ?)
                SQL,
            <<< SQL
                DELETE FROM "test"
                    WHERE "associative_array" IN (?)
                SQL,
        ];
    }
}
