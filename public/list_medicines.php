<?php
global $pdo;
require_once '../config/auth.php';
require_once '../config/db.php';
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
      class="h-full bg-gray-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>All Medicines - Medicine Inventory</title>
    <link href="../assets/output.css"
          rel="stylesheet">
</head>
<body class="h-full">

<main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">

    <!-- Header -->
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-3xl font-bold text-white"></h1>
        <a href="add_medicine.php"
           class="bg-indigo-500 hover:bg-indigo-400 text-white font-semibold px-6 py-2.5 rounded-md transition shadow-lg shadow-indigo-500/50 flex items-center space-x-2">
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
        <div class="mb-6 rounded-md bg-green-900/50 p-4 border border-green-700">
            <p class="text-sm text-green-300"><?= htmlspecialchars($success) ?></p>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="mb-6 rounded-md bg-red-900/50 p-4 border border-red-700">
            <p class="text-sm text-red-300"><?= htmlspecialchars($error) ?></p>
        </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="bg-gray-800 rounded-lg p-6 mb-6 border border-gray-700">
        <form method="GET"
              class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Search</label>
                <input type="text"
                       name="search"
                       value="<?= htmlspecialchars($searchTerm) ?>"
                       placeholder="Search medicines..."
                       class="w-full px-3 py-2 rounded-md bg-white/5 text-white placeholder-gray-400 focus:outline-2 focus:outline-indigo-500 border border-gray-700">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Category</label>
                <select name="category"
                        class="w-full px-3 py-2 rounded-md bg-white/5 text-white focus:outline-2 focus:outline-indigo-500 border border-gray-700">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Quality</label>
                <select name="quality"
                        class="w-full px-3 py-2 rounded-md bg-white/5 text-white focus:outline-2 focus:outline-indigo-500 border border-gray-700">
                    <option value="">All Qualities</option>
                    <option value="Excellent" <?= $quality === 'Excellent' ? 'selected' : '' ?>>Excellent</option>
                    <option value="Good" <?= $quality === 'Good' ? 'selected' : '' ?>>Good</option>
                    <option value="Average" <?= $quality === 'Average' ? 'selected' : '' ?>>Average</option>
                    <option value="Poor" <?= $quality === 'Poor' ? 'selected' : '' ?>>Poor</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Sort By</label>
                <select name="sort"
                        class="w-full px-3 py-2 rounded-md bg-white/5 text-white focus:outline-2 focus:outline-indigo-500 border border-gray-700">
                    <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Name</option>
                    <option value="expiry" <?= $sort === 'expiry' ? 'selected' : '' ?>>Expiry Date</option>
                    <option value="quantity" <?= $sort === 'quantity' ? 'selected' : '' ?>>Quantity</option>
                    <option value="quality" <?= $sort === 'quality' ? 'selected' : '' ?>>Quality</option>
                </select>
            </div>

            <div class="md:col-span-4 flex gap-2">
                <button type="submit"
                        class="bg-indigo-500 hover:bg-indigo-400 text-white px-6 py-2 rounded-md font-semibold transition">
                    Apply Filters
                </button>
                <a href="list_medicines.php"
                   class="bg-gray-700 hover:bg-gray-600 text-white px-6 py-2 rounded-md font-semibold transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Results Count -->
    <?php if ($searchTerm || $category || $quality): ?>
        <div class="mb-4 text-sm text-gray-400">
            Found <?= count($medicines) ?> medicine(s)
        </div>
    <?php endif; ?>

    <!-- Medicines Table -->
    <div class="bg-gray-800 rounded-lg shadow overflow-x-auto border border-gray-700">
        <?php if ($medicines): ?>
            <table class="min-w-full divide-y divide-gray-700">
                <thead class="bg-gray-700 font-display">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Image
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                        Category
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Quality
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Expiry
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                        Quantity
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Box ID
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Actions
                    </th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                <?php foreach ($medicines as $med):
                    $isExpiringSoon = strtotime($med['expiry_date']) <= strtotime('+30 days');
                    $isLowStock = $med['quantity'] < 10;
                    ?>
                    <tr class="hover:bg-gray-700/50 transition">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <img src="<?= $med['image'] ? '../uploads/' . htmlspecialchars($med['image']) : 'https://ui-avatars.com/api/?name=' . urlencode($med['name']) . '&background=4f46e5&color=fff' ?>"
                                 alt="<?= htmlspecialchars($med['name']) ?>"
                                 class="h-12 w-12 rounded-lg object-cover ring-2 ring-indigo-500/50">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-white"><?= htmlspecialchars($med['name']) ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 font-mono uppercase inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-900/50 text-indigo-300">
                                    <?= htmlspecialchars($med['category']) ?>
                                </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-mono text-gray-300"><?= htmlspecialchars($med['quality']) ?></span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-mono <?= $isExpiringSoon ? 'text-red-400 font-semibold' : 'text-gray-300' ?>">
                                    <?= htmlspecialchars($med['expiry_date']) ?>
                                </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-mono <?= $isLowStock ? 'text-yellow-400 font-semibold' : 'text-gray-300' ?>">
                                    <?= htmlspecialchars($med['quantity']) ?>
                                </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm  font-mono text-gray-300">
                            <?= htmlspecialchars($med['box_id'] ?? 'N/A') ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-3">
                            <div class="flex space-x-2">
                                <a href="edit_medicine.php?id=<?= $med['id'] ?>"
                                   class="px-3 py-1 rounded-md text-xs font-medium bg-indigo-600 text-white hover:bg-indigo-500 transition">
                                    Edit
                                </a>
                                <a href="?delete=<?= $med['id'] ?>"
                                   onclick="return confirm('Are you sure you want to delete this medicine?')"
                                   class="px-3 py-1 rounded-md text-xs font-medium bg-red-600 text-white hover:bg-red-500 transition">
                                    Delete
                                </a>
                            </div>

                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="text-center py-12">
                <p class="text-gray-400 text-lg mb-4">No medicines found.</p>
                <a href="add_medicine.php"
                   class="inline-block bg-indigo-500 hover:bg-indigo-400 text-white font-semibold px-6 py-2 rounded-md transition">
                    Add your first medicine
                </a>
            </div>
        <?php endif; ?>
    </div>

</main>

<?php include '../includes/footer.php'; ?>

</body>
</html>