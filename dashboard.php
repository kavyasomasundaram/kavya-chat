<?php
session_start();
if(!isset($_SESSION['unique_id'])){
    header("location: login.php");
    exit();
}

include_once "php/config.php";

$current_id = $_SESSION['unique_id'];

// Fetch current user info
$current_sql = mysqli_query($conn, "SELECT * FROM users WHERE unique_id={$current_id}") or die(mysqli_error($conn));
$current_user = mysqli_fetch_assoc($current_sql);

// Check if chat user is selected
$chat_user_id = intval($_GET['user_id'] ?? 0);
if($chat_user_id){
    $chat_sql = mysqli_query($conn, "SELECT * FROM users WHERE unique_id={$chat_user_id}") or die(mysqli_error($conn));
    $chat_user = mysqli_fetch_assoc($chat_sql);

    // Mark messages as read
    mysqli_query($conn, "UPDATE messages SET msg_status=1 WHERE outgoing_msg_id={$chat_user_id} AND incoming_msg_id={$current_id}");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard</title>
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Poppins,sans-serif;}
body{background:#f7f7f7;color:#000;height:100vh;display:flex;transition:0.3s;overflow:hidden;}
.wrapper{display:flex;width:100%;height:100vh;}

/* Left panel */
.users-panel{width:300px;background:#fff;display:flex;flex-direction:column;box-shadow:2px 0 8px rgba(0,0,0,0.1);}
.users-panel header{display:flex;align-items:center;padding:10px;border-bottom:1px solid #ccc;}
.users-panel header img{width:35px;height:35px;border-radius:50%;}
.users-panel .details{margin-left:10px;}
.users-panel .details span{font-weight:bold;font-size:14px;}
.users-panel .details p{font-size:11px;color:#888;margin-top:2px;}
.users-panel .logout{margin-left:auto;background:#333;color:#fff;padding:4px 8px;font-size:12px;border:none;border-radius:5px;cursor:pointer;}
.users-panel .search{padding:10px;position:relative;}
.users-panel .search input{width:100%;padding:6px 10px;border-radius:5px;border:1px ;background: #e0e0e0;outline:none;}
.users-list{flex:1;overflow-y:auto;}
.users-list a{display:flex;align-items:center;padding:8px 10px;text-decoration:none;color:#000;border-bottom:1px solid #eee;position:relative;transition:0.2s;}
.users-list a:hover{background:#f1f1f1;}
.users-list a img{width:35px;height:35px;border-radius:50%;margin-right:10px;}
.users-list .details span{font-weight:bold;font-size:13px;}
.users-list .details p{font-size:11px;color:#888;margin:0;}
.unread{background:#e0e0e0;color:#0e0e0e;border-radius:50%;padding:2px 6px;font-size:10px;margin-left:5px;}

/* Right panel */
.chat-panel{flex:1;display:flex;flex-direction:column;background:#fff;}
.chat-header{display:flex;align-items:center;padding:10px;border-bottom:1px solid #ccc;}
.chat-header img{width:35px;height:35px;border-radius:50%;margin:0 10px;}
.chat-header .details span{font-weight:bold;font-size:14px;}
.chat-header .details p{font-size:11px;color:#888;margin:0;}
.back-icon i{font-size:16px;color:#000;margin-right:8px;cursor:pointer;}

/* Chat messages */
.chat-box{flex:1;overflow-y:auto;padding:10px;background:#f7f7f7;display:flex;flex-direction:column;}
.chat-box .chat{margin-bottom:10px;display:flex;align-items:flex-end;max-width:100%;}

/* Outgoing messages */
.chat-box .outgoing{
    align-self:flex-end;
    display:flex;
    flex-direction:row-reverse; /* profile right */
    max-width:100%;
}
.chat-box .outgoing img{
    width:35px;
    height:35px;
    border-radius:50%;
    margin-left:5px;
}
.chat-box .outgoing p{
    background:#333;
    color:#fff;
    padding:10px 16px;
    border-radius:15px 15px 0 15px;
    max-width:70%;
    word-wrap:break-word;
    white-space:pre-wrap;
    line-height:1.4em;
}

/* Incoming messages */
.chat-box .incoming{
    align-self:flex-start;
    display:flex;
    flex-direction:row; /* profile left */
    max-width:100%;
}
.chat-box .incoming img{
    width:35px;
    height:35px;
    border-radius:50%;
    margin-right:5px;
}
.chat-box .incoming p{
    background:#eee;
    color:#000;
    padding:10px 16px;
    border-radius:15px 15px 15px 0;
    max-width:70%;
    word-wrap:break-word;
    white-space:pre-wrap;
    line-height:1.4em;
}

/* Typing area */
.typing-area{display:flex;padding:10px;border-top:1px solid #ccc;position:relative;}
.typing-area input{flex:1;padding:12px;border-radius:5px 0 0 5px;border:1px solid #ccc;outline:none;font-size:14px;}
.typing-area button{background:#333;color:#fff;border:none;padding:0 14px;cursor:pointer;border-radius:0 5px 5px 0;margin-left:2px;position:relative;font-size:16px;display:flex;align-items:center;justify-content:center;}

/* Emoji menu */
#emoji-menu{position:absolute;bottom:50px;left:0;display:none;background:#fff;border:1px solid #ccc;flex-wrap:wrap;width:220px;max-height:180px;overflow-y:auto;padding:5px;z-index:999;}
#emoji-menu span{cursor:pointer;font-size:20px;margin:3px;}

/* Dark mode */
body.dark-mode{background:#121212;color:#e0e0e0;}
body.dark-mode .users-panel, body.dark-mode .chat-panel{background:#1e1e1e;color:#e0e0e0;}
body.dark-mode .users-list a{background:#1e1e1e;color:#e0e0e0;border-color:#333;}
body.dark-mode .users-list a:hover{background:#2a2a2a;}
body.dark-mode .chat-header, body.dark-mode .typing-area{background:#1e1e1e;color:#e0e0e0;border-color:#333;}
body.dark-mode .chat-box{background:#1a1a1a;}
body.dark-mode .chat-box .outgoing p{background:#333;color:#fff;}
body.dark-mode .chat-box .incoming p{background:#2a2a2a;color:#fff;}
body.dark-mode .back-icon i{color:#fff !important;}
body.dark-mode .typing-area input{background:#e0e0e0;color:#0e0e0e;border:1px solid #333;}
body.dark-mode .typing-area button{background:#444;color:#fff;}
body.dark-mode #emoji-menu{background:#2a2a2a;color:#fff;border-color:#555;}

/* Scrollbar */
.chat-box::-webkit-scrollbar{width:6px;}
.chat-box::-webkit-scrollbar-thumb{background:rgba(0,0,0,0.2);border-radius:3px;}
</style>
</head>
<script>
if(window.innerWidth < 768){ window.location.href = "users.php"; }
</script>
<body>
<div class="wrapper">
    <!-- Left panel -->
    <div class="users-panel">
        <header>
            <img src="php/images/<?php echo htmlspecialchars($current_user['img']); ?>" alt="">
            <div class="details">
                <span><?php echo htmlspecialchars($current_user['fname'].' '.$current_user['lname']); ?></span>
                <p><?php echo htmlspecialchars($current_user['status']); ?></p>
            </div>
            <button id="dark-mode-toggle">🌓</button>
            <a href="php/logout.php?logout_id=<?php echo $current_user['unique_id']; ?>" class="logout">Logout</a>
        </header>
        <div class="search">
            <input type="text" placeholder="Search user..." id="search-bar">
        </div>
        <div class="users-list" id="users-list">
            <?php
            $users_sql = mysqli_query($conn, "SELECT * FROM users WHERE unique_id != {$current_id} ORDER BY fname ASC") or die(mysqli_error($conn));
            if(mysqli_num_rows($users_sql) > 0){
                while($user = mysqli_fetch_assoc($users_sql)){
                    ?>
                    <a href="?user_id=<?php echo (int)$user['unique_id']; ?>">
                        <img src="php/images/<?php echo htmlspecialchars($user['img']); ?>" alt="">
                        <div class="details">
                            <span><?php echo htmlspecialchars($user['fname'].' '.$user['lname']); ?></span>
                            <p><?php echo htmlspecialchars($user['status']); ?></p>
                        </div>
                    </a>
                <?php }
            } else { echo "<p>No users available</p>"; } ?>
        </div>
    </div>

    <!-- Right panel -->
    <div class="chat-panel">
        <header class="chat-header">
            <?php if(isset($chat_user)): ?>
                <a href="?"><i class="fas fa-arrow-left back-icon"></i></a>
                <img src="php/images/<?php echo htmlspecialchars($chat_user['img']); ?>" alt="">
                <div class="details">
                    <span><?php echo htmlspecialchars($chat_user['fname'].' '.$chat_user['lname']); ?></span>
                    <p><?php echo htmlspecialchars($chat_user['status']); ?></p>
                </div>
            <?php else: ?>
                <div class="details"><span>Select a user to start chat</span></div>
            <?php endif; ?>
        </header>
        <div class="chat-box" id="chat-box"></div>
        <form class="typing-area" id="chat-form" style="position: relative;">
            <input type="hidden" name="outgoing_id" value="<?php echo $current_id; ?>">
            <input type="hidden" name="incoming_id" value="<?php echo $chat_user['unique_id'] ?? 0; ?>">
            <input type="text" name="message" id="message-input" placeholder="Type a message..." required>
            <button type="button" id="emoji-btn">😊</button>
            <button type="submit">✈️</button>
            <div id="emoji-menu">
                <?php
                $emojis = ["😀","😂","😍","😎","😭","👍","🎉","❤️","💔","😅","🤔","🥰","😇","😜","🙌","💪"];
                foreach($emojis as $e){ echo '<span>'.$e.'</span>'; }
                ?>
            </div>
        </form>
    </div>
</div>

<script>
const body = document.body;
const darkModeToggle = document.getElementById('dark-mode-toggle');
if(localStorage.getItem('dark-mode')==='enabled') body.classList.add('dark-mode');
darkModeToggle.addEventListener('click', ()=>{
    body.classList.toggle('dark-mode');
    localStorage.setItem('dark-mode', body.classList.contains('dark-mode')?'enabled':'disabled');
});

// Emoji menu
const emojiBtn = document.getElementById('emoji-btn');
const emojiMenu = document.getElementById('emoji-menu');
const messageInput = document.getElementById('message-input');
emojiMenu.querySelectorAll('span').forEach(span=>{
    span.addEventListener('click',()=>{ messageInput.value+=span.textContent; emojiMenu.style.display='none'; messageInput.focus(); });
});
emojiBtn.addEventListener('click',()=>{ emojiMenu.style.display = emojiMenu.style.display==='none'?'flex':'none'; });

// Search users
const searchBar = document.getElementById('search-bar');
const usersList = document.getElementById('users-list');
searchBar.addEventListener('input', ()=>{
    const filter = searchBar.value.toLowerCase();
    Array.from(usersList.getElementsByTagName('a')).forEach(user=>{
        const name = user.querySelector('.details span').textContent.toLowerCase();
        user.style.display = name.includes(filter)?'flex':'none';
    });
});

// Chat send & fetch
const chatForm = document.getElementById('chat-form');
const chatBox = document.getElementById('chat-box');
chatForm.addEventListener('submit', e=>{
    e.preventDefault();
    const formData = new FormData(chatForm);
    fetch('php/insert-chat.php',{method:'POST',body:formData}).then(res=>res.text()).then(()=>{
        messageInput.value='';
        chatBox.scrollTop = chatBox.scrollHeight;
    });
});

// Fetch messages
setInterval(()=>{
    const formData = new FormData(chatForm);
    fetch('php/get-chat.php',{method:'POST',body:formData}).then(res=>res.text()).then(data=>{
        chatBox.innerHTML = data;
        chatBox.scrollTop = chatBox.scrollHeight;
    });
},500);

// Fetch unread + status
setInterval(()=>{
    fetch('php/get-unread.php')
    .then(res=>res.json())
    .then(data=>{
        document.querySelectorAll('.users-list a').forEach(a=>{
            const url = new URL(a.href, window.location.href);
            const uid = url.searchParams.get('user_id');
            if(data[uid]){
                const unreadSpan = a.querySelector('.unread');
                if(data[uid].unread > 0){
                    if(unreadSpan) unreadSpan.textContent = data[uid].unread;
                    else{
                        const span = document.createElement('span');
                        span.classList.add('unread');
                        span.textContent = data[uid].unread;
                        a.querySelector('.details span').appendChild(span);
                    }
                } else if(unreadSpan) unreadSpan.remove();
                a.querySelector('.details p').textContent = data[uid].status;
            }
        });
        const incomingId = document.querySelector('input[name="incoming_id"]').value;
        if(incomingId && data[incomingId]){
            document.querySelector('.chat-header .details p').textContent = data[incomingId].status;
        }
    });
},2000);

// mark offline on close
window.addEventListener("beforeunload", function () {
    navigator.sendBeacon("php/set-offline.php");
});
</script>
</body>
</html>
