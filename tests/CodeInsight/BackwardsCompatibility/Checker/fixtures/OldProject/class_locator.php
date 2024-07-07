<?php
$base_path = __DIR__;

return function ($class) use ($base_path) {
    if (strpos($class, '\\') !== false) {
        return $base_path . '/example_ns.php';
	}

    return $base_path . '/example.php';
};
