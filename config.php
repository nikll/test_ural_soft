<?

$dbServer   = '127.0.0.1';
$dbPort     = '3306';
$dbName     = 'forum';
$dbUser     = 'root';
$dbPassword = '1';

ini_set('error_log', !empty($env['error_log']) ? $env['error_log'] : ROOT_PATH.'logs/php_errors.log');
ini_set('display_errors', true);
