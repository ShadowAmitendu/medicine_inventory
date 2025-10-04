<?php
global $pdo;
require_once '../config/auth.php';
require_once '../config/db.php';
require_once '../config/paths.php';
requireAuth();

$error = '';
$success = '';
$deleteError = '';

// Handle profile deletion
if (isset($_POST['delete_profile'])) {
    $confirmPassword = trim($_POST['confirm_delete_password']);

    try {
        $stmt = $pdo->prepare("SELECT password, profile_pic FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $userData = $stmt->fetch();

        if ($userData && password_verify($confirmPassword, $userData['password'])) {
            // Delete profile picture if exists
            if ($userData['profile_pic'] && file_exists("../uploads/" . $userData['profile_pic'])) {
                unlink("../uploads/" . $userData['profile_pic']);
            }

            // Delete user account
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);

            // Logout and redirect
            session_destroy();
            header('Location: login.php?deleted=1');
            exit;
        } else {
            $deleteError = "Incorrect password. Account deletion failed.";
        }
    } catch (PDOException $e) {
        $deleteError = "Failed to delete account.";
        error_log("Delete account error: " . $e->getMessage());
    }
}

// Fetch current user data
try {
    $stmt = $pdo->prepare("SELECT id, username, name, email, phone, profile_pic, created_at FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user) {
        header('Location: logout.php');
        exit;
    }
} catch (PDOException $e) {
    $error = "Failed to fetch user data.";
    error_log("Fetch user error: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete_profile'])) {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $profile_pic = $_FILES['profile_pic'] ?? null;

    if (empty($name) || empty($username) || empty($email)) {
        $error = "Name, username, and email are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($username) < 3) {
        $error = "Username must be at least 3 characters long.";
    } else {
        // Check if username or email already exists for other users
        $stmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
        $stmt->execute([$username, $email, $_SESSION['user_id']]);
        if ($stmt->fetch()) {
            $error = "Username or email already exists.";
        } else {
            $profile_filename = $user['profile_pic'];

            // Handle new profile picture upload
            if ($profile_pic && $profile_pic['tmp_name'] && $profile_pic['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($profile_pic['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];

                if ($profile_pic['size'] > 5 * 1024 * 1024) {
                    $error = "Profile picture must be less than 5MB.";
                } elseif (!in_array($ext, $allowed)) {
                    $error = "Invalid format. Allowed: jpg, jpeg, png, gif.";
                } else {
                    // Delete old profile picture if exists
                    if ($user['profile_pic'] && file_exists("../uploads/" . $user['profile_pic'])) {
                        unlink("../uploads/" . $user['profile_pic']);
                    }

                    $profile_filename = uniqid('profile_', true) . '.' . $ext;
                    $upload_path = "../uploads/$profile_filename";

                    if (!file_exists('../uploads')) {
                        mkdir('../uploads', 0755, true);
                    }

                    if (!move_uploaded_file($profile_pic['tmp_name'], $upload_path)) {
                        $error = "Failed to upload profile picture.";
                        $profile_filename = $user['profile_pic'];
                    }
                }
            }

            // Update user profile
            if (!$error) {
                try {
                    $stmt = $pdo->prepare("UPDATE users SET name = ?, username = ?, email = ?, phone = ?, profile_pic = ? WHERE id = ?");
                    $stmt->execute([$name, $username, $email, $phone, $profile_filename, $_SESSION['user_id']]);

                    // Update session
                    $_SESSION['name'] = $name;
                    $_SESSION['username'] = $username;
                    $_SESSION['profile_pic'] = $profile_filename;

                    $success = "Profile updated successfully!";

                    // Refresh user data
                    $stmt = $pdo->prepare("SELECT id, username, name, email, phone, profile_pic, created_at FROM users WHERE id = ?");
                    $stmt->execute([$_SESSION['user_id']]);
                    $user = $stmt->fetch();
                } catch (PDOException $e) {
                    $error = "Failed to update profile.";
                    error_log("Profile update error: " . $e->getMessage());
                }
            }
        }
    }
}

$pageTitle = "My Profile";
include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en"
      class="h-full bg-gradient-to-br from-secondary-100 via-white to-secondary-200">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>Profile - Medicine Inventory</title>
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
<body class="h-full">

<main class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-200">

        <!-- Profile Header -->
        <div class="flex flex-col sm:flex-row items-center sm:items-start space-y-4 sm:space-y-0 sm:space-x-6 mb-8 pb-8 border-b border-gray-200">
            <div class="relative group">
                <div class="absolute -inset-1 bg-gradient-to-r from-primary-500 to-accent-500 rounded-full blur opacity-30 group-hover:opacity-50 transition"></div>
                <img src="<?= !empty($user['profile_pic']) ? '../uploads/' . htmlspecialchars($user['profile_pic']) : 'https://ui-avatars.com/api/?name=' . urlencode($user['name']) . '&background=00809D&color=fff&size=128' ?>"
                     alt="Profile Picture"
                     class="relative h-24 w-24 rounded-full object-cover ring-4 ring-primary-200 shadow-lg">
            </div>
            <div class="flex-1 text-center sm:text-left">
                <h2 class="text-3xl font-bold bg-gradient-to-r from-primary-600 to-primary-500 bg-clip-text text-transparent"><?= htmlspecialchars($user['name']) ?></h2>
                <p class="text-sm text-gray-600 mt-1 font-medium">@<?= htmlspecialchars($user['username']) ?></p>
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-3 mt-3 text-xs text-gray-500">
                    <span class="inline-flex items-center px-3 py-1 bg-primary-50 text-primary-700 rounded-full font-medium">
                        <svg class="h-3.5 w-3.5 mr-1.5"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                        </svg>
                        ID #<?= str_pad($user['id'], 4, '0', STR_PAD_LEFT) ?>
                    </span>
                    <span class="inline-flex items-center">
                        <svg class="h-3.5 w-3.5 mr-1.5 text-gray-400"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Joined <?= date('F Y', strtotime($user['created_at'])) ?>
                    </span>
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
        <?php elseif ($success): ?>
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

        <!-- Edit Profile Form -->
        <form method="POST"
              enctype="multipart/form-data"
              class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name"
                           class="block text-sm font-medium text-gray-700 mb-2">
                        Full Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="name"
                           name="name"
                           required
                           value="<?= htmlspecialchars($user['name']) ?>"
                           class="block w-full rounded-xl bg-gray-50 px-4 py-3.5 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 border border-gray-300 transition-all"/>
                </div>

                <div>
                    <label for="username"
                           class="block text-sm font-medium text-gray-700 mb-2">
                        Username <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="username"
                           name="username"
                           required
                           minlength="3"
                           value="<?= htmlspecialchars($user['username']) ?>"
                           class="block w-full rounded-xl bg-gray-50 px-4 py-3.5 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 border border-gray-300 transition-all"/>
                </div>

                <div>
                    <label for="email"
                           class="block text-sm font-medium text-gray-700 mb-2">
                        Email Address <span class="text-red-500">*</span>
                    </label>
                    <input type="email"
                           id="email"
                           name="email"
                           required
                           value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                           class="block w-full rounded-xl bg-gray-50 px-4 py-3.5 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 border border-gray-300 transition-all"
                           placeholder="you@example.com"/>
                </div>

                <div>
                    <label for="phone"
                           class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                    <input type="tel"
                           id="phone"
                           name="phone"
                           value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                           class="block w-full rounded-xl bg-gray-50 px-4 py-3.5 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 border border-gray-300 transition-all"
                           placeholder="+1 (555) 000-0000"/>
                </div>
            </div>

            <div>
                <label for="profile_pic"
                       class="block text-sm font-medium text-gray-700 mb-2">Change Profile Picture</label>
                <input type="file"
                       id="profile_pic"
                       name="profile_pic"
                       accept="image/jpeg,image/jpg,image/png,image/gif"
                       class="block w-full text-sm text-gray-600 file:mr-4 file:py-3 file:px-5 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-gradient-to-r file:from-primary-500 file:to-primary-600 file:text-white hover:file:from-primary-600 hover:file:to-primary-700 file:transition-all file:shadow-md"/>
                <p class="mt-2 text-xs text-gray-500">Max size: 5MB. Formats: JPG, PNG, GIF. Leave empty to keep current
                    image.</p>
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
                    Update Profile
                </button>
                <a href="<?= URL_CHANGE_PASSWORD ?>"
                   class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3.5 px-4 rounded-xl transition-all text-center border border-gray-300 flex items-center justify-center">
                    <svg class="h-5 w-5 mr-2"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                    Change Password
                </a>
            </div>
        </form>

        <!-- Danger Zone -->
        <div class="mt-8 pt-8 border-t border-red-200">
            <div class="flex items-center mb-4">
                <svg class="h-5 w-5 text-red-600 mr-2"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <h3 class="text-lg font-semibold text-red-600">Danger Zone</h3>
            </div>
            <div class="bg-red-50 border border-red-200 rounded-xl p-6">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div>
                        <h4 class="text-gray-900 font-semibold mb-1">Delete Account</h4>
                        <p class="text-sm text-gray-600">Once you delete your account, there is no going back. Please be
                            certain.</p>
                    </div>
                    <button type="button"
                            onclick="openDeleteModal()"
                            class="bg-red-600 hover:bg-red-700 text-white font-semibold px-5 py-2.5 rounded-xl transition-all shadow-md hover:shadow-lg flex-shrink-0 flex items-center">
                        <svg class="h-4 w-4 mr-2"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Delete Account
                    </button>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Delete Account Modal -->
<div id="deleteModal"
     class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-8 border border-red-200 animate-fadeIn">
        <div class="flex items-center space-x-3 mb-6">
            <div class="bg-red-100 p-3 rounded-xl">
                <svg class="h-6 w-6 text-red-600"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-xl font-bold text-gray-900">Delete Account</h3>
                <p class="text-xs text-gray-500">This action cannot be undone</p>
            </div>
        </div>

        <p class="text-gray-700 mb-6 text-sm">
            Are you absolutely sure you want to delete your account? All your data will be permanently removed from our
            servers forever. This action cannot be undone.
        </p>

        <?php if ($deleteError): ?>
            <div class="mb-4 rounded-xl bg-red-50 p-3 border border-red-200">
                <p class="text-sm text-red-800"><?= htmlspecialchars($deleteError) ?></p>
            </div>
        <?php endif; ?>

        <form method="POST"
              class="space-y-4">
            <div>
                <label for="confirm_delete_password"
                       class="block text-sm font-medium text-gray-700 mb-2">
                    Enter your password to confirm
                </label>
                <input type="password"
                       id="confirm_delete_password"
                       name="confirm_delete_password"
                       required
                       class="block w-full rounded-xl bg-gray-50 px-4 py-3.5 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-red-500 border border-gray-300 transition-all"
                       placeholder="Enter your password"/>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button"
                        onclick="closeDeleteModal()"
                        class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3.5 px-4 rounded-xl transition-all border border-gray-300">
                    Cancel
                </button>
                <button type="submit"
                        name="delete_profile"
                        class="flex-1 bg-red-600 hover:bg-red-700 text-white font-semibold py-3.5 px-4 rounded-xl transition-all shadow-md hover:shadow-lg">
                    Delete Forever
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openDeleteModal() {
        document.getElementById('deleteModal').classList.remove('hidden');
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
        document.getElementById('confirm_delete_password').value = '';
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeDeleteModal();
    });

    document.getElementById('deleteModal').addEventListener('click', function (e) {
        if (e.target === this) closeDeleteModal();
    });

    <?php if ($deleteError): ?>
    openDeleteModal();
    <?php endif; ?>

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