<?php
global $pdo;
require_once '../config/auth.php';
require_once '../config/db.php';
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Fetch stats
$totalStmt = $pdo->query("SELECT COUNT(*) as total FROM medicines");
$totalMedicines = $totalStmt->fetch()['total'] ?? 0;

$categoriesStmt = $pdo->query("SELECT COUNT(DISTINCT category) as total FROM medicines");
$totalCategories = $categoriesStmt->fetch()['total'] ?? 0;

$lowStockStmt = $pdo->query("SELECT COUNT(*) as total FROM medicines WHERE quantity < 10");
$lowStock = $lowStockStmt->fetch()['total'] ?? 0;

$worstStmt = $pdo->query("SELECT name, quality FROM medicines ORDER BY quality ASC LIMIT 1");
$worst = $worstStmt->fetch();
$worstName = $worst['name'] ?? 'N/A';
$worstQuality = $worst['quality'] ?? 'N/A';

$expStmt = $pdo->prepare("SELECT COUNT(*) as exp_count FROM medicines WHERE expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)");
$expStmt->execute();
$expiring = $expStmt->fetch()['exp_count'] ?? 0;

// Handle search - FIXED
$searchTerm = trim($_GET['search'] ?? '');
if ($searchTerm) {
    $searchParam = "%$searchTerm%";
    $stmt = $pdo->prepare("SELECT * FROM medicines WHERE name LIKE ? OR category LIKE ? ORDER BY name ASC");
    $stmt->execute([$searchParam, $searchParam]);
} else {
    $stmt = $pdo->query("SELECT * FROM medicines ORDER BY name ASC LIMIT 12");
}
$medicines = $stmt->fetchAll();

// Set custom page title
$pageTitle = "Dashboard";

include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en"
      class="h-full bg-gray-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Medicine Inventory</title>
    <link href="../assets/output.css"
          rel="stylesheet">
</head>
<body class="h-full">

