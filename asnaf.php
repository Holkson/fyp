<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Set default query
$query = "SELECT id, name, ic, phone FROM asnaf";
$where = [];
$params = [];
$types = '';

// Check for status filter
if (isset($_GET['status']) && $_GET['status'] == 'pending') {
    $where[] = "status = ?";
    $params[] = 'pending';
    $types .= 's';
} elseif (isset($_GET['status']) && $_GET['status'] == 'verified') {
    $where[] = "status IN (?, ?)";
    $params[] = 'verified';
    $params[] = 'assisted';
    $types .= 'ss';
}

// Build final query
if (!empty($where)) {
    $query .= " WHERE " . implode(" AND ", $where);
}
$query .= " ORDER BY created_at DESC";

// Prepare and execute query
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($_GET['status']) ? ucfirst($_GET['status']) : 'All'; ?> Asnaf - One Heart</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <?php include 'includes/header.php'; ?>

    <main class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold tracking-tight">
                    <?php 
                    if (isset($_GET['status'])) {
                        echo $_GET['status'] == 'pending' ? 'Pending Asnaf' : 'Verified/Assisted Asnaf';
                    } else {
                        echo 'All Asnaf Records';
                    }
                    ?>
                </h1>
                <a href="add_asnaf.php" class="rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-500">
                    Add New Asnaf
                </a>
            </div>

            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <?php if ($result->num_rows > 0): ?>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IC</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($row['name']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($row['ic']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($row['phone']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <a href="edit_asnaf.php?id=<?php echo $row['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                                        <a href="view_asnaf.php?id=<?php echo $row['id']; ?>" class="text-green-600 hover:text-green-900">View</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="p-6 text-center text-gray-500">
                        No asnaf records found
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>