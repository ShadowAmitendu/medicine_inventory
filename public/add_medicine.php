<?php
global $pdo;
require_once '../config/auth.php';
require_once '../config/db.php';
requireAuth();

$error = '';
$success = '';

// Fetch custom categories
$stmt = $pdo->prepare("SELECT name FROM categories WHERE user_id = ? ORDER BY name");
$stmt->execute([$_SESSION['user_id']]);
$customCategories = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Default categories
$defaultCategories = ['Tablet', 'Capsule', 'Syrup', 'Injection', 'Cream', 'Drops', 'Other'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $category = trim($_POST['category']);
    $quality = trim($_POST['quality']);
    $expiry_date = trim($_POST['expiry_date']);
    $quantity = (int)$_POST['quantity'];
    $box_id = trim($_POST['box_id']);
    $image = $_FILES['image'] ?? null;

    // Validation
    if (empty($name) || empty($category) || empty($quality) || empty($expiry_date) || $quantity < 0) {
        $error = "Please fill all required fields with valid data.";
    } else {
        // Handle image upload
        $image_filename = 'default.png'; // Set default image
        if ($image && $image['tmp_name'] && $image['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];

            if ($image['size'] > 5 * 1024 * 1024) {
                $error = "Image must be less than 5MB.";
            } elseif (!in_array($ext, $allowed)) {
                $error = "Invalid image format. Allowed: jpg, jpeg, png, gif.";
            } else {
                $image_filename = uniqid('medicine_', true) . '.' . $ext;
                $upload_path = "../uploads/$image_filename";

                if (!file_exists('../uploads')) {
                    mkdir('../uploads', 0755, true);
                }

                if (!move_uploaded_file($image['tmp_name'], $upload_path)) {
                    $error = "Failed to upload image.";
                    $image_filename = 'default.png'; // Fallback to default
                }
            }
        }

        // Insert medicine
        if (!$error) {
            try {
                $stmt = $pdo->prepare("INSERT INTO medicines (name, category, quality, expiry_date, quantity, box_id, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $category, $quality, $expiry_date, $quantity, $box_id, $image_filename]);
                $success = "Medicine added successfully!";

                // Clear form
                $_POST = [];
            } catch (PDOException $e) {
                $error = "Failed to add medicine: " . $e->getMessage();
                error_log("Add medicine error: " . $e->getMessage());
            }
        }
    }
}

$pageTitle = "Add New Medicine";
include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en"
      class="h-full bg-gray-900 dark:bg-black">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>Add Medicine - Medicine Inventory</title>
    <link href="../assets/output.css"
          rel="stylesheet">
    <style>
        select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%239ca3af' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
        }

        select:focus {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236366f1' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        }

        .file-upload-label:hover .upload-icon {
            transform: scale(1.1);
        }

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
<body class="h-full bg-gray-900 dark:bg-black">

<main class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="bg-gray-800 dark:bg-gray-900 rounded-xl shadow-2xl p-8 border border-gray-700 dark:border-gray-800">

        <!-- Header -->
        <div class="mb-8 border-b border-gray-700 dark:border-gray-800 pb-6">
            <h2 class="text-2xl font-bold text-white dark:text-gray-100 flex items-center">
                <div class="bg-indigo-500/10 dark:bg-indigo-600/10 p-2 rounded-lg mr-3">
                    <svg class="h-6 w-6 text-indigo-400 dark:text-indigo-300"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                Add New Medicine
            </h2>
            <p class="mt-2 text-sm text-gray-400 dark:text-gray-500">Fill in the details below to add a new medicine to
                your inventory</p>
        </div>

        <!-- Messages -->
        <?php if ($error): ?>
            <div class="mb-6 rounded-lg bg-red-500/10 dark:bg-red-600/10 p-4 border border-red-500/50 dark:border-red-600/50 animate-fadeIn">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-red-400 dark:text-red-300 mr-3 flex-shrink-0"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm text-red-300 dark:text-red-200"><?= htmlspecialchars($error) ?></p>
                </div>
            </div>
        <?php elseif ($success): ?>
            <div class="mb-6 rounded-lg bg-green-500/10 dark:bg-green-600/10 p-4 border border-green-500/50 dark:border-green-600/50 animate-fadeIn">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-green-400 dark:text-green-300 mr-3 flex-shrink-0"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="text-sm text-green-300 dark:text-green-200 font-medium"><?= htmlspecialchars($success) ?></p>
                        <a href="list_medicines.php"
                           class="text-sm text-green-400 dark:text-green-300 hover:text-green-300 dark:hover:text-green-200 underline mt-1 inline-block">
                            View all medicines →
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <form method="POST"
              enctype="multipart/form-data"
              class="space-y-8"
              id="medicineForm">

            <!-- Basic Information -->
            <div class="space-y-6">
                <h3 class="text-lg font-semibold text-white dark:text-gray-100 flex items-center">
                    <span class="bg-indigo-500/20 dark:bg-indigo-600/20 text-indigo-400 dark:text-indigo-300 rounded-full h-6 w-6 flex items-center justify-center text-sm mr-2">1</span>
                    Basic Information
                </h3>

                <div>
                    <label for="name"
                           class="text-sm font-medium text-gray-100 dark:text-gray-200 mb-2 flex items-center">
                        Medicine Name
                        <span class="text-red-400 ml-1">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-500 dark:text-gray-600"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                        <input type="text"
                               id="name"
                               name="name"
                               required
                               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                               class="block w-full rounded-lg bg-gray-700/50 dark:bg-gray-800/50 pl-10 pr-4 py-3.5 text-white dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 border border-gray-600 dark:border-gray-700 focus:border-transparent transition-all"
                               placeholder="e.g., Paracetamol 500mg"/>
                    </div>
                </div>
            </div>

            <!-- Classification -->
            <div class="space-y-6">
                <h3 class="text-lg font-semibold text-white dark:text-gray-100 flex items-center">
                    <span class="bg-indigo-500/20 dark:bg-indigo-600/20 text-indigo-400 dark:text-indigo-300 rounded-full h-6 w-6 flex items-center justify-center text-sm mr-2">2</span>
                    Classification
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="category"
                               class="text-sm font-medium text-gray-100 dark:text-gray-200 mb-2 flex items-center">
                            Category <span class="text-red-400 ml-1">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-500 dark:text-gray-600"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                            </div>
                            <select id="category"
                                    name="category"
                                    required
                                    class="block w-full rounded-lg bg-gray-700/50 dark:bg-gray-800/50 pl-10 pr-10 py-3.5 text-white dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 border border-gray-600 dark:border-gray-700 focus:border-transparent transition-all appearance-none">
                                <option value="">Select Category</option>
                                <optgroup label="Default Categories"
                                          class="bg-gray-700 dark:bg-gray-800">
                                    <?php foreach ($defaultCategories as $cat): ?>
                                        <option value="<?= htmlspecialchars($cat) ?>" <?= ($_POST['category'] ?? '') === $cat ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                                <?php if ($customCategories): ?>
                                    <optgroup label="Your Custom Categories"
                                              class="bg-gray-700 dark:bg-gray-800">
                                        <?php foreach ($customCategories as $cat): ?>
                                            <option value="<?= htmlspecialchars($cat) ?>" <?= ($_POST['category'] ?? '') === $cat ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($cat) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endif; ?>
                            </select>
                        </div>
                        <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">
                            <a href="manage_categories.php"
                               class="text-indigo-400 dark:text-indigo-300 hover:text-indigo-300 dark:hover:text-indigo-200 underline">Manage
                                custom categories</a>
                        </p>
                    </div>

                    <div>
                        <label for="quality"
                               class="text-sm font-medium text-gray-100 dark:text-gray-200 mb-2 flex items-center">
                            Quality Rating <span class="text-red-400 ml-1">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-500 dark:text-gray-600"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <select id="quality"
                                    name="quality"
                                    required
                                    class="block w-full rounded-lg bg-gray-700/50 dark:bg-gray-800/50 pl-10 pr-10 py-3.5 text-white dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 border border-gray-600 dark:border-gray-700 focus:border-transparent transition-all appearance-none">
                                <option value="">Select Quality</option>
                                <option value="Excellent" <?= ($_POST['quality'] ?? '') === 'Excellent' ? 'selected' : '' ?>>
                                    ⭐⭐⭐⭐⭐ Excellent
                                </option>
                                <option value="Good" <?= ($_POST['quality'] ?? '') === 'Good' ? 'selected' : '' ?>>⭐⭐⭐⭐
                                    Good
                                </option>
                                <option value="Average" <?= ($_POST['quality'] ?? '') === 'Average' ? 'selected' : '' ?>>
                                    ⭐⭐⭐ Average
                                </option>
                                <option value="Poor" <?= ($_POST['quality'] ?? '') === 'Poor' ? 'selected' : '' ?>>⭐⭐
                                    Poor
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stock & Storage -->
            <div class="space-y-6">
                <h3 class="text-lg font-semibold text-white dark:text-gray-100 flex items-center">
                    <span class="bg-indigo-500/20 dark:bg-indigo-600/20 text-indigo-400 dark:text-indigo-300 rounded-full h-6 w-6 flex items-center justify-center text-sm mr-2">3</span>
                    Stock & Storage Details
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="quantity"
                               class="text-sm font-medium text-gray-100 dark:text-gray-200 mb-2 flex items-center">
                            Quantity <span class="text-red-400 ml-1">*</span>
                        </label>
                        <input type="number"
                               id="quantity"
                               name="quantity"
                               required
                               min="0"
                               value="<?= htmlspecialchars($_POST['quantity'] ?? '') ?>"
                               class="block w-full rounded-lg bg-gray-700/50 dark:bg-gray-800/50 px-4 py-3.5 text-white dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 border border-gray-600 dark:border-gray-700 focus:border-transparent transition-all"
                               placeholder="0"/>
                    </div>

                    <div>
                        <label for="expiry_date"
                               class="text-sm font-medium text-gray-100 dark:text-gray-200 mb-2 flex items-center">
                            Expiry Date <span class="text-red-400 ml-1">*</span>
                        </label>
                        <input type="date"
                               id="expiry_date"
                               name="expiry_date"
                               required
                               value="<?= htmlspecialchars($_POST['expiry_date'] ?? '') ?>"
                               min="<?= date('Y-m-d') ?>"
                               class="block w-full rounded-lg bg-gray-700/50 dark:bg-gray-800/50 px-4 py-3.5 text-white dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 border border-gray-600 dark:border-gray-700 focus:border-transparent transition-all"/>
                    </div>

                    <div>
                        <label for="box_id"
                               class="block text-sm font-medium text-gray-100 dark:text-gray-200 mb-2">
                            Storage Box ID
                        </label>
                        <input type="text"
                               id="box_id"
                               name="box_id"
                               value="<?= htmlspecialchars($_POST['box_id'] ?? '') ?>"
                               class="block w-full rounded-lg bg-gray-700/50 dark:bg-gray-800/50 px-4 py-3.5 text-white dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 border border-gray-600 dark:border-gray-700 focus:border-transparent transition-all"
                               placeholder="e.g., A01"/>
                    </div>
                </div>
            </div>

            <!-- Image Upload -->
            <div class="space-y-6">
                <h3 class="text-lg font-semibold text-white dark:text-gray-100 flex items-center">
                    <span class="bg-indigo-500/20 dark:bg-indigo-600/20 text-indigo-400 dark:text-indigo-300 rounded-full h-6 w-6 flex items-center justify-center text-sm mr-2">4</span>
                    Medicine Image
                    <span class="ml-2 text-xs text-gray-500 dark:text-gray-600 font-normal">(Optional)</span>
                </h3>

                <div>
                    <label for="image"
                           class="file-upload-label cursor-pointer block">
                        <div class="flex items-center justify-center w-full px-6 py-12 border-2 border-dashed border-gray-600 dark:border-gray-700 rounded-xl hover:border-indigo-500 dark:hover:border-indigo-400 transition-all bg-gray-700/30 dark:bg-gray-800/30 hover:bg-gray-700/50 dark:hover:bg-gray-800/50 group">
                            <div class="text-center">
                                <div class="upload-icon transition-transform">
                                    <svg class="mx-auto h-16 w-16 text-gray-400 dark:text-gray-500 group-hover:text-indigo-400 dark:group-hover:text-indigo-300 transition-colors"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <p class="mt-4 text-sm text-gray-300 dark:text-gray-400 font-medium">
                                    <span class="text-indigo-400 dark:text-indigo-300">Click to upload</span> or drag
                                    and drop
                                </p>
                                <p class="mt-2 text-xs text-gray-500 dark:text-gray-600">PNG, JPG, GIF up to 5MB</p>
                            </div>
                        </div>
                        <input type="file"
                               id="image"
                               name="image"
                               accept="image/jpeg,image/jpg,image/png,image/gif"
                               class="hidden"/>
                    </label>
                    <div id="imagePreview"
                         class="mt-4 hidden">
                        <p class="text-sm text-gray-400 dark:text-gray-500 mb-2">Selected image:</p>
                        <div class="flex items-center space-x-3 bg-gray-700/30 dark:bg-gray-800/30 rounded-lg p-3 border border-gray-600 dark:border-gray-700">
                            <img id="previewImg"
                                 src=""
                                 alt="Preview"
                                 class="h-16 w-16 rounded-lg object-cover">
                            <div class="flex-1 min-w-0">
                                <p id="fileName"
                                   class="text-sm font-medium text-white dark:text-gray-100 truncate"></p>
                                <p id="fileSize"
                                   class="text-xs text-gray-400 dark:text-gray-500"></p>
                            </div>
                            <button type="button"
                                    onclick="clearImage()"
                                    class="text-red-400 hover:text-red-300 transition-colors">
                                <svg class="h-5 w-5"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 pt-6 border-t border-gray-700 dark:border-gray-800">
                <button type="submit"
                        class="flex-1 bg-gradient-to-r from-indigo-500 to-indigo-600 hover:from-indigo-400 hover:to-indigo-500 text-white font-semibold py-4 px-6 rounded-lg transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-gray-900 shadow-lg shadow-indigo-500/50 hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center">
                    <svg class="h-5 w-5 mr-2"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M5 13l4 4L19 7"/>
                    </svg>
                    Add Medicine
                </button>
                <a href="list_medicines.php"
                   class="flex-1 bg-gray-700 hover:bg-gray-600 text-white font-semibold py-4 px-6 rounded-lg transition-all text-center focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 focus:ring-offset-gray-900 border border-gray-600 flex items-center justify-center">
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

    </div>
</main>

<script>
    document.getElementById('image').addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (file) {
            if (file.size > 5 * 1024 * 1024) {
                alert('File size must be less than 5MB');
                this.value = '';
                return;
            }

            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if (!validTypes.includes(file.type)) {
                alert('Invalid file type. Please upload JPG, PNG, or GIF');
                this.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('previewImg').src = e.target.result;
                document.getElementById('fileName').textContent = file.name;
                document.getElementById('fileSize').textContent = formatFileSize(file.size);
                document.getElementById('imagePreview').classList.remove('hidden');
                document.getElementById('imagePreview').classList.add('animate-fadeIn');
            };
            reader.readAsDataURL(file);
        }
    });

    function clearImage() {
        document.getElementById('image').value = '';
        document.getElementById('imagePreview').classList.add('hidden');
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

    document.getElementById('medicineForm').addEventListener('submit', function (e) {
        const requiredFields = this.querySelectorAll('[required]');
        let isValid = true;

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                field.classList.add('ring-2', 'ring-red-500');
                setTimeout(() => {
                    field.classList.remove('ring-2', 'ring-red-500');
                }, 2000);
            }
        });

        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields');
        }
    });

    const successMsg = document.querySelector('.bg-green-500\\/10');
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