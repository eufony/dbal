<?php

echo "\n";
echo "Unit tests for the Eufony ORM Package by Alpin Gencer.\n";
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
