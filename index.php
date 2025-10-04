<?php
global $pdo;
require_once './config/auth.php';
require_once './config/db.php';
if (!isLoggedIn()) {
    header('Location: ./public/login.php');
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

// Handle search
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Medicine Inventory</title>
    <link href="assets/output.css"
          rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-secondary-100 via-white to-secondary-200">
<?php
include './includes/header.php';?>
<main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

    <!-- Welcome Banner -->
    <div class="relative mb-8 overflow-hidden rounded-2xl bg-gradient-to-r from-primary-600 via-primary-500 to-accent-500 p-8 shadow-xl">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZGVmcz48cGF0dGVybiBpZD0iZ3JpZCIgd2lkdGg9IjQwIiBoZWlnaHQ9IjQwIiBwYXR0ZXJuVW5pdHM9InVzZXJTcGFjZU9uVXNlIj48cGF0aCBkPSJNIDQwIDAgTCAwIDAgMCA0MCIgZmlsbD0ibm9uZSIgc3Ryb2tlPSJ3aGl0ZSIgc3Ryb2tlLW9wYWNpdHk9IjAuMSIgc3Ryb2tlLXdpZHRoPSIxIi8+PC9wYXR0ZXJuPjwvZGVmcz48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSJ1cmwoI2dyaWQpIi8+PC9zdmc+')] opacity-30"></div>
        <div class="relative flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">Welcome
                    back, <?= htmlspecialchars($_SESSION['name'] ?? 'User') ?>!</h1>
                <p class="text-primary-100">Here's your inventory overview for today</p>
            </div>
            <div class="hidden md:block">
                <div class="bg-white/10 backdrop-blur-sm rounded-xl px-4 py-2 border border-white/20">
                    <p class="text-xs text-primary-100">Today</p>
                    <p class="text-lg font-bold text-white"><?= date('M d, Y') ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-10">
        <!-- Total Medicines -->
        <div class="group relative overflow-hidden bg-white rounded-2xl p-6 shadow-lg hover:shadow-2xl transition-all duration-300 border border-gray-200 hover:border-primary-300">
            <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-primary-500/10 to-transparent rounded-full -mr-16 -mt-16 group-hover:scale-150 transition-transform duration-500"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl shadow-lg group-hover:scale-110 transition-transform">
                        <svg class="size-6 text-white"
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
                <h3 class="text-sm font-medium text-gray-600 mb-1">Total Medicines</h3>
                <p class="text-4xl font-bold bg-gradient-to-r from-primary-600 to-primary-500 bg-clip-text text-transparent"><?= $totalMedicines ?></p>
                <div class="mt-2 flex items-center text-xs text-green-600">
                    <svg class="size-3 mr-1"
                         fill="currentColor"
                         viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                              d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z"
                              clip-rule="evenodd"/>
                    </svg>
                    <span>Active inventory</span>
                </div>
            </div>
        </div>

        <!-- Categories -->
        <div class="group relative overflow-hidden bg-white rounded-2xl p-6 shadow-lg hover:shadow-2xl transition-all duration-300 border border-gray-200 hover:border-tertiary-300">
            <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-tertiary-500/10 to-transparent rounded-full -mr-16 -mt-16 group-hover:scale-150 transition-transform duration-500"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-gradient-to-br from-tertiary-500 to-tertiary-600 rounded-xl shadow-lg group-hover:scale-110 transition-transform">
                        <svg class="size-6 text-white"
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
                <h3 class="text-sm font-medium text-gray-600 mb-1">Categories</h3>
                <p class="text-4xl font-bold bg-gradient-to-r from-tertiary-600 to-tertiary-500 bg-clip-text text-transparent"><?= $totalCategories ?></p>
                <div class="mt-2 text-xs text-gray-500">
                    <span>Organized types</span>
                </div>
            </div>
        </div>

        <!-- Low Stock -->
        <div class="group relative overflow-hidden bg-white rounded-2xl p-6 shadow-lg hover:shadow-2xl transition-all duration-300 border border-gray-200 hover:border-accent-300">
            <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-accent-500/10 to-transparent rounded-full -mr-16 -mt-16 group-hover:scale-150 transition-transform duration-500"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-gradient-to-br from-accent-500 to-accent-600 rounded-xl shadow-lg group-hover:scale-110 transition-transform">
                        <svg class="size-6 text-white"
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
                <h3 class="text-sm font-medium text-gray-600 mb-1">Low Stock Alert</h3>
                <p class="text-4xl font-bold bg-gradient-to-r from-accent-600 to-accent-500 bg-clip-text text-transparent"><?= $lowStock ?></p>
                <div class="mt-2 flex items-center text-xs text-accent-600">
                    <svg class="size-3 mr-1"
                         fill="currentColor"
                         viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                              d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                              clip-rule="evenodd"/>
                    </svg>
                    <span>Needs restock</span>
                </div>
            </div>
        </div>

        <!-- Worst Quality -->
        <div class="group relative overflow-hidden bg-white rounded-2xl p-6 shadow-lg hover:shadow-2xl transition-all duration-300 border border-gray-200 hover:border-primary-300">
            <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-primary-600/10 to-transparent rounded-full -mr-16 -mt-16 group-hover:scale-150 transition-transform duration-500"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-gradient-to-br from-primary-600 to-primary-700 rounded-xl shadow-lg group-hover:scale-110 transition-transform">
                        <svg class="size-6 text-white"
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
                <h3 class="text-sm font-medium text-gray-600 mb-1">Lowest Quality</h3>
                <p class="text-lg font-bold text-gray-900 truncate"><?= htmlspecialchars($worstName) ?></p>
                <div class="mt-1 text-xs text-gray-500">
                    <span><?= htmlspecialchars($worstQuality) ?></span>
                </div>
            </div>
        </div>

        <!-- Expiring Soon -->
        <div class="group relative overflow-hidden bg-white rounded-2xl p-6 shadow-lg hover:shadow-2xl transition-all duration-300 border border-gray-200 hover:border-red-300">
            <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-red-500/10 to-transparent rounded-full -mr-16 -mt-16 group-hover:scale-150 transition-transform duration-500"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-gradient-to-br from-red-500 to-red-600 rounded-xl shadow-lg group-hover:scale-110 transition-transform">
                        <svg class="size-6 text-white"
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
                <h3 class="text-sm font-medium text-gray-600 mb-1">Expiring Soon</h3>
                <p class="text-4xl font-bold bg-gradient-to-r from-red-600 to-red-500 bg-clip-text text-transparent"><?= $expiring ?></p>
                <div class="mt-2 flex items-center text-xs text-red-600">
                    <svg class="size-3 mr-1"
                         fill="currentColor"
                         viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                              d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                              clip-rule="evenodd"/>
                    </svg>
                    <span>Within 30 days</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="flex justify-center mb-10">
        <form method="GET"
              action="index.php"
              class="w-full max-w-3xl">
            <div class="relative group">
                <div class="absolute -inset-1 bg-gradient-to-r from-primary-500 to-accent-500 rounded-2xl blur opacity-25 group-hover:opacity-40 transition duration-300"></div>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="size-5 text-gray-400"
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
                           class="w-full pl-12 pr-32 py-4 rounded-xl bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 border border-gray-200 shadow-lg transition-all"/>
                    <button type="submit"
                            class="absolute right-2 top-2 px-6 py-2 rounded-lg bg-gradient-to-r from-primary-500 to-primary-600 text-white hover:from-primary-600 hover:to-primary-700 transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5 font-medium">
                        Search
                    </button>
                </div>
            </div>
            <?php if ($searchTerm): ?>
                <div class="mt-4 flex items-center justify-between bg-white rounded-xl px-4 py-3 shadow-md border border-gray-200">
                    <p class="text-sm text-gray-600 flex items-center">
                        <svg class="size-4 mr-2 text-primary-500"
                             fill="currentColor"
                             viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                  d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                  clip-rule="evenodd"/>
                        </svg>
                        Found <span class="font-semibold text-gray-900 mx-1"><?= count($medicines) ?></span> result(s)
                        for
                        "<span class="font-semibold text-primary-600"><?= htmlspecialchars($searchTerm) ?></span>"
                    </p>
                    <a href="index.php"
                       class="text-sm text-primary-600 hover:text-primary-700 font-medium flex items-center group">
                        <span>Clear search</span>
                        <svg class="size-4 ml-1 group-hover:translate-x-1 transition-transform"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </a>
                </div>
            <?php endif; ?>
        </form>
    </div>

    <!-- Medicine List Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent">
                <?= $searchTerm ? 'Search Results' : 'Recent Medicines' ?>
            </h2>
            <p class="text-sm text-gray-500 mt-1">
                <?= $searchTerm ? 'Filtered inventory items' : 'Your latest 12 medicine entries' ?>
            </p>
        </div>
        <a href="public/list_medicines.php"
           class="group flex items-center px-4 py-2 bg-white hover:bg-gray-50 text-primary-600 hover:text-primary-700 rounded-xl font-medium transition-all shadow-md hover:shadow-lg border border-gray-200">
            <span>View All</span>
            <svg class="size-4 ml-2 group-hover:translate-x-1 transition-transform"
                 fill="none"
                 stroke="currentColor"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>

    <!-- Medicine Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php if ($medicines): ?>
            <?php foreach ($medicines as $med):
                $isExpiringSoon = strtotime($med['expiry_date']) <= strtotime('+30 days');
                $isLowStock = $med['quantity'] < 10;
                ?>
                <div class="group relative bg-white rounded-2xl p-6 shadow-lg hover:shadow-2xl transition-all duration-300 border border-gray-200 hover:border-primary-300 overflow-hidden">
                    <!-- Decorative corner -->
                    <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-primary-500/10 to-transparent rounded-bl-full group-hover:scale-150 transition-transform duration-500"></div>

                    <!-- Alert badges -->
                    <div class="absolute top-4 right-4 flex flex-col gap-2 z-10">
                        <?php if ($isExpiringSoon): ?>
                            <span class="px-2 py-1 bg-red-100 text-red-700 text-xs font-semibold rounded-lg flex items-center shadow-sm">
                                <svg class="size-3 mr-1"
                                     fill="currentColor"
                                     viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                          d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                          clip-rule="evenodd"/>
                                </svg>
                                Expiring
                            </span>
                        <?php endif; ?>
                        <?php if ($isLowStock): ?>
                            <span class="px-2 py-1 bg-accent-100 text-accent-700 text-xs font-semibold rounded-lg flex items-center shadow-sm">
                                <svg class="size-3 mr-1"
                                     fill="currentColor"
                                     viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                          d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                          clip-rule="evenodd"/>
                                </svg>
                                Low Stock
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="relative flex flex-col items-center text-center mb-4">
                        <div class="relative mb-4">
                            <div class="absolute -inset-1 bg-gradient-to-r from-primary-500 to-accent-500 rounded-2xl blur opacity-20 group-hover:opacity-40 transition"></div>
                            <img src="<?= $med['image'] ? './uploads/' . htmlspecialchars($med['image']) : 'https://ui-avatars.com/api/?name=' . urlencode($med['name']) . '&background=00809D&color=fff&size=128' ?>"
                                 alt="<?= htmlspecialchars($med['name']) ?>"
                                 class="relative w-20 h-20 rounded-2xl object-cover ring-2 ring-white shadow-lg group-hover:scale-110 transition-transform"/>
                        </div>
                        <h3 class="text-gray-900 font-bold text-xl mb-1"><?= htmlspecialchars($med['name']) ?></h3>
                        <span class="inline-block px-3 py-1 bg-primary-100 text-primary-700 text-xs font-medium rounded-full">
                            <?= htmlspecialchars($med['category']) ?>
                        </span>
                    </div>

                    <div class="space-y-3 relative">
                        <div class="flex items-center justify-between py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-500 flex items-center">
                                <svg class="size-4 mr-2 text-gray-400"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Quality
                            </span>
                            <span class="text-sm font-semibold text-gray-900"><?= htmlspecialchars($med['quality']) ?></span>
                        </div>

                        <div class="flex items-center justify-between py-2 border-b border-gray-100">
                            <span class="text-sm text-gray-500 flex items-center">
                                <svg class="size-4 mr-2 text-gray-400"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                Expiry
                            </span>
                            <span class="text-sm font-semibold <?= $isExpiringSoon ? 'text-red-600' : 'text-gray-900' ?>">
                                <?= htmlspecialchars($med['expiry_date']) ?>
                            </span>
                        </div>

                        <div class="flex items-center justify-between py-2">
                            <span class="text-sm text-gray-500 flex items-center">
                                <svg class="size-4 mr-2 text-gray-400"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                                Quantity
                            </span>
                            <span class="text-sm font-semibold <?= $isLowStock ? 'text-accent-600' : 'text-gray-900' ?>">
                                <?= htmlspecialchars($med['quantity']) ?>
                            </span>
                        </div>

                        <?php if ($med['box_id']): ?>
                            <div class="mt-3 pt-3 border-t border-gray-100">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-500">Box ID</span>
                                    <span class="text-xs font-mono font-semibold text-primary-600 bg-primary-50 px-2 py-1 rounded">
                                        <?= htmlspecialchars($med['box_id']) ?>
                                    </span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <a href="public/edit_medicine.php?id=<?= $med['id'] ?>"
                           class="group/btn flex items-center justify-center w-full py-2.5 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-medium rounded-xl transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                            <svg class="size-4 mr-2 group-hover/btn:rotate-12 transition-transform"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            <span>Edit Details</span>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-span-full">
                <div class="bg-white rounded-2xl p-12 text-center shadow-lg border border-gray-200">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full mb-4">
                        <svg class="size-10 text-gray-400"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">
                        <?= $searchTerm ? 'No medicines found' : 'No medicines yet' ?>
                    </h3>
                    <p class="text-gray-600 mb-6">
                        <?= $searchTerm ? 'Try adjusting your search terms or filters.' : 'Start by adding your first medicine to the inventory.' ?>
                    </p>
                    <div class="flex justify-center gap-3">
                        <?php if ($searchTerm): ?>
                            <a href="index.php"
                               class="inline-flex items-center px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-xl transition-all shadow-md hover:shadow-lg">
                                <svg class="size-5 mr-2"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Clear Search
                            </a>
                        <?php endif; ?>
                        <a href="public/add_medicine.php"
                           class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-semibold rounded-xl transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                            <svg class="size-5 mr-2"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M12 4v16m8-8H4"/>
                            </svg>
                            Add Medicine
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($medicines && count($medicines) >= 12 && !$searchTerm): ?>
        <!-- View More Section -->
        <div class="mt-10 text-center">
            <a href="public/list_medicines.php"
               class="inline-flex items-center px-8 py-4 bg-white hover:bg-gray-50 text-gray-900 font-semibold rounded-xl transition-all shadow-lg hover:shadow-xl border border-gray-200 group">
                <span>View Complete Inventory</span>
                <svg class="size-5 ml-2 group-hover:translate-x-1 transition-transform"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </a>
        </div>
    <?php endif; ?>

</main>

<?php include './includes/footer.php'; ?>

</body>
</html>