<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['assign_volunteer'])) {
        // Validate required fields
        if (empty($_POST['volunteer_id']) || empty($_POST['kampung_id'])) {
            $error = 'All required fields must be filled';
        } else {
            $volunteer_id = (int)$_POST['volunteer_id'];
            $kampung_id = (int)$_POST['kampung_id'];
            $is_leader = isset($_POST['is_leader']) ? 1 : 0;
            $assigned_by = $_SESSION['user_id'];
            
            // Check if volunteer is already assigned to this kampung
            $check_stmt = $conn->prepare("SELECT id FROM volunteer_assignments WHERE volunteer_id = ? AND kampung_id = ?");
            $check_stmt->bind_param("ii", $volunteer_id, $kampung_id);
            $check_stmt->execute();
            
            if ($check_stmt->get_result()->num_rows > 0) {
                $error = 'This volunteer is already assigned to this kampung';
            } else {
                // Insert new assignment
                $stmt = $conn->prepare("INSERT INTO volunteer_assignments (volunteer_id, kampung_id, is_leader, assigned_by) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiii", $volunteer_id, $kampung_id, $is_leader, $assigned_by);
                
                if ($stmt->execute()) {
                    $success = 'Volunteer assigned successfully';
                    // Refresh page to show new assignment
                    header("Location: admin_assign_volunteers.php?kampung=".$kampung_id);
                    exit();
                } else {
                    $error = 'Failed to assign volunteer: ' . $conn->error;
                }
            }
        }
    } elseif (isset($_POST['remove_assignment'])) {
        $assignment_id = (int)$_POST['assignment_id'];
        $kampung_id = (int)$_POST['kampung_id'];
        
        $stmt = $conn->prepare("DELETE FROM volunteer_assignments WHERE id = ?");
        $stmt->bind_param("i", $assignment_id);
        
        if ($stmt->execute()) {
            $success = 'Assignment removed successfully';
            // Refresh page
            header("Location: admin_assign_volunteers.php?kampung=".$kampung_id);
            exit();
        } else {
            $error = 'Failed to remove assignment: ' . $conn->error;
        }
    }
}

// Fetch all kampung groups
$kampungs = $conn->query("SELECT * FROM kampung_groups ORDER BY name")->fetch_all(MYSQLI_ASSOC);

// Fetch all active volunteers
$volunteers = $conn->query("SELECT id, name FROM users WHERE role = 'volunteer' AND status = 'active' ORDER BY name")->fetch_all(MYSQLI_ASSOC);

// Fetch current assignments if kampung is selected
$current_assignments = [];
$selected_kampung = null;

if (isset($_GET['kampung']) && is_numeric($_GET['kampung'])) {
    $kampung_id = (int)$_GET['kampung'];
    $selected_kampung = $conn->query("SELECT * FROM kampung_groups WHERE id = $kampung_id")->fetch_assoc();
    
    $current_assignments = $conn->query("
        SELECT va.id, u.id as volunteer_id, u.name, va.is_leader
        FROM volunteer_assignments va
        JOIN users u ON va.volunteer_id = u.id
        WHERE va.kampung_id = $kampung_id
        ORDER BY va.is_leader DESC, u.name
    ")->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Volunteers - One Heart Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex">
        <?php include 'admin_sidebar.php'; ?>

        <div class="flex-1 ml-64">
            <header class="bg-white shadow-sm p-4">
                <h2 class="text-xl font-semibold text-gray-800">
                    <?= $selected_kampung ? 'Assign Volunteers to '.htmlspecialchars($selected_kampung['name']) : 'Assign Volunteers' ?>
                </h2>
            </header>

            <main class="p-6">
                <?php if ($error): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>

                <div class="space-y-6">
                    <!-- Kampung Selection -->
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="p-6">
                            <form method="GET" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Select Kampung</label>
                                    <select name="kampung" onchange="this.form.submit()" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">-- Select Kampung --</option>
                                        <?php foreach ($kampungs as $kampung): ?>
                                            <option value="<?= $kampung['id'] ?>" <?= isset($_GET['kampung']) && $_GET['kampung'] == $kampung['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($kampung['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </form>
                        </div>
                    </div>

                    <?php if (isset($_GET['kampung'])): ?>
                        <!-- Current Assignments -->
                        <div class="bg-white rounded-lg shadow overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-medium">Current Assignments</h3>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Volunteer</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php foreach ($current_assignments as $assignment): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($assignment['name']) ?></div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php if ($assignment['is_leader']): ?>
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
                                                <form method="POST" class="inline">
                                                    <input type="hidden" name="remove_assignment" value="1">
                                                    <input type="hidden" name="assignment_id" value="<?= $assignment['id'] ?>">
                                                    <input type="hidden" name="kampung_id" value="<?= $_GET['kampung'] ?>">
                                                    <button type="submit" class="text-red-600 hover:text-red-900">Remove</button>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($current_assignments)): ?>
                                        <tr>
                                            <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">
                                                No volunteers assigned to this kampung yet
                                            </td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Add Volunteer Form -->
                        <div class="bg-white rounded-lg shadow overflow-hidden">
                            <div class="p-6">
                                <h3 class="text-lg font-medium mb-4">Assign New Volunteer</h3>
                                <form method="POST">
                                    <input type="hidden" name="assign_volunteer" value="1">
                                    <input type="hidden" name="kampung_id" value="<?= $_GET['kampung'] ?>">
                                    
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Volunteer *</label>
                                            <select name="volunteer_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                <option value="">-- Select Volunteer --</option>
                                                <?php foreach ($volunteers as $volunteer): 
                                                    // Check if volunteer is already assigned
                                                    $is_assigned = false;
                                                    foreach ($current_assignments as $a) {
                                                        if ($a['volunteer_id'] == $volunteer['id']) {
                                                            $is_assigned = true;
                                                            break;
                                                        }
                                                    }
                                                    if (!$is_assigned): ?>
                                                    <option value="<?= $volunteer['id'] ?>"><?= htmlspecialchars($volunteer['name']) ?></option>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="flex items-end">
                                            <div class="flex items-center h-10">
                                                <input id="is_leader" name="is_leader" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                <label for="is_leader" class="ml-2 block text-sm text-gray-700">
                                                    Make group leader
                                                </label>
                                            </div>
                                        </div>
                                        <div class="flex items-end">
                                            <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                                                Assign Volunteer
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
</body>
</html>