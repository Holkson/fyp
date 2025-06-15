<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

// Get kampung ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: admin_kampung.php');
    exit();
}

$kampung_id = $_GET['id'];

// Fetch kampung details
$kampung = $conn->query("SELECT * FROM kampung_groups WHERE id = $kampung_id")->fetch_assoc();

// Fetch assigned volunteers
$volunteers = $conn->query("
    SELECT u.id, u.name, u.email, va.is_leader
    FROM volunteer_assignments va
    JOIN users u ON va.volunteer_id = u.id
    WHERE va.kampung_id = $kampung_id
    ORDER BY va.is_leader DESC, u.name
")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kampung Volunteers - One Heart Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex">
        <?php include 'admin_sidebar.php'; ?>

        <div class="flex-1 ml-64">
            <header class="bg-white shadow-sm p-4">
                <h2 class="text-xl font-semibold text-gray-800">Volunteers for <?= htmlspecialchars($kampung['name']) ?></h2>
            </header>

            <main class="p-6">
                <div class="space-y-6">
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($volunteers as $volunteer): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                    <span class="text-blue-600 font-medium">
                                                        <?= strtoupper(substr($volunteer['name'], 0, 1)) ?>
                                                    </span>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        <?= htmlspecialchars($volunteer['name']) ?>
                                                        <?php if ($volunteer['is_leader']): ?>
                                                            <span class="ml-2 text-xs text-yellow-600">(Leader)</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= htmlspecialchars($volunteer['email']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if ($volunteer['is_leader']): ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    Group Leader
                                                </span>
                                            <?php else: ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                    Volunteer
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <a href="admin_view_user.php?id=<?= $volunteer['id'] ?>" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                                            <a href="#" class="text-red-600 hover:text-red-900">Remove</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($volunteers)): ?>
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                                            No volunteers assigned to this kampung yet
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <a href="admin_assign_volunteers.php?kampung=<?= $kampung_id ?>" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                            <i class="fas fa-user-plus mr-2"></i> Assign More Volunteers
                        </a>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>