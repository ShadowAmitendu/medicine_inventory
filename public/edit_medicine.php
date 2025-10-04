<?php
global $pdo;
require_once '../config/auth.php';
require_once '../config/db.php';
requireAuth();

$error = '';
$success = '';
$medicine = null;

// Get medicine ID
$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    header('Location: list_medicines.php');
    exit;
}

// Fetch medicine data
try {
    $stmt = $pdo->prepare("SELECT * FROM medicines WHERE id = ?");
    $stmt->execute([$id]);
    $medicine = $stmt->fetch();

    if (!$medicine) {
        header('Location: list_medicines.php');
        exit;
    }
} catch (PDOException $e) {
    $error = "Failed to fetch medicine data.";
    error_log("Fetch medicine error: " . $e->getMessage());
}

// Handle form submission
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
        $image_filename = $medicine['image'];
        if ($image && $image['tmp_name'] && $image['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];

            if ($image['size'] > 5 * 1024 * 1024) {
                $error = "Image must be less than 5MB.";
            } elseif (!in_array($ext, $allowed)) {
                $error = "Invalid image format. Allowed: jpg, jpeg, png, gif.";
            } else {
                // Delete old image if exists and it's not the default
                if ($medicine['image'] && $medicine['image'] !== 'default.png' && file_exists("../uploads/" . $medicine['image'])) {
                    unlink("../uploads/" . $medicine['image']);
                }

                $image_filename = uniqid('medicine_', true) . '.' . $ext;
                $upload_path = "../uploads/$image_filename";

                if (!file_exists('../uploads')) {
                    mkdir('../uploads', 0755, true);
                }

                if (!move_uploaded_file($image['tmp_name'], $upload_path)) {
                    $error = "Failed to upload image.";
                    $image_filename = $medicine['image'];
                }
            }
        }

        // Update medicine
        if (!$error) {
            try {
                $stmt = $pdo->prepare("UPDATE medicines SET name = ?, category = ?, quality = ?, expiry_date = ?, quantity = ?, box_id = ?, image = ? WHERE id = ?");
                $stmt->execute([$name, $category, $quality, $expiry_date, $quantity, $box_id, $image_filename, $id]);
                $success = "Medicine updated successfully!";

                // Refresh medicine data
                $stmt = $pdo->prepare("SELECT * FROM medicines WHERE id = ?");
                $stmt->execute([$id]);
                $medicine = $stmt->fetch();
            } catch (PDOException $e) {
                $error = "Failed to update medicine: " . $e->getMessage();
                error_log("Update medicine error: " . $e->getMessage());
            }
        }
    }
}

// Set custom page title
$pageTitle = "Edit Medicine";

include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en"
      class="h-full bg-gradient-to-br from-secondary-100 via-white to-secondary-200">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>Edit Medicine - Medicine Inventory</title>
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
    </style>
</head>
<body class="">

