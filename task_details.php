<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if task ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ' . ($_SESSION['user_role'] == 'admin' ? 'admin_tasks.php' : 'volunteer_tasks.php'));
    exit();
}

$task_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];
$is_admin = ($_SESSION['user_role'] == 'admin');

// Fetch task details with related information
$query = "
    SELECT t.*, 
           kg.name as kampung_name,
           uc.name as created_by_name,
           ua.name as assigned_to_name,
           ua.email as assigned_to_email,
           ua.phone as assigned_to_phone
    FROM tasks t
    LEFT JOIN kampung_groups kg ON t.kampung_id = kg.id
    LEFT JOIN users uc ON t.created_by = uc.id
    LEFT JOIN users ua ON t.assigned_to = ua.id
    WHERE t.id = ?
";

// For volunteers, ensure they can only see their own tasks
if (!$is_admin) {
    $query .= " AND t.assigned_to = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $task_id, $user_id);
} else {
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $task_id);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: ' . ($is_admin ? 'admin_tasks.php' : 'volunteer_tasks.php'));
    exit();
}

$task = $result->fetch_assoc();

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $new_status = $conn->real_escape_string($_POST['status']);
    
    $stmt = $conn->prepare("UPDATE tasks SET status = ? WHERE id = ?" . (!$is_admin ? " AND assigned_to = ?" : ""));
    
    if (!$is_admin) {
        $stmt->bind_param("sii", $new_status, $task_id, $user_id);
    } else {
        $stmt->bind_param("si", $new_status, $task_id);
    }
    
    if ($stmt->execute()) {
        $task['status'] = $new_status;
        $success = "Task status updated successfully";
    } else {
        $error = "Failed to update task status";
    }
}

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_comment'])) {
    $comment = $conn->real_escape_string(trim($_POST['comment']));
    
    if (!empty($comment)) {
        $stmt = $conn->prepare("INSERT INTO task_comments (task_id, user_id, comment) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $task_id, $user_id, $comment);
        $stmt->execute();
    }
}

// // Fetch task comments
// $comments = $conn->query("
//     SELECT tc.*, u.name as user_name, u.role as user_role
//     FROM task_comments tc
//     JOIN users u ON tc.user_id = u.id
//     WHERE tc.task_id = $task_id
//     ORDER BY tc.created_at DESC
// ")->fetch_all(MYSQLI_ASSOC);
// ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Details - One Heart</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <?php include ($is_admin ? 'admin_sidebar.php' : 'includes/header.php'); ?>

    <main class="<?= $is_admin ? 'ml-64' : '' ?> p-6">
        <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-md overflow-hidden">
            <!-- Task Header -->
            <div class="bg-blue-600 px-6 py-4 text-white">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-bold"><?= htmlspecialchars($task['title']) ?></h1>
                    <span class="px-3 py-1 rounded-full text-sm font-semibold 
                        <?= $task['status'] == 'completed' ? 'bg-green-100 text-green-800' : 
                           ($task['status'] == 'in_progress' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') ?>">
                        <?= ucfirst(str_replace('_', ' ', $task['status'])) ?>
                    </span>
                </div>
                <p class="mt-1 text-blue-100"><?= htmlspecialchars($task['description']) ?></p>
            </div>

            <!-- Task Details -->
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Task Information</h2>
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Kampung</p>
                            <p class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($task['kampung_name'] ?? 'Not assigned') ?></p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Due Date</p>
                            <p class="mt-1 text-sm text-gray-900"><?= date('M d, Y', strtotime($task['due_date'])) ?></p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Created By</p>
                            <p class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($task['created_by_name']) ?></p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Created On</p>
                            <p class="mt-1 text-sm text-gray-900"><?= date('M d, Y H:i', strtotime($task['created_at'])) ?></p>
                        </div>
                    </div>
                </div>

                <div>
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Assignment</h2>
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Assigned To</p>
                            <?php if ($task['assigned_to_name']): ?>
                                <div class="mt-1 flex items-center">
                                    <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center mr-2">
                                        <span class="text-blue-600 text-xs font-medium">
                                            <?= strtoupper(substr($task['assigned_to_name'], 0, 1)) ?>
                                        </span>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-900"><?= htmlspecialchars($task['assigned_to_name']) ?></p>
                                        <p class="text-xs text-gray-500"><?= htmlspecialchars($task['assigned_to_email']) ?></p>
                                    </div>
                                </div>
                            <?php else: ?>
                                <p class="mt-1 text-sm text-gray-500">Not assigned</p>
                            <?php endif; ?>
                        </div>

                        <!-- Status Update Form -->
                        <form method="POST">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Update Status</label>
                                <div class="mt-1 flex space-x-2">
                                    <select name="status" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="pending" <?= $task['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="in_progress" <?= $task['status'] == 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                        <option value="completed" <?= $task['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                                    </select>
                                    <button type="submit" name="update_status" class="inline-flex justify-center rounded-md border border-transparent bg-blue-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                        Update
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Task Comments Section -->
            <div class="border-t border-gray-200 px-6 py-4">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Comments</h2>
                
                <!-- Comment Form -->
                <form method="POST" class="mb-6">
                    <div class="flex space-x-2">
                        <input type="text" name="comment" placeholder="Add a comment..." required
                            class="flex-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <button type="submit" name="add_comment" class="inline-flex justify-center rounded-md border border-transparent bg-blue-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Post
                        </button>
                    </div>
                </form>

                <!-- Comments List -->
                <div class="space-y-4">
                    <?php if (!empty($comments)): ?>
                        <?php foreach ($comments as $comment): ?>
                            <div class="flex space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                        <span class="text-blue-600 text-xs font-medium">
                                            <?= strtoupper(substr($comment['user_name'], 0, 1)) ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between">
                                        <p class="text-sm font-medium text-gray-900">
                                            <?= htmlspecialchars($comment['user_name']) ?>
                                            <span class="text-xs text-gray-500 ml-1">
                                                (<?= ucfirst($comment['user_role']) ?>)
                                            </span>
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            <?= date('M d, Y H:i', strtotime($comment['created_at'])) ?>
                                        </p>
                                    </div>
                                    <p class="text-sm text-gray-700 mt-1"><?= htmlspecialchars($comment['comment']) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-sm text-gray-500">No comments yet</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Task Actions -->
            <div class="border-t border-gray-200 px-6 py-4 bg-gray-50 flex justify-end space-x-3">
                <a href="<?= $is_admin ? 'admin_tasks.php' : 'volunteer_tasks.php' ?>" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Back to Tasks
                </a>
                <?php if ($is_admin): ?>
                    <a href="admin_create_task.php?id=<?= $task['id'] ?>" class="rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Edit Task
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>