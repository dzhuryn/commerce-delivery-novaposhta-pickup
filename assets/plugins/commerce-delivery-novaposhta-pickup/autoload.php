<?php

spl_autoload_register(function ($class) {
    static $classes = null;

    if (strpos($class, 'CommerceDeliveryNpPickup\\') === 0) {
        $parts = explode('\\', $class);
        array_shift($parts);
        
        $filename = __DIR__ . '/src/' . implode('/', $parts) . '.php';

        if (is_readable($filename)) {
            require $filename;
        }
    }
}, true);
