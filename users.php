<?php
session_start();
if(!isset($_SESSION['unique_id'])){
    header("location: login.php");
    exit();
}
?>

<?php include_once "header.php"; ?>
<script>
  // Redirect desktop users to dashboard.php
  if(window.innerWidth >= 768){
      window.location.href = "dashboard.php";
  }
</script>

<body>
    <div class="wrapper">
        <section class="users">
            <header>
                <?php 
                include_once "php/config.php";

                $current_id = $_SESSION['unique_id'] ?? 0;
                $sql = mysqli_query($conn, "SELECT * FROM users WHERE unique_id = {$current_id}") 
                       or die(mysqli_error($conn));

                if(mysqli_num_rows($sql) > 0){
                    $row = mysqli_fetch_assoc($sql);
                } else {
                    echo "User not found!";
                    exit();
                }
                ?>
                
                <div class="content">
                    <img src="php/images/<?php echo htmlspecialchars($row['img'], ENT_QUOTES, 'UTF-8'); ?>" alt="">
                    <div class="details">
                        <span><?php echo htmlspecialchars($row['fname'] . " " . $row['lname'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <p><?php echo htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                </div>

                <!-- Dark mode toggle button -->
                <button id="dark-mode-toggle" title="Toggle Dark/Light Mode">🌓</button>

                <a href="php/logout.php?logout_id=<?php echo $row['unique_id']; ?>" class="logout">Logout</a>
            </header>

            <div class="search">
                <span class="text">Select a user to start chat</span>
                <input type="text" placeholder="Enter name to search..." id="search-bar">
                <button><i class="fas fa-search"></i></button>
            </div>

            <div class="users-list">
                <?php
                // Fetch all other users
                $users_sql = mysqli_query($conn, "SELECT * FROM users WHERE unique_id != {$current_id} ORDER BY fname ASC") or die(mysqli_error($conn));

                if(mysqli_num_rows($users_sql) > 0){
                    while($user = mysqli_fetch_assoc($users_sql)){
                        // Count unread messages
                        $unread_sql = mysqli_query($conn, "SELECT COUNT(*) AS unread_count FROM messages WHERE outgoing_msg_id = ".(int)$user['unique_id']." AND incoming_msg_id = {$current_id} AND msg_status = 0") or die(mysqli_error($conn));
                        $unread_row = mysqli_fetch_assoc($unread_sql);
                        $unread_count = (int)$unread_row['unread_count'];
                        ?>
                        <a href="chat.php?user_id=<?php echo (int)$user['unique_id']; ?>">
                            <img src="php/images/<?php echo htmlspecialchars($user['img'], ENT_QUOTES, 'UTF-8'); ?>" alt="">
                            <div class="details">
                                <span>
                                    <?php echo htmlspecialchars($user['fname'] . ' ' . $user['lname']); ?>
                                    <?php if($unread_count > 0){ ?>
                                        <span class="unread"><?php echo $unread_count; ?></span>
                                    <?php } ?>
                                </span>
                                <p><?php echo htmlspecialchars($user['status'], ENT_QUOTES, 'UTF-8'); ?></p>
                            </div>
                        </a>
                    <?php }
                } else { ?>
                    <p class="text">No users are available to chat</p>
                <?php } ?>
            </div>
        </section>
    </div>

    <script src="javascript/users.js"></script>
    <script>
        const darkModeToggle = document.getElementById('dark-mode-toggle');
        const body = document.body;

        if(localStorage.getItem('dark-mode') === 'enabled'){
            body.classList.add('dark-mode');
            darkModeToggle.textContent = '🌞';
        }

        darkModeToggle.addEventListener('click', () => {
            body.classList.toggle('dark-mode');
            if(body.classList.contains('dark-mode')){
                darkModeToggle.textContent = '🌞';
                localStorage.setItem('dark-mode', 'enabled');
            } else {
                darkModeToggle.textContent = '🌓';
                localStorage.setItem('dark-mode', 'disabled');
            }
        });
    </script>

    <style>
        body.dark-mode {
            background: #121212;
            color: #e0e0e0;
        }
        body.dark-mode .wrapper {
            background: #1e1e1e;
            color: #e0e0e0;
        }
        body.dark-mode .users header,
        body.dark-mode .users header * {
            background: #1e1e1e;
            color: #e0e0e0 !important;
        }
        body.dark-mode .users header .logout,
        body.dark-mode #dark-mode-toggle {
            background: #444;
            color: #e0e0e0 !important;
        }
        body.dark-mode .users .search input,
        body.dark-mode .users .search button {
            background: #e0e0e0;
            color: #0e0e0e;
            border-color: #333;
        }
        body.dark-mode .users-list a,
        body.dark-mode .users-list a * {
            background: #1e1e1e;
            color: #e0e0e0 !important;
            border-color: #333;
        }

        .unread {
    background:  #e0e0e0;
    color: #1e1e1e;
    border-radius: 50%;
    padding: 2px 6px;
    font-size: 10px;
    margin-left: 5px;
}

body.dark-mode .users-list a .unread {
    background: #e0e0e0 !important;
    color: #1e1e1e !important;
}

        
        
        /* .unread {
            background:  #e0e0e0;
            color: #1e1e1e;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 10px;
            margin-left: 5px;
        }
         */
        
    </style>
</body>
</html>
