

const eyePass = document.querySelector(".password img");
const inputPass = document.querySelector(".password input");

let closeEye = true;

eyePass.addEventListener("click", (e) => {

    if (closeEye) {
        eyePass.src = "images/eye-closed.svg";
        eyePass.title = "hide";
        inputPass.type = "text";
        closeEye = false;
    } else {
        eyePass.src = "images/eye.svg"
        eyePass.title = "show";
        inputPass.type = "password";
        closeEye = true;
    }
})