<?php
global $pdo;
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/paths.php';

$user_name = $_SESSION['name'] ?? 'User';
$profile_pic = $_SESSION['profile_pic'] ?? null;
$current_page = getCurrentPage();

// Define page titles
$pageTitles = [
        'index.php' => 'Dashboard',
        'list_medicines.php' => 'All Medicines',
        'add_medicine.php' => 'Add New Medicine',
        'edit_medicine.php' => 'Edit Medicine',
        'manage_categories.php' => 'Manage Categories',
        'profile.php' => 'My Profile',
        'change_pw.php' => 'Change Password'
];

$pageTitle = $pageTitle ?? $pageTitles[$current_page] ?? 'Medicine Inventory';

// Get notification counts
try {
    $lowStockCount = $pdo->query("SELECT COUNT(*) FROM medicines WHERE quantity < 10")->fetchColumn();
    $expiringCount = $pdo->query("SELECT COUNT(*) FROM medicines WHERE expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)")->fetchColumn();
    $totalNotifications = $lowStockCount + $expiringCount;
} catch (Exception $e) {
    $totalNotifications = 0;
    $lowStockCount = 0;
    $expiringCount = 0;
}

// Get profile picture URL
$profilePicUrl = $profile_pic ? getProfilePicUrl($profile_pic) : getAvatarUrl($user_name);
?>

