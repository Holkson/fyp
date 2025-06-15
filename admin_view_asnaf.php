<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

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

// Prepare Google Maps URL
$maps_url = "https://www.google.com/maps/search/?api=1&query=" . urlencode($asnaf['address']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Asnaf - One Heart Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex">
        <?php include 'admin_sidebar.php'; ?>

        <div class="flex-1 ml-64">
            <header class="bg-white shadow-sm p-4">
                <h2 class="text-xl font-semibold text-gray-800">Asnaf Profile</h2>
            </header>

            <main class="p-6">
                <div class="space-y-6">
                    <div class="flex justify-between items-center">
                        <h1 class="text-2xl font-bold tracking-tight"><?= htmlspecialchars($asnaf['name']) ?></h1>
                        <div class="flex space-x-2">
                            <a href="admin_edit_asnaf.php?id=<?= $asnaf['id'] ?>" class="rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </a>
                            <a href="admin_asnaf.php" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                Back to List
                            </a>
                        </div>
                    </div>

                    <div class="bg-white shadow-md rounded-lg overflow-hidden">
                        <!-- Picture Section -->
                        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                            <div class="flex items-center justify-center">
                                <?php if (!empty($asnaf['picture']) && file_exists($asnaf['picture'])): ?>
                                    <div class="text-center">
                                        <img src="<?= htmlspecialchars($asnaf['picture']) ?>" alt="Asnaf Picture" class="mx-auto h-48 w-48 object-cover rounded-lg shadow-md">
                                        <p class="mt-2 text-sm text-gray-500">Uploaded Picture</p>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center">
                                        <div class="mx-auto h-48 w-48 bg-gray-200 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-user text-gray-400 text-6xl"></i>
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
                                            <p class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($asnaf['name']) ?></p>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-500">IC Number</p>
                                            <p class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($asnaf['ic']) ?></p>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-500">Phone Number</p>
                                            <p class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($asnaf['phone']) ?></p>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-500">Address</p>
                                            <p class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($asnaf['address']) ?></p>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-500">Kampung/Village</p>
                                            <p class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($asnaf['kampung'] ?? 'N/A') ?></p>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-500">TL</p>
                                            <p class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($asnaf['tl'] ?? 'N/A') ?></p>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <h2 class="text-lg font-medium text-gray-900">Additional Information</h2>
                                    <div class="mt-4 space-y-4">
                                        <div>
                                            <p class="text-sm font-medium text-gray-500">Occupation</p>
                                            <p class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($asnaf['occupation']) ?></p>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-500">Status</p>
                                            <span class="mt-1 px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                <?= $asnaf['status'] == 'verified' ? 'bg-green-100 text-green-800' : 
                                                   ($asnaf['status'] == 'assisted' ? 'bg-purple-100 text-purple-800' : 'bg-yellow-100 text-yellow-800') ?>">
                                                <?= ucfirst($asnaf['status']) ?>
                                            </span>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-500">Total Dependent</p>
                                            <p class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($asnaf['total_dependent']) ?></p>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-500">Dependent Names</p>
                                            <p class="mt-1 text-sm text-gray-900"><?= nl2br(htmlspecialchars($asnaf['dependent_names'] ?? 'N/A')) ?></p>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-500">Problems Faced</p>
                                            <p class="mt-1 text-sm text-gray-900"><?= nl2br(htmlspecialchars($asnaf['problems'])) ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-8">
                                <a href="<?= htmlspecialchars($maps_url) ?>" target="_blank" class="inline-flex justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700">
                                    <i class="fas fa-map-marker-alt mr-2"></i> View in Google Maps
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>