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
                    $success = "Registration successful! You can now <a href='login.php?registered=1' class='text-indigo-400 underline hover:text-indigo-300'>log in</a>.";
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
<html class="h-full bg-gray-900"
      lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>Register - Medicine Inventory</title>
    <link href="../assets/output.css"
          rel="stylesheet">
</head>
<body class="h-full">

<div class="flex min-h-full flex-col justify-center px-6 py-12 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-sm text-center">
        <img src="https://tailwindcss.com/plus-assets/img/logos/mark.svg?color=indigo&shade=500"
             alt="Logo"
             class="mx-auto h-10 w-auto"/>
        <h2 class="mt-10 text-center text-2xl font-bold tracking-tight text-white">Create your account</h2>

        <?php if ($error): ?>
            <div class="mt-4 rounded-md bg-red-900/50 p-4">
                <p class="text-sm text-red-300"><?= htmlspecialchars($error) ?></p>
            </div>
        <?php elseif ($success): ?>
            <div class="mt-4 rounded-md bg-green-900/50 p-4">
                <p class="text-sm text-green-300"><?= $success ?></p>
            </div>
        <?php endif; ?>
    </div>

    <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
        <form method="POST"
              enctype="multipart/form-data"
              class="space-y-6">
            <div>
                <label for="name"
                       class="block text-sm font-medium text-gray-100">Full Name</label>
                <input id="name"
                       name="name"
                       type="text"
                       required
                       value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                       class="mt-2 block w-full rounded-md bg-white/5 px-3 py-2 text-base text-white outline-1 -outline-offset-1 outline-white/10 placeholder:text-gray-500 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-500 sm:text-sm"/>
            </div>

            <div>
                <label for="username"
                       class="block text-sm font-medium text-gray-100">Username</label>
                <input id="username"
                       name="username"
                       type="text"
                       required
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                       class="mt-2 block w-full rounded-md bg-white/5 px-3 py-2 text-base text-white outline-1 -outline-offset-1 outline-white/10 placeholder:text-gray-500 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-500 sm:text-sm"/>
            </div>

            <div>
                <label for="email"
                       class="block text-sm font-medium text-gray-100">Email Address</label>
                <input id="email"
                       name="email"
                       type="email"
                       required
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       class="mt-2 block w-full rounded-md bg-white/5 px-3 py-2 text-base text-white outline-1 -outline-offset-1 outline-white/10 placeholder:text-gray-500 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-500 sm:text-sm"
                       placeholder="you@example.com"/>
            </div>

            <div>
                <label for="phone"
                       class="block text-sm font-medium text-gray-100">Phone Number (optional)</label>
                <input id="phone"
                       name="phone"
                       type="tel"
                       value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                       class="mt-2 block w-full rounded-md bg-white/5 px-3 py-2 text-base text-white outline-1 -outline-offset-1 outline-white/10 placeholder:text-gray-500 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-500 sm:text-sm"
                       placeholder="+1 (555) 000-0000"/>
            </div>

            <div>
                <label for="password"
                       class="block text-sm font-medium text-gray-100">Password</label>
                <input id="password"
                       name="password"
                       type="password"
                       required
                       class="mt-2 block w-full rounded-md bg-white/5 px-3 py-2 text-base text-white outline-1 -outline-offset-1 outline-white/10 placeholder:text-gray-500 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-500 sm:text-sm"/>
            </div>

            <div>
                <label for="confirm_password"
                       class="block text-sm font-medium text-gray-100">Confirm Password</label>
                <input id="confirm_password"
                       name="confirm_password"
                       type="password"
                       required
                       class="mt-2 block w-full rounded-md bg-white/5 px-3 py-2 text-base text-white outline-1 -outline-offset-1 outline-white/10 placeholder:text-gray-500 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-500 sm:text-sm"/>
            </div>

            <div>
                <label for="profile_pic"
                       class="block text-sm font-medium text-gray-100">Profile Picture (optional)</label>
                <input id="profile_pic"
                       name="profile_pic"
                       type="file"
                       accept="image/jpeg,image/jpg,image/png,image/gif"
                       class="mt-2 block w-full text-sm text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-500 file:text-white hover:file:bg-indigo-400"/>
                <p class="mt-1 text-xs text-gray-400">Max size: 5MB. Formats: JPG, PNG, GIF</p>
            </div>

            <div>
                <button type="submit"
                        class="flex w-full justify-center rounded-md bg-indigo-500 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-400 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500 transition">
                    Register
                </button>
            </div>
        </form>

        <p class="mt-6 text-center text-sm text-gray-400">
            Already have an account?
            <a href="login.php"
               class="font-semibold text-indigo-400 hover:text-indigo-300">Login here</a>
        </p>
    </div>
</div>

</body>
</html>