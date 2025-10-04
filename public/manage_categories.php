<?php
global $pdo;
require_once '../config/auth.php';
require_once '../config/db.php';
require_once '../config/paths.php';
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
      class="h-full bg-gradient-to-br from-secondary-100 via-white to-secondary-200">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - Medicine Inventory</title>
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

<main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

    <!-- Add Category Form -->
    <div class="bg-white rounded-2xl shadow-xl p-8 mb-8 border border-gray-200">
        <div class="flex items-center mb-6">
            <div class="bg-primary-500/10 p-3 rounded-xl mr-3 shadow-sm">
                <svg class="h-6 w-6 text-primary-600"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M12 4v16m8-8H4"/>
                </svg>
            </div>
            <h2 class="text-2xl font-bold bg-gradient-to-r from-primary-600 to-primary-500 bg-clip-text text-transparent">
                Add New Category</h2>
        </div>

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

        <form method="POST"
              class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-1">
                <label for="category_name"
                       class="block text-sm font-medium text-gray-700 mb-2">
                    Category Name <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       id="category_name"
                       name="category_name"
                       required
                       class="block w-full rounded-xl bg-gray-50 px-4 py-3.5 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 border border-gray-300 transition-all"
                       placeholder="e.g., Antibiotics"/>
            </div>

            <div class="md:col-span-1">
                <label for="description"
                       class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <input type="text"
                       id="description"
                       name="description"
                       class="block w-full rounded-xl bg-gray-50 px-4 py-3.5 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 border border-gray-300 transition-all"
                       placeholder="Optional description"/>
            </div>

            <div class="flex items-end">
                <button type="submit"
                        name="add_category"
                        class="w-full bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-semibold py-3.5 px-4 rounded-xl transition-all shadow-lg shadow-primary-500/30 hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center">
                    <svg class="h-5 w-5 mr-2"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Category
                </button>
            </div>
        </form>
    </div>

    <!-- Default Categories Info -->
    <div class="bg-gradient-to-r from-primary-50 to-accent-50 border border-primary-200 rounded-xl p-5 mb-6 shadow-sm">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-primary-600"
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
                <p class="font-semibold text-primary-900 mb-1">Default Categories Available</p>
                <p>The system includes <?= count($defaultCategories) ?> built-in categories:
                    <span class="font-medium"><?= implode(', ', $defaultCategories) ?></span>. You can add your own
                    custom categories to organize medicines your way.</p>
            </div>
        </div>
    </div>

    <!-- Categories List -->
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
        <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <svg class="h-6 w-6 text-primary-600 mr-2"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    <h2 class="text-xl font-bold text-gray-900">Your Custom Categories</h2>
                </div>
                <span class="px-3 py-1 bg-primary-100 text-primary-700 text-sm font-semibold rounded-full">
                    <?= count($categories) ?> Total
                </span>
            </div>
        </div>

        <?php if ($categories): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            Category Name
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            Description
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            Medicines
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            Created
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                    <?php foreach ($categories as $cat): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1.5 inline-flex text-sm font-semibold rounded-full bg-primary-100 text-primary-800">
                                    <?= htmlspecialchars($cat['name']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-gray-700"><?= htmlspecialchars($cat['description'] ?: 'No description') ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <svg class="h-4 w-4 text-gray-400 mr-1"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                    </svg>
                                    <span class="text-sm text-gray-900 font-medium"><?= $cat['medicine_count'] ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-gray-500"><?= date('M d, Y', strtotime($cat['created_at'])) ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button onclick="editCategory(<?= $cat['id'] ?>, '<?= htmlspecialchars($cat['name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($cat['description'] ?: '', ENT_QUOTES) ?>')"
                                            class="flex items-center px-3 py-1.5 rounded-lg text-xs font-medium bg-primary-100 text-primary-700 hover:bg-primary-200 transition-colors">
                                        <svg class="h-3.5 w-3.5 mr-1"
                                             fill="none"
                                             stroke="currentColor"
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  stroke-width="2"
                                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Edit
                                    </button>
                                    <form method="POST"
                                          class="inline"
                                          onsubmit="return confirm('Are you sure you want to delete this category?')">
                                        <input type="hidden"
                                               name="category_id"
                                               value="<?= $cat['id'] ?>">
                                        <button type="submit"
                                                name="delete_category"
                                                class="flex items-center px-3 py-1.5 rounded-lg text-xs font-medium bg-red-100 text-red-700 hover:bg-red-200 transition-colors">
                                            <svg class="h-3.5 w-3.5 mr-1"
                                                 fill="none"
                                                 stroke="currentColor"
                                                 viewBox="0 0 24 24">
                                                <path stroke-linecap="round"
                                                      stroke-linejoin="round"
                                                      stroke-width="2"
                                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-16 px-6">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full mb-4">
                    <svg class="h-10 w-10 text-gray-400"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">No custom categories yet</h3>
                <p class="text-gray-600 mb-6">Create your first custom category to better organize your medicines.</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- Edit Modal -->
<div id="editModal"
     class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-8 border border-gray-200 animate-fadeIn">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-2xl font-bold bg-gradient-to-r from-primary-600 to-primary-500 bg-clip-text text-transparent">
                Edit Category</h3>
            <button type="button"
                    onclick="closeEditModal()"
                    class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="h-6 w-6"
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

        <form method="POST"
              class="space-y-5">
            <input type="hidden"
                   id="edit_category_id"
                   name="category_id">
            <div>
                <label for="edit_category_name"
                       class="block text-sm font-medium text-gray-700 mb-2">
                    Category Name <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       id="edit_category_name"
                       name="category_name"
                       required
                       class="block w-full rounded-xl bg-gray-50 px-4 py-3.5 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 border border-gray-300 transition-all"/>
            </div>
            <div>
                <label for="edit_description"
                       class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <input type="text"
                       id="edit_description"
                       name="description"
                       class="block w-full rounded-xl bg-gray-50 px-4 py-3.5 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 border border-gray-300 transition-all"/>
            </div>
            <div class="flex gap-3 pt-4">
                <button type="button"
                        onclick="closeEditModal()"
                        class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3.5 px-4 rounded-xl transition-all border border-gray-300">
                    Cancel
                </button>
                <button type="submit"
                        name="edit_category"
                        class="flex-1 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-semibold py-3.5 px-4 rounded-xl transition-all shadow-lg shadow-primary-500/30 hover:shadow-xl">
                    Update Category
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