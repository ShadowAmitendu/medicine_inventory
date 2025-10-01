<?php
global $pdo;
require_once '../config/auth.php';
require_once '../config/db.php';
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

// Set custom page title
$pageTitle = "My Profile";

include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en"
      class="h-full bg-gray-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>Profile - Medicine Inventory</title>
    <link href="../assets/output.css"
          rel="stylesheet">
</head>
<body class="h-full ">

<main class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="bg-gray-800 rounded-lg shadow-lg p-8">

        <!-- Profile Header -->
        <div class="flex items-center space-x-6 mb-8 pb-8 border-b border-gray-700">
            <img src="<?= !empty($user['profile_pic']) ? '../uploads/' . htmlspecialchars($user['profile_pic']) : 'https://ui-avatars.com/api/?name=' . urlencode($user['name']) . '&background=6366f1&color=fff&size=128' ?>"
                 alt="Profile Picture"
                 class="h-24 w-24 rounded-full object-cover ring-4 ring-indigo-500/50">
            <div class="flex-1">
                <h2 class="text-3xl font-bold text-white"><?= htmlspecialchars($user['name']) ?></h2>
                <p class="text-sm text-gray-400 mt-1">@<?= htmlspecialchars($user['username']) ?></p>
                <p class="text-xs text-gray-500 mt-2">
                    <span class="inline-flex items-center">
                        <svg class="h-4 w-4 mr-1"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                        </svg>
                        Profile #<?= str_pad($user['id'], 4, '0', STR_PAD_LEFT) ?>
                    </span>
                    <span class="mx-2">•</span>
                    Member since <?= date('F Y', strtotime($user['created_at'])) ?>
                </p>
            </div>
        </div>

        <!-- Messages -->
        <?php if ($error): ?>
            <div class="mb-6 rounded-md bg-red-900/50 p-4">
                <p class="text-sm text-red-300"><?= htmlspecialchars($error) ?></p>
            </div>
        <?php elseif ($success): ?>
            <div class="mb-6 rounded-md bg-green-900/50 p-4">
                <p class="text-sm text-green-300"><?= htmlspecialchars($success) ?></p>
            </div>
        <?php endif; ?>

        <!-- Edit Profile Form -->
        <form method="POST"
              enctype="multipart/form-data"
              class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div>
                    <label for="name"
                           class="block text-sm font-medium text-gray-100 mb-2">Full Name *</label>
                    <input type="text"
                           id="name"
                           name="name"
                           required
                           value="<?= htmlspecialchars($user['name']) ?>"
                           class="block w-full rounded-md bg-white/5 px-4 py-3 text-white placeholder-gray-400 focus:outline-2 focus:outline-indigo-500 transition"/>
                </div>

                <div>
                    <label for="username"
                           class="block text-sm font-medium text-gray-100 mb-2">Username *</label>
                    <input type="text"
                           id="username"
                           name="username"
                           required
                           minlength="3"
                           value="<?= htmlspecialchars($user['username']) ?>"
                           class="block w-full rounded-md bg-white/5 px-4 py-3 text-white placeholder-gray-400 focus:outline-2 focus:outline-indigo-500 transition"/>
                </div>

                <div>
                    <label for="email"
                           class="block text-sm font-medium text-gray-100 mb-2">Email Address *</label>
                    <input type="email"
                           id="email"
                           name="email"
                           required
                           value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                           class="block w-full rounded-md bg-white/5 px-4 py-3 text-white placeholder-gray-400 focus:outline-2 focus:outline-indigo-500 transition"
                           placeholder="you@example.com"/>
                </div>

                <div>
                    <label for="phone"
                           class="block text-sm font-medium text-gray-100 mb-2">Phone Number</label>
                    <input type="tel"
                           id="phone"
                           name="phone"
                           value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                           class="block w-full rounded-md bg-white/5 px-4 py-3 text-white placeholder-gray-400 focus:outline-2 focus:outline-indigo-500 transition"
                           placeholder="+1 (555) 000-0000"/>
                </div>

            </div>

            <div>
                <label for="profile_pic"
                       class="block text-sm font-medium text-gray-100 mb-2">Change Profile Picture</label>
                <input type="file"
                       id="profile_pic"
                       name="profile_pic"
                       accept="image/jpeg,image/jpg,image/png,image/gif"
                       class="block w-full text-sm text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-500 file:text-white hover:file:bg-indigo-400 transition"/>
                <p class="mt-1 text-xs text-gray-400">Max size: 5MB. Formats: JPG, PNG, GIF. Leave empty to keep current
                    image.</p>
            </div>

            <div class="flex gap-4 pt-4">
                <button type="submit"
                        class="flex-1 bg-indigo-500 hover:bg-indigo-400 text-white font-semibold py-3 px-4 rounded-md transition focus:outline-2 focus:outline-offset-2 focus:outline-indigo-500">
                    Update Profile
                </button>
                <a href="change_pw.php"
                   class="flex-1 bg-gray-700 hover:bg-gray-600 text-white font-semibold py-3 px-4 rounded-md transition text-center focus:outline-2 focus:outline-offset-2 focus:outline-gray-500">
                    Change Password
                </a>
            </div>
        </form>

        <!-- Danger Zone -->
        <div class="mt-8 pt-8 border-t border-red-900/50">
            <h3 class="text-lg font-semibold text-red-400 mb-4">Danger Zone</h3>
            <div class="bg-red-900/20 border border-red-900/50 rounded-lg p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <h4 class="text-white font-semibold mb-1">Delete Account</h4>
                        <p class="text-sm text-gray-400">Once you delete your account, there is no going back. Please be
                            certain.</p>
                    </div>
                    <button type="button"
                            onclick="openDeleteModal()"
                            class="bg-red-600 hover:bg-red-500 text-white font-semibold px-4 py-2 rounded-md transition focus:outline-2 focus:outline-offset-2 focus:outline-red-500 flex-shrink-0">
                        Delete Account
                    </button>
                </div>
            </div>
        </div>

    </div>
</main>

<!-- Delete Account Modal -->
<div id="deleteModal"
     class="hidden fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6 border border-red-900/50">
        <div class="flex items-center space-x-3 mb-4">
            <div class="bg-red-900/30 p-3 rounded-full">
                <svg class="h-6 w-6 text-red-400"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-white">Delete Account</h3>
        </div>

        <p class="text-gray-300 mb-6">
            Are you absolutely sure you want to delete your account? This action cannot be undone. All your data will be
            permanently removed.
        </p>

        <?php if ($deleteError): ?>
            <div class="mb-4 rounded-md bg-red-900/50 p-3 border border-red-700">
                <p class="text-sm text-red-300"><?= htmlspecialchars($deleteError) ?></p>
            </div>
        <?php endif; ?>

        <form method="POST"
              class="space-y-4">
            <div>
                <label for="confirm_delete_password"
                       class="block text-sm font-medium text-gray-100 mb-2">
                    Enter your password to confirm
                </label>
                <input type="password"
                       id="confirm_delete_password"
                       name="confirm_delete_password"
                       required
                       class="block w-full rounded-md bg-white/5 px-4 py-3 text-white placeholder-gray-400 focus:outline-2 focus:outline-red-500 transition"
                       placeholder="Enter your password"/>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button"
                        onclick="closeDeleteModal()"
                        class="flex-1 bg-gray-700 hover:bg-gray-600 text-white font-semibold py-3 px-4 rounded-md transition">
                    Cancel
                </button>
                <button type="submit"
                        name="delete_profile"
                        class="flex-1 bg-red-600 hover:bg-red-500 text-white font-semibold py-3 px-4 rounded-md transition focus:outline-2 focus:outline-offset-2 focus:outline-red-500">
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

    // Close modal on ESC key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeDeleteModal();
        }
    });

    // Close modal on backdrop click
    document.getElementById('deleteModal').addEventListener('click', function (e) {
        if (e.target === this) {
            closeDeleteModal();
        }
    });

    <?php if ($deleteError): ?>
    // Auto-open modal if there's a delete error
    openDeleteModal();
    <?php endif; ?>
</script>
</form>

</div>
</main>

<?php include '../includes/footer.php'; ?>

</body>
</html>