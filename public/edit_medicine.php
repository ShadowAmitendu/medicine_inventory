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
      class="h-full bg-gray-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>Edit Medicine - Medicine Inventory</title>
    <link href="../assets/output.css"
          rel="stylesheet">
</head>
<body class="h-full">

<main class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="bg-gray-800 rounded-lg shadow-lg p-8">

        <h2 class="text-2xl font-bold text-white mb-6">Edit Medicine</h2>

        <!-- Messages -->
        <?php if ($error): ?>
            <div class="mb-6 rounded-md bg-red-900/50 p-4 border border-red-700">
                <p class="text-sm text-red-300"><?= htmlspecialchars($error) ?></p>
            </div>
        <?php elseif ($success): ?>
            <div class="mb-6 rounded-md bg-green-900/50 p-4 border border-green-700">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-green-300"><?= htmlspecialchars($success) ?></p>
                    <a href="list_medicines.php"
                       class="text-sm text-green-400 hover:text-green-300 underline">View all medicines →</a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Current Image Preview -->
        <?php if ($medicine['image'] && $medicine['image'] !== 'default.png'): ?>
            <div class="mb-6 text-center">
                <p class="text-sm text-gray-400 mb-2">Current Image:</p>
                <img src="../uploads/<?= htmlspecialchars($medicine['image']) ?>"
                     alt="<?= htmlspecialchars($medicine['name']) ?>"
                     class="h-32 w-32 mx-auto rounded-lg object-cover ring-2 ring-indigo-500/50">
            </div>
        <?php endif; ?>

        <!-- Form -->
        <form method="POST"
              enctype="multipart/form-data"
              class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div class="md:col-span-2">
                    <label for="name"
                           class="block text-sm font-medium text-gray-100 mb-2">Medicine Name *</label>
                    <input type="text"
                           id="name"
                           name="name"
                           required
                           value="<?= htmlspecialchars($medicine['name']) ?>"
                           class="block w-full rounded-md bg-white/5 px-4 py-3 text-white placeholder-gray-400 focus:outline-2 focus:outline-indigo-500 transition border border-gray-700"
                           placeholder="e.g., Paracetamol 500mg"/>
                </div>

                <div>
                    <label for="category"
                           class="block text-sm font-medium text-gray-100 mb-2">Category *</label>
                    <select id="category"
                            name="category"
                            required
                            class="block w-full rounded-md bg-white/5 px-4 py-3 text-white focus:outline-2 focus:outline-indigo-500 transition border border-gray-700">
                        <option value="">Select Category</option>
                        <option value="Tablet" <?= $medicine['category'] === 'Tablet' ? 'selected' : '' ?>>Tablet
                        </option>
                        <option value="Capsule" <?= $medicine['category'] === 'Capsule' ? 'selected' : '' ?>>Capsule
                        </option>
                        <option value="Syrup" <?= $medicine['category'] === 'Syrup' ? 'selected' : '' ?>>Syrup</option>
                        <option value="Injection" <?= $medicine['category'] === 'Injection' ? 'selected' : '' ?>>
                            Injection
                        </option>
                        <option value="Cream" <?= $medicine['category'] === 'Cream' ? 'selected' : '' ?>>
                            Cream/Ointment
                        </option>
                        <option value="Drops" <?= $medicine['category'] === 'Drops' ? 'selected' : '' ?>>Drops</option>
                        <option value="Other" <?= $medicine['category'] === 'Other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>

                <div>
                    <label for="quality"
                           class="block text-sm font-medium text-gray-100 mb-2">Quality *</label>
                    <select id="quality"
                            name="quality"
                            required
                            class="block w-full rounded-md bg-white/5 px-4 py-3 text-white focus:outline-2 focus:outline-indigo-500 transition border border-gray-700">
                        <option value="">Select Quality</option>
                        <option value="Excellent" <?= $medicine['quality'] === 'Excellent' ? 'selected' : '' ?>>⭐⭐⭐⭐⭐
                            Excellent
                        </option>
                        <option value="Good" <?= $medicine['quality'] === 'Good' ? 'selected' : '' ?>>⭐⭐⭐⭐ Good</option>
                        <option value="Average" <?= $medicine['quality'] === 'Average' ? 'selected' : '' ?>>⭐⭐⭐
                            Average
                        </option>
                        <option value="Poor" <?= $medicine['quality'] === 'Poor' ? 'selected' : '' ?>>⭐⭐ Poor</option>
                    </select>
                </div>

                <div>
                    <label for="expiry_date"
                           class="block text-sm font-medium text-gray-100 mb-2">Expiry Date *</label>
                    <input type="date"
                           id="expiry_date"
                           name="expiry_date"
                           required
                           value="<?= htmlspecialchars($medicine['expiry_date']) ?>"
                           class="block w-full rounded-md bg-white/5 px-4 py-3 text-white focus:outline-2 focus:outline-indigo-500 transition border border-gray-700"/>
                </div>

                <div>
                    <label for="quantity"
                           class="block text-sm font-medium text-gray-100 mb-2">Quantity *</label>
                    <input type="number"
                           id="quantity"
                           name="quantity"
                           required
                           min="0"
                           value="<?= htmlspecialchars($medicine['quantity']) ?>"
                           class="block w-full rounded-md bg-white/5 px-4 py-3 text-white placeholder-gray-400 focus:outline-2 focus:outline-indigo-500 transition border border-gray-700"
                           placeholder="0"/>
                </div>

                <div>
                    <label for="box_id"
                           class="block text-sm font-medium text-gray-100 mb-2">Storage Box ID</label>
                    <input type="text"
                           id="box_id"
                           name="box_id"
                           value="<?= htmlspecialchars($medicine['box_id'] ?? '') ?>"
                           class="block w-full rounded-md bg-white/5 px-4 py-3 text-white placeholder-gray-400 focus:outline-2 focus:outline-indigo-500 transition border border-gray-700"
                           placeholder="e.g., A01"/>
                </div>

                <div class="md:col-span-2">
                    <label for="image"
                           class="block text-sm font-medium text-gray-100 mb-2">Change Medicine Image</label>
                    <input type="file"
                           id="image"
                           name="image"
                           accept="image/jpeg,image/jpg,image/png,image/gif"
                           class="block w-full text-sm text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-500 file:text-white hover:file:bg-indigo-400 transition"/>
                    <p class="mt-1 text-xs text-gray-400">Max size: 5MB. Formats: JPG, PNG, GIF. Leave empty to keep
                        current image.</p>
                </div>

            </div>

            <div class="flex gap-4 pt-4 border-t border-gray-700">
                <button type="submit"
                        class="flex-1 bg-indigo-500 hover:bg-indigo-400 text-white font-semibold py-3 px-4 rounded-md transition focus:outline-2 focus:outline-offset-2 focus:outline-indigo-500">
                    Update Medicine
                </button>
                <a href="list_medicines.php"
                   class="flex-1 bg-gray-700 hover:bg-gray-600 text-white font-semibold py-3 px-4 rounded-md transition text-center focus:outline-2 focus:outline-offset-2 focus:outline-gray-500">
                    Cancel
                </a>
            </div>
        </form>

    </div>
</main>

<?php include '../includes/footer.php'; ?>

</body>
</html>