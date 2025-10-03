const form = document.querySelector(".login form"),
      continueBtn = form.querySelector(".button input"),
      errorText = form.querySelector(".error-txt");

// Prevent default form submission
form.onsubmit = (e) => { e.preventDefault(); }

// Handle login button click
continueBtn.onclick = () => {
    let email = form.querySelector("input[name='email']").value.trim();
    let password = form.querySelector("input[name='password']").value.trim();

    if(email === "" || password === ""){
        showError("All fields are required!");
        return;
    }

    continueBtn.disabled = true;

    let xhr = new XMLHttpRequest();
    xhr.open("POST", "php/login.php", true);
    xhr.onload = () => {
        if(xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200){
            let data = xhr.response.trim();
            if(data === "success"){
                // Redirect based on screen size
                if(window.innerWidth < 768){
                    location.href = "users.php"; // mobile
                } else {
                    location.href = "dashboard.php"; // desktop
                }
            } else {
                showError(data);
                continueBtn.disabled = false;
            }
        }
    }

    let formData = new FormData(form);
    xhr.send(formData);
}

// Show error function
function showError(message){
    errorText.textContent = message;
    errorText.style.display = "block";
    setTimeout(() => { errorText.style.display = "none"; }, 5000);
}
