<?php
$db = require __DIR__ . '/db.php';
// test database! Important not to run tests on production or development databases
$db['dsn'] = 'mysql:host=192.168.1.19;dbname=yii2basic_test';

return $db;
