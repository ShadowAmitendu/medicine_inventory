<?php
http_response_code(404);
require_once 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="en"
      class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found | MediTrack</title>
    <link href="../assets/output.css"
          rel="stylesheet">
</head>
<body class="h-full bg-gray-900">

<main class="grid min-h-screen place-items-center px-6 py-24 sm:py-32 lg:px-8">
    <div class="text-center">
        <!-- Animated Icon -->
        <div class="flex justify-center mb-8">
            <div class="relative">
                <svg class="h-32 w-32 text-indigo-500 animate-pulse"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="1.5"
                          d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                    <span class="text-4xl">😵</span>
                </div>
            </div>
        </div>

        <!-- Error Code -->
        <div class="mb-4">
            <span class="inline-block bg-indigo-500/10 text-indigo-400 px-4 py-2 rounded-full text-sm font-semibold tracking-wider border border-indigo-500/20">
                ERROR 404
            </span>
        </div>

        <!-- Main Heading -->
        <h1 class="mt-4 text-4xl font-bold tracking-tight text-white sm:text-6xl bg-gradient-to-r from-white to-gray-400 bg-clip-text">
            Page Not Found
        </h1>

        <!-- Description -->
        <p class="mt-6 text-lg text-gray-400 max-w-2xl mx-auto leading-relaxed">
            Oops! Looks like this medicine is out of stock. The page you're looking for doesn't exist or has been moved.
        </p>

        <!-- Suggestions Box -->
        <div class="mt-10 bg-gray-800 border border-gray-700 rounded-lg p-6 max-w-md mx-auto">
            <h2 class="text-white font-semibold mb-4 text-sm uppercase tracking-wider">Quick Suggestions</h2>
            <ul class="space-y-3 text-left">
                <li class="flex items-start text-gray-400 text-sm">
                    <svg class="h-5 w-5 text-indigo-400 mr-2 flex-shrink-0 mt-0.5"
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
                <li class="flex items-start text-gray-400 text-sm">
                    <svg class="h-5 w-5 text-indigo-400 mr-2 flex-shrink-0 mt-0.5"
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
                <li class="flex items-start text-gray-400 text-sm">
                    <svg class="h-5 w-5 text-indigo-400 mr-2 flex-shrink-0 mt-0.5"
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
        <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="index.php"
               class="inline-flex items-center rounded-lg bg-indigo-500 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-500/50 hover:bg-indigo-400 transition-all transform hover:-translate-y-0.5">
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
               class="inline-flex items-center rounded-lg bg-gray-800 border border-gray-700 px-6 py-3 text-sm font-semibold text-white hover:bg-gray-700 transition-all">
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

            <a href="javascript:history.back()"
               class="text-sm font-semibold text-gray-400 hover:text-white transition flex items-center">
                Go Back
                <svg class="h-4 w-4 ml-1"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
        </div>

        <!-- Support Link -->
        <div class="mt-12 pt-8 border-t border-gray-800">
            <p class="text-sm text-gray-500">
                Still having trouble?
                <a href="mailto:support@meditrack.com"
                   class="text-indigo-400 hover:text-indigo-300 font-medium ml-1">
                    Contact Support
                </a>
            </p>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>

</body>
</html>