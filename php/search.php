<?php
session_start();
include_once "config.php";

$outgoing_id = $_SESSION['unique_id'] ?? 0;
$searchTerm = mysqli_real_escape_string($conn, $_POST['searchTerm'] ?? '');
$output = "";

if (!empty($searchTerm)) {
    $sql = mysqli_query($conn, "SELECT * FROM users 
                                WHERE unique_id != {$outgoing_id} 
                                AND (fname LIKE '%{$searchTerm}%' OR lname LIKE '%{$searchTerm}%')") 
            or die(mysqli_error($conn));

    if (mysqli_num_rows($sql) > 0) {
        while ($row = mysqli_fetch_assoc($sql)) {
            $output .= '
            <a href="chat.php?user_id='. $row['unique_id'] .'">
                <div class="content">
                    <img src="php/images/'. htmlspecialchars($row['img'], ENT_QUOTES, 'UTF-8') .'" alt="">
                    <div class="details">
                        <span>'. htmlspecialchars($row['fname'] . " " . $row['lname'], ENT_QUOTES, 'UTF-8') .'</span>
                        <p>'. htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8') .'</p>
                    </div>
                </div>
            </a>';
        }
    } else {
        $output .= "No user found related to your search term";
    }
} else {
    $output .= "Please enter a search term";
}

echo $output;
?>
