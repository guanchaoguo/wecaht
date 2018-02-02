<?php
return [
    'driver' => env('MAIL_DRIVER', 'smtp'),
    'host' => env('MAIL_HOST', 'smtp.exmail.qq.com'),
    'port' => env('MAIL_PORT', 465),
    'from' => ['address' => 'user_service@34era.com', 'name' => 'user_service@34era.com'],
    'encryption' => env('MAIL_ENCRYPTION', 'ssl'),
    'username' => env('MAIL_USERNAME','user_service@34era.com'),
    'password' => env('MAIL_PASSWORD','Us20171818@'),
    'sendmail' => '/usr/sbin/sendmail -bs',
    'pretend' => false,
];