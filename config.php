<?php
return [
    'db_driver' => 'sqlite', // change to 'mysql' to use MySQL

    // SQLite settings
    'sqlite_path' => __DIR__ . '/data/questions.db',

    // MySQL settings
    'mysql_host' => '127.0.0.1',
    'mysql_db' => 'mocktest',
    'mysql_user' => 'root',
    'mysql_pass' => '',
    'mysql_charset' => 'utf8mb4',
];
