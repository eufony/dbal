<?php
/*
 * Testsuite for the Eufony ORM Package
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

require __DIR__ . "/../vendor/autoload.php";

echo "\n";
echo "Testsuite for the Eufony ORM Package by Alpin Gencer and contributors.\n";
echo "Using php.ini file: '" . php_ini_loaded_file() . "'\n";
echo "\n";

// Assert that the following php.ini settings are set correctly
$php_ini_settings = [
    'xdebug.mode' => 'coverage',
];

foreach ($php_ini_settings as $setting => $expected_value) {
    if (ini_get($setting) === $expected_value) continue;
    throw new Exception("The php.ini setting '$setting' must have a value of '$expected_value'");
}

unset($php_ini_settings);
