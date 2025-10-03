<?php
session_start();
if(!isset($_SESSION['unique_id'])){
    exit("You must be logged in.");
}

include_once "config.php";

$current_id = (int)$_SESSION['unique_id'];
$output = "";

// Fetch all users except current
$users_sql = mysqli_query($conn, "SELECT * FROM users WHERE unique_id != {$current_id} ORDER BY fname ASC") or die(mysqli_error($conn));

if(mysqli_num_rows($users_sql) > 0){
    while($user = mysqli_fetch_assoc($users_sql)){

        // Count unread messages
        $unread_count = 0;
        $unread_html = '';

        $unread_sql = mysqli_query($conn, 
            "SELECT COUNT(*) AS unread_count 
             FROM messages 
             WHERE outgoing_msg_id = {$user['unique_id']} 
               AND incoming_msg_id = {$current_id} 
               AND msg_status = 0"
        );
        if($unread_sql){
            $unread_count = (int)mysqli_fetch_assoc($unread_sql)['unread_count'];
            $unread_html = $unread_count > 0 ? '<span class="unread">'.$unread_count.'</span>' : '';
        }

        // Active class if user is online
        $active_class = strtolower($user['status']) === "active now" ? "active" : "";

        $output .= '
        <a href="chat.php?user_id='. (int)$user['unique_id'] .'" class="'. $active_class .'" data-userid="'. (int)$user['unique_id'] .'">
            <div class="content">
                <img src="php/images/'. htmlspecialchars($user['img'], ENT_QUOTES, 'UTF-8') .'" alt="">
                <div class="details">
                    <span>'. htmlspecialchars($user['fname'] . " " . $user['lname'], ENT_QUOTES, 'UTF-8') .' '. $unread_html .'</span>
                    <p>'. htmlspecialchars($user['status'], ENT_QUOTES, 'UTF-8') .'</p>
                </div>
            </div>
        </a>';
    }
}else{
    $output = '<p class="text">No users are available to chat</p>';
}

echo $output;
?>
