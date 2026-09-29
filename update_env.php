<?php
$env = file_get_contents('.env');
$env = preg_replace('/DB_CONNECTION=pgsql/', 'DB_CONNECTION=sqlite', $env);
file_put_contents('.env', $env);
touch(database_path('database.sqlite'));
