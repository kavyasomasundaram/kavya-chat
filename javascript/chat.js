// Update users list dynamically
async function updateUsersList() {
    // Only fetch if search is not active
    if (!searchBar.classList.contains("active")) {
        const data = await fetchUsers("php/users.php");
        usersList.innerHTML = data;

        // Attach click to remove unread badge on user click
        usersList.querySelectorAll("a").forEach(link => {
            link.onclick = async () => {
                const unreadSpan = link.querySelector(".unread");
                if (unreadSpan) unreadSpan.remove();

                // Mark messages as read on the server
                const userId = link.getAttribute("href").split("user_id=")[1];
                if (userId) {
                    await fetch("php/mark_read.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/x-www-form-urlencoded" },
                        body: `incoming_id=${userId}`
                    });
                }
            };
        });
    }
}

// Auto refresh users list every 500ms
setInterval(updateUsersList, 500);
