// Select all password fields and their toggle icons
const passwordFields = document.querySelectorAll(".form input[type='password']");

passwordFields.forEach(pswrdField => {
    const toggleBtn = pswrdField.parentElement.querySelector("i");

    if(toggleBtn){
        toggleBtn.onclick = () => {
            if(pswrdField.type === "password"){
                pswrdField.type = "text";
                toggleBtn.classList.add("active");
            } else {
                pswrdField.type = "password";
                toggleBtn.classList.remove("active");
            }
        }
    }
});
