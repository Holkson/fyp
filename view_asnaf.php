<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get asnaf ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: asnaf.php');
    exit();
}

$asnaf_id = $_GET['id'];

// Fetch asnaf data
$stmt = $conn->prepare("SELECT * FROM asnaf WHERE id = ?");
$stmt->bind_param("i", $asnaf_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: asnaf.php');
    exit();
}

$asnaf = $result->fetch_assoc();

// Prepare Google Maps URL
$maps_url = "https://www.google.com/maps/search/?api=1&query=" . urlencode($asnaf['address']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Asnaf - One Heart</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <?php include 'includes/header.php'; ?>

    <main class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 py-8">
        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold tracking-tight">Asnaf Profile</h1>
                <a href="asnaf.php" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Back to List
                </a>
            </div>

            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <!-- Picture Section at Top -->
<div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
    <div class="flex items-center justify-center">
        <?php 
        // Check if picture exists and is not empty
        if (!empty($asnaf['picture']) && file_exists($asnaf['picture'])): 
        ?>
            <div class="text-center">
                <img src="<?php echo htmlspecialchars($asnaf['picture']); ?>" 
                     alt="Asnaf Picture" 
                     class="mx-auto h-48 w-48 object-cover rounded-lg shadow-md">
                <p class="mt-2 text-sm text-gray-500">Uploaded Picture</p>
            </div>
        <?php else: ?>
            <div class="text-center">
                <div class="mx-auto h-48 w-48 bg-gray-200 rounded-lg flex items-center justify-center">
                    <svg class="h-24 w-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <p class="mt-2 text-sm text-gray-500">No picture uploaded</p>
            </div>
        <?php endif; ?>
    </div>
</div>

                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <h2 class="text-lg font-medium text-gray-900">Personal Information</h2>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Full Name</p>
                                    <p class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($asnaf['name']); ?></p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">IC Number</p>
                                    <p class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($asnaf['ic']); ?></p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Phone Number</p>
                                    <p class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($asnaf['phone']); ?></p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Address</p>
                                    <p class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($asnaf['address']); ?></p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">TL</p>
                                    <p class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($asnaf['tl']); ?></p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h2 class="text-lg font-medium text-gray-900">Additional Information</h2>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Occupation</p>
                                    <p class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($asnaf['occupation']); ?></p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Status</p>
                                    <span class="mt-1 px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?php
                                        switch ($asnaf['status']) {
                                            case 'pending': echo 'bg-yellow-100 text-yellow-800'; break;
                                            case 'verified': echo 'bg-blue-100 text-blue-800'; break;
                                            case 'assisted': echo 'bg-green-100 text-green-800'; break;
                                        }
                                        ?>">
                                        <?php echo ucfirst(htmlspecialchars($asnaf['status'])); ?>
                                    </span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Total Dependent</p>
                                    <p class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($asnaf['total_dependent']); ?></p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Dependent Names</p>
                                    <p class="mt-1 text-sm text-gray-900"><?php echo nl2br(htmlspecialchars($asnaf['dependent_names'])); ?></p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Problems Faced</p>
                                    <p class="mt-1 text-sm text-gray-900"><?php echo nl2br(htmlspecialchars($asnaf['problems'])); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8">
                        <a href="<?php echo htmlspecialchars($maps_url); ?>" target="_blank" class="inline-flex justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700">
                            View in Google Maps
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>