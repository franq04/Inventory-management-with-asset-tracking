import $ from "jquery";

$(document).ready(function () {
    const alertBox = $("#alertBox");

    function showAlert(message, type) {
        alertBox
            .removeClass("hidden bg-red-100 text-red-700 bg-green-100 text-green-700")
            .addClass(type === "error" ? "bg-red-100 text-red-700" : "bg-green-100 text-green-700")
            .text(message);
    }

    $("#loginForm").submit(function (e) {
        e.preventDefault();

        $.ajax({
            url: "/login",
            method: "POST",
            data: $(this).serialize(),
            success: function (response) {
                if (response.status === "success") {
                    showAlert("Login successful! Redirecting...", "success");
                    setTimeout(() => {
                        window.location.href = response.redirect;
                    }, 1500);
                } else {
                    showAlert(response.message, "error");
                }
            },
            error: function () {
                showAlert("Something went wrong. Please try again.", "error");
            }
        });
    });
});