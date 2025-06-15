<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

$error = '';
$success = '';
$asnaf = null;

// Get asnaf ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: admin_asnaf.php');
    exit();
}

$asnaf_id = $_GET['id'];

// Fetch asnaf data
$stmt = $conn->prepare("SELECT * FROM asnaf WHERE id = ?");
$stmt->bind_param("i", $asnaf_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: admin_asnaf.php');
    exit();
}

$asnaf = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $conn->real_escape_string(trim($_POST['name']));
    $ic = $conn->real_escape_string(trim($_POST['ic']));
    $phone = $conn->real_escape_string(trim($_POST['phone']));
    $address = $conn->real_escape_string(trim($_POST['address']));
    $kampung = $conn->real_escape_string(trim($_POST['kampung'] ?? ''));
    $tl = $conn->real_escape_string(trim($_POST['tl'] ?? ''));
    $occupation = $conn->real_escape_string(trim($_POST['occupation']));
    $status = $conn->real_escape_string(trim($_POST['status']));
    $total_dependent = (int)$_POST['total_dependent'];
    $dependent_names = $conn->real_escape_string(trim($_POST['dependent_names'] ?? ''));
    $problems = $conn->real_escape_string(trim($_POST['problems']));

    // Handle file upload
    $picture = $asnaf['picture'];
    if (isset($_FILES['picture']) && $_FILES['picture']['error'] == UPLOAD_ERR_OK) {
        // Delete old picture if exists
        if (!empty($picture) && file_exists($picture)) {
            unlink($picture);
        }
        
        $uploadDir = '../uploads/asnaf/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileExt = pathinfo($_FILES['picture']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid() . '.' . $fileExt;
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['picture']['tmp_name'], $targetPath)) {
            $picture = $targetPath;
        }
    }

    if (empty($name) || empty($ic) || empty($phone) || empty($address) || empty($occupation) || empty($problems)) {
        $error = 'Please fill in all required fields';
    } else {
        $stmt = $conn->prepare("UPDATE asnaf SET name = ?, ic = ?, phone = ?, address = ?, kampung = ?, tl = ?, occupation = ?, status = ?, total_dependent = ?, dependent_names = ?, problems = ?, picture = ? WHERE id = ?");
        $stmt->bind_param("ssssssssisssi", $name, $ic, $phone, $address, $kampung, $tl, $occupation, $status, $total_dependent, $dependent_names, $problems, $picture, $asnaf_id);
        
        if ($stmt->execute()) {
            $success = 'Asnaf updated successfully';
            // Refresh data
            $stmt = $conn->prepare("SELECT * FROM asnaf WHERE id = ?");
            $stmt->bind_param("i", $asnaf_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $asnaf = $result->fetch_assoc();
        } else {
            $error = 'Failed to update asnaf: ' . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Asnaf - One Heart Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex">
        <?php include 'admin_sidebar.php'; ?>

        <div class="flex-1 ml-64">
            <header class="bg-white shadow-sm p-4">
                <h2 class="text-xl font-semibold text-gray-800">Edit Asnaf</h2>
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

                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <form method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Full Name *</label>
                                <input type="text" name="name" required value="<?= htmlspecialchars($asnaf['name']) ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">IC Number *</label>
                                <input type="text" name="ic" required value="<?= htmlspecialchars($asnaf['ic']) ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Phone Number *</label>
                                <input type="tel" name="phone" required value="<?= htmlspecialchars($asnaf['phone']) ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Kampung/Village</label>
                                <input type="text" name="kampung" value="<?= htmlspecialchars($asnaf['kampung']) ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Address *</label>
                                <textarea name="address" required rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?= htmlspecialchars($asnaf['address']) ?></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">TL (Optional)</label>
                                <input type="text" name="tl" value="<?= htmlspecialchars($asnaf['tl']) ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Occupation *</label>
                                <input type="text" name="occupation" required value="<?= htmlspecialchars($asnaf['occupation']) ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Status *</label>
                                <select name="status" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="pending" <?= $asnaf['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="verified" <?= $asnaf['status'] == 'verified' ? 'selected' : '' ?>>Verified</option>
                                    <option value="assisted" <?= $asnaf['status'] == 'assisted' ? 'selected' : '' ?>>Assisted</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Total Dependent *</label>
                                <input type="number" name="total_dependent" required min="0" value="<?= htmlspecialchars($asnaf['total_dependent']) ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Dependent's Names</label>
                                <textarea name="dependent_names" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?= htmlspecialchars($asnaf['dependent_names']) ?></textarea>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Problems Faced *</label>
                                <textarea name="problems" required rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?= htmlspecialchars($asnaf['problems']) ?></textarea>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Picture</label>
                                <?php if (!empty($asnaf['picture']) && file_exists($asnaf['picture'])): ?>
                                    <div class="mb-2">
                                        <img src="<?= htmlspecialchars($asnaf['picture']) ?>" alt="Current Picture" class="h-24 w-24 object-cover rounded-md">
                                        <label class="block mt-1">
                                            <input type="checkbox" name="remove_picture" value="1"> Remove current picture
                                        </label>
                                    </div>
                                <?php endif; ?>
                                <input type="file" name="picture" accept="image/*" class="mt-1 block w-full">
                                <p class="mt-1 text-sm text-gray-500">Optional: Upload a new photo</p>
                            </div>
                        </div>
                        <div class="flex justify-end space-x-3 pt-4">
                            <a href="admin_asnaf.php" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</a>
                            <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Update Asnaf</button>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>
</body>
</html>