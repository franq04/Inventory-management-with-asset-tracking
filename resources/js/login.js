import $ from "jquery";

$(document).ready(function () {
    const alertBox = $("#alertBox");
    const toastId = "loginAjaxToast";
    let toastHideTimer = null;

    function ensureToast() {
        let toast = document.getElementById(toastId);
        if (toast) {
            return toast;
        }

        toast = document.createElement("div");
        toast.id = toastId;
        toast.className = "pointer-events-none fixed right-4 top-6 z-[130] hidden min-w-[260px] max-w-md rounded-xl border border-emerald-800 bg-[#1a3a2d] px-5 py-3 text-sm font-semibold text-white shadow-2xl opacity-0 translate-y-2 transition-all duration-300 ease-out";
        document.body.appendChild(toast);
        return toast;
    }

    function showToast(message, type = "success") {
        const toast = ensureToast();
        if (!toast || !message) {
            return 0;
        }

        if (toastHideTimer) {
            window.clearTimeout(toastHideTimer);
            toastHideTimer = null;
        }

        toast.getAnimations().forEach((animation) => animation.cancel());

        toast.textContent = message;
        toast.classList.remove("hidden", "border-emerald-800", "bg-[#1a3a2d]", "border-red-800", "bg-red-700", "opacity-0", "translate-y-2");
        if (type === "error") {
            toast.classList.add("border-red-800", "bg-red-700");
        } else {
            toast.classList.add("border-emerald-800", "bg-[#1a3a2d]");
        }

        const enterDuration = 280;
        const holdDuration = 1100;
        const exitDuration = 220;

        const enterAnimation = toast.animate(
            [
                { opacity: 0, transform: "translateY(10px) scale(0.98)" },
                { opacity: 1, transform: "translateY(0) scale(1)" },
            ],
            { duration: enterDuration, easing: "cubic-bezier(0.22, 1, 0.36, 1)", fill: "forwards" }
        );

        enterAnimation.onfinish = () => {
            toastHideTimer = window.setTimeout(() => {
                const exitAnimation = toast.animate(
                    [
                        { opacity: 1, transform: "translateY(0) scale(1)" },
                        { opacity: 0, transform: "translateY(8px) scale(0.98)" },
                    ],
                    { duration: exitDuration, easing: "ease-in", fill: "forwards" }
                );

                exitAnimation.onfinish = () => {
                    toast.classList.add("hidden");
                };
            }, holdDuration);
        };

        return enterDuration + holdDuration;
    }

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
                    const redirectDelay = showToast("Login successful. Redirecting...", "success");
                    window.setTimeout(() => {
                        window.location.href = response.redirect;
                    }, redirectDelay || 1100);
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