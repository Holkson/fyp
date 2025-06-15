<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

$error = '';
$success = '';
$task = null;

// Check if editing existing task
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $stmt = $conn->prepare("SELECT * FROM tasks WHERE id = ?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $task = $result->fetch_assoc();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $conn->real_escape_string(trim($_POST['title']));
    $description = $conn->real_escape_string(trim($_POST['description'] ?? ''));
    $kampung_id = (int)$_POST['kampung_id'];
    $assigned_to = !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null;
    $due_date = $conn->real_escape_string(trim($_POST['due_date']));
    $status = $conn->real_escape_string(trim($_POST['status']));
    $created_by = $_SESSION['user_id'];

    if (empty($title) || empty($due_date)) {
        $error = 'Title and Due Date are required';
    } else {
        if ($task) {
            // Update existing task
            $stmt = $conn->prepare("UPDATE tasks SET 
                title = ?, description = ?, kampung_id = ?, assigned_to = ?, 
                due_date = ?, status = ? WHERE id = ?");
            $stmt->bind_param("ssiissi", $title, $description, $kampung_id, 
                $assigned_to, $due_date, $status, $task['id']);
        } else {
            // Create new task
            $stmt = $conn->prepare("INSERT INTO tasks 
                (title, description, kampung_id, assigned_to, due_date, status, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssiissi", $title, $description, $kampung_id, 
                $assigned_to, $due_date, $status, $created_by);
        }

        if ($stmt->execute()) {
            $success = $task ? 'Task updated successfully' : 'Task created successfully';
            header("Location: admin_tasks.php");
            exit();
        } else {
            $error = 'Database error: ' . $conn->error;
        }
    }
}

// Fetch kampungs and volunteers
$kampungs = $conn->query("SELECT * FROM kampung_groups ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$volunteers = $conn->query("SELECT id, name FROM users WHERE role = 'volunteer' AND status = 'active' ORDER BY name")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $task ? 'Edit' : 'Create' ?> Task - One Heart Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex">
        <?php include 'admin_sidebar.php'; ?>

        <div class="flex-1 ml-64">
            <header class="bg-white shadow-sm p-4">
                <h2 class="text-xl font-semibold text-gray-800"><?= $task ? 'Edit' : 'Create' ?> Task</h2>
            </header>

            <main class="p-6">
                <?php if ($error): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <form method="POST" class="p-6 space-y-6">
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Title *</label>
                                <input type="text" name="title" required 
                                    value="<?= htmlspecialchars($task['title'] ?? '') ?>"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea name="description" rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?= htmlspecialchars($task['description'] ?? '') ?></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Kampung *</label>
                                <select name="kampung_id" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">-- Select Kampung --</option>
                                    <?php foreach ($kampungs as $kampung): ?>
                                        <option value="<?= $kampung['id'] ?>" 
                                            <?= ($task['kampung_id'] ?? '') == $kampung['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($kampung['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Assign To</label>
                                <select name="assigned_to"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">-- Unassigned --</option>
                                    <?php foreach ($volunteers as $volunteer): ?>
                                        <option value="<?= $volunteer['id'] ?>" 
                                            <?= ($task['assigned_to'] ?? '') == $volunteer['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($volunteer['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Due Date *</label>
                                <input type="date" name="due_date" required
                                    value="<?= htmlspecialchars($task['due_date'] ?? '') ?>"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Status *</label>
                                <select name="status" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="pending" <?= ($task['status'] ?? 'pending') == 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="in_progress" <?= ($task['status'] ?? '') == 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                    <option value="completed" <?= ($task['status'] ?? '') == 'completed' ? 'selected' : '' ?>>Completed</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3 pt-4">
                            <a href="admin_tasks.php" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</a>
                            <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                                <?= $task ? 'Update' : 'Create' ?> Task
                            </button>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>
</body>
</html>