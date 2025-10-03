<?php
session_start();
if(!isset($_SESSION['unique_id'])) exit();

include_once "config.php";

$user_id = mysqli_real_escape_string($conn, $_GET['user_id'] ?? 0);
$sql = mysqli_query($conn, "SELECT status FROM users WHERE unique_id = {$user_id}");
if(mysqli_num_rows($sql) > 0){
    $row = mysqli_fetch_assoc($sql);
    echo $row['status'];
}
?>
