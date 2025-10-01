<?php
global $pdo;
require_once __DIR__ . '/../config/auth.php';

$user_name = $_SESSION['name'] ?? 'User';
$profile_pic = $_SESSION['profile_pic'] ?? null;
$current_page = basename($_SERVER['PHP_SELF']);

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
?>

<nav class="bg-gray-800/50 dark:bg-gray-800/80 relative backdrop-blur-sm border-b border-gray-700 dark:border-gray-600 z-50">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
            <!-- Logo & Brand -->
            <div class="flex items-center">
                <div class="shrink-0">
                    <a href="../public/index.php"
                       class="flex items-center space-x-3 group">
                        <div class="relative">
                            <img src="https://tailwindcss.com/plus-assets/img/logos/mark.svg?color=indigo&shade=500"
                                 alt="Logo"
                                 class="size-8 transition-transform group-hover:scale-110">
                            <div class="absolute -inset-1 bg-indigo-500 rounded-full blur opacity-0 group-hover:opacity-30 transition-opacity"></div>
                        </div>
                        <span class="text-white dark:text-gray-100 font-bold text-xl hidden sm:block tracking-tight">
                            Medi<span class="text-indigo-400 dark:text-indigo-300">Track</span>
                        </span>
                    </a>
                </div>

                <!-- Desktop Menu Links -->
                <div class="hidden md:block ml-10">
                    <div class="flex items-baseline space-x-2">
                        <a href="../public/index.php"
                           class="<?= $current_page === 'index.php' ? 'bg-gray-900 dark:bg-gray-700 text-white' : 'text-gray-300 dark:text-gray-400 hover:bg-gray-700 dark:hover:bg-gray-600 hover:text-white' ?> rounded-lg px-3 py-2 text-sm font-medium transition-all duration-200">
                            <div class="flex items-center space-x-2">
                                <svg class="h-4 w-4"
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
                        </a>

                        <a href="../public/list_medicines.php"
                           class="<?= $current_page === 'list_medicines.php' ? 'bg-gray-900 dark:bg-gray-700 text-white' : 'text-gray-300 dark:text-gray-400 hover:bg-gray-700 dark:hover:bg-gray-600 hover:text-white' ?> rounded-lg px-3 py-2 text-sm font-medium transition-all duration-200">
                            <div class="flex items-center space-x-2">
                                <svg class="h-4 w-4"
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
                        </a>

                        <a href="../public/add_medicine.php"
                           class="<?= $current_page === 'add_medicine.php' ? 'bg-gray-900 dark:bg-gray-700 text-white' : 'text-gray-300 dark:text-gray-400 hover:bg-gray-700 dark:hover:bg-gray-600 hover:text-white' ?> rounded-lg px-3 py-2 text-sm font-medium transition-all duration-200">
                            <div class="flex items-center space-x-2">
                                <svg class="h-4 w-4"
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
                        </a>

                        <a href="../public/manage_categories.php"
                           class="<?= $current_page === 'manage_categories.php' ? 'bg-gray-900 dark:bg-gray-700 text-white' : 'text-gray-300 dark:text-gray-400 hover:bg-gray-700 dark:hover:bg-gray-600 hover:text-white' ?> rounded-lg px-3 py-2 text-sm font-medium transition-all duration-200">
                            <div class="flex items-center space-x-2">
                                <svg class="h-4 w-4"
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
                        </a>

                        <a href="../public/profile.php"
                           class="<?= $current_page === 'profile.php' ? 'bg-gray-900 dark:bg-gray-700 text-white' : 'text-gray-300 dark:text-gray-400 hover:bg-gray-700 dark:hover:bg-gray-600 hover:text-white' ?> rounded-lg px-3 py-2 text-sm font-medium transition-all duration-200">
                            <div class="flex items-center space-x-2">
                                <svg class="h-4 w-4"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span>Profile</span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Right side: Notifications & Profile -->
            <div class="hidden md:flex md:items-center md:space-x-4">

                <!-- Notifications -->
                <div class="relative">
                    <button id="notificationBtn"
                            type="button"
                            class="relative p-2 rounded-lg text-gray-400 dark:text-gray-500 hover:text-white dark:hover:text-gray-200 hover:bg-gray-700 dark:hover:bg-gray-600 transition-all duration-200">
                        <svg class="h-6 w-6"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <?php if ($totalNotifications > 0): ?>
                            <span class="absolute top-1 right-1 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white bg-red-500 dark:bg-red-600 rounded-full">
                                <?= $totalNotifications ?>
                            </span>
                        <?php endif; ?>
                    </button>

                    <!-- Notifications Dropdown -->
                    <div id="notificationMenu"
                         class="hidden absolute right-0 mt-3 w-80 origin-top-right rounded-lg bg-gray-800 dark:bg-gray-700 shadow-xl ring-1 ring-white/10 dark:ring-white/20 z-[100]">
                        <div class="p-4 border-b border-gray-700 dark:border-gray-600">
                            <h3 class="text-sm font-semibold text-white dark:text-gray-100">Notifications</h3>
                        </div>
                        <div class="max-h-96 overflow-y-auto">
                            <?php if ($totalNotifications > 0): ?>
                                <?php if ($lowStockCount > 0): ?>
                                    <a href="../public/list_medicines.php"
                                       class="block px-4 py-3 hover:bg-gray-700 dark:hover:bg-gray-600 transition">
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0">
                                                <svg class="h-6 w-6 text-yellow-500 dark:text-yellow-400"
                                                     fill="none"
                                                     stroke="currentColor"
                                                     viewBox="0 0 24 24">
                                                    <path stroke-linecap="round"
                                                          stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                </svg>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-white dark:text-gray-100">Low Stock
                                                    Alert</p>
                                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1"><?= $lowStockCount ?>
                                                    medicine(s) running low on stock</p>
                                            </div>
                                        </div>
                                    </a>
                                <?php endif; ?>

                                <?php if ($expiringCount > 0): ?>
                                    <a href="../public/list_medicines.php"
                                       class="block px-4 py-3 hover:bg-gray-700 dark:hover:bg-gray-600 transition border-t border-gray-700 dark:border-gray-600">
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0">
                                                <svg class="h-6 w-6 text-red-500 dark:text-red-400"
                                                     fill="none"
                                                     stroke="currentColor"
                                                     viewBox="0 0 24 24">
                                                    <path stroke-linecap="round"
                                                          stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-white dark:text-gray-100">Expiry
                                                    Alert</p>
                                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1"><?= $expiringCount ?>
                                                    medicine(s) expiring within 30 days</p>
                                            </div>
                                        </div>
                                    </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="px-4 py-8 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-600 dark:text-gray-500"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                    </svg>
                                    <p class="mt-2 text-sm text-gray-400 dark:text-gray-500">No notifications</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Profile Dropdown -->
                <div class="relative">
                    <button id="profileBtn"
                            type="button"
                            class="flex items-center space-x-3 rounded-lg p-1.5 hover:bg-gray-700 dark:hover:bg-gray-600 transition-all duration-200 focus:outline-2 focus:outline-offset-2 focus:outline-indigo-500">
                        <img class="size-9 rounded-full object-cover ring-2 ring-gray-700 dark:ring-gray-600 hover:ring-indigo-500 transition-all"
                             src="<?= $profile_pic ? '../uploads/' . htmlspecialchars($profile_pic) : 'https://ui-avatars.com/api/?name=' . urlencode($user_name) . '&background=6366f1&color=fff&size=64' ?>"
                             alt="<?= htmlspecialchars($user_name) ?>">
                        <span class="text-white dark:text-gray-100 font-medium text-sm hidden lg:block"><?= htmlspecialchars($user_name) ?></span>
                        <svg class="h-5 w-5 text-gray-400 dark:text-gray-500 hidden lg:block transition-transform"
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
                         class="hidden absolute right-0 top-full mt-3 w-64 origin-top-right rounded-lg bg-gray-800 dark:bg-gray-700 shadow-xl ring-1 ring-white/10 dark:ring-white/20 z-[100] overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-700 dark:border-gray-600 bg-gray-750">
                            <p class="text-xs text-gray-400 dark:text-gray-500">Signed in as</p>
                            <p class="text-sm font-medium text-white dark:text-gray-100 truncate mt-1"><?= htmlspecialchars($user_name) ?></p>
                            <p class="text-xs text-gray-500 dark:text-gray-600 truncate">
                                @<?= htmlspecialchars($_SESSION['username'] ?? 'user') ?></p>
                        </div>
                        <div class="py-1">
                            <a href="../public/profile.php"
                               class="group flex items-center px-4 py-2.5 text-sm text-gray-300 dark:text-gray-400 hover:bg-gray-700 dark:hover:bg-gray-600 hover:text-white transition-all">
                                <svg class="mr-3 h-5 w-5 text-gray-400 dark:text-gray-500 group-hover:text-indigo-400 transition-colors"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                Your Profile
                            </a>
                            <a href="../public/change_pw.php"
                               class="group flex items-center px-4 py-2.5 text-sm text-gray-300 dark:text-gray-400 hover:bg-gray-700 dark:hover:bg-gray-600 hover:text-white transition-all">
                                <svg class="mr-3 h-5 w-5 text-gray-400 dark:text-gray-500 group-hover:text-indigo-400 transition-colors"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                </svg>
                                Change Password
                            </a>
                        </div>
                        <div class="border-t border-gray-700 dark:border-gray-600">
                            <a href="../public/logout.php"
                               class="group flex items-center px-4 py-2.5 text-sm text-red-400 dark:text-red-300 hover:bg-red-900/20 hover:text-red-300 transition-all">
                                <svg class="mr-3 h-5 w-5 group-hover:translate-x-0.5 transition-transform"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Sign out
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile menu button -->
            <div class="md:hidden">
                <button id="mobileMenuBtn"
                        type="button"
                        class="inline-flex items-center justify-center p-2 rounded-lg text-gray-400 dark:text-gray-500 hover:text-white dark:hover:text-gray-200 hover:bg-gray-700 dark:hover:bg-gray-600 focus:outline-2 focus:outline-indigo-500 transition-all">
                    <svg class="h-6 w-6"
                         id="menuIconOpen"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg class="h-6 w-6 hidden"
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
         class="hidden md:hidden bg-gray-300 dark:bg-gray-900 border-t border-gray-500 dark:border-gray-800">
        <div class="px-2 pt-2 pb-3 space-y-1">
            <a href="../public/index.php"
               class="<?= $current_page === 'index.php' ? 'bg-gray-900 dark:bg-gray-600 text-white' : 'text-gray-300 dark:text-gray-400 hover:bg-gray-700 dark:hover:bg-gray-600 hover:text-white' ?> block rounded-lg px-3 py-2 text-base font-medium transition-all">
                Dashboard
            </a>
            <a href="../public/list_medicines.php"
               class="<?= $current_page === 'list_medicines.php' ? 'bg-gray-900 dark:bg-gray-600 text-white' : 'text-gray-300 dark:text-gray-400 hover:bg-gray-700 dark:hover:bg-gray-600 hover:text-white' ?> block rounded-lg px-3 py-2 text-base font-medium transition-all">
                Medicines
            </a>
            <a href="../public/add_medicine.php"
               class="<?= $current_page === 'add_medicine.php' ? 'bg-gray-900 dark:bg-gray-600 text-white' : 'text-gray-300 dark:text-gray-400 hover:bg-gray-700 dark:hover:bg-gray-600 hover:text-white' ?> block rounded-lg px-3 py-2 text-base font-medium transition-all">
                Add Medicine
            </a>
            <a href="../public/manage_categories.php"
               class="<?= $current_page === 'manage_categories.php' ? 'bg-gray-900 dark:bg-gray-600 text-white' : 'text-gray-300 dark:text-gray-400 hover:bg-gray-700 dark:hover:bg-gray-600 hover:text-white' ?> block rounded-lg px-3 py-2 text-base font-medium transition-all">
                Categories
            </a>
            <a href="../public/profile.php"
               class="<?= $current_page === 'profile.php' ? 'bg-gray-900 dark:bg-gray-600 text-white' : 'text-gray-300 dark:text-gray-400 hover:bg-gray-700 dark:hover:bg-gray-600 hover:text-white' ?> block rounded-lg px-3 py-2 text-base font-medium transition-all">
                Profile
            </a>
        </div>
        <div class="border-t border-gray-700 dark:border-gray-800 pt-4 pb-3">
            <div class="flex items-center px-5">
                <img class="w-10 h-10 rounded-full object-cover ring-2 ring-gray-700 dark:ring-gray-600"
                     src="<?= $profile_pic ? '../uploads/' . htmlspecialchars($profile_pic) : 'https://ui-avatars.com/api/?name=' . urlencode($user_name) . '&background=6366f1&color=fff&size=64' ?>"
                     alt="<?= htmlspecialchars($user_name) ?>">
                <div class="ml-3">
                    <div class="text-base font-medium text-white dark:text-gray-100">
                        <?= htmlspecialchars($user_name) ?>
                    </div>
                    <div class="text-sm text-gray-400 dark:text-gray-500">
                        @<?= htmlspecialchars($_SESSION['username'] ?? 'user') ?>
                    </div>
                </div>
            </div>
            <div class="mt-3 px-2 space-y-1">
                <a href="../public/change_pw.php"
                   class="block rounded-lg px-3 py-2 text-base font-medium text-gray-300 dark:text-gray-400 hover:bg-gray-700 dark:hover:bg-gray-600 hover:text-white transition-all">
                    Change Password
                </a>
                <a href="../public/logout.php"
                   class="block rounded-lg px-3 py-2 text-base font-medium text-red-400 dark:text-red-300 hover:bg-red-900/20 hover:text-red-300 transition-all">
                    Sign out
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- Page Header Section -->
<header class="relative bg-gray-800 dark:bg-gray-700 after:pointer-events-none after:absolute after:inset-x-0 after:inset-y-0 after:border-y after:border-white/10 z-10">
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold tracking-tight text-white dark:text-gray-100">
            <?= htmlspecialchars($pageTitle) ?>
        </h1>
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