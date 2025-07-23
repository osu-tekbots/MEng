<?php
// Define an autoloader for custom PHP classes so we don't have to
// include their files manually. The autoloader should check for the
// repository specific classes.
spl_autoload_register(function ($className) {
    $phpFile = str_replace('\\', '/', $className) . '.php';
    $local = PUBLIC_FILES . '/lib/classes/' . $phpFile;
    if(file_exists($local)) {
        include $local;
        return true;
    }
    return false;
});