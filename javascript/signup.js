const form = document.querySelector(".signup form"),
      continueBtn = form.querySelector(".button input"),
      errorText = form.querySelector(".error-txt");

// Prevent default form submit
form.onsubmit = (e) => {
    e.preventDefault();
}

// Handle signup button click
continueBtn.onclick = () => {
    const fname = form.querySelector("input[name='fname']").value.trim();
    const lname = form.querySelector("input[name='lname']").value.trim();
    const email = form.querySelector("input[name='email']").value.trim();
    const password = form.querySelector("input[name='password']").value.trim();
    const image = form.querySelector("input[name='image']").files[0];

    if(!fname || !lname || !email || !password || !image){
        showError("All fields are required!");
        return;
    }

    // Disable button while processing
    continueBtn.disabled = true;

    let formData = new FormData(form);
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "php/signup.php", true);
    xhr.onload = () => {
        if(xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200){
            let data = xhr.response.trim();
            if(data === "success"){
                location.href = "users.php";
            } else {
                showError(data);
                continueBtn.disabled = false;
            }
        }
    }
    xhr.send(formData);
}

// Function to show error messages
function showError(message){
    errorText.textContent = message;
    errorText.style.display = "block";
    setTimeout(() => {
        errorText.style.display = "none";
    }, 5000); // hide after 5 seconds
}
