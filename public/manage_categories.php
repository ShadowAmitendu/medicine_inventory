<?php
global $pdo;
require_once '../config/auth.php';
require_once '../config/db.php';
requireAuth();

$error = '';
$success = '';

// Handle category operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_category'])) {
        $name = trim($_POST['category_name']);
        $description = trim($_POST['description']);

        if (empty($name)) {
            $error = "Category name is required.";
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO categories (name, description, user_id) VALUES (?, ?, ?)");
                $stmt->execute([$name, $description, $_SESSION['user_id']]);
                $success = "Category added successfully!";
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $error = "Category already exists.";
                } else {
                    $error = "Failed to add category.";
                    error_log("Add category error: " . $e->getMessage());
                }
            }
        }
    } elseif (isset($_POST['delete_category'])) {
        $id = (int)$_POST['category_id'];
        try {
            // Check if category is in use
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM medicines WHERE category COLLATE utf8mb4_general_ci = (SELECT name COLLATE utf8mb4_general_ci FROM categories WHERE id = ?)");
            $stmt->execute([$id]);
            $count = $stmt->fetchColumn();

            if ($count > 0) {
                $error = "Cannot delete category. It is being used by $count medicine(s).";
            } else {
                $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ? AND user_id = ?");
                $stmt->execute([$id, $_SESSION['user_id']]);
                $success = "Category deleted successfully!";
            }
        } catch (PDOException $e) {
            $error = "Failed to delete category.";
            error_log("Delete category error: " . $e->getMessage());
        }
    } elseif (isset($_POST['edit_category'])) {
        $id = (int)$_POST['category_id'];
        $name = trim($_POST['category_name']);
        $description = trim($_POST['description']);

        if (empty($name)) {
            $error = "Category name is required.";
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ? AND user_id = ?");
                $stmt->execute([$name, $description, $id, $_SESSION['user_id']]);
                $success = "Category updated successfully!";
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $error = "Category name already exists.";
                } else {
                    $error = "Failed to update category.";
                    error_log("Update category error: " . $e->getMessage());
                }
            }
        }
    }
}

// Fetch all categories
$stmt = $pdo->prepare("SELECT c.*, COUNT(m.id) as medicine_count FROM categories c LEFT JOIN medicines m ON c.name COLLATE utf8mb4_general_ci = m.category COLLATE utf8mb4_general_ci WHERE c.user_id = ? GROUP BY c.id ORDER BY c.name");
$stmt->execute([$_SESSION['user_id']]);
$categories = $stmt->fetchAll();

// Get default categories count
$defaultCategories = ['Tablet', 'Capsule', 'Syrup', 'Injection', 'Cream', 'Drops', 'Other'];

$pageTitle = "Manage Categories";
include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en"
      class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - Medicine Inventory</title>
    <link href="../assets/output.css"
          rel="stylesheet">
</head>
<body class="h-full dark:bg-gray-900">