<main class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
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
                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-2xl font-bold bg-gradient-to-r from-primary-600 to-primary-500 bg-clip-text text-transparent">
                    Edit Medicine</h2>
                <p class="text-sm text-gray-600 mt-0.5">Update medicine information</p>
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
                <div class="flex items-center justify-between">
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
                    <a href="list_medicines.php"
                       class="text-sm text-green-700 hover:text-green-900 underline font-medium">View all medicines
                        →</a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Current Image Preview -->
        <?php if ($medicine['image'] && $medicine['image'] !== 'default.png'): ?>
            <div class="mb-8 text-center bg-gradient-to-br from-secondary-50 to-white p-6 rounded-xl border border-gray-200">
                <p class="text-sm font-medium text-gray-700 mb-3">Current Image</p>
                <img src="../uploads/<?= htmlspecialchars($medicine['image']) ?>"
                     alt="<?= htmlspecialchars($medicine['name']) ?>"
                     class="h-40 w-40 mx-auto rounded-xl object-cover ring-4 ring-primary-500/30 shadow-lg">
            </div>
        <?php endif; ?>

        <!-- Form -->
        <form method="POST"
              enctype="multipart/form-data"
              class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div class="md:col-span-2">
                    <label for="name"
                           class="block text-sm font-medium text-gray-700 mb-2">
                        Medicine Name <span class="text-red-500">*</span>
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
                                      d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                            </svg>
                        </div>
                        <input type="text"
                               id="name"
                               name="name"
                               required
                               value="<?= htmlspecialchars($medicine['name']) ?>"
                               class="block w-full rounded-xl bg-gray-50 pl-10 pr-4 py-3.5 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 border border-gray-300 transition-all"
                               placeholder="e.g., Paracetamol 500mg"/>
                    </div>
                </div>

                <div>
                    <label for="category"
                           class="block text-sm font-medium text-gray-700 mb-2">
                        Category <span class="text-red-500">*</span>
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
                                      d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                        </div>
                        <select id="category"
                                name="category"
                                required
                                class="block w-full rounded-xl bg-gray-50 pl-10 pr-4 py-3.5 text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-500 border border-gray-300 transition-all appearance-none">
                            <option value="">Select Category</option>
                            <option value="Tablet" <?= $medicine['category'] === 'Tablet' ? 'selected' : '' ?>>Tablet
                            </option>
                            <option value="Capsule" <?= $medicine['category'] === 'Capsule' ? 'selected' : '' ?>>
                                Capsule
                            </option>
                            <option value="Syrup" <?= $medicine['category'] === 'Syrup' ? 'selected' : '' ?>>Syrup
                            </option>
                            <option value="Injection" <?= $medicine['category'] === 'Injection' ? 'selected' : '' ?>>
                                Injection
                            </option>
                            <option value="Cream" <?= $medicine['category'] === 'Cream' ? 'selected' : '' ?>>
                                Cream/Ointment
                            </option>
                            <option value="Drops" <?= $medicine['category'] === 'Drops' ? 'selected' : '' ?>>Drops
                            </option>
                            <option value="Other" <?= $medicine['category'] === 'Other' ? 'selected' : '' ?>>Other
                            </option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="quality"
                           class="block text-sm font-medium text-gray-700 mb-2">
                        Quality <span class="text-red-500">*</span>
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
                                      d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                            </svg>
                        </div>
                        <select id="quality"
                                name="quality"
                                required
                                class="block w-full rounded-xl bg-gray-50 pl-10 pr-4 py-3.5 text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-500 border border-gray-300 transition-all appearance-none">
                            <option value="">Select Quality</option>
                            <option value="Excellent" <?= $medicine['quality'] === 'Excellent' ? 'selected' : '' ?>>
                                ⭐⭐⭐⭐⭐ Excellent
                            </option>
                            <option value="Good" <?= $medicine['quality'] === 'Good' ? 'selected' : '' ?>>⭐⭐⭐⭐ Good
                            </option>
                            <option value="Average" <?= $medicine['quality'] === 'Average' ? 'selected' : '' ?>>⭐⭐⭐
                                Average
                            </option>
                            <option value="Poor" <?= $medicine['quality'] === 'Poor' ? 'selected' : '' ?>>⭐⭐ Poor
                            </option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="expiry_date"
                           class="block text-sm font-medium text-gray-700 mb-2">
                        Expiry Date <span class="text-red-500">*</span>
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
                                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <input type="date"
                               id="expiry_date"
                               name="expiry_date"
                               required
                               value="<?= htmlspecialchars($medicine['expiry_date']) ?>"
                               class="block w-full rounded-xl bg-gray-50 pl-10 pr-4 py-3.5 text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-500 border border-gray-300 transition-all"/>
                    </div>
                </div>

                <div>
                    <label for="quantity"
                           class="block text-sm font-medium text-gray-700 mb-2">
                        Quantity <span class="text-red-500">*</span>
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
                                      d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                            </svg>
                        </div>
                        <input type="number"
                               id="quantity"
                               name="quantity"
                               required
                               min="0"
                               value="<?= htmlspecialchars($medicine['quantity']) ?>"
                               class="block w-full rounded-xl bg-gray-50 pl-10 pr-4 py-3.5 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 border border-gray-300 transition-all"
                               placeholder="0"/>
                    </div>
                </div>

                <div>
                    <label for="box_id"
                           class="block text-sm font-medium text-gray-700 mb-2">
                        Storage Box ID
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
                                      d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <input type="text"
                               id="box_id"
                               name="box_id"
                               value="<?= htmlspecialchars($medicine['box_id'] ?? '') ?>"
                               class="block w-full rounded-xl bg-gray-50 pl-10 pr-4 py-3.5 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 border border-gray-300 transition-all"
                               placeholder="e.g., A01"/>
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label for="image"
                           class="block text-sm font-medium text-gray-700 mb-2">
                        Change Medicine Image
                    </label>
                    <div class="relative">
                        <input type="file"
                               id="image"
                               name="image"
                               accept="image/jpeg,image/jpg,image/png,image/gif"
                               class="block w-full text-sm text-gray-700 file:mr-4 file:py-3 file:px-6 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-gradient-to-r file:from-primary-500 file:to-primary-600 file:text-white hover:file:from-primary-600 hover:file:to-primary-700 file:shadow-lg file:shadow-primary-500/30 transition-all border border-gray-300 rounded-xl bg-gray-50 py-3 px-4"/>
                    </div>
                    <p class="mt-2 text-xs text-gray-500">Max size: 5MB. Formats: JPG, PNG, GIF. Leave empty to keep
                        current image.</p>
                </div>

            </div>

            <div class="flex flex-col sm:flex-row gap-4 pt-6 border-t border-gray-200">
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
                    Update Medicine
                </button>
                <a href="list_medicines.php"
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