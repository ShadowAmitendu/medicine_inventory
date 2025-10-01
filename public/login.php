<?php
require_once '../config/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
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
                setcookie('remember_user', $username, time() + (86400 * 30), '/'); // 30 days
            }

            // Redirect to intended page or dashboard
            $redirect = $_GET['redirect'] ?? 'index.php';
            header("Location: " . $redirect);
            exit();
        } else {
            $error = "Invalid username or password.";
        }
    }
}

// Pre-fill username if remember me cookie exists
$remembered_user = $_COOKIE['remember_user'] ?? '';
?>

<!DOCTYPE html>
<html class="h-full bg-gray-900"
      lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>Login - Medicine Inventory</title>
    <link href="../assets/output.css"
          rel="stylesheet">
</head>
<body class="h-full">

<div class="flex min-h-full flex-col justify-center px-6 py-12 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-sm text-center">
        <img src="https://tailwindcss.com/plus-assets/img/logos/mark.svg?color=indigo&shade=500"
             alt="MediTrack Logo"
             class="mx-auto h-12 w-auto"/>
        <h2 class="mt-10 text-center text-3xl font-bold tracking-tight text-white">Sign in to your account</h2>
        <p class="mt-2 text-center text-sm text-gray-400">
            Manage your medicine inventory efficiently
        </p>

        <!-- Success Message (from registration) -->
        <?php if ($success): ?>
            <div class="mt-4 rounded-md bg-green-900/50 p-4">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-green-400 mr-2"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm text-green-300">Registration successful! Please sign in.</p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Account Deleted Message -->
        <?php if ($deleted): ?>
            <div class="mt-4 rounded-md bg-blue-900/50 p-4">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-blue-400 mr-2"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm text-blue-300">Your account has been successfully deleted. We're sorry to see you
                        go!</p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Error Message -->
        <?php if ($error): ?>
            <div class="mt-4 rounded-md bg-red-900/50 p-4">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-red-400 mr-2"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm text-red-300"><?= htmlspecialchars($error) ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
        <form method="POST"
              class="space-y-6"
              autocomplete="on">
            <!-- Username Field -->
            <div>
                <label for="username"
                       class="block text-sm font-medium text-gray-100">Username</label>
                <div class="mt-2 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-500"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <input id="username"
                           name="username"
                           type="text"
                           required
                           value="<?= htmlspecialchars($remembered_user) ?>"
                           autocomplete="username"
                           class="block w-full rounded-md bg-white/5 pl-10 pr-3 py-3 text-white outline-1 -outline-offset-1 outline-white/10 placeholder:text-gray-500 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-500 sm:text-sm transition"
                           placeholder="Enter your username"/>
                </div>
            </div>

            <!-- Password Field -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label for="password"
                           class="block text-sm font-medium text-gray-100">Password</label>
                    <a href="forgot_password.php"
                       class="text-sm font-semibold text-indigo-400 hover:text-indigo-300 transition">Forgot
                        password?</a>
                </div>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-500"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <input id="password"
                           name="password"
                           type="password"
                           required
                           autocomplete="current-password"
                           class="block w-full rounded-md bg-white/5 pl-10 pr-10 py-3 text-white outline-1 -outline-offset-1 outline-white/10 placeholder:text-gray-500 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-500 sm:text-sm transition"
                           placeholder="Enter your password"/>
                    <button type="button"
                            onclick="togglePassword()"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <svg id="eyeIcon"
                             class="h-5 w-5 text-gray-500 hover:text-gray-300 transition"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Remember Me -->
            <div class="flex items-center">
                <input id="remember"
                       name="remember"
                       type="checkbox"
                       class="h-4 w-4 rounded border-gray-600 bg-white/5 text-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0 transition">
                <label for="remember"
                       class="ml-2 block text-sm text-gray-300">
                    Remember me for 30 days
                </label>
            </div>

            <!-- Submit Button -->
            <div>
                <button type="submit"
                        class="flex w-full justify-center rounded-md bg-indigo-500 px-3 py-3 text-sm font-semibold text-white hover:bg-indigo-400 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500 transition shadow-lg shadow-indigo-500/50">
                    Sign in
                </button>
            </div>
        </form>

        <!-- Register Link -->
        <p class="mt-10 text-center text-sm text-gray-400">
            Not a member?
            <a href="register.php"
               class="font-semibold text-indigo-400 hover:text-indigo-300 transition">Create an account</a>
        </p>

        <!-- Demo Credentials -->
        <div class="mt-8 p-4 rounded-lg bg-gray-800/50 border border-gray-700">
            <p class="text-xs text-gray-400 text-center mb-2">Demo Credentials:</p>
            <p class="text-xs text-gray-300 text-center"><strong>Username:</strong> admin | <strong>Password:</strong>
                admin123</p>
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