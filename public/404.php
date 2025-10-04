<?php
require_once '../config/auth.php';
require_once '../config/db.php';
require_once '../config/paths.php';
http_response_code(404);
?>

<!DOCTYPE html>
<html lang="en"
      class="bg-gradient-to-br from-primary-900 via-gray-900 to-gray-800">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found | MediTrack</title>
    <link href="<?= assetUrl('output.css') ?>"
          rel="stylesheet">
    <style>
        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-20px);
            }
        }

        .float-animation {
            animation: float 6s ease-in-out infinite;
        }

        @keyframes pulse-glow {
            0%, 100% {
                opacity: 0.5;
            }
            50% {
                opacity: 1;
            }
        }

        .pulse-glow {
            animation: pulse-glow 3s ease-in-out infinite;
        }
    </style>
</head>
<body class="">

<!-- Background decorative elements -->
<div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZGVmcz48cGF0dGVybiBpZD0iZ3JpZCIgd2lkdGg9IjQwIiBoZWlnaHQ9IjQwIiBwYXR0ZXJuVW5pdHM9InVzZXJTcGFjZU9uVXNlIj48cGF0aCBkPSJNIDQwIDAgTCAwIDAgMCA0MCIgZmlsbD0ibm9uZSIgc3Ryb2tlPSJ3aGl0ZSIgc3Ryb2tlLW9wYWNpdHk9IjAuMDMiIHN0cm9rZS13aWR0aD0iMSIvPjwvcGF0dGVybj48L2RlZnM+PHJlY3Qgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgZmlsbD0idXJsKCNncmlkKSIvPjwvc3ZnPg==')] opacity-40"></div>
<div class="absolute top-20 left-20 w-72 h-72 bg-primary-600/20 rounded-full blur-3xl float-animation"></div>
<div class="absolute bottom-20 right-20 w-96 h-96 bg-accent-600/15 rounded-full blur-3xl float-animation"
     style="animation-delay: 2s;"></div>

<main class="relative grid min-h-screen place-items-center px-6 py-24 sm:py-32 lg:px-8">
    <div class="text-center max-w-3xl mx-auto">
        <!-- Animated Icon -->
        <div class="flex justify-center mb-8">
            <div class="relative group">
                <div class="absolute -inset-4 bg-gradient-to-r from-red-500/30 to-accent-500/30 rounded-full blur-2xl pulse-glow"></div>
                <div class="relative size-32 bg-gradient-to-br from-gray-800 to-gray-900 rounded-full flex items-center justify-center border-4 border-gray-700/50 shadow-2xl">
                    <svg class="size-16 text-red-400"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="1.5"
                              d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Error Code -->
        <div class="mb-6">
            <span class="inline-block bg-red-500/10 text-red-400 px-6 py-2.5 rounded-full text-sm font-bold tracking-wider border border-red-500/30 backdrop-blur-sm shadow-lg">
                ERROR 404
            </span>
        </div>

        <!-- Main Heading -->
        <h1 class="mt-4 text-5xl font-bold tracking-tight sm:text-7xl bg-gradient-to-r from-white via-primary-100 to-white bg-clip-text text-transparent mb-6">
            Page Not Found
        </h1>

        <!-- Description -->
        <p class="text-lg text-gray-400 max-w-2xl mx-auto leading-relaxed mb-10">
            Oops! Looks like this medicine is out of stock. The page you're looking for doesn't exist or has been moved
            to another shelf.
        </p>

        <!-- Suggestions Box -->
        <div class="mb-10 bg-gray-800/50 backdrop-blur-xl border border-gray-700/50 rounded-2xl p-6 max-w-md mx-auto shadow-2xl">
            <h2 class="text-white font-semibold mb-4 text-sm uppercase tracking-wider flex items-center justify-center">
                <svg class="h-5 w-5 text-primary-400 mr-2"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
                Quick Suggestions
            </h2>
            <ul class="space-y-3 text-left">
                <li class="flex items-start text-gray-300 text-sm group">
                    <svg class="h-5 w-5 text-primary-400 mr-3 flex-shrink-0 mt-0.5 group-hover:scale-110 transition-transform"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Check the URL for typos
                </li>
                <li class="flex items-start text-gray-300 text-sm group">
                    <svg class="h-5 w-5 text-primary-400 mr-3 flex-shrink-0 mt-0.5 group-hover:scale-110 transition-transform"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Return to the dashboard
                </li>
                <li class="flex items-start text-gray-300 text-sm group">
                    <svg class="h-5 w-5 text-primary-400 mr-3 flex-shrink-0 mt-0.5 group-hover:scale-110 transition-transform"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Contact support if the problem persists
                </li>
            </ul>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-8">
            <a href="../index.php"
               class="inline-flex items-center rounded-xl bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 px-6 py-3.5 text-sm font-semibold text-white shadow-lg shadow-primary-500/30 hover:shadow-xl transition-all transform hover:-translate-y-0.5">
                <svg class="h-5 w-5 mr-2"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Back to Dashboard
            </a>

            <a href="list_medicines.php"
               class="inline-flex items-center rounded-xl bg-gray-700/50 hover:bg-gray-700 border border-gray-600 hover:border-gray-500 px-6 py-3.5 text-sm font-semibold text-gray-200 hover:text-white transition-all">
                <svg class="h-5 w-5 mr-2"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                View Medicines
            </a>
        </div>

        <div class="flex justify-center">
            <a href="javascript:history.back()"
               class="text-sm font-semibold text-gray-400 hover:text-primary-400 transition flex items-center group">
                <svg class="h-4 w-4 mr-2 group-hover:-translate-x-1 transition-transform"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Go Back
            </a>
        </div>

        <!-- Support Link -->
        <div class="mt-12 pt-8 border-t border-gray-700/50">
            <p class="text-sm text-gray-500">
                Still having trouble?
                <a href="mailto:support@meditrack.com"
                   class="text-primary-400 hover:text-primary-300 font-medium ml-1 transition-colors">
                    Contact Support
                </a>
            </p>
        </div>
    </div>
</main>

</body>
</html>