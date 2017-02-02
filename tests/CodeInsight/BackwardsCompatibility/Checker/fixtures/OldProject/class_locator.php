<?php
$base_path = __DIR__;

return function ($class) use ($base_path) {
	return $base_path . '/example.php';
};
