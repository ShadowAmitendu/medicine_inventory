<?php
require_once '../config/auth.php';
require_once '../config/paths.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(URL_HOME);
}

$error = '';
$success = $_GET['registered'] ?? '';
$deleted = $_GET['deleted'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $remember = isset($_POST['remember']);

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        if (login($username, $password)) {
            // Set remember me cookie if checked
            if ($remember) {
                setcookie('remember_user', $username, time() + (86400 * 30), '/');
            }

            // Redirect to intended page or dashboard
            $redirect = $_GET['redirect'] ?? URL_HOME;
            redirect($redirect);
        } else {
            $error = "Invalid username or password.";
        }
    }
}

// Pre-fill username if remember me cookie exists
$remembered_user = $_COOKIE['remember_user'] ?? '';
?>

<!DOCTYPE html>
<html lang="en" class="h-full bg-gradient-to-br from-primary-900 via-gray-900 to-gray-800">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Medicine Inventory</title>
    <link href="<?= assetUrl('output.css') ?>" rel="stylesheet">
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        .float-animation {
            animation: float 6s ease-in-out infinite;
        }
    </style>
</head>
<body class="relative overflow-hidden">

<!-- Background decorative elements -->
<div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZGVmcz48cGF0dGVybiBpZD0iZ3JpZCIgd2lkdGg9IjQwIiBoZWlnaHQ9IjQwIiBwYXR0ZXJuVW5pdHM9InVzZXJTcGFjZU9uVXNlIj48cGF0aCBkPSJNIDQwIDAgTCAwIDAgMCA0MCIgZmlsbD0ibm9uZSIgc3Ryb2tlPSJ3aGl0ZSIgc3Ryb2tlLW9wYWNpdHk9IjAuMDMiIHN0cm9rZS13aWR0aD0iMSIvPjwvcGF0dGVybj48L2RlZnM+PHJlY3Qgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgZmlsbD0idXJsKCNncmlkKSIvPjwvc3ZnPg==')] opacity-40"></div>
<div class="absolute top-20 left-20 w-72 h-72 bg-primary-600/20 rounded-full blur-3xl float-animation"></div>
<div class="absolute bottom-20 right-20 w-96 h-96 bg-accent-600/15 rounded-full blur-3xl float-animation" style="animation-delay: 2s;"></div>

<div class="relative flex min-h-full flex-col justify-center px-6 py-12 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <!-- Logo -->
        <div class="flex justify-center mb-6">
            <div class="relative group">
                <div class="absolute -inset-2 bg-gradient-to-r from-primary-500 to-accent-500 rounded-2xl blur-lg opacity-30 group-hover:opacity-50 transition-opacity"></div>
                <div class="relative size-16 bg-gradient-to-br from-primary-500 to-primary-600 rounded-2xl flex items-center justify-center shadow-2xl transform group-hover:scale-110 transition-transform">
                    <svg class="size-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="text-center">
            <h2 class="text-4xl font-bold tracking-tight bg-gradient-to-r from-white via-primary-100 to-white bg-clip-text text-transparent mb-3">
                Welcome Back
            </h2>
            <p class="text-gray-400">
                Sign in to manage your medicine inventory
            </p>
        </div>

        <!-- Messages -->
        <?php if ($success): ?>
            <div class="mt-6 rounded-xl bg-green-500/10 backdrop-blur-sm p-4 border border-green-500/30 shadow-lg">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-green-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm text-green-300">Registration successful! Please sign in.</p>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($deleted): ?>
            <div class="mt-6 rounded-xl bg-blue-500/10 backdrop-blur-sm p-4 border border-blue-500/30 shadow-lg">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-blue-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm text-blue-300">Your account has been successfully deleted. We're sorry to see you go!</p>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="mt-6 rounded-xl bg-red-500/10 backdrop-blur-sm p-4 border border-red-500/30 shadow-lg">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-red-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm text-red-300"><?= htmlspecialchars($error) ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <!-- Login Card -->
        <div class="bg-gray-800/50 backdrop-blur-xl border border-gray-700/50 rounded-2xl shadow-2xl p-8">
            <form method="POST" class="space-y-6" autocomplete="on">
                <!-- Username Field -->
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-200 mb-2">Username</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-500 group-focus-within:text-primary-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <input id="username" name="username" type="text" required
                               value="<?= htmlspecialchars($remembered_user) ?>"
                               autocomplete="username"
                               class="block w-full rounded-xl bg-gray-700/50 pl-10 pr-4 py-3.5 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 border border-gray-600 focus:border-transparent transition-all"
                               placeholder="Enter your username"/>
                    </div>
                </div>

                <!-- Password Field -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="password" class="block text-sm font-medium text-gray-200">Password</label>
                        <a href="<?= URL_CHANGE_PASSWORD ?>"
                           class="text-sm font-semibold text-primary-400 hover:text-primary-300 transition">
                            Forgot password?
                        </a>
                    </div>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-500 group-focus-within:text-primary-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input id="password" name="password" type="password" required
                               autocomplete="current-password"
                               class="block w-full rounded-xl bg-gray-700/50 pl-10 pr-12 py-3.5 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 border border-gray-600 focus:border-transparent transition-all"
                               placeholder="Enter your password"/>
                        <button type="button" onclick="togglePassword()"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center group">
                            <svg id="eyeIcon" class="h-5 w-5 text-gray-500 hover:text-gray-300 transition"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox"
                           class="h-4 w-4 rounded border-gray-600 bg-gray-700 text-primary-500 focus:ring-2 focus:ring-primary-500 focus:ring-offset-0 transition">
                    <label for="remember" class="ml-2 block text-sm text-gray-300">
                        Remember me for 30 days
                    </label>
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit"
                            class="flex w-full justify-center items-center rounded-xl bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 px-4 py-3.5 text-sm font-semibold text-white shadow-lg shadow-primary-500/30 hover:shadow-xl hover:shadow-primary-500/40 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-500 transition-all transform hover:-translate-y-0.5">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                        Sign in to your account
                    </button>
                </div>
            </form>

            <!-- Divider -->
            <div class="mt-8 relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-600"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-4 bg-gray-800/50 text-gray-400">New to MediTrack?</span>
                </div>
            </div>

            <!-- Register Link -->
            <div class="mt-6">
                <a href="<?= URL_REGISTER ?>"
                   class="flex w-full justify-center items-center rounded-xl bg-gray-700/50 hover:bg-gray-700 border border-gray-600 hover:border-gray-500 px-4 py-3.5 text-sm font-semibold text-gray-200 hover:text-white transition-all">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    Create a new account
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>';
        } else {
            passwordInput.type = 'password';
            eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';
        }
    }
</script>

</body>
</html>