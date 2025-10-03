<?php
session_start();

if(isset($_SESSION['unique_id'])) {

    include_once "config.php";

    $outgoing_id = (int)$_POST['outgoing_id'];
    $incoming_id = (int)$_POST['incoming_id'];
    $last_msg_id = isset($_POST['last_msg_id']) ? (int)$_POST['last_msg_id'] : 0;

    // 1️⃣ Mark all incoming messages from this user as read
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

    if(mysqli_num_rows($query) > 0) {
        while($row = mysqli_fetch_assoc($query)) {
            $msgId = (int)$row['msg_id'];
            $isOutgoing = ($row['outgoing_msg_id'] == $outgoing_id);

            $time_display = isset($row['msg_time']) ? '<span class="msg-time">'.date("h:i a", strtotime($row['msg_time'])).'</span>' : '';

            if($isOutgoing) {
                $output .= '<div class="chat outgoing" data-msg-id="'. $msgId .'">
                                <div class="details">
                                    <img src="php/images/'. htmlspecialchars($row['img'], ENT_QUOTES) .'" alt="">
                                    <p>'. htmlspecialchars($row['msg'], ENT_QUOTES) .' '. $time_display .'</p>
                                </div>
                            </div>';
            } else {
                $newClass = $msgId > $last_msg_id ? ' new-message' : '';
                $output .= '<div class="chat incoming'. $newClass .'" data-msg-id="'. $msgId .'">
                                <img src="php/images/'. htmlspecialchars($row['img'], ENT_QUOTES) .'" alt="">
                                <div class="details">
                                    <p>'. htmlspecialchars($row['msg'], ENT_QUOTES) .' '. $time_display .'</p>
                                </div>
                            </div>';
            }
        }
    } else {
        $output = '<div class="text">No messages are available</div>';
    }

    echo $output;

} else {
    header("location: ../login.php");
}
?>
