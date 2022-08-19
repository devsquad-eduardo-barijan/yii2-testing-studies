<?php

return [
    'admin' => [
        'id' => 1,
        'username' => 'admin',
        'password' => Yii::$app->getSecurity()->generatePasswordHash('admin'),
        'authKey' => 'valid authkey'
    ]
];
