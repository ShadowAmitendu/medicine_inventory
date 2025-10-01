<?php
global $pdo;
require_once '../config/auth.php';
require_once '../config/db.php';
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
        // Verify current password
        try {
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($current, $user['password'])) {
                // Update password
                $hashed = password_hash($new, PASSWORD_DEFAULT);
                $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");

                if ($update->execute([$hashed, $_SESSION['user_id']])) {
                    $success = "Password changed successfully! You can now use your new password to log in.";

                    // Clear form
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

// Set custom page title
$pageTitle = "Change Password";

include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en"
      class="h-full bg-gray-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>Change Password - Medicine Inventory</title>
    <link href="../assets/output.css"
          rel="stylesheet">
</head>
<body class="h-full">

<main class="mx-auto max-w-2xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="bg-gray-800 rounded-lg shadow-lg p-8">

        <!-- Info Banner -->
        <div class="mb-6 rounded-md bg-blue-900/50 p-4 border border-blue-700">
            <div class="flex">
                <svg class="h-5 w-5 text-blue-400 mr-3 flex-shrink-0 mt-0.5"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="text-sm text-blue-300">
                    <p><strong class="font-semibold">Password Requirements:</strong></p>
                    <ul class="mt-2 list-disc list-inside space-y-1">
                        <li>Minimum 6 characters long</li>
                        <li>Must be different from current password</li>
                        <li>Use a strong, unique password</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Messages -->
        <?php if ($error): ?>
            <div class="mb-6 rounded-md bg-red-900/50 p-4 border border-red-700">
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

        <?php if ($success): ?>
            <div class="mb-6 rounded-md bg-green-900/50 p-4 border border-green-700">
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
                    <p class="text-sm text-green-300"><?= htmlspecialchars($success) ?></p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Change Password Form -->
        <form method="POST"
              class="space-y-6">
            <div>
                <label for="current_password"
                       class="block text-sm font-medium text-gray-100 mb-2">Current Password</label>
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
                    <input type="password"
                           id="current_password"
                           name="current_password"
                           required
                           class="block w-full rounded-md bg-white/5 pl-10 pr-3 py-3 text-white placeholder-gray-400 focus:outline-2 focus:outline-indigo-500 transition"
                           placeholder="Enter current password"/>
                </div>
            </div>

            <div class="border-t border-gray-700 pt-6">
                <div class="mb-6">
                    <label for="new_password"
                           class="block text-sm font-medium text-gray-100 mb-2">New Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-500"
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
                               class="block w-full rounded-md bg-white/5 pl-10 pr-3 py-3 text-white placeholder-gray-400 focus:outline-2 focus:outline-indigo-500 transition"
                               placeholder="Enter new password"/>
                    </div>
                    <p class="mt-1 text-xs text-gray-400">Must be at least 6 characters</p>
                </div>

                <div>
                    <label for="confirm_password"
                           class="block text-sm font-medium text-gray-100 mb-2">Confirm New Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-500"
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
                               class="block w-full rounded-md bg-white/5 pl-10 pr-3 py-3 text-white placeholder-gray-400 focus:outline-2 focus:outline-indigo-500 transition"
                               placeholder="Confirm new password"/>
                    </div>
                </div>
            </div>

            <div class="flex gap-4 pt-4">
                <button type="submit"
                        class="flex-1 bg-indigo-500 hover:bg-indigo-400 text-white font-semibold py-3 px-4 rounded-md transition focus:outline-2 focus:outline-offset-2 focus:outline-indigo-500 shadow-lg shadow-indigo-500/50">
                    Change Password
                </button>
                <a href="profile.php"
                   class="flex-1 bg-gray-700 hover:bg-gray-600 text-white font-semibold py-3 px-4 rounded-md transition text-center focus:outline-2 focus:outline-offset-2 focus:outline-gray-500">
                    Cancel
                </a>
            </div>
        </form>

        <!-- Security Tips -->
        <div class="mt-8 pt-6 border-t border-gray-700">
            <h3 class="text-sm font-semibold text-gray-300 mb-3">Security Tips:</h3>
            <ul class="space-y-2 text-sm text-gray-400">
                <li class="flex items-start">
                    <svg class="h-5 w-5 text-green-500 mr-2 flex-shrink-0"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M5 13l4 4L19 7"/>
                    </svg>
                    Use a unique password that you don't use elsewhere
                </li>
                <li class="flex items-start">
                    <svg class="h-5 w-5 text-green-500 mr-2 flex-shrink-0"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M5 13l4 4L19 7"/>
                    </svg>
                    Consider using a password manager
                </li>
                <li class="flex items-start">
                    <svg class="h-5 w-5 text-green-500 mr-2 flex-shrink-0"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M5 13l4 4L19 7"/>
                    </svg>
                    Never share your password with anyone
                </li>
            </ul>
        </div>

    </div>
</main>

<?php include '../includes/footer.php'; ?>

</body>
</html>