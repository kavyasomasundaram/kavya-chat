const searchBar = document.querySelector(".users .search input"),
      searchBtn = document.querySelector(".users .search button"),
      usersList = document.querySelector(".users .users-list");

let searchTimeout;

// Toggle search input visibility
searchBtn.onclick = () => {
    searchBar.classList.toggle("active");
    searchBtn.classList.toggle("active");
    searchBar.value = "";
    searchBar.focus();
};

// Generic fetch function
async function fetchUsers(url, method = "GET", params = null) {
    let options = { method };
    if (method === "POST" && params) {
        options.headers = { "Content-type": "application/x-www-form-urlencoded" };
        options.body = params;
    }
    try {
        const response = await fetch(url, options);
        if (!response.ok) throw new Error("Network response was not ok");
        const data = await response.text();
        return data;
    } catch (error) {
        console.error("Fetch error:", error);
        return "";
    }
}

// Update users list dynamically
async function updateUsersList() {
    if (!searchBar.classList.contains("active")) {
        const data = await fetchUsers("php/users.php");
        usersList.innerHTML = data;

        // Attach click event to remove unread badge and mark messages as read
        const links = usersList.querySelectorAll("a");
        links.forEach(link => {
            link.onclick = async (e) => {
                const unreadSpan = link.querySelector(".unread");
                if (unreadSpan) unreadSpan.remove();

                // Mark messages as read on server
                const userId = link.getAttribute("data-userid");
                if (userId) {
                    try {
                        await fetch("php/mark_read.php", {
                            method: "POST",
                            headers: { "Content-Type": "application/x-www-form-urlencoded" },
                            body: `incoming_id=${userId}`
                        });
                    } catch (err) {
                        console.error("Failed to mark messages as read:", err);
                    }
                }
            };
        });
    }
}

// Handle search input with debounce
searchBar.onkeyup = () => {
    const searchTerm = searchBar.value.trim();

    if (searchTerm !== "") {
        searchBar.classList.add("active");
    } else {
        searchBar.classList.remove("active");
    }

    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(async () => {
        if (searchTerm !== "") {
            const data = await fetchUsers("php/search.php", "POST", "searchTerm=" + encodeURIComponent(searchTerm));
            usersList.innerHTML = data;
        } else {
            updateUsersList();
        }
    }, 300);
};

// Auto refresh every 500ms
setInterval(updateUsersList, 500);

// Initial fetch
updateUsersList();
