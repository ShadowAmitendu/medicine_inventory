<?php
global $pdo;
require_once '../config/auth.php';
require_once '../config/db.php';
require_once '../config/paths.php';
requireAuth();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = trim($_POST['current_password']);
    $new = trim($_POST['new_password']);
    $confirm = trim($_POST['confirm_password']);

    if (empty($current) || empty($new) || empty($confirm)) {
        $error = "All fields are required.";
    } elseif (strlen($new) < 6) {
        $error = "New password must be at least 6 characters long.";
    } elseif ($new !== $confirm) {
        $error = "New password and confirm password do not match.";
    } elseif ($current === $new) {
        $error = "New password must be different from current password.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($current, $user['password'])) {
                $hashed = password_hash($new, PASSWORD_DEFAULT);
                $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");

                if ($update->execute([$hashed, $_SESSION['user_id']])) {
                    $success = "Password changed successfully! You can now use your new password to log in.";
                    $_POST = [];
                } else {
                    $error = "Failed to update password. Please try again.";
                }
            } else {
                $error = "Current password is incorrect.";
            }
        } catch (PDOException $e) {
            $error = "An error occurred. Please try again.";
            error_log("Change password error: " . $e->getMessage());
        }
    }
}

$pageTitle = "Change Password";
include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en"
      class="h-full bg-gradient-to-br from-secondary-100 via-white to-secondary-200">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>Change Password - Medicine Inventory</title>
    <link href="<?= assetUrl('output.css') ?>"
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
    </style>
</head>
<body class="">

<main class="mx-auto max-w-2xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-200">

        <!-- Header -->
        <div class="flex items-center mb-6 pb-6 border-b border-gray-200">
            <div class="bg-primary-500/10 p-3 rounded-xl mr-3 shadow-sm">
                <svg class="h-6 w-6 text-primary-600"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-2xl font-bold bg-gradient-to-r from-primary-600 to-primary-500 bg-clip-text text-transparent">
                    Change Password</h2>
                <p class="text-sm text-gray-600 mt-0.5">Update your account security</p>
            </div>
        </div>

        <!-- Info Banner -->
        <div class="mb-6 rounded-xl bg-gradient-to-r from-primary-50 to-accent-50 p-5 border border-primary-200">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-primary-600"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-3 text-sm text-gray-700">
                    <p class="font-semibold text-primary-900 mb-2">Password Requirements:</p>
                    <ul class="space-y-1.5">
                        <li class="flex items-center">
                            <svg class="h-4 w-4 text-primary-600 mr-2 flex-shrink-0"
                                 fill="currentColor"
                                 viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                      d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                      clip-rule="evenodd"/>
                            </svg>
                            Minimum 6 characters long
                        </li>
                        <li class="flex items-center">
                            <svg class="h-4 w-4 text-primary-600 mr-2 flex-shrink-0"
                                 fill="currentColor"
                                 viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                      d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                      clip-rule="evenodd"/>
                            </svg>
                            Must be different from current password
                        </li>
                        <li class="flex items-center">
                            <svg class="h-4 w-4 text-primary-600 mr-2 flex-shrink-0"
                                 fill="currentColor"
                                 viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                      d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                      clip-rule="evenodd"/>
                            </svg>
                            Use a strong, unique password
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Messages -->
        <?php if ($error): ?>
            <div class="mb-6 rounded-xl bg-red-50 p-4 border border-red-200 animate-fadeIn">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-red-600 mr-3 flex-shrink-0"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm text-red-800 font-medium"><?= htmlspecialchars($error) ?></p>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="mb-6 rounded-xl bg-green-50 p-4 border border-green-200 animate-fadeIn">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-green-600 mr-3 flex-shrink-0"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm text-green-800 font-medium"><?= htmlspecialchars($success) ?></p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Change Password Form -->
        <form method="POST"
              class="space-y-6">
            <div>
                <label for="current_password"
                       class="block text-sm font-medium text-gray-700 mb-2">
                    Current Password <span class="text-red-500">*</span>
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-primary-500 transition-colors"
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
                           id="current_password"
                           name="current_password"
                           required
                           class="block w-full rounded-xl bg-gray-50 pl-10 pr-4 py-3.5 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 border border-gray-300 transition-all"
                           placeholder="Enter current password"/>
                </div>
            </div>

            <div class="border-t border-gray-200 pt-6 space-y-6">
                <div>
                    <label for="new_password"
                           class="block text-sm font-medium text-gray-700 mb-2">
                        New Password <span class="text-red-500">*</span>
                    </label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400 group-focus-within:text-primary-500 transition-colors"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                            </svg>
                        </div>
                        <input type="password"
                               id="new_password"
                               name="new_password"
                               required
                               minlength="6"
                               class="block w-full rounded-xl bg-gray-50 pl-10 pr-4 py-3.5 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 border border-gray-300 transition-all"
                               placeholder="Enter new password"/>
                    </div>
                    <p class="mt-2 text-xs text-gray-500">Must be at least 6 characters</p>
                </div>

                <div>
                    <label for="confirm_password"
                           class="block text-sm font-medium text-gray-700 mb-2">
                        Confirm New Password <span class="text-red-500">*</span>
                    </label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400 group-focus-within:text-primary-500 transition-colors"
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
                               minlength="6"
                               class="block w-full rounded-xl bg-gray-50 pl-10 pr-4 py-3.5 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 border border-gray-300 transition-all"
                               placeholder="Confirm new password"/>
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 pt-4">
                <button type="submit"
                        class="flex-1 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-semibold py-3.5 px-4 rounded-xl transition-all shadow-lg shadow-primary-500/30 hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center">
                    <svg class="h-5 w-5 mr-2"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M5 13l4 4L19 7"/>
                    </svg>
                    Change Password
                </button>
                <a href="<?= URL_PROFILE ?>"
                   class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3.5 px-4 rounded-xl transition-all text-center border border-gray-300 flex items-center justify-center">
                    <svg class="h-5 w-5 mr-2"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Cancel
                </a>
            </div>
        </form>

        <!-- Security Tips -->
        <div class="mt-8 pt-6 border-t border-gray-200">
            <div class="flex items-center mb-4">
                <svg class="h-5 w-5 text-primary-600 mr-2"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <h3 class="text-sm font-semibold text-gray-900">Security Best Practices</h3>
            </div>
            <ul class="space-y-3">
                <li class="flex items-start text-sm text-gray-600">
                    <svg class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5"
                         fill="currentColor"
                         viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                              d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                              clip-rule="evenodd"/>
                    </svg>
                    Use a unique password that you don't use elsewhere
                </li>
                <li class="flex items-start text-sm text-gray-600">
                    <svg class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5"
                         fill="currentColor"
                         viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                              d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                              clip-rule="evenodd"/>
                    </svg>
                    Consider using a password manager for stronger security
                </li>
                <li class="flex items-start text-sm text-gray-600">
                    <svg class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5"
                         fill="currentColor"
                         viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                              d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                              clip-rule="evenodd"/>
                    </svg>
                    Never share your password with anyone
                </li>
            </ul>
        </div>

    </div>
</main>

<script>
    const successMsg = document.querySelector('.bg-green-50');
    if (successMsg) {
        setTimeout(() => {
            successMsg.style.transition = 'opacity 0.5s';
            successMsg.style.opacity = '0';
            setTimeout(() => successMsg.remove(), 500);
        }, 5000);
    }
</script>

<?php include '../includes/footer.php'; ?>

</body>
</html>