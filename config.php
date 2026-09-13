<?php
// Railway / Docker environment configuration
// Values are read from environment variables first, with the original placeholders
// kept as a fallback for traditional installations.
$request_exec_timeout = null;

function env_value(string $key, string $fallback = ''): string
{
    $value = getenv($key);
    return ($value !== false && trim((string)$value) !== '') ? trim((string)$value) : $fallback;
}

$dbhost = env_value('DB_HOST', env_value('MYSQLHOST', '{database_url}'));
$dbname = env_value('DB_NAME', env_value('MYSQLDATABASE', '{database_name}'));
$usernamedb = env_value('DB_USER', env_value('MYSQLUSER', '{username_db}'));
$passworddb = env_value('DB_PASSWORD', env_value('MYSQLPASSWORD', '{password_db}'));
$dbport = env_value('DB_PORT', env_value('MYSQLPORT', '3306'));

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
];

$dsn = "mysql:host=$dbhost;port=$dbport;dbname=$dbname;charset=utf8mb4";
try {
    $pdo = new PDO($dsn, $usernamedb, $passworddb, $options);
} catch (\PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("error: database connection failed");
}

$APIKEY = env_value('API_KEY', env_value('BOT_TOKEN', '{API_KEY}'));
$adminnumber = env_value('ADMIN_NUMBER', env_value('ADMIN_ID', '{admin_number}'));
$domainhosts = env_value('DOMAIN_NAME', env_value('RAILWAY_PUBLIC_DOMAIN', '{domain_name}'));
$usernamebot = env_value('USERNAME_BOT', env_value('BOT_USERNAME', '{username_bot}'));

// Railway usually provides RAILWAY_PUBLIC_DOMAIN without the scheme.
$domainhosts = preg_replace('#^https?://#', '', trim($domainhosts));
$domainhosts = rtrim($domainhosts, '/');

?>
