<?php
$config = require __DIR__ . '/console.php';
$config['components']['db'] = require __DIR__ . '/test_db.php';

return $config;
