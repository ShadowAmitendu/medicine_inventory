<?php
global $pdo;
require_once '../config/auth.php';
require_once '../config/db.php';
require_once '../config/paths.php';
requireAuth();

$success = '';
$error = '';

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        // Get medicine image before deleting
        $stmt = $pdo->prepare("SELECT image FROM medicines WHERE id = ?");
        $stmt->execute([$id]);
        $medicine = $stmt->fetch();

        // Delete the medicine
        $stmt = $pdo->prepare("DELETE FROM medicines WHERE id = ?");
        $stmt->execute([$id]);

        // Delete image file if exists (but not default.png)
        if ($medicine && $medicine['image'] && $medicine['image'] !== 'default.png' && file_exists("../uploads/" . $medicine['image'])) {
            unlink("../uploads/" . $medicine['image']);
        }

        $success = "Medicine deleted successfully!";
    } catch (PDOException $e) {
        $error = "Failed to delete medicine.";
        error_log("Delete error: " . $e->getMessage());
    }
}

// Handle search and filters
$searchTerm = trim($_GET['search'] ?? '');
$category = $_GET['category'] ?? '';
$quality = $_GET['quality'] ?? '';
$sort = $_GET['sort'] ?? 'name';

// Build query
$sql = "SELECT * FROM medicines WHERE 1=1";
$params = [];

if ($searchTerm) {
    $sql .= " AND (name LIKE ? OR category LIKE ?)";
    $params[] = "%$searchTerm%";
    $params[] = "%$searchTerm%";
}

if ($category) {
    $sql .= " AND category = ?";
    $params[] = $category;
}

if ($quality) {
    $sql .= " AND quality = ?";
    $params[] = $quality;
}

// Sorting
switch ($sort) {
    case 'name':
        $sql .= " ORDER BY name ASC";
        break;
    case 'expiry':
        $sql .= " ORDER BY expiry_date ASC";
        break;
    case 'quantity':
        $sql .= " ORDER BY quantity ASC";
        break;
    case 'quality':
        $sql .= " ORDER BY quality DESC";
        break;
    default:
        $sql .= " ORDER BY name ASC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$medicines = $stmt->fetchAll();

// Get unique categories for filter
$categoriesStmt = $pdo->query("SELECT DISTINCT category FROM medicines ORDER BY category");
$categories = $categoriesStmt->fetchAll(PDO::FETCH_COLUMN);

// Set custom page title
$pageTitle = "All Medicines";

include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en"
      class="h-full bg-gradient-to-br from-secondary-100 via-white to-secondary-200">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>All Medicines - Medicine Inventory</title>
    <link href="<?= assetUrl('output.css') ?>"
          rel="stylesheet">
</head>
<body class="h-full">

<main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">

    <!-- Header -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold bg-gradient-to-r from-primary-600 to-primary-500 bg-clip-text text-transparent">
                Medicine Inventory</h1>
            <p class="text-sm text-gray-600 mt-1">Manage and organize your medicines</p>
        </div>
        <a href="<?= URL_ADD_MEDICINE ?>"
           class="bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-semibold px-6 py-3 rounded-xl transition-all shadow-lg shadow-primary-500/30 hover:shadow-xl transform hover:-translate-y-0.5 flex items-center space-x-2">
            <svg class="h-5 w-5"
                 fill="none"
                 stroke="currentColor"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M12 4v16m8-8H4"/>
            </svg>
            <span>Add New Medicine</span>
        </a>
    </div>

    <!-- Messages -->
    <?php if ($success): ?>
        <div class="mb-6 rounded-xl bg-green-50 p-4 border border-green-200 animate-fadeIn">
            <div class="flex items-center">
                <svg class="h-5 w-5 text-green-600 mr-3"
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
    <?php if ($error): ?>
        <div class="mb-6 rounded-xl bg-red-50 p-4 border border-red-200 animate-fadeIn">
            <div class="flex items-center">
                <svg class="h-5 w-5 text-red-600 mr-3"
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

    <!-- Filters -->
    <div class="bg-white rounded-2xl shadow-lg p-6 mb-6 border border-gray-200">
        <div class="flex items-center mb-4">
            <svg class="h-5 w-5 text-primary-600 mr-2"
                 fill="none"
                 stroke="currentColor"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
            </svg>
            <h2 class="text-lg font-semibold text-gray-900">Filter & Search</h2>
        </div>

        <form method="GET"
              class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text"
                           name="search"
                           value="<?= htmlspecialchars($searchTerm) ?>"
                           placeholder="Search medicines..."
                           class="w-full pl-10 px-4 py-2.5 rounded-xl bg-gray-50 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 border border-gray-300 transition-all">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                <select name="category"
                        class="w-full px-4 py-2.5 rounded-xl bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-500 border border-gray-300 appearance-none transition-all">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Quality</label>
                <select name="quality"
                        class="w-full px-4 py-2.5 rounded-xl bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-500 border border-gray-300 appearance-none transition-all">
                    <option value="">All Qualities</option>
                    <option value="Excellent" <?= $quality === 'Excellent' ? 'selected' : '' ?>>⭐⭐⭐⭐⭐ Excellent</option>
                    <option value="Good" <?= $quality === 'Good' ? 'selected' : '' ?>>⭐⭐⭐⭐ Good</option>
                    <option value="Average" <?= $quality === 'Average' ? 'selected' : '' ?>>⭐⭐⭐ Average</option>
                    <option value="Poor" <?= $quality === 'Poor' ? 'selected' : '' ?>>⭐⭐ Poor</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
                <select name="sort"
                        class="w-full px-4 py-2.5 rounded-xl bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-500 border border-gray-300 appearance-none transition-all">
                    <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Name (A-Z)</option>
                    <option value="expiry" <?= $sort === 'expiry' ? 'selected' : '' ?>>Expiry Date</option>
                    <option value="quantity" <?= $sort === 'quantity' ? 'selected' : '' ?>>Quantity (Low-High)</option>
                    <option value="quality" <?= $sort === 'quality' ? 'selected' : '' ?>>Quality (High-Low)</option>
                </select>
            </div>

            <div class="md:col-span-4 flex gap-3">
                <button type="submit"
                        class="bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white px-8 py-2.5 rounded-xl font-semibold transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5 flex items-center">
                    <svg class="h-4 w-4 mr-2"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    Apply Filters
                </button>
                <a href="<?= URL_LIST_MEDICINES ?>"
                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-8 py-2.5 rounded-xl font-semibold transition-all border border-gray-300 flex items-center">
                    <svg class="h-4 w-4 mr-2"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Results Count -->
    <?php if ($searchTerm || $category || $quality): ?>
        <div class="mb-4 flex items-center text-sm text-gray-600 bg-primary-50 px-4 py-3 rounded-xl border border-primary-200">
            <svg class="h-4 w-4 text-primary-600 mr-2"
                 fill="currentColor"
                 viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                      d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                      clip-rule="evenodd"/>
            </svg>
            Found <span class="font-semibold text-gray-900 mx-1"><?= count($medicines) ?></span> medicine(s)
            <?php if ($searchTerm): ?>
                for "<span class="font-semibold text-primary-600"><?= htmlspecialchars($searchTerm) ?></span>"
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Medicines Table -->
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
        <?php if ($medicines): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-primary-50 to-primary-100/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-primary-900 uppercase tracking-wider">
                            Image
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-primary-900 uppercase tracking-wider">
                            Name
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-primary-900 uppercase tracking-wider">
                            Category
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-primary-900 uppercase tracking-wider">
                            Quality
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-primary-900 uppercase tracking-wider">
                            Expiry
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-primary-900 uppercase tracking-wider">
                            Quantity
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-primary-900 uppercase tracking-wider">
                            Box ID
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-primary-900 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                    <?php foreach ($medicines as $med):
                        $isExpiringSoon = strtotime($med['expiry_date']) <= strtotime('+30 days');
                        $isLowStock = $med['quantity'] < 10;
                        ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <img src="<?= $med['image'] ? '../uploads/' . htmlspecialchars($med['image']) : 'https://ui-avatars.com/api/?name=' . urlencode($med['name']) . '&background=00809D&color=fff' ?>"
                                     alt="<?= htmlspecialchars($med['name']) ?>"
                                     class="h-12 w-12 rounded-xl object-cover ring-2 ring-primary-200 shadow-sm">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900"><?= htmlspecialchars($med['name']) ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-primary-100 text-primary-800">
                                    <?= htmlspecialchars($med['category']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-gray-900"><?= htmlspecialchars($med['quality']) ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <?php if ($isExpiringSoon): ?>
                                        <svg class="h-4 w-4 text-red-500 mr-1"
                                             fill="currentColor"
                                             viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                  d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                                  clip-rule="evenodd"/>
                                        </svg>
                                    <?php endif; ?>
                                    <span class="text-sm font-mono <?= $isExpiringSoon ? 'text-red-600 font-semibold' : 'text-gray-900' ?>">
                                        <?= htmlspecialchars($med['expiry_date']) ?>
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <?php if ($isLowStock): ?>
                                        <svg class="h-4 w-4 text-accent-500 mr-1"
                                             fill="currentColor"
                                             viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                  d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                  clip-rule="evenodd"/>
                                        </svg>
                                    <?php endif; ?>
                                    <span class="text-sm font-mono <?= $isLowStock ? 'text-accent-600 font-semibold' : 'text-gray-900' ?>">
                                        <?= htmlspecialchars($med['quantity']) ?>
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">
                                <?= htmlspecialchars($med['box_id'] ?? 'N/A') ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="<?= URL_EDIT_MEDICINE ?>?id=<?= $med['id'] ?>"
                                       class="group flex items-center px-3 py-1.5 rounded-lg text-xs font-medium bg-primary-100 text-primary-700 hover:bg-primary-200 transition-colors">
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
                                    </a>
                                    <a href="?delete=<?= $med['id'] ?>"
                                       onclick="return confirm('Are you sure you want to delete this medicine?')"
                                       class="group flex items-center px-3 py-1.5 rounded-lg text-xs font-medium bg-red-100 text-red-700 hover:bg-red-200 transition-colors">
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
                                    </a>
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
                              d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">No medicines found</h3>
                <p class="text-gray-600 mb-6">
                    <?= $searchTerm || $category || $quality ? 'Try adjusting your filters or search terms.' : 'Start by adding your first medicine to the inventory.' ?>
                </p>
                <a href="<?= URL_ADD_MEDICINE ?>"
                   class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-semibold rounded-xl transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                    <svg class="h-5 w-5 mr-2"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M12 4v16m8-8H4"/>
                    </svg>
                    Add your first medicine
                </a>
            </div>
        <?php endif; ?>
    </div>

</main>

<?php include '../includes/footer.php'; ?>

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

</body>
</html>