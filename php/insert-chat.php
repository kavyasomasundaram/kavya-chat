<?php
session_start();

if(isset($_SESSION['unique_id'])) {

    include_once "config.php";

    $outgoing_id = (int)$_POST['outgoing_id'];
    $incoming_id = (int)$_POST['incoming_id'];
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    if(!empty($message)) {
        // 1️⃣ Insert the new message
        $sql = mysqli_query($conn, 
            "INSERT INTO messages (incoming_msg_id, outgoing_msg_id, msg, msg_time, msg_status)
             VALUES ({$incoming_id}, {$outgoing_id}, '{$message}', NOW(), 0)"
        ) or die(mysqli_error($conn));

        if($sql) {
            $msg_id = mysqli_insert_id($conn);

            // 2️⃣ Fetch sender image for outgoing message
            $user_sql = mysqli_query($conn, "SELECT img FROM users WHERE unique_id = {$outgoing_id}") or die(mysqli_error($conn));
            $user = mysqli_fetch_assoc($user_sql);
            $img = htmlspecialchars($user['img'], ENT_QUOTES, 'UTF-8');

            // 3️⃣ Mark all incoming messages from this user as read
            mysqli_query($conn, 
                "UPDATE messages SET msg_status = 1 
                 WHERE outgoing_msg_id = {$incoming_id} 
                   AND incoming_msg_id = {$outgoing_id} 
                   AND msg_status = 0"
            );

            // 4️⃣ Prepare HTML for outgoing message
            $msg_html = '
            <div class="chat outgoing" data-msg-id="'. $msg_id .'">
                <div class="details">
                    <img src="php/images/'.$img.'" alt="">
                    <p>'.htmlspecialchars($message, ENT_QUOTES, 'UTF-8').' 
                        <span class="msg-time">'.date("h:i a").'</span>
                    </p>
                </div>
            </div>';

            echo $msg_html;
        }
    }

} else {
    header("location: ../login.php");
}
?>
