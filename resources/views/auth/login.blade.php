<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Gateway</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;800&display=swap" rel="stylesheet">

    <link rel="icon" type="image/png" href="{{ asset('images/pqslogo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/login.js'])
    
    <style>
        :root {
            --primary-color: #f9bf0f; /* Yellow/Gold */
            --primary-color-glow: rgba(249, 191, 15, 0.4);
            --secondary-color: #1a3a2d; /* Dark Green */
            --input-bg-color: #ffffff; /* Solid white for inputs */
        }
        
        body { 
            font-family: 'Inter', sans-serif; 
            background-color: #f0f4f8;
            overflow: hidden; /* Hide scrollbars from gradient overflow */
        }

        /* Animated Gradient Background */
        #background-gradient {
            width: 200%;
            height: 200%;
            position: fixed;
            top: -50%;
            left: -50%;
            z-index: -1;
            background: linear-gradient(-45deg, #e0e7ff, #f0f4f8, #fef3c7, #d1fae5);
            background-size: 400% 400%;
            animation: gradientShift 20s ease infinite;
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Entrance Animation for the main card */
        .animate-card-enter {
            animation: cardEnter 1s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
            opacity: 0;
            transform: scale(0.95);
        }

        @keyframes cardEnter {
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        /* Floating Label Styling */
        .floating-label-group {
            position: relative;
        }
        .floating-input {
            padding-top: 1.5rem;
            background-color: var(--input-bg-color);
        }
        .floating-label {
            position: absolute;
            top: 50%;
            left: 2.5rem; /* Space for icon */
            transform: translateY(-50%);
            transition: all 0.2s ease-in-out;
            pointer-events: none;
            color: #6b7280;
            background-color: var(--input-bg-color);
            padding: 0 0.25rem;
        }
        .floating-input:focus ~ .floating-label,
        .floating-input:not(:placeholder-shown) ~ .floating-label {
            top: 0;
            transform: translateY(-50%) scale(0.85);
            font-size: 1rem;
            color: var(--secondary-color);
            left: 0.75rem; 
        }
        
        /* Input Focus Glow Effect */
        .input-glow:focus-within {
             box-shadow: 0 0 0 3px var(--primary-color-glow);
        }

        /* Shake animation for validation */
        .shake {
            animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both;
        }

        @keyframes shake {
            10%, 90% { transform: translate3d(-1px, 0, 0); }
            20%, 80% { transform: translate3d(2px, 0, 0); }
            30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
            40%, 60% { transform: translate3d(4px, 0, 0); }
        }

        /* Button Shimmer Effect */
        .shimmer-btn {
            position: relative;
            overflow: hidden;
        }
        .shimmer-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 75%;
            height: 100%;
            background: linear-gradient(120deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.3) 50%, rgba(255,255,255,0) 100%);
            transition: left 0.6s ease-in-out;
        }
        .shimmer-btn:hover::before {
            left: 125%;
        }

        /* Interactive SVG Orb Styling */
        #orb-outer-1 { animation: rotate 30s linear infinite; }
        #orb-outer-2 { animation: rotate 45s linear infinite reverse; }
        #orb-core { transition: all 0.5s ease; }
        .user-focused #orb-outer-1, .user-focused #orb-outer-2 { animation-duration: 10s; }
        .pass-focused #orb-core { fill: var(--primary-color); transform: scale(1.1); }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">

    <div id="background-gradient"></div>

    <div class="bg-white/70 backdrop-blur-xl rounded-2xl shadow-2xl overflow-hidden max-w-4xl w-full flex flex-col md:flex-row animate-card-enter">
        
        <div class="w-full md:w-1/2 flex flex-col items-center justify-center p-8 bg-black/5 md:p-12 relative overflow-hidden">
            <div id="interactive-orb" class="text-center space-y-4">
                <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" class="w-48 h-48 mx-auto transition-all duration-500 ease-out">
                    <defs>
                        <linearGradient id="coreGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" style="stop-color: var(--secondary-color); stop-opacity: 1" />
                            <stop offset="100%" style="stop-color: #2c5d4a; stop-opacity: 1" />
                        </linearGradient>
                    </defs>
                    <g transform="translate(100 100)" stroke-width="2" fill="none">
                        <circle id="orb-core" r="30" fill="url(#coreGradient)" transform-origin="center"></circle>
                        <path id="orb-outer-1" d="M 0 -80 A 80 80 0 1 1 0 80 A 80 80 0 1 1 0 -80" stroke="var(--primary-color)" transform-origin="center"></path>
                        <path id="orb-outer-2" d="M -60 0 A 60 60 0 1 1 60 0 A 60 60 0 1 1 -60 0" stroke="var(--secondary-color)" opacity="0.5" transform-origin="center"></path>
                    </g>
                </svg>
                <h1 class="text-3xl font-bold tracking-tight text-[var(--secondary-color)] mt-6">Bureau of Plant Industry</h1>
                <p class="text-gray-600 max-w-xs mx-auto">Welcome. Please authenticate to access the system.</p>
            </div>
        </div>
        
        <div class="w-full md:w-1/2 p-8 md:p-12 flex flex-col justify-center">
            
            <div class="text-center md:text-left mb-8">
                 <img src="{{ asset('images/pqslogo.png') }}" alt="Logo" class="w-16 h-16 rounded-lg shadow-md mb-4 mx-auto md:mx-0">
                 <h2 class="text-3xl font-bold text-[var(--secondary-color)]">Welcome Back</h2>
                 <p class="text-gray-500 mt-1">Please enter your credentials to continue.</p>
            </div>

            <div id="alertBox" class="hidden mb-4 p-3 rounded-lg text-sm font-medium"></div>

            <form id="loginForm" class="space-y-6">
                @csrf
                
                <div class="floating-label-group input-glow rounded-xl transition-shadow duration-300">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none z-10">
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                    </div>
                    <input type="text" id="login-username" name="username" class="floating-input block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-0 focus:border-[var(--primary-color)] transition" placeholder=" ">
                    <label for="login-username" class="floating-label">Username</label>
                </div>
                
                <div class="floating-label-group input-glow rounded-xl transition-shadow duration-300">
                     <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none z-10">
                       <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
                    </div>
                    <input type="password" id="login-password" name="password" class="floating-input block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-0 focus:border-[var(--primary-color)] transition" placeholder=" ">
                    <label for="login-password" class="floating-label">Password</label>
                    <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 flex items-center pr-3 cursor-pointer z-10">
                        <svg id="eye-icon" class="h-5 w-5 text-gray-500 hover:text-[var(--secondary-color)]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        <svg id="eye-slash-icon" class="h-5 w-5 text-gray-500 hover:text-[var(--secondary-color)] hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.572M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.243 4.243L6.228 6.228" /></svg>
                    </button>
                </div>
                
                <div>
                    <button id="loginSubmitBtn" type="submit" data-no-global-loading="true" class="shimmer-btn group w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-lg text-lg font-semibold text-white bg-[var(--secondary-color)] hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--primary-color)] transition-all duration-300 ease-in-out active:scale-[0.98]">
                        Sign In
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="loginPageToast" class="pointer-events-none fixed right-4 top-6 z-[130] hidden min-w-[260px] max-w-md rounded-xl border border-emerald-800 bg-[#1a3a2d] px-5 py-3 text-sm font-semibold text-white shadow-2xl opacity-0 translate-y-2 transition-all duration-300 ease-out"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const usernameInput = document.getElementById('login-username');
            const passwordInput = document.getElementById('login-password');
            const toggleButton = document.getElementById('togglePassword');
            const eyeIcon = document.getElementById('eye-icon');
            const eyeSlashIcon = document.getElementById('eye-slash-icon');
            const interactiveOrb = document.getElementById('interactive-orb');
            const loginForm = document.getElementById('loginForm');
            const loginSubmitBtn = document.getElementById('loginSubmitBtn');
            const loginPageToast = document.getElementById('loginPageToast');
            let loginToastTimer = null;

            const showLoginToast = (message, type = 'success') => {
                if (!loginPageToast || !message) {
                    return;
                }

                if (loginToastTimer) {
                    clearTimeout(loginToastTimer);
                    loginToastTimer = null;
                }

                loginPageToast.getAnimations().forEach((animation) => animation.cancel());

                loginPageToast.textContent = message;
                loginPageToast.classList.remove('hidden', 'border-emerald-800', 'bg-[#1a3a2d]', 'border-red-800', 'bg-red-700', 'opacity-0', 'translate-y-2');

                if (type === 'error') {
                    loginPageToast.classList.add('border-red-800', 'bg-red-700');
                } else {
                    loginPageToast.classList.add('border-emerald-800', 'bg-[#1a3a2d]');
                }

                const enterDuration = 280;
                const holdDuration = 1600;
                const exitDuration = 220;

                const enterAnimation = loginPageToast.animate([
                    { opacity: 0, transform: 'translateY(10px) scale(0.98)' },
                    { opacity: 1, transform: 'translateY(0) scale(1)' },
                ], {
                    duration: enterDuration,
                    easing: 'cubic-bezier(0.22, 1, 0.36, 1)',
                    fill: 'forwards',
                });

                enterAnimation.onfinish = () => {
                    loginToastTimer = setTimeout(() => {
                        const exitAnimation = loginPageToast.animate([
                            { opacity: 1, transform: 'translateY(0) scale(1)' },
                            { opacity: 0, transform: 'translateY(8px) scale(0.98)' },
                        ], {
                            duration: exitDuration,
                            easing: 'ease-in',
                            fill: 'forwards',
                        });

                        exitAnimation.onfinish = () => {
                            loginPageToast.classList.add('hidden');
                        };
                    }, holdDuration);
                };
            };

            // --- Interactive Orb Logic ---
            if (usernameInput && interactiveOrb) {
                usernameInput.addEventListener('focus', () => interactiveOrb.classList.add('user-focused'));
                usernameInput.addEventListener('blur', () => interactiveOrb.classList.remove('user-focused'));
            }
            if (passwordInput && interactiveOrb) {
                passwordInput.addEventListener('focus', () => interactiveOrb.classList.add('pass-focused'));
                passwordInput.addEventListener('blur', () => interactiveOrb.classList.remove('pass-focused'));
            }

            // --- Password Visibility Toggle Logic ---
            if (toggleButton) {
                toggleButton.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    eyeIcon.classList.toggle('hidden');
                    eyeSlashIcon.classList.toggle('hidden');
                });
            }

            // --- Basic Form Validation Shake ---
            if (loginForm) {
                 loginForm.addEventListener('submit', function(e) {
                    let isInvalid = false;
                    if (!usernameInput.value) {
                        usernameInput.parentElement.classList.add('shake');
                        isInvalid = true;
                    }
                    if (!passwordInput.value) {
                        passwordInput.parentElement.classList.add('shake');
                        isInvalid = true;
                    }

                    if (isInvalid) {
                        e.preventDefault(); 
                        setTimeout(() => {
                           usernameInput.parentElement.classList.remove('shake');
                           passwordInput.parentElement.classList.remove('shake');
                        }, 500);
                        return;
                    }

                    if (loginSubmitBtn) {
                        loginSubmitBtn.disabled = true;
                        loginSubmitBtn.classList.add('opacity-80', 'cursor-not-allowed', 'pointer-events-none');
                        loginSubmitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2" aria-hidden="true"></i><span>Signing in...</span>';
                    }
                 });
            }

            @if (session('toast'))
                showLoginToast(@json(session('toast.message')), @json(session('toast.type', 'success')));
            @endif
        });
    </script>
</body>
</html>