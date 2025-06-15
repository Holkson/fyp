<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

// Handle status filter
$status_filter = '';
if (isset($_GET['status']) && in_array($_GET['status'], ['pending', 'verified', 'assisted'])) {
    $status_filter = "WHERE status = '" . $conn->real_escape_string($_GET['status']) . "'";
}

// Fetch asnaf with optional filter
$asnaf = $conn->query("SELECT * FROM asnaf $status_filter ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asnaf Management - One Heart Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex">
        <!-- Sidebar (same as dashboard) -->
        <?php include 'admin_sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 ml-64">
            <header class="bg-white shadow-sm p-4">
                <h2 class="text-xl font-semibold text-gray-800">Asnaf Management</h2>
            </header>

            <main class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <div class="flex space-x-2">
                        <a href="admin_asnaf.php" class="px-4 py-2 rounded-md <?= !isset($_GET['status']) ? 'bg-blue-100 text-blue-800' : 'bg-white' ?>">All</a>
                        <a href="admin_asnaf.php?status=pending" class="px-4 py-2 rounded-md <?= isset($_GET['status']) && $_GET['status'] == 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-white' ?>">Pending</a>
                        <a href="admin_asnaf.php?status=verified" class="px-4 py-2 rounded-md <?= isset($_GET['status']) && $_GET['status'] == 'verified' ? 'bg-green-100 text-green-800' : 'bg-white' ?>">Verified</a>
                        <a href="admin_asnaf.php?status=assisted" class="px-4 py-2 rounded-md <?= isset($_GET['status']) && $_GET['status'] == 'assisted' ? 'bg-purple-100 text-purple-800' : 'bg-white' ?>">Assisted</a>
                    </div>
                    <a href="admin_add_asnaf.php" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                        <i class="fas fa-plus mr-2"></i> Add Asnaf
                    </a>
                </div>

                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IC Number</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kampung</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($asnaf as $a): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($a['name']) ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= htmlspecialchars($a['ic']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= htmlspecialchars($a['phone']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= htmlspecialchars($a['kampung'] ?? 'N/A') ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?= $a['status'] == 'verified' ? 'bg-green-100 text-green-800' : 
                                               ($a['status'] == 'assisted' ? 'bg-purple-100 text-purple-800' : 'bg-yellow-100 text-yellow-800') ?>">
                                            <?= ucfirst($a['status']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <a href="admin_edit_asnaf.php?id=<?= $a['id'] ?>" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                                        <a href="admin_view_asnaf.php?id=<?= $a['id'] ?>" class="text-green-600 hover:text-green-900 mr-3">View</a>
                                        <a href="#" class="text-red-600 hover:text-red-900">Delete</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>