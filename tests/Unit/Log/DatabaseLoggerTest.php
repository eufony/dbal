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

namespace Eufony\ORM\Tests\Unit\Log;

use Eufony\ORM\DBAL\Connection;
use Eufony\ORM\Log\DatabaseLogger;
use Mockery;
use Psr\Log\LoggerInterface;

/**
 * Unit tests for `\Eufony\ORM\Log\DatabaseLogger`.
 */
class DatabaseLoggerTest extends AbstractLogTest
{
    use LoggerTraitTestTrait;

    /**
     * The internal mock `Connection` object used to test the `DatabaseLogger`.
     *
     * @var \Eufony\ORM\DBAL\Connection $internalDatabase
     */
    protected Connection $internalDatabase;

    /**
     * @inheritDoc
     */
    public function getLogger(): LoggerInterface
    {
        $this->internalDatabase = Mockery::mock(Connection::class);
        return new DatabaseLogger($this->internalDatabase);
    }

    public function testGetInternalDatabase()
    {
        $this->assertSame($this->internalDatabase, $this->logger->database());
    }
}
