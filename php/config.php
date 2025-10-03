<?php
$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');
$db   = getenv('DB_NAME');
$port = getenv('DB_PORT');

$ca_cert_path = __DIR__ . "/certs/ca.pem";

$conn = mysqli_init();
mysqli_ssl_set($conn, NULL, NULL, $ca_cert_path, NULL, NULL);
mysqli_real_connect($conn, $host, $user, $pass, $db, $port, NULL, MYSQLI_CLIENT_SSL);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
} else {
    echo "Connected successfully with SSL!";
}
?>