<main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 ">

    <!-- Add Category Form -->
    <div class="bg-gray-800 rounded-lg shadow-lg p-8 mb-8">
        <h2 class="text-2xl font-bold text-white mb-6">Add New Category</h2>

        <?php if ($error): ?>
            <div class="mb-6 rounded-md bg-red-900/50 p-4 border border-red-700">
                <p class="text-sm text-red-300"><?= htmlspecialchars($error) ?></p>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="mb-6 rounded-md bg-green-900/50 p-4 border border-green-700">
                <p class="text-sm text-green-300"><?= htmlspecialchars($success) ?></p>
            </div>
        <?php endif; ?>

        <form method="POST"
              class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-1">
                <label for="category_name"
                       class="block text-sm font-medium text-gray-100 mb-2">Category Name *</label>
                <input type="text"
                       id="category_name"
                       name="category_name"
                       required
                       class="block w-full rounded-md bg-white/5 px-4 py-3 text-white placeholder-gray-400 focus:outline-2 focus:outline-indigo-500 transition"
                       placeholder="e.g., Antibiotics"/>
            </div>

            <div class="md:col-span-1">
                <label for="description"
                       class="block text-sm font-medium text-gray-100 mb-2">Description</label>
                <input type="text"
                       id="description"
                       name="description"
                       class="block w-full rounded-md bg-white/5 px-4 py-3 text-white placeholder-gray-400 focus:outline-2 focus:outline-indigo-500 transition"
                       placeholder="Optional description"/>
            </div>

            <div class="flex items-end">
                <button type="submit"
                        name="add_category"
                        class="w-full bg-indigo-500 hover:bg-indigo-400 text-white font-semibold py-3 px-4 rounded-md transition focus:outline-2 focus:outline-offset-2 focus:outline-indigo-500">
                    Add Category
                </button>
            </div>
        </form>
    </div>

    <!-- Default Categories Info -->
    <div class="bg-blue-900/20 border border-blue-700 rounded-lg p-4 mb-6">
        <div class="flex items-start">
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
                <p><strong class="font-semibold">Default Categories:</strong> The system
                    includes <?= count($defaultCategories) ?> built-in categories
                    (<?= implode(', ', $defaultCategories) ?>). You can add your own custom categories below.</p>
            </div>
        </div>
    </div>

    <!-- Categories List -->
    <div class="bg-gray-800 rounded-lg shadow-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-700">
            <h2 class="text-xl font-bold text-white">Your Custom Categories (<?= count($categories) ?>)</h2>
        </div>

        <?php if ($categories): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-700">
                    <thead class="bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                            Category Name
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                            Description
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                            Medicines
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                            Created
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                    <?php foreach ($categories as $cat): ?>
                        <tr class="hover:bg-gray-700/50 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-indigo-900/50 text-indigo-300">
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-gray-300"><?= htmlspecialchars($cat['description'] ?: 'No description') ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-gray-300"><?= $cat['medicine_count'] ?> medicine(s)</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-gray-400"><?= date('M d, Y', strtotime($cat['created_at'])) ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <button onclick="editCategory(<?= $cat['id'] ?>, '<?= htmlspecialchars($cat['name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($cat['description'] ?: '', ENT_QUOTES) ?>')"
                                        class="text-indigo-400 hover:text-indigo-300">Edit
                                </button>
                                <form method="POST"
                                      class="inline"
                                      onsubmit="return confirm('Are you sure you want to delete this category?')">
                                    <input type="hidden"
                                           name="category_id"
                                           value="<?= $cat['id'] ?>">
                                    <button type="submit"
                                            name="delete_category"
                                            class="text-red-400 hover:text-red-300">Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-600"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                <p class="mt-4 text-gray-400">No custom categories yet.</p>
                <p class="mt-2 text-sm text-gray-500">Add your first custom category using the form above.</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- Edit Modal -->
<div id="editModal"
     class="hidden fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6">
        <h3 class="text-xl font-bold text-white mb-4">Edit Category</h3>
        <form method="POST"
              class="space-y-4">
            <input type="hidden"
                   id="edit_category_id"
                   name="category_id">
            <div>
                <label for="edit_category_name"
                       class="block text-sm font-medium text-gray-100 mb-2">Category Name *</label>
                <input type="text"
                       id="edit_category_name"
                       name="category_name"
                       required
                       class="block w-full rounded-md bg-white/5 px-4 py-3 text-white placeholder-gray-400 focus:outline-2 focus:outline-indigo-500 transition"/>
            </div>
            <div>
                <label for="edit_description"
                       class="block text-sm font-medium text-gray-100 mb-2">Description</label>
                <input type="text"
                       id="edit_description"
                       name="description"
                       class="block w-full rounded-md bg-white/5 px-4 py-3 text-white placeholder-gray-400 focus:outline-2 focus:outline-indigo-500 transition"/>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button"
                        onclick="closeEditModal()"
                        class="flex-1 bg-gray-700 hover:bg-gray-600 text-white font-semibold py-3 px-4 rounded-md transition">
                    Cancel
                </button>
                <button type="submit"
                        name="edit_category"
                        class="flex-1 bg-indigo-500 hover:bg-indigo-400 text-white font-semibold py-3 px-4 rounded-md transition">
                    Update
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function editCategory(id, name, description) {
        document.getElementById('edit_category_id').value = id;
        document.getElementById('edit_category_name').value = name;
        document.getElementById('edit_description').value = description;
        document.getElementById('editModal').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeEditModal();
    });

    document.getElementById('editModal').addEventListener('click', function (e) {
        if (e.target === this) closeEditModal();
    });
</script>

<?php include '../includes/footer.php'; ?>

</body>
</html>