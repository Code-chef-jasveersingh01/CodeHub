/*
Author:
File: Notifications init js
*/

function message(text, className) {
    Toastify({
        newWindow: true,
        text: text,
        gravity: 'top',
        position: 'center',
        className: "bg-" + className,
        stopOnFocus: true,
        duration: 3000,
        close: 'false',
        style: "",
    }).showToast();
}