<nav class="sticky top-0 bg-gradient-to-r from-gray-900 via-primary-900 to-gray-900 border-b border-primary-700/30 z-50 shadow-2xl backdrop-blur-xl">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
            <!-- Logo & Brand -->
            <div class="flex items-center">
                <div class="shrink-0">
                    <a href="<?= URL_HOME ?>"
                       class="flex items-center space-x-3 group">
                        <div class="relative">
                            <div class="absolute -inset-1 bg-gradient-to-r from-primary-500 to-accent-500 rounded-full blur opacity-30 group-hover:opacity-60 transition-opacity duration-300"></div>
                            <div class="relative size-10 bg-gradient-to-br from-primary-500 to-primary-600 rounded-full flex items-center justify-center shadow-lg transform group-hover:scale-110 transition-transform duration-300">
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
                        <div class="hidden sm:block">
                            <span class="text-2xl font-bold bg-gradient-to-r from-secondary-100 via-white to-secondary-100 bg-clip-text text-transparent tracking-tight">
                                Medi<span class="bg-gradient-to-r from-primary-400 to-accent-500 bg-clip-text text-transparent">Track</span>
                            </span>
                        </div>
                    </a>
                </div>

                <!-- Desktop Menu Links -->
                <div class="hidden md:block ml-10">
                    <div class="flex items-baseline space-x-1">
                        <a href="<?= URL_HOME ?>"
                           class="<?= isCurrentPage('index.php') ? 'bg-gradient-to-r from-primary-600/20 to-primary-500/20 text-white border border-primary-500/30' : 'text-gray-300 hover:bg-primary-800/30 hover:text-white border border-transparent' ?> rounded-lg px-4 py-2 text-sm font-medium transition-all duration-200 relative group">
                            <div class="flex items-center space-x-2">
                                <svg class="size-4 <?= isCurrentPage('index.php') ? 'text-primary-400' : '' ?>"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                                <span>Dashboard</span>
                            </div>
                            <?php if (isCurrentPage('index.php')): ?>
                                <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-1/2 h-0.5 bg-gradient-to-r from-transparent via-primary-400 to-transparent"></div>
                            <?php endif; ?>
                        </a>

                        <a href="<?= URL_LIST_MEDICINES ?>"
                           class="<?= isCurrentPage('list_medicines.php') ? 'bg-gradient-to-r from-primary-600/20 to-primary-500/20 text-white border border-primary-500/30' : 'text-gray-300 hover:bg-primary-800/30 hover:text-white border border-transparent' ?> rounded-lg px-4 py-2 text-sm font-medium transition-all duration-200 relative group">
                            <div class="flex items-center space-x-2">
                                <svg class="size-4 <?= isCurrentPage('list_medicines.php') ? 'text-primary-400' : '' ?>"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                                <span>Medicines</span>
                            </div>
                            <?php if (isCurrentPage('list_medicines.php')): ?>
                                <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-1/2 h-0.5 bg-gradient-to-r from-transparent via-primary-400 to-transparent"></div>
                            <?php endif; ?>
                        </a>

                        <a href="<?= URL_ADD_MEDICINE ?>"
                           class="<?= isCurrentPage('add_medicine.php') ? 'bg-gradient-to-r from-primary-600/20 to-primary-500/20 text-white border border-primary-500/30' : 'text-gray-300 hover:bg-primary-800/30 hover:text-white border border-transparent' ?> rounded-lg px-4 py-2 text-sm font-medium transition-all duration-200 relative group">
                            <div class="flex items-center space-x-2">
                                <svg class="size-4 <?= isCurrentPage('add_medicine.php') ? 'text-primary-400' : '' ?>"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M12 4v16m8-8H4"/>
                                </svg>
                                <span>Add Medicine</span>
                            </div>
                            <?php if (isCurrentPage('add_medicine.php')): ?>
                                <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-1/2 h-0.5 bg-gradient-to-r from-transparent via-primary-400 to-transparent"></div>
                            <?php endif; ?>
                        </a>

                        <a href="<?= URL_MANAGE_CATEGORIES ?>"
                           class="<?= isCurrentPage('manage_categories.php') ? 'bg-gradient-to-r from-primary-600/20 to-primary-500/20 text-white border border-primary-500/30' : 'text-gray-300 hover:bg-primary-800/30 hover:text-white border border-transparent' ?> rounded-lg px-4 py-2 text-sm font-medium transition-all duration-200 relative group">
                            <div class="flex items-center space-x-2">
                                <svg class="size-4 <?= isCurrentPage('manage_categories.php') ? 'text-primary-400' : '' ?>"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                                <span>Categories</span>
                            </div>
                            <?php if (isCurrentPage('manage_categories.php')): ?>
                                <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-1/2 h-0.5 bg-gradient-to-r from-transparent via-primary-400 to-transparent"></div>
                            <?php endif; ?>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Right side: Notifications & Profile -->
            <div class="hidden md:flex md:items-center md:space-x-3">

                <!-- Notifications -->
                <div class="relative">
                    <button id="notificationBtn"
                            type="button"
                            class="relative p-2.5 rounded-xl text-gray-400 hover:text-white hover:bg-primary-800/30 transition-all duration-200 group">
                        <svg class="size-5 group-hover:scale-110 transition-transform"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <?php if ($totalNotifications > 0): ?>
                            <span class="absolute -top-1 -right-1 flex size-5 items-center justify-center">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-accent-400 opacity-75"></span>
                                <span class="relative inline-flex items-center justify-center size-5 rounded-full bg-gradient-to-br from-accent-500 to-accent-600 text-[10px] font-bold text-white shadow-lg">
                                    <?= $totalNotifications ?>
                                </span>
                            </span>
                        <?php endif; ?>
                    </button>

                    <!-- Notifications Dropdown -->
                    <div id="notificationMenu"
                         class="hidden absolute right-0 mt-3 w-80 origin-top-right rounded-2xl bg-gradient-to-b from-gray-800 to-gray-900 shadow-2xl ring-1 ring-primary-500/20 z-[100] overflow-hidden">
                        <div class="p-4 border-b border-primary-700/30 bg-gradient-to-r from-primary-900/20 to-transparent">
                            <h3 class="text-sm font-semibold text-white">Notifications</h3>
                        </div>
                        <div class="max-h-96 overflow-y-auto">
                            <?php if ($totalNotifications > 0): ?>
                                <?php if ($lowStockCount > 0): ?>
                                    <a href="<?= URL_LIST_MEDICINES ?>"
                                       class="block px-4 py-3 hover:bg-primary-800/20 transition-all group">
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0 p-2 bg-accent-500/10 rounded-lg group-hover:bg-accent-500/20 transition-colors">
                                                <svg class="size-5 text-accent-400"
                                                     fill="none"
                                                     stroke="currentColor"
                                                     viewBox="0 0 24 24">
                                                    <path stroke-linecap="round"
                                                          stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                </svg>
                                            </div>
                                            <div class="ml-3 flex-1">
                                                <p class="text-sm font-medium text-white">Low Stock Alert</p>
                                                <p class="text-xs text-gray-400 mt-1"><?= $lowStockCount ?> medicine(s)
                                                    running low on stock</p>
                                            </div>
                                        </div>
                                    </a>
                                <?php endif; ?>

                                <?php if ($expiringCount > 0): ?>
                                    <a href="<?= URL_LIST_MEDICINES ?>"
                                       class="block px-4 py-3 hover:bg-primary-800/20 transition-all border-t border-gray-700/50 group">
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0 p-2 bg-red-500/10 rounded-lg group-hover:bg-red-500/20 transition-colors">
                                                <svg class="size-5 text-red-400"
                                                     fill="none"
                                                     stroke="currentColor"
                                                     viewBox="0 0 24 24">
                                                    <path stroke-linecap="round"
                                                          stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </div>
                                            <div class="ml-3 flex-1">
                                                <p class="text-sm font-medium text-white">Expiry Alert</p>
                                                <p class="text-xs text-gray-400 mt-1"><?= $expiringCount ?> medicine(s)
                                                    expiring within 30 days</p>
                                            </div>
                                        </div>
                                    </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="px-4 py-12 text-center">
                                    <div class="inline-flex items-center justify-center size-16 bg-primary-900/30 rounded-full mb-3">
                                        <svg class="size-8 text-primary-600"
                                             fill="none"
                                             stroke="currentColor"
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  stroke-width="2"
                                                  d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                        </svg>
                                    </div>
                                    <p class="text-sm text-gray-400">No new notifications</p>
                                    <p class="text-xs text-gray-600 mt-1">You're all caught up!</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Profile Dropdown -->
                <div class="relative">
                    <button id="profileBtn"
                            type="button"
                            class="flex items-center space-x-3 rounded-xl px-3 py-2 hover:bg-primary-800/30 transition-all duration-200 group">
                        <div class="relative">
                            <img class="size-9 rounded-xl object-cover ring-2 ring-primary-600/30 group-hover:ring-primary-500 transition-all shadow-lg"
                                 src="<?= htmlspecialchars($profilePicUrl) ?>"
                                 alt="<?= htmlspecialchars($user_name) ?>">
                            <div class="absolute -bottom-0.5 -right-0.5 size-3 bg-green-400 rounded-full ring-2 ring-gray-900"></div>
                        </div>
                        <span class="text-white font-medium text-sm hidden lg:block"><?= htmlspecialchars($user_name) ?></span>
                        <svg class="size-4 text-gray-400 hidden lg:block transition-transform group-hover:text-primary-400"
                             id="profileChevron"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <!-- Dropdown Menu -->
                    <div id="profileMenu"
                         class="hidden absolute right-0 top-full mt-3 w-64 origin-top-right rounded-2xl bg-gradient-to-b from-gray-800 to-gray-900 shadow-2xl ring-1 ring-primary-500/20 z-[100] overflow-hidden">
                        <div class="px-4 py-4 border-b border-primary-700/30 bg-gradient-to-r from-primary-900/20 to-transparent">
                            <p class="text-xs text-gray-500 mb-1">Signed in as</p>
                            <p class="text-sm font-semibold text-white truncate"><?= htmlspecialchars($user_name) ?></p>
                            <p class="text-xs text-primary-400 truncate">
                                @<?= htmlspecialchars($_SESSION['username'] ?? 'user') ?></p>
                        </div>
                        <div class="py-2">
                            <a href="<?= URL_PROFILE ?>"
                               class="group flex items-center px-4 py-3 text-sm text-gray-300 hover:bg-primary-800/20 hover:text-white transition-all">
                                <div class="p-2 bg-primary-900/30 rounded-lg group-hover:bg-primary-600/20 transition-colors mr-3">
                                    <svg class="size-4 text-primary-400"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <span>Your Profile</span>
                            </a>
                            <a href="<?= URL_CHANGE_PASSWORD ?>"
                               class="group flex items-center px-4 py-3 text-sm text-gray-300 hover:bg-primary-800/20 hover:text-white transition-all">
                                <div class="p-2 bg-primary-900/30 rounded-lg group-hover:bg-primary-600/20 transition-colors mr-3">
                                    <svg class="size-4 text-primary-400"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                    </svg>
                                </div>
                                <span>Change Password</span>
                            </a>
                        </div>
                        <div class="border-t border-gray-700/50">
                            <a href="<?= URL_LOGOUT ?>"
                               class="group flex items-center px-4 py-3 text-sm text-red-400 hover:bg-red-900/20 hover:text-red-300 transition-all">
                                <div class="p-2 bg-red-900/20 rounded-lg group-hover:bg-red-900/30 transition-colors mr-3">
                                    <svg class="size-4"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                </div>
                                <span>Sign out</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile menu button -->
            <div class="md:hidden">
                <button id="mobileMenuBtn"
                        type="button"
                        class="inline-flex items-center justify-center p-2 rounded-xl text-gray-400 hover:text-white hover:bg-primary-800/30 transition-all">
                    <svg class="size-6"
                         id="menuIconOpen"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg class="size-6 hidden"
                         id="menuIconClose"
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

    <!-- Mobile Menu -->
    <div id="mobileMenu"
         class="hidden md:hidden bg-gray-900 border-t border-primary-700/30">
        <div class="px-3 pt-2 pb-3 space-y-1">
            <a href="<?= URL_HOME ?>"
               class="<?= isCurrentPage('index.php') ? 'bg-gradient-to-r from-primary-600/20 to-primary-500/20 text-white border-l-4 border-primary-500' : 'text-gray-300 hover:bg-primary-800/20 hover:text-white border-l-4 border-transparent' ?> block rounded-r-lg px-4 py-3 text-base font-medium transition-all">
                Dashboard
            </a>
            <a href="<?= URL_LIST_MEDICINES ?>"
               class="<?= isCurrentPage('list_medicines.php') ? 'bg-gradient-to-r from-primary-600/20 to-primary-500/20 text-white border-l-4 border-primary-500' : 'text-gray-300 hover:bg-primary-800/20 hover:text-white border-l-4 border-transparent' ?> block rounded-r-lg px-4 py-3 text-base font-medium transition-all">
                Medicines
            </a>
            <a href="<?= URL_ADD_MEDICINE ?>"
               class="<?= isCurrentPage('add_medicine.php') ? 'bg-gradient-to-r from-primary-600/20 to-primary-500/20 text-white border-l-4 border-primary-500' : 'text-gray-300 hover:bg-primary-800/20 hover:text-white border-l-4 border-transparent' ?> block rounded-r-lg px-4 py-3 text-base font-medium transition-all">
                Add Medicine
            </a>
            <a href="<?= URL_MANAGE_CATEGORIES ?>"
               class="<?= isCurrentPage('manage_categories.php') ? 'bg-gradient-to-r from-primary-600/20 to-primary-500/20 text-white border-l-4 border-primary-500' : 'text-gray-300 hover:bg-primary-800/20 hover:text-white border-l-4 border-transparent' ?> block rounded-r-lg px-4 py-3 text-base font-medium transition-all">
                Categories
            </a>
            <a href="<?= URL_PROFILE ?>"
               class="<?= isCurrentPage('profile.php') ? 'bg-gradient-to-r from-primary-600/20 to-primary-500/20 text-white border-l-4 border-primary-500' : 'text-gray-300 hover:bg-primary-800/20 hover:text-white border-l-4 border-transparent' ?> block rounded-r-lg px-4 py-3 text-base font-medium transition-all">
                Profile
            </a>
        </div>
        <div class="border-t border-primary-700/30 pt-4 pb-3">
            <div class="flex items-center px-5 mb-3">
                <img class="size-10 rounded-xl object-cover ring-2 ring-primary-600/30"
                     src="<?= htmlspecialchars($profilePicUrl) ?>"
                     alt="<?= htmlspecialchars($user_name) ?>">
                <div class="ml-3">
                    <div class="text-base font-semibold text-white"><?= htmlspecialchars($user_name) ?></div>
                    <div class="text-sm text-primary-400">
                        @<?= htmlspecialchars($_SESSION['username'] ?? 'user') ?></div>
                </div>
            </div>
            <div class="px-3 space-y-1">
                <a href="<?= URL_CHANGE_PASSWORD ?>"
                   class="block rounded-lg px-4 py-3 text-base font-medium text-gray-300 hover:bg-primary-800/20 hover:text-white transition-all">
                    Change Password
                </a>
                <a href="<?= URL_LOGOUT ?>"
                   class="block rounded-lg px-4 py-3 text-base font-medium text-red-400 hover:bg-red-900/20 hover:text-red-300 transition-all">
                    Sign out
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- Page Header Section -->
<header class="relative bg-gradient-to-br from-primary-900 via-gray-900 to-gray-800 overflow-hidden">
    <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZGVmcz48cGF0dGVybiBpZD0iZ3JpZCIgd2lkdGg9IjQwIiBoZWlnaHQ9IjQwIiBwYXR0ZXJuVW5pdHM9InVzZXJTcGFjZU9uVXNlIj48cGF0aCBkPSJNIDQwIDAgTCAwIDAgMCA0MCIgZmlsbD0ibm9uZSIgc3Ryb2tlPSJ3aGl0ZSIgc3Ryb2tlLW9wYWNpdHk9IjAuMDUiIHN0cm9rZS13aWR0aD0iMSIvPjwvcGF0dGVybj48L2RlZnM+PHJlY3Qgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgZmlsbD0idXJsKCNncmlkKSIvPjwvc3ZnPg==')] opacity-30"></div>
    <div class="absolute top-0 right-0 w-96 h-96 bg-primary-500/10 rounded-full blur-3xl"></div>
    <div class="absolute bottom-0 left-0 w-96 h-96 bg-accent-500/10 rounded-full blur-3xl"></div>

    <div class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-4xl font-bold tracking-tight bg-gradient-to-r from-white via-primary-100 to-white bg-clip-text text-transparent">
                    <?= htmlspecialchars($pageTitle) ?>
                </h1>
                <div class="mt-2 flex items-center space-x-2">
                    <div class="h-1 w-12 bg-gradient-to-r from-primary-500 to-accent-500 rounded-full"></div>
                    <p class="text-sm text-gray-400">Manage your inventory efficiently</p>
                </div>
            </div>
            <div class="hidden lg:flex items-center space-x-3">
                <div class="px-4 py-2 bg-primary-800/30 rounded-xl backdrop-blur-sm border border-primary-700/30">
                    <p class="text-xs text-gray-400">Last updated</p>
                    <p class="text-sm font-semibold text-white"><?= date('M d, Y') ?></p>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const profileBtn = document.getElementById('profileBtn');
        const profileMenu = document.getElementById('profileMenu');
        const profileChevron = document.getElementById('profileChevron');

        if (profileBtn && profileMenu) {
            profileBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                profileMenu.classList.toggle('hidden');
                profileChevron?.classList.toggle('rotate-180');
            });

            document.addEventListener('click', (e) => {
                if (!profileBtn.contains(e.target) && !profileMenu.contains(e.target)) {
                    profileMenu.classList.add('hidden');
                    profileChevron?.classList.remove('rotate-180');
                }
            });
        }

        const notificationBtn = document.getElementById('notificationBtn');
        const notificationMenu = document.getElementById('notificationMenu');

        if (notificationBtn && notificationMenu) {
            notificationBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                notificationMenu.classList.toggle('hidden');
            });

            document.addEventListener('click', (e) => {
                if (!notificationBtn.contains(e.target) && !notificationMenu.contains(e.target)) {
                    notificationMenu.classList.add('hidden');
                }
            });
        }

        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileMenu = document.getElementById('mobileMenu');
        const menuIconOpen = document.getElementById('menuIconOpen');
        const menuIconClose = document.getElementById('menuIconClose');

        if (mobileMenuBtn && mobileMenu) {
            mobileMenuBtn.addEventListener('click', () => {
                mobileMenu.classList.toggle('hidden');
                menuIconOpen.classList.toggle('hidden');
                menuIconClose.classList.toggle('hidden');
            });
        }
    });
</script>