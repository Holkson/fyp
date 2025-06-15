<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: login.php');
    exit();
}

// Fetch report data - updated for kampung-based system
$asnaf_by_status = $conn->query("
    SELECT status, COUNT(*) as count 
    FROM asnaf 
    GROUP BY status
")->fetch_all(MYSQLI_ASSOC);

$volunteers_by_status = $conn->query("
    SELECT status, COUNT(*) as count 
    FROM users 
    WHERE role = 'volunteer'
    GROUP BY status
")->fetch_all(MYSQLI_ASSOC);

$asnaf_by_kampung = $conn->query("
    SELECT kg.name as kampung, COUNT(a.id) as count
    FROM kampung_groups kg
    LEFT JOIN asnaf a ON a.kampung = kg.name
    GROUP BY kg.name
    ORDER BY count DESC
")->fetch_all(MYSQLI_ASSOC);

$tasks_by_status = $conn->query("
    SELECT status, COUNT(*) as count 
    FROM tasks 
    GROUP BY status
")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - One Heart Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100">
    <div class="flex">
        <?php include 'admin_sidebar.php'; ?>

        <div class="flex-1 ml-64">
            <header class="bg-white shadow-sm p-4">
                <h2 class="text-xl font-semibold text-gray-800">Reports</h2>
            </header>

            <main class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Asnaf Status Chart -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-medium mb-4">Asnaf by Status</h3>
                        <canvas id="asnafChart" height="300"></canvas>
                    </div>

                    <!-- Volunteers Status Chart -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-medium mb-4">Volunteers by Status</h3>
                        <canvas id="volunteersChart" height="300"></canvas>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Asnaf by Kampung Chart -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-medium mb-4">Asnaf Distribution by Kampung</h3>
                        <canvas id="kampungChart" height="300"></canvas>
                    </div>

                    <!-- Tasks Status Chart -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-medium mb-4">Tasks by Status</h3>
                        <canvas id="tasksChart" height="300"></canvas>
                    </div>
                </div>

                <!-- Data Export Section -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium mb-4">Export Data</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="border rounded-lg p-4">
                            <h4 class="font-medium mb-2">Asnaf Data</h4>
                            <div class="flex space-x-2">
                                <a href="export_asnaf.php?type=pdf" class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700">
                                    <i class="fas fa-file-pdf mr-1"></i> PDF
                                </a>
                                <a href="export_asnaf.php?type=excel" class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700">
                                    <i class="fas fa-file-excel mr-1"></i> Excel
                                </a>
                            </div>
                        </div>
                        <div class="border rounded-lg p-4">
                            <h4 class="font-medium mb-2">Volunteer Data</h4>
                            <div class="flex space-x-2">
                                <a href="export_volunteers.php?type=pdf" class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700">
                                    <i class="fas fa-file-pdf mr-1"></i> PDF
                                </a>
                                <a href="export_volunteers.php?type=excel" class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700">
                                    <i class="fas fa-file-excel mr-1"></i> Excel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Asnaf Status Chart
        const asnafCtx = document.getElementById('asnafChart').getContext('2d');
        new Chart(asnafCtx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode(array_column($asnaf_by_status, 'status')) ?>,
                datasets: [{
                    data: <?= json_encode(array_column($asnaf_by_status, 'count')) ?>,
                    backgroundColor: [
                        '#F59E0B', // Pending (yellow)
                        '#10B981', // Verified (green)
                        '#8B5CF6'  // Assisted (purple)
                    ],
                    borderWidth: 1
                }]
            }
        });

        // Volunteers Status Chart
        const volunteersCtx = document.getElementById('volunteersChart').getContext('2d');
        new Chart(volunteersCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($volunteers_by_status, 'status')) ?>,
                datasets: [{
                    label: 'Volunteers',
                    data: <?= json_encode(array_column($volunteers_by_status, 'count')) ?>,
                    backgroundColor: '#3B82F6',
                    borderColor: '#1D4ED8',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Asnaf by Kampung Chart
        const kampungCtx = document.getElementById('kampungChart').getContext('2d');
        new Chart(kampungCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($asnaf_by_kampung, 'kampung')) ?>,
                datasets: [{
                    label: 'Number of Asnaf',
                    data: <?= json_encode(array_column($asnaf_by_kampung, 'count')) ?>,
                    backgroundColor: '#10B981',
                    borderColor: '#047857',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Tasks Status Chart
        const tasksCtx = document.getElementById('tasksChart').getContext('2d');
        new Chart(tasksCtx, {
            type: 'pie',
            data: {
                labels: <?= json_encode(array_column($tasks_by_status, 'status')) ?>,
                datasets: [{
                    data: <?= json_encode(array_column($tasks_by_status, 'count')) ?>,
                    backgroundColor: [
                        '#F59E0B', // Pending (yellow)
                        '#3B82F6', // In Progress (blue)
                        '#10B981'  // Completed (green)
                    ],
                    borderWidth: 1
                }]
            }
        });
    </script>
</body>
</html>