<main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 text-white">

    <!-- Top Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6 mb-10">
        <div class="bg-gradient-to-br from-indigo-600 to-indigo-800 rounded-lg p-6 shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-medium text-indigo-200 mb-1">Total Medicines</h2>
                    <p class="text-3xl font-bold text-white"><?= $totalMedicines ?></p>
                </div>
                <div class="bg-white/10 p-3 rounded-lg">
                    <svg class="h-8 w-8 text-white"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-purple-600 to-purple-800 rounded-lg p-6 shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-medium text-purple-200 mb-1">Categories</h2>
                    <p class="text-3xl font-bold text-white"><?= $totalCategories ?></p>
                </div>
                <div class="bg-white/10 p-3 rounded-lg">
                    <svg class="h-8 w-8 text-white"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-yellow-600 to-yellow-800 rounded-lg p-6 shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-medium text-yellow-200 mb-1">Low Stock</h2>
                    <p class="text-3xl font-bold text-white"><?= $lowStock ?></p>
                </div>
                <div class="bg-white/10 p-3 rounded-lg">
                    <svg class="h-8 w-8 text-white"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-green-600 to-green-800 rounded-lg p-6 shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-medium text-green-200 mb-1">Worst Quality</h2>
                    <p class="text-sm font-bold text-white truncate"><?= htmlspecialchars($worstName) ?></p>
                    <p class="text-xs text-green-200">(<?= htmlspecialchars($worstQuality) ?>)</p>
                </div>
                <div class="bg-white/10 p-3 rounded-lg">
                    <svg class="h-8 w-8 text-white"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-red-600 to-red-800 rounded-lg p-6 shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-medium text-red-200 mb-1">Expiring Soon</h2>
                    <p class="text-3xl font-bold text-white"><?= $expiring ?></p>
                </div>
                <div class="bg-white/10 p-3 rounded-lg">
                    <svg class="h-8 w-8 text-white"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="flex justify-center mb-10">
        <form method="GET"
              action="index.php"
              class="w-full max-w-2xl">
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
                       placeholder="Search medicines by name or category..."
                       value="<?= htmlspecialchars($searchTerm) ?>"
                       class="w-full pl-10 pr-24 py-3 rounded-lg bg-gray-800 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 border border-gray-700"/>
                <button type="submit"
                        class="absolute right-2 top-2 px-4 py-1.5 rounded-md bg-indigo-500 text-white hover:bg-indigo-400 transition">
                    Search
                </button>
            </div>
            <?php if ($searchTerm): ?>
                <div class="mt-3 flex items-center justify-between">
                    <p class="text-sm text-gray-400">
                        Found <span class="text-white font-semibold"><?= count($medicines) ?></span> result(s) for
                        "<span class="text-indigo-400"><?= htmlspecialchars($searchTerm) ?></span>"
                    </p>
                    <a href="index.php"
                       class="text-sm text-indigo-400 hover:text-indigo-300">Clear search</a>
                </div>
            <?php endif; ?>
        </form>
    </div>

    <!-- Medicine List -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-white"><?= $searchTerm ? 'Search Results' : 'Recent Medicines' ?></h2>
        <a href="list_medicines.php"
           class="text-indigo-400 hover:text-indigo-300 text-sm font-medium">
            View All →
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php if ($medicines): ?>
            <?php foreach ($medicines as $med):
                $isExpiringSoon = strtotime($med['expiry_date']) <= strtotime('+30 days');
                $isLowStock = $med['quantity'] < 10;
                ?>
                <div class="bg-gray-800 rounded-lg p-5 shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-1 border border-gray-700">
                    <div class="flex items-start gap-4">
                        <img src="<?= $med['image'] ? '../uploads/' . htmlspecialchars($med['image']) : 'https://ui-avatars.com/api/?name=' . urlencode($med['name']) . '&background=4f46e5&color=fff&size=128' ?>"
                             alt="<?= htmlspecialchars($med['name']) ?>"
                             class="w-16 h-16 rounded-lg object-cover ring-2 ring-indigo-500/50">
                        <div class="flex-1 min-w-0">
                            <h3 class="text-white font-bold text-lg mb-1 truncate"><?= htmlspecialchars($med['name']) ?></h3>
                            <div class="space-y-1.5">
                                <p class="text-gray-400 text-sm flex items-center">
                                    <span class="inline-block w-20 text-gray-500">Category:</span>
                                    <span class="text-gray-300"><?= htmlspecialchars($med['category']) ?></span>
                                </p>
                                <p class="text-gray-400 text-sm flex items-center">
                                    <span class="inline-block w-20 text-gray-500">Quality:</span>
                                    <span class="text-gray-300"><?= htmlspecialchars($med['quality']) ?></span>
                                </p>
                                <p class="text-gray-400 text-sm flex items-center">
                                    <span class="inline-block w-20 text-gray-500">Expiry:</span>
                                    <span class="<?= $isExpiringSoon ? 'text-red-400 font-semibold' : 'text-gray-300' ?>">
                                        <?= htmlspecialchars($med['expiry_date']) ?>
                                    </span>
                                </p>
                                <p class="text-gray-400 text-sm flex items-center">
                                    <span class="inline-block w-20 text-gray-500">Quantity:</span>
                                    <span class="<?= $isLowStock ? 'text-yellow-400 font-semibold' : 'text-gray-300' ?>">
                                        <?= htmlspecialchars($med['quantity']) ?>
                                    </span>
                                </p>
                                <?php if ($med['box_id']): ?>
                                    <p class="text-gray-400 text-sm flex items-center">
                                        <span class="inline-block w-20 text-gray-500">Box ID:</span>
                                        <span class="text-gray-300"><?= htmlspecialchars($med['box_id']) ?></span>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-span-full text-center py-12">
                <p class="text-gray-400 text-lg mb-4"><?= $searchTerm ? 'No medicines found matching your search.' : 'No medicines found.' ?></p>
                <?php if ($searchTerm): ?>
                    <a href="index.php"
                       class="inline-block bg-gray-700 hover:bg-gray-600 text-white font-semibold px-6 py-2 rounded-md transition mr-3">
                        Clear Search
                    </a>
                <?php endif; ?>
                <a href="add_medicine.php"
                   class="inline-block bg-indigo-500 hover:bg-indigo-400 text-white font-semibold px-6 py-2 rounded-md transition">
                    Add Medicine
                </a>
            </div>
        <?php endif; ?>
    </div>

</main>

<?php include '../includes/footer.php'; ?>

</body>
</html>