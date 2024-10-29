<?php

spl_autoload_register(function ($class) {
    // project-specific namespace prefix
    $prefix = 'CF7PA_Pay_Addons';
    $prefixNamespace = 'cf7pa_';

    // base directory for the namespace prefix
    $base_dir = CF7PA_ADDONS_PATH . '/includes/';

    // does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // no, move to the next registered autoloader
        return;
    }

    // get the relative class name
    $relative_class = strtolower(substr($class, $len));
    $relative_class = str_replace($prefixNamespace, '', $relative_class);
    $relative_class = str_replace('_', '-', $relative_class);

    // replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    // if the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});
