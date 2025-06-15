<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Handle different HTTP methods
switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if (isset($_GET['id'])) {
            // Fetch single asnaf
            $id = $conn->real_escape_string($_GET['id']);
            $query = "SELECT * FROM asnaf WHERE id = '$id'";
            $result = $conn->query($query);
            echo json_encode($result->fetch_assoc());
        }
        break;

    case 'POST':
        // Create new asnaf
        $name = $conn->real_escape_string($_POST['name']);
        $ic = $conn->real_escape_string($_POST['ic']);
        $phone = $conn->real_escape_string($_POST['phone']);
        $address = $conn->real_escape_string($_POST['address']);
        $tl = $conn->real_escape_string($_POST['tl']);
        $occupation = $conn->real_escape_string($_POST['occupation']);
        $status = $conn->real_escape_string($_POST['status']);
        $total_dependent = (int)$_POST['total_dependent'];
        $dependent_names = $conn->real_escape_string($_POST['dependent_names']);
        $problems = $conn->real_escape_string($_POST['problems']);
        
        // Handle file upload
        $picture_path = '';
        if (isset($_FILES['picture']) && $_FILES['picture']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/asnaf/';
            $file_ext = pathinfo($_FILES['picture']['name'], PATHINFO_EXTENSION);
            $file_name = uniqid() . '.' . $file_ext;
            move_uploaded_file($_FILES['picture']['tmp_name'], $upload_dir . $file_name);
            $picture_path = 'uploads/asnaf/' . $file_name;
        }
        
        $query = "INSERT INTO asnaf (name, ic, phone, address, tl, occupation, status, total_dependent, dependent_names, problems, picture, user_id) 
                 VALUES ('$name', '$ic', '$phone', '$address', '$tl', '$occupation', '$status', $total_dependent, '$dependent_names', '$problems', '$picture_path', '{$_SESSION['user_id']}')";
        
        if ($conn->query($query)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => $conn->error]);
        }
        break;

    case 'PUT':
        // Update asnaf
        parse_str(file_get_contents("php://input"), $put_vars);
        $id = $conn->real_escape_string($put_vars['id']);
        $name = $conn->real_escape_string($put_vars['name']);
        $ic = $conn->real_escape_string($put_vars['ic']);
        $phone = $conn->real_escape_string($put_vars['phone']);
        $address = $conn->real_escape_string($put_vars['address']);
        $tl = $conn->real_escape_string($put_vars['tl']);
        $occupation = $conn->real_escape_string($put_vars['occupation']);
        $status = $conn->real_escape_string($put_vars['status']);
        $total_dependent = (int)$put_vars['total_dependent'];
        $dependent_names = $conn->real_escape_string($put_vars['dependent_names']);
        $problems = $conn->real_escape_string($put_vars['problems']);
        
        $query = "UPDATE asnaf 
                 SET name = '$name',
                     ic = '$ic',
                     phone = '$phone',
                     address = '$address',
                     tl = '$tl',
                     occupation = '$occupation',
                     status = '$status',
                     total_dependent = $total_dependent,
                     dependent_names = '$dependent_names',
                     problems = '$problems'
                 WHERE id = '$id' AND user_id = '{$_SESSION['user_id']}'";
        
        if ($conn->query($query)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => $conn->error]);
        }
        break;

    case 'DELETE':
        // Delete asnaf
        $id = $conn->real_escape_string($_GET['id']);
        $query = "DELETE FROM asnaf WHERE id = '$id' AND user_id = '{$_SESSION['user_id']}'";
        
        if ($conn->query($query)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => $conn->error]);
        }
        break;
}

$conn->close();
?>