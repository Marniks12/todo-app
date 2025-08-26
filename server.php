<?php
$port = getenv('PORT') ?: 3000; // fallback voor lokaal
$host = '0.0.0.0';

// Start PHP built-in server
exec("php -S $host:$port -t public");
