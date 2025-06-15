<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_kampung'])) {
        $name = $conn->real_escape_string(trim($_POST['name']));
        $description = $conn->real_escape_string(trim($_POST['description'] ?? ''));
        
        $stmt = $conn->prepare("INSERT INTO kampung_groups (name, description) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $description);
        $stmt->execute();
    } elseif (isset($_POST['delete_kampung'])) {
        $id = (int)$_POST['kampung_id'];
        $conn->query("DELETE FROM kampung_groups WHERE id = $id");
    }
}

// Fetch all kampung groups
$kampungs = $conn->query("SELECT * FROM kampung_groups ORDER BY name")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Kampung Groups - One Heart Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex">
        <?php include 'admin_sidebar.php'; ?>

        <div class="flex-1 ml-64">
            <header class="bg-white shadow-sm p-4">
                <h2 class="text-xl font-semibold text-gray-800">Manage Kampung Groups</h2>
            </header>

            <main class="p-6">
                <div class="space-y-6">
                    <!-- Add Kampung Form -->
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="p-6">
                            <h3 class="text-lg font-medium mb-4">Add New Kampung Group</h3>
                            <form method="POST">
                                <input type="hidden" name="add_kampung" value="1">
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                    <div class="sm:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700">Kampung Name *</label>
                                        <input type="text" name="name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Description</label>
                                        <input type="text" name="description" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                                        Add Kampung
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Kampung Groups List -->
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium">All Kampung Groups</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($kampungs as $kampung): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($kampung['name']) ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= htmlspecialchars($kampung['description'] ?? 'N/A') ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <a href="admin_kampung_volunteers.php?id=<?= $kampung['id'] ?>" class="text-blue-600 hover:text-blue-900 mr-3">View Volunteers</a>
                                            <form method="POST" class="inline">
                                                <input type="hidden" name="delete_kampung" value="1">
                                                <input type="hidden" name="kampung_id" value="<?= $kampung['id'] ?>">
                                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>