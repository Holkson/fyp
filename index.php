<?php
session_start();
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>One Heart Team Management</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gray-50">
    <?php include 'includes/header.php'; ?>

    <main class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
        <div class="text-center">
            <h1 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-6xl">
                One Heart Team Management
            </h1>
            <p class="mt-6 text-lg leading-8 text-gray-600">
                Empowering communities through efficient management and coordination of humanitarian efforts.
            </p>
            <div class="mt-10 flex items-center justify-center gap-x-6">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php" class="rounded-md bg-blue-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-500">
                        Go to Dashboard
                    </a>
                <?php else: ?>
                    <a href="register.php" class="rounded-md bg-blue-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-500">
                        Get started
                    </a>
                <?php endif; ?>
                <a href="about.php" class="text-sm font-semibold leading-6 text-gray-900">
                    Learn more <span aria-hidden="true">→</span>
                </a>
            </div>
        </div>
    </main>

    <script src="assets/js/main.js"></script>
</body>
</html>