import $ from "jquery";

$(document).ready(function () {
    const loginForm = $("#loginForm");
    const usernameInput = $("#login-username");
    const passwordInput = $("#login-password");
    const usernameError = $("#login-username-error");
    const passwordError = $("#login-password-error");
    const loginSubmitBtn = $("#loginSubmitBtn");
    const defaultSubmitHtml = loginSubmitBtn.length ? loginSubmitBtn.html() : "Sign In";
    const toastId = "loginAjaxToast";
    let toastHideTimer = null;

    const validationRules = {
        username(value) {
            const trimmed = (value || "").trim();
            if (!trimmed) {
                return "Username is required.";
            }
            if (!/^[A-Za-z0-9._-]+$/.test(trimmed)) {
                return "Username can only contain letters, numbers, dot, underscore, or hyphen.";
            }
            if (trimmed.length < 3) {
                return "Username must be at least 3 characters.";
            }
            if (trimmed.length > 50) {
                return "Username must not exceed 50 characters.";
            }
            return "";
        },
        password(value) {
            if (!value) {
                return "Password is required.";
            }
            if (value.length < 6) {
                return "Password must be at least 6 characters.";
            }
            if (value.length > 255) {
                return "Password must not exceed 255 characters.";
            }
            return "";
        },
    };

    function setSubmitLoading(isLoading) {
        if (!loginSubmitBtn.length) {
            return;
        }

        if (isLoading) {
            loginSubmitBtn.prop("disabled", true);
            loginSubmitBtn
                .addClass("opacity-80 cursor-not-allowed pointer-events-none")
                .html('<i class="fas fa-spinner fa-spin mr-2" aria-hidden="true"></i><span>Signing in...</span>');
            return;
        }

        loginSubmitBtn.prop("disabled", false);
        loginSubmitBtn
            .removeClass("opacity-80 cursor-not-allowed pointer-events-none")
            .html(defaultSubmitHtml);
    }

    function ensureToast() {
        let toast = document.getElementById("loginPageToast") || document.getElementById(toastId);
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

    function showFieldError($input, $errorNode, message) {
        const hasError = Boolean(message);
        $errorNode.text(message || "");
        $input.attr("aria-invalid", hasError ? "true" : "false");

        if (hasError) {
            $input.addClass("border-red-500");
            return;
        }

        $input.removeClass("border-red-500");
    }

    function validateUsername() {
        const message = validationRules.username(usernameInput.val());
        showFieldError(usernameInput, usernameError, message);
        return !message;
    }

    function validatePassword() {
        const message = validationRules.password(passwordInput.val());
        showFieldError(passwordInput, passwordError, message);
        return !message;
    }

    function validateForm() {
        const usernameOk = validateUsername();
        const passwordOk = validatePassword();
        return usernameOk && passwordOk;
    }

    usernameInput.on("input blur", function () {
        validateUsername();
    });

    passwordInput.on("input blur", function () {
        validatePassword();
    });

    loginForm.submit(function (e) {
        e.preventDefault();

        if (!validateForm()) {
            return;
        }

        setSubmitLoading(true);

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
                    showToast(response.message || "Invalid username or password.", "error");
                    setSubmitLoading(false);
                }
            },
            error: function () {
                showToast("Something went wrong. Please try again.", "error");
                setSubmitLoading(false);
            }
        });
    });
});