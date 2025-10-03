<?php
if(session_status() == PHP_SESSION_NONE){
    session_start();
}

if(!isset($_SESSION['unique_id'])){
    header("location: ../login.php");
    exit;
}

include_once "config.php";

if(isset($_POST['outgoing_id'], $_POST['incoming_id'])){
    $outgoing_id = (int)$_POST['outgoing_id'];
    $incoming_id = (int)$_POST['incoming_id'];
} else {
    echo "Error: Missing data";
    exit;
}

// 1️⃣ Mark all incoming messages as read
mysqli_query($conn, 
    "UPDATE messages 
     SET msg_status = 1 
     WHERE outgoing_msg_id = {$incoming_id} 
       AND incoming_msg_id = {$outgoing_id} 
       AND msg_status = 0"
);

$output = "";

// 2️⃣ Fetch all messages between the two users
$sql = "SELECT messages.*, users.img, users.fname, users.lname
        FROM messages 
        LEFT JOIN users ON users.unique_id = messages.outgoing_msg_id
        WHERE (outgoing_msg_id = {$outgoing_id} AND incoming_msg_id = {$incoming_id})
           OR (outgoing_msg_id = {$incoming_id} AND incoming_msg_id = {$outgoing_id}) 
        ORDER BY msg_id ASC";

$query = mysqli_query($conn, $sql);

if(mysqli_num_rows($query) > 0){
    while($row = mysqli_fetch_assoc($query)){

        // Format timestamp
        $time_display = '';
        if(isset($row['msg_time'])){
            $time_display = '<span class="msg-time">'.date("h:i a", strtotime($row['msg_time'])).'</span>';
        }

        // Determine outgoing or incoming
        if($row['outgoing_msg_id'] === $outgoing_id){  // sender
            $output .= '<div class="chat outgoing" data-msg-id="'. $row['msg_id'] .'">
                            <div class="details">
                                <img src="php/images/'. htmlspecialchars($row['img'], ENT_QUOTES) .'" alt="">
                                <p>'. htmlspecialchars($row['msg'], ENT_QUOTES, 'UTF-8') .' '. $time_display .'</p>
                            </div>
                        </div>';
        } else {  // receiver
            $output .= '<div class="chat incoming" data-msg-id="'. $row['msg_id'] .'">
                            <img src="php/images/'. htmlspecialchars($row['img'], ENT_QUOTES) .'" alt="">
                            <div class="details">
                                <p>'. htmlspecialchars($row['msg'], ENT_QUOTES, 'UTF-8') .' '. $time_display .'</p>
                            </div>
                        </div>';
        }
    }

    echo $output;

} else {
    echo '<div class="text">No messages are available</div>';
}
?>
