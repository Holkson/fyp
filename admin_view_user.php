<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: login.php');
    exit();
}

// Get user ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: admin_users.php');
    exit();
}

$user_id = $_GET['id'];

// Fetch user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: admin_users.php');
    exit();
}

$user = $result->fetch_assoc();

// Fetch asnaf records if this is a volunteer
$asnaf_records = [];
if ($user['role'] == 'volunteer') {
    $asnaf_records = $conn->query("
        SELECT * FROM asnaf WHERE user_id = $user_id ORDER BY created_at DESC
    ")->fetch_all(MYSQLI_ASSOC);
}

// Fetch assigned tasks if this is a volunteer
$assigned_tasks = [];
if ($user['role'] == 'volunteer') {
    $assigned_tasks = $conn->query("
        SELECT t.*, kg.name as kampung_name
        FROM tasks t
        JOIN kampung_groups kg ON t.kampung_id = kg.id
        WHERE t.assigned_to = $user_id
        ORDER BY t.due_date DESC
    ")->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View User - One Heart Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex">
        <?php include 'admin_sidebar.php'; ?>

        <div class="flex-1 ml-64">
            <header class="bg-white shadow-sm p-4">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-semibold text-gray-800">User Profile</h2>
                    <div class="flex space-x-2">
                        <a href="admin_users.php" class="rounded-md border border-gray-300 px-3 py-1 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Back to Users
                        </a>
                    </div>
                </div>
            </header>

            <main class="p-6">
                <div class="space-y-6">
                    <!-- User Profile Card -->
                    <div class="bg-white shadow-md rounded-lg overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0 h-16 w-16 rounded-full bg-blue-100 flex items-center justify-center">
                                    <span class="text-blue-600 text-xl font-medium">
                                        <?= strtoupper(substr($user['name'], 0, 1)) ?>
                                    </span>
                                </div>
                                <div>
                                    <h1 class="text-2xl font-bold"><?= htmlspecialchars($user['name']) ?></h1>
                                    <div class="flex items-center space-x-4 mt-1">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?= $user['status'] == 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                            <?= ucfirst($user['status']) ?>
                                        </span>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            <?= ucfirst($user['role']) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <h2 class="text-lg font-medium text-gray-900">Contact Information</h2>
                                    <div class="mt-4 space-y-4">
                                        <div>
                                            <p class="text-sm font-medium text-gray-500">Email</p>
                                            <p class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($user['email']) ?></p>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-500">Phone</p>
                                            <p class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($user['phone'] ?? 'N/A') ?></p>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-500">IC Number</p>
                                            <p class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($user['ic'] ?? 'N/A') ?></p>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <h2 class="text-lg font-medium text-gray-900">Account Information</h2>
                                    <div class="mt-4 space-y-4">
                                        <div>
                                            <p class="text-sm font-medium text-gray-500">Member Since</p>
                                            <p class="mt-1 text-sm text-gray-900"><?= date('M j, Y', strtotime($user['created_at'])) ?></p>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-500">Last Updated</p>
                                            <p class="mt-1 text-sm text-gray-900"><?= date('M j, Y', strtotime($user['updated_at'])) ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Volunteer Specific Sections -->
                    <?php if ($user['role'] == 'volunteer'): ?>
                        <!-- Asnaf Records Section -->
                        <div class="bg-white shadow-md rounded-lg overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-medium">Asnaf Records</h3>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IC Number</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date Added</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php foreach ($asnaf_records as $asnaf): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($asnaf['name']) ?></div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?= htmlspecialchars($asnaf['ic']) ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    <?= $asnaf['status'] == 'verified' ? 'bg-green-100 text-green-800' : 
                                                       ($asnaf['status'] == 'assisted' ? 'bg-purple-100 text-purple-800' : 'bg-yellow-100 text-yellow-800') ?>">
                                                    <?= ucfirst($asnaf['status']) ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?= date('M j, Y', strtotime($asnaf['created_at'])) ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <a href="admin_view_asnaf.php?id=<?= $asnaf['id'] ?>" class="text-blue-600 hover:text-blue-900">View</a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($asnaf_records)): ?>
                                        <tr>
                                            <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                                No asnaf records found
                                            </td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Assigned Tasks Section -->
                        <div class="bg-white shadow-md rounded-lg overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-medium">Assigned Tasks</h3>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Task Title</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kampung</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Due Date</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php foreach ($assigned_tasks as $task): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($task['title']) ?></div>
                                                <div class="text-sm text-gray-500"><?= htmlspecialchars($task['description']) ?></div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?= htmlspecialchars($task['kampung_name']) ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    <?= $task['status'] == 'completed' ? 'bg-green-100 text-green-800' : 
                                                       ($task['status'] == 'in_progress' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') ?>">
                                                    <?= ucfirst(str_replace('_', ' ', $task['status'])) ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?= $task['due_date'] ? date('M j, Y', strtotime($task['due_date'])) : 'No due date' ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($assigned_tasks)): ?>
                                        <tr>
                                            <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                                                No assigned tasks found
                                            </td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
</body>
</html>