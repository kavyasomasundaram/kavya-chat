<?php
session_start();
if(!isset($_SESSION['unique_id'])) exit;

include_once "config.php";

$current_id = $_SESSION['unique_id'];

$users_sql = mysqli_query($conn, "SELECT unique_id, status FROM users WHERE unique_id != {$current_id}") or die(mysqli_error($conn));

$result = [];

while($user = mysqli_fetch_assoc($users_sql)){
    $uid = $user['unique_id'];

    $unread_sql = mysqli_query($conn, "SELECT COUNT(*) AS unread_count 
        FROM messages 
        WHERE outgoing_msg_id={$uid} 
        AND incoming_msg_id={$current_id} 
        AND msg_status=0");
    $row = mysqli_fetch_assoc($unread_sql);

    $result[$uid] = [
        "unread" => (int)$row['unread_count'],
        "status" => $user['status']
    ];
}

echo json_encode($result);
