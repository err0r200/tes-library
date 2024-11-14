<?php
/**
 * This file is part of the SetaPDF package
 *
 * @copyright  Copyright (c) 2024 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @package    SetaPDF
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id: Autoload.php 1926 2024-03-13 15:33:09Z jan.slabon $
 */

spl_autoload_register(static function ($class) {
    if (strpos($class, 'SetaPDF_') === 0) {
        $filename = str_replace('_', DIRECTORY_SEPARATOR, substr($class, 8)) . '.php';
        $fullpath = __DIR__ . DIRECTORY_SEPARATOR . $filename;

        if (file_exists($fullpath)) {
            /** @noinspection PhpIncludeInspection */
            require_once $fullpath;
        }
    }
});