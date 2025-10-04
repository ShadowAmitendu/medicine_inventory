<?php
global $pdo;
require_once '../config/db.php';
require_once '../config/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $profile_pic = $_FILES['profile_pic'] ?? null;

    // Validation
    if (empty($username) || empty($name) || empty($email) || empty($password)) {
        $error = "All fields except phone and profile picture are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($username) < 3) {
        $error = "Username must be at least 3 characters long.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if username or email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $error = "Username or email already exists.";
        } else {
            // Handle profile picture upload
            $profile_filename = null;
            if ($profile_pic && $profile_pic['tmp_name'] && $profile_pic['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($profile_pic['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];

                // Check file size (max 5MB)
                if ($profile_pic['size'] > 5 * 1024 * 1024) {
                    $error = "Profile picture must be less than 5MB.";
                } elseif (!in_array($ext, $allowed)) {
                    $error = "Invalid profile picture format. Allowed: jpg, jpeg, png, gif.";
                } else {
                    $profile_filename = uniqid('profile_', true) . '.' . $ext;
                    $upload_path = "../uploads/$profile_filename";

                    // Create uploads directory if it doesn't exist
                    if (!file_exists('../uploads')) {
                        mkdir('../uploads', 0755, true);
                    }

                    if (!move_uploaded_file($profile_pic['tmp_name'], $upload_path)) {
                        $error = "Failed to upload profile picture.";
                        $profile_filename = null;
                    }
                }
            }

            // Insert user into database
            if (!$error) {
                try {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO users (username, password, name, email, phone, profile_pic) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$username, $hashed_password, $name, $email, $phone, $profile_filename]);
                    $success = "Registration successful! You can now <a href='login.php?registered=1' class='text-primary-400 underline hover:text-primary-300 font-semibold'>log in</a>.";
                } catch (PDOException $e) {
                    $error = "Registration failed. Please try again.";
                    error_log("Registration error: " . $e->getMessage());
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en"
      class="h-full bg-gradient-to-br from-primary-900 via-gray-900 to-gray-800">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>Register - Medicine Inventory</title>
    <link href="../assets/output.css"
          rel="stylesheet">
    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fadeIn {
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-20px);
            }
        }

        .float-animation {
            animation: float 6s ease-in-out infinite;
        }
    </style>
</head>
<body class="bg-gradient-to-br relative from-primary-900 via-gray-900 to-gray-800">

<!-- Background decorative elements -->
<div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZGVmcz48cGF0dGVybiBpZD0iZ3JpZCIgd2lkdGg9IjQwIiBoZWlnaHQ9IjQwIiBwYXR0ZXJuVW5pdHM9InVzZXJTcGFjZU9uVXNlIj48cGF0aCBkPSJNIDQwIDAgTCAwIDAgMCA0MCIgZmlsbD0ibm9uZSIgc3Ryb2tlPSJ3aGl0ZSIgc3Ryb2tlLW9wYWNpdHk9IjAuMDMiIHN0cm9rZS13aWR0aD0iMSIvPjwvcGF0dGVybj48L2RlZnM+PHJlY3Qgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgZmlsbD0idXJsKCNncmlkKSIvPjwvc3ZnPg==')] opacity-40"></div>
<div class="absolute top-20 left-20 w-72 h-72 bg-primary-600/20 rounded-full blur-3xl float-animation"></div>
<div class="absolute bottom-20 right-20 w-96 h-96 bg-accent-600/15 rounded-full blur-3xl float-animation"
     style="animation-delay: 2s;"></div>

<div class="relative flex min-h-full flex-col justify-center px-6 py-12 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-lg">
        <!-- Logo and Header -->
        <div class="text-center mb-8">
            <div class="flex justify-center mb-6">
                <div class="relative group">
                    <div class="absolute -inset-2 bg-gradient-to-r from-primary-500 to-accent-500 rounded-2xl blur-lg opacity-30 group-hover:opacity-50 transition-opacity"></div>
                    <div class="relative size-16 bg-gradient-to-br from-primary-500 to-primary-600 rounded-2xl flex items-center justify-center shadow-2xl transform group-hover:scale-110 transition-transform">
                        <svg class="size-10 text-white"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                    </div>
                </div>
            </div>
            <h2 class="text-4xl font-bold tracking-tight bg-gradient-to-r from-white via-primary-100 to-white bg-clip-text text-transparent mb-3">
                Create Account
            </h2>
            <p class="text-gray-400">Join Medicine Inventory System</p>
        </div>

        <!-- Main Card -->
        <div class="bg-gray-800/50 backdrop-blur-xl border border-gray-700/50 rounded-2xl shadow-2xl p-8">
            <!-- Messages -->
            <?php if ($error): ?>
                <div class="mb-6 rounded-xl bg-red-500/10 backdrop-blur-sm p-4 border border-red-500/30 shadow-lg animate-fadeIn">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-red-400 mr-3 flex-shrink-0"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm text-red-300 font-medium"><?= htmlspecialchars($error) ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="mb-6 rounded-xl bg-green-500/10 backdrop-blur-sm p-4 border border-green-500/30 shadow-lg animate-fadeIn">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-green-400 mr-3 flex-shrink-0"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm text-green-300 font-medium"><?= $success ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Registration Form -->
            <form method="POST"
                  enctype="multipart/form-data"
                  class="space-y-5">
                <div>
                    <label for="name"
                           class="block text-sm font-medium text-gray-200 mb-2">
                        Full Name <span class="text-red-400">*</span>
                    </label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-500 group-focus-within:text-primary-400 transition-colors"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <input type="text"
                               id="name"
                               name="name"
                               required
                               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                               class="block w-full rounded-xl bg-gray-700/50 pl-10 pr-4 py-3.5 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 border border-gray-600 focus:border-transparent transition-all"
                               placeholder="Enter your full name"/>
                    </div>
                </div>

                <div>
                    <label for="username"
                           class="block text-sm font-medium text-gray-200 mb-2">
                        Username <span class="text-red-400">*</span>
                    </label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-500 group-focus-within:text-primary-400 transition-colors"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <input type="text"
                               id="username"
                               name="username"
                               required
                               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                               class="block w-full rounded-xl bg-gray-700/50 pl-10 pr-4 py-3.5 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 border border-gray-600 focus:border-transparent transition-all"
                               placeholder="Choose a username"/>
                    </div>
                    <p class="mt-1.5 text-xs text-gray-400">At least 3 characters</p>
                </div>

                <div>
                    <label for="email"
                           class="block text-sm font-medium text-gray-200 mb-2">
                        Email Address <span class="text-red-400">*</span>
                    </label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-500 group-focus-within:text-primary-400 transition-colors"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <input type="email"
                               id="email"
                               name="email"
                               required
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                               class="block w-full rounded-xl bg-gray-700/50 pl-10 pr-4 py-3.5 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 border border-gray-600 focus:border-transparent transition-all"
                               placeholder="you@example.com"/>
                    </div>
                </div>

                <div>
                    <label for="phone"
                           class="block text-sm font-medium text-gray-200 mb-2">
                        Phone Number <span class="text-gray-400 text-xs">(optional)</span>
                    </label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-500 group-focus-within:text-primary-400 transition-colors"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                        </div>
                        <input type="tel"
                               id="phone"
                               name="phone"
                               value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                               class="block w-full rounded-xl bg-gray-700/50 pl-10 pr-4 py-3.5 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 border border-gray-600 focus:border-transparent transition-all"
                               placeholder="+1 (555) 000-0000"/>
                    </div>
                </div>

                <div>
                    <label for="password"
                           class="block text-sm font-medium text-gray-200 mb-2">
                        Password <span class="text-red-400">*</span>
                    </label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-500 group-focus-within:text-primary-400 transition-colors"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input type="password"
                               id="password"
                               name="password"
                               required
                               class="block w-full rounded-xl bg-gray-700/50 pl-10 pr-4 py-3.5 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 border border-gray-600 focus:border-transparent transition-all"
                               placeholder="Create a password"/>
                    </div>
                    <p class="mt-1.5 text-xs text-gray-400">Minimum 6 characters</p>
                </div>

                <div>
                    <label for="confirm_password"
                           class="block text-sm font-medium text-gray-200 mb-2">
                        Confirm Password <span class="text-red-400">*</span>
                    </label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-500 group-focus-within:text-primary-400 transition-colors"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <input type="password"
                               id="confirm_password"
                               name="confirm_password"
                               required
                               class="block w-full rounded-xl bg-gray-700/50 pl-10 pr-4 py-3.5 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 border border-gray-600 focus:border-transparent transition-all"
                               placeholder="Confirm your password"/>
                    </div>
                </div>

                <div>
                    <label for="profile_pic"
                           class="block text-sm font-medium text-gray-200 mb-2">
                        Profile Picture <span class="text-gray-400 text-xs">(optional)</span>
                    </label>
                    <div class="relative">
                        <input type="file"
                               id="profile_pic"
                               name="profile_pic"
                               accept="image/jpeg,image/jpg,image/png,image/gif"
                               class="block w-full text-sm text-gray-300 file:mr-4 file:py-3 file:px-6 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-gradient-to-r file:from-primary-500 file:to-primary-600 file:text-white hover:file:from-primary-600 hover:file:to-primary-700 file:shadow-lg file:shadow-primary-500/30 transition-all border border-gray-600 rounded-xl bg-gray-700/50 py-3 px-4"/>
                    </div>
                    <p class="mt-1.5 text-xs text-gray-400">Max size: 5MB. Formats: JPG, PNG, GIF</p>
                </div>

                <div class="pt-4">
                    <button type="submit"
                            class="w-full bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-semibold py-3.5 px-4 rounded-xl transition-all shadow-lg shadow-primary-500/30 hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center">
                        <svg class="h-5 w-5 mr-2"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                        Create Account
                    </button>
                </div>
            </form>

            <!-- Login Link -->
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-400">
                    Already have an account?
                    <a href="login.php"
                       class="font-semibold text-primary-400 hover:text-primary-300 transition-colors">
                        Sign in here
                    </a>
                </p>
            </div>
        </div>

        <!-- Additional Info -->
        <div class="mt-6 text-center">
            <p class="text-xs text-gray-500">
                By creating an account, you agree to our terms of service and privacy policy.
            </p>
        </div>
    </div>
</div>

<script>
    const successMsg = document.querySelector('.bg-green-500\\/10');
    if (successMsg) {
        setTimeout(() => {
            successMsg.style.transition = 'opacity 0.5s';
            successMsg.style.opacity = '0';
            setTimeout(() => successMsg.remove(), 500);
        }, 7000);
    }
</script>

</body>
</html>