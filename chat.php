<?php
session_start();
if(!isset($_SESSION['unique_id'])){
    header("location: login.php");
    exit();
}
?>

<?php include_once "header.php"; ?>
<body>
<div class="wrapper">
    <section class="chat-area">

        <header>
            <?php
            include_once "php/config.php";

            $incoming_id = mysqli_real_escape_string($conn, $_GET['user_id'] ?? 0);
            $outgoing_id = $_SESSION['unique_id'];

            $sql = mysqli_query($conn, "SELECT * FROM users WHERE unique_id = {$incoming_id}") 
                   or die(mysqli_error($conn));

            if(mysqli_num_rows($sql) > 0){
                $row = mysqli_fetch_assoc($sql);
            } else {
                echo "User not found!";
                exit();
            }

            // Mark messages as read when opening chat
            mysqli_query($conn, "UPDATE messages SET msg_status = 1 
                                 WHERE outgoing_msg_id = {$incoming_id} 
                                   AND incoming_msg_id = {$outgoing_id}");
            ?>
            <a href="users.php" class="back-icon"><i class="fas fa-arrow-left"></i></a>
            <img src="php/images/<?php echo htmlspecialchars($row['img'], ENT_QUOTES, 'UTF-8'); ?>" alt="">
            <div class="details">
                <span><?php echo htmlspecialchars($row['fname'] . " " . $row['lname'], ENT_QUOTES, 'UTF-8'); ?></span>
                <p id="user-status"><?php echo htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8'); ?></p>
            </div>

            <button id="dark-mode-toggle" title="Toggle Dark/Light Mode">🌓</button>
        </header>

        <div class="chat-box">
            <!-- Messages will be loaded via get-chat.php -->
        </div>

        <form class="typing-area" action="#" method="POST" autocomplete="off" id="chat-form">
            <input type="hidden" name="outgoing_id" value="<?php echo $outgoing_id; ?>">
            <input type="hidden" name="incoming_id" value="<?php echo $incoming_id; ?>">

            <div class="input-row">
                <input type="text" name="message" placeholder="Type a message here..." required id="message-input">
                <button type="button" id="emoji-btn">😊</button>
                <button type="submit"><i class="fab fa-telegram-plane"></i></button>
            </div>
            <!-- Emoji menu -->
            <div id="emoji-menu"></div>
        </form>
    </section>
</div>

<script>
const body = document.body;
const darkModeToggle = document.getElementById('dark-mode-toggle');

// Apply saved mode
if(localStorage.getItem('dark-mode') === 'enabled'){
    body.classList.add('dark-mode');
    darkModeToggle.textContent = '🌞';
}

// Toggle dark/light mode
darkModeToggle.addEventListener('click', () => {
    body.classList.toggle('dark-mode');
    darkModeToggle.textContent = body.classList.contains('dark-mode') ? '🌞' : '🌓';
    localStorage.setItem('dark-mode', body.classList.contains('dark-mode') ? 'enabled' : 'disabled');
    updateEmojiMenuColors();
});

// Emoji picker
const emojiBtn = document.getElementById('emoji-btn');
const messageInput = document.getElementById('message-input');
const emojiMenu = document.getElementById('emoji-menu');
const emojis = ["😀","😂","😍","😎","😭","👍","🎉","❤️","💔","😅","🤔","🥰","😇","😜","🙌","💪"];

// Style emoji menu
Object.assign(emojiMenu.style, {
    display: 'none',
    flexWrap: 'wrap',
    width: '100%',
    maxHeight: '150px',
    overflowY: 'auto',
    border: '1px solid #ccc',
    padding: '5px',
    marginTop: '5px',
    boxShadow: '0 2px 6px rgba(0,0,0,0.2)',
    borderRadius: '6px'
});

function updateEmojiMenuColors(){
    if(body.classList.contains('dark-mode')){
        emojiMenu.style.background = '#333';
        emojiMenu.style.color = '#fff';
        emojiMenu.style.borderColor = '#555';
    } else {
        emojiMenu.style.background = '#fff';
        emojiMenu.style.color = '#000';
        emojiMenu.style.borderColor = '#ccc';
    }
}
updateEmojiMenuColors();

// Populate emojis
emojis.forEach(e => {
    let span = document.createElement('span');
    span.textContent = e;
    Object.assign(span.style, { cursor:'pointer', fontSize:'20px', margin:'5px' });
    span.addEventListener('click', () => {
        messageInput.value += e;
        messageInput.focus();
    });
    emojiMenu.appendChild(span);
});

// Show/hide emoji menu on click
emojiBtn.addEventListener('click', () => {
    emojiMenu.style.display = (emojiMenu.style.display === 'none') ? 'flex' : 'none';
});

// Send message via AJAX
const chatForm = document.getElementById('chat-form');
const chatBox = document.querySelector('.chat-box');

chatForm.addEventListener('submit', e => {
    e.preventDefault();
    const formData = new FormData(chatForm);
    fetch('php/insert-chat.php', { method:'POST', body:formData })
        .then(res => res.text())
        .then(() => {
            messageInput.value = '';
            chatBox.scrollTo({ top: chatBox.scrollHeight, behavior: 'smooth' });
        });
});

// Auto fetch messages every 500ms
setInterval(() => {
    const formData = new FormData(chatForm);
    fetch('php/get-chat.php', { method:'POST', body:formData })
        .then(res => res.text())
        .then(data => {
            chatBox.innerHTML = data;
            chatBox.scrollTo({ top: chatBox.scrollHeight, behavior: 'smooth' });
        });
}, 500);

// Auto fetch user status every 5 seconds
function fetchUserStatus(){
    fetch("php/get-status.php?user_id=<?php echo $incoming_id; ?>")
        .then(res => res.text())
        .then(status => {
            document.getElementById("user-status").textContent = status;
        });
}
setInterval(fetchUserStatus, 5000);
</script>

<style>
body { font-family: Arial, sans-serif; }
body.dark-mode { background:#121212; color:#e0e0e0; }
body.dark-mode .wrapper { background:#1e1e1e; box-shadow:0 0 128px rgba(0,0,0,0.2),0 32px 64px -48px rgba(0,0,0,0.5);}
body.dark-mode .chat-area header { background:#1e1e1e; color:#e0e0e0;}
body.dark-mode .chat-area header .details span,
body.dark-mode .chat-area header .details p { color:#e0e0e0;}
body.dark-mode .chat-box { background:#1a1a1a; overflow-y:auto; max-height:400px;}
body.dark-mode .chat-box .outgoing p { background:#333; color:#fff;}
body.dark-mode .chat-box .incoming p { background:#2a2a2a; color:#fff;}
body.dark-mode .typing-area input { background:#1e1e1e; color:#e0e0e0; border:1px solid #333;}
body.dark-mode .typing-area button { background:#444; color:#fff;}
.typing-area {
    position: relative;
    padding: 5px;
    display: flex;
    flex-direction: column;
}
.typing-area .input-row {
    display: flex;
    align-items: center;
    gap: 5px;
}
.typing-area input {
    flex: 1;
    padding: 8px;
    border-radius: 4px;
    border: 1px solid #ccc;
}
#emoji-menu {
    position: relative;
}
.back-icon i {
    color: #000;   /* black in light mode */
    font-size: 20px;
}
body.dark-mode .back-icon i {
    color: #fff !important;  /* force white in dark mode */
}
.back-icon i:hover {
    opacity: 0.7;
    transition: 0.3s;
}
</style>
</body>
</html>
