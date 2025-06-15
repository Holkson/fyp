<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';
$asnaf = null;

// Get asnaf ID from URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $asnaf_id = $_GET['id'];
    
    // Fetch asnaf data
    $stmt = $conn->prepare("SELECT * FROM asnaf WHERE id = ?");
    $stmt->bind_param("i", $asnaf_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $asnaf = $result->fetch_assoc();
    } else {
        $error = 'Asnaf record not found';
    }
} else {
    $error = 'Invalid Asnaf ID';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string(trim($_POST['name']));
    $ic = $conn->real_escape_string(trim($_POST['ic']));
    $phone = $conn->real_escape_string(trim($_POST['phone']));
    $address = $conn->real_escape_string(trim($_POST['address']));
    $tl = $conn->real_escape_string(trim($_POST['tl'] ?? ''));
    $occupation = $conn->real_escape_string(trim($_POST['occupation']));
    $status = $conn->real_escape_string(trim($_POST['status']));
    $total_dependent = (int)$_POST['total_dependent'];
    $dependent_names = $conn->real_escape_string(trim($_POST['dependent_names'] ?? ''));
    $problems = $conn->real_escape_string(trim($_POST['problems']));

    // Basic validation
    if (empty($name) || empty($ic) || empty($phone) || empty($address) || empty($occupation) || empty($problems)) {
        $error = 'Please fill in all required fields';
    } else {
        // Update asnaf
        $stmt = $conn->prepare("UPDATE asnaf SET 
            name = ?, 
            ic = ?, 
            phone = ?, 
            address = ?, 
            tl = ?, 
            occupation = ?, 
            status = ?, 
            total_dependent = ?, 
            dependent_names = ?, 
            problems = ?
            WHERE id = ?");
        
        $stmt->bind_param("sssssssissi", 
            $name,
            $ic,
            $phone,
            $address,
            $tl,
            $occupation,
            $status,
            $total_dependent,
            $dependent_names,
            $problems,
            $asnaf_id
        );
        
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
    <title>Edit Asnaf - One Heart</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <?php include 'includes/header.php'; ?>

    <main class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 py-8">
        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold tracking-tight">Edit Asnaf</h1>
                <a href="asnaf.php" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Back to List
                </a>
            </div>

            <?php if ($error): ?>
                <div class="rounded-md bg-red-50 p-4">
                    <div class="flex">
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800"><?php echo htmlspecialchars($error); ?></h3>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="rounded-md bg-green-50 p-4">
                    <div class="flex">
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-green-800"><?php echo htmlspecialchars($success); ?></h3>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($asnaf): ?>
                <div class="bg-white shadow-md rounded-lg overflow-hidden">
                    <form method="POST" class="p-6 space-y-6">
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Full Name *</label>
                                <input type="text" name="name" required 
                                    value="<?php echo htmlspecialchars($asnaf['name']); ?>" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">IC Number *</label>
                                <input type="text" name="ic" required 
                                    value="<?php echo htmlspecialchars($asnaf['ic']); ?>" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Phone Number *</label>
                                <input type="tel" name="phone" required 
                                    value="<?php echo htmlspecialchars($asnaf['phone']); ?>" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Address *</label>
                                <textarea name="address" required rows="2" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"><?php echo htmlspecialchars($asnaf['address']); ?></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Kampung/Village</label>
                                <input type="text" name="kampung" value="<?= isset($asnaf) ? htmlspecialchars($asnaf['kampung']) : '' ?>" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">TL (Optional)</label>
                                <input type="text" name="tl" 
                                    value="<?php echo htmlspecialchars($asnaf['tl']); ?>" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Occupation *</label>
                                <input type="text" name="occupation" required 
                                    value="<?php echo htmlspecialchars($asnaf['occupation']); ?>" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Status *</label>
                                <select name="status" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="pending" <?php echo $asnaf['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="verified" <?php echo $asnaf['status'] === 'verified' ? 'selected' : ''; ?>>Verified</option>
                                    <option value="assisted" <?php echo $asnaf['status'] === 'assisted' ? 'selected' : ''; ?>>Assisted</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Total Dependent *</label>
                                <input type="number" name="total_dependent" required min="0" 
                                    value="<?php echo htmlspecialchars($asnaf['total_dependent']); ?>" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Dependent's Names</label>
                                <textarea name="dependent_names" rows="2" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"><?php echo htmlspecialchars($asnaf['dependent_names']); ?></textarea>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Problems Faced *</label>
                                <textarea name="problems" required rows="3" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"><?php echo htmlspecialchars($asnaf['problems']); ?></textarea>
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
                        <div class="flex justify-end">
                            <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                                Update Asnaf
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>