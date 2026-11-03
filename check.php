<?php
header('Content-Type: text/plain; charset=utf-8');
echo 'date.timezone        = ', ini_get('date.timezone'), PHP_EOL;
echo 'display_errors       = ', ini_get('display_errors'), PHP_EOL;
echo 'log_errors           = ', ini_get('log_errors'), PHP_EOL;
echo 'error_log            = ', ini_get('error_log'), PHP_EOL;
echo 'upload_max_filesize  = ', ini_get('upload_max_filesize'), PHP_EOL;
echo 'post_max_size        = ', ini_get('post_max_size'), PHP_EOL;
echo 'session.cookie_httponly = ', ini_get('session.cookie_httponly'), PHP_EOL;
echo 'session.use_strict_mode = ', ini_get('session.use_strict_mode'), PHP_EOL;
echo 'PHP loaded ini       = ', php_ini_loaded_file(), PHP_EOL;