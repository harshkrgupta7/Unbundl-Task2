<?php
session_start();

define('DB_HOST', 'carsdekho_mysql');
define('DB_USER', 'root');
define('DB_PASS', 'rootpassword');
define('DB_NAME', 'carsdekho');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

$uploadDirs = ['uploads/profiles/', 'uploads/banners/', 'uploads/cars/'];
foreach ($uploadDirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

function getImagePath($imageName, $folder) {
    if (empty($imageName)) return '';
    if (strpos($imageName, 'http') === 0) {
        return $imageName;
    }
    return $folder . $imageName;
}

function handleFileUpload($file, $targetDir, $prefix = 'img_') {
    if ($file['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $prefix . time() . '_' . rand(100, 999) . '.' . $ext;
        if (move_uploaded_file($file['tmp_name'], $targetDir . $filename)) {
            return $filename;
        }
    }
    return null;
}

if (isset($_POST['action']) && $_POST['action'] === 'login') {
    header('Content-Type: application/json');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Email and password are required!']);
        exit;
    }
    
    $stmt = $conn->prepare("SELECT id, username, email, password, full_name, role, status, profile_image FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if ($user['status'] !== 'active') {
            echo json_encode(['success' => false, 'message' => 'Your account is inactive!']);
            exit;
        }
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['profile_image'] = $user['profile_image'];
            $_SESSION['logged_in'] = true;
            $updateStmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $updateStmt->bind_param("i", $user['id']);
            $updateStmt->execute();
            echo json_encode(['success' => true, 'message' => 'Login successful!', 'role' => $user['role']]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Incorrect password!']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No account found with this email!']);
    }
    exit;
}

if (isset($_POST['action']) && $_POST['action'] === 'register') {
    header('Content-Type: application/json');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'user';
    
    if (empty($username) || empty($email) || empty($full_name) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required!']);
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format!']);
        exit;
    }
    if ($password !== $confirm_password) {
        echo json_encode(['success' => false, 'message' => 'Passwords do not match!']);
        exit;
    }
    
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    if ($checkStmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email already registered!']);
        exit;
    }
    
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    $insertStmt = $conn->prepare("INSERT INTO users (username, email, full_name, password, role, status) VALUES (?, ?, ?, ?, ?, 'active')");
    $insertStmt->bind_param("sssss", $username, $email, $full_name, $hashed_password, $role);
    
    if ($insertStmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Registration successful!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Registration failed!']);
    }
    exit;
}

if (isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    header('Content-Type: application/json');
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        echo json_encode(['success' => false, 'message' => 'You must be logged in!']);
        exit;
    }
    $user_id = $_SESSION['user_id'];
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $getUserStmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $getUserStmt->bind_param("i", $user_id);
    $getUserStmt->execute();
    $userData = $getUserStmt->get_result()->fetch_assoc();
    
    $updatePassword = false;
    $hashedPassword = '';
    
    if (!empty($new_password) || !empty($current_password)) {
        if (!password_verify($current_password, $userData['password'])) {
            echo json_encode(['success' => false, 'message' => 'Current password is incorrect!']);
            exit;
        }
        if ($new_password !== $confirm_password) {
            echo json_encode(['success' => false, 'message' => 'New passwords do not match!']);
            exit;
        }
        $hashedPassword = password_hash($new_password, PASSWORD_BCRYPT);
        $updatePassword = true;
    }
    
    $profileImage = null;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $extension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
        $profileImage = 'profile_' . $user_id . '_' . time() . '.' . $extension;
        move_uploaded_file($_FILES['profile_image']['tmp_name'], 'uploads/profiles/' . $profileImage);
    }
    
    if ($updatePassword && $profileImage) {
        $updateStmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, password = ?, profile_image = ? WHERE id = ?");
        $updateStmt->bind_param("ssssi", $full_name, $email, $hashedPassword, $profileImage, $user_id);
    } elseif ($updatePassword) {
        $updateStmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, password = ? WHERE id = ?");
        $updateStmt->bind_param("sssi", $full_name, $email, $hashedPassword, $user_id);
    } elseif ($profileImage) {
        $updateStmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, profile_image = ? WHERE id = ?");
        $updateStmt->bind_param("sssi", $full_name, $email, $profileImage, $user_id);
    } else {
        $updateStmt = $conn->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ?");
        $updateStmt->bind_param("ssi", $full_name, $email, $user_id);
    }
    
    if ($updateStmt->execute()) {
        $_SESSION['full_name'] = $full_name;
        $_SESSION['email'] = $email;
        if ($profileImage) $_SESSION['profile_image'] = $profileImage;
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully!', 'full_name' => $full_name, 'email' => $email, 'profile_image' => $profileImage]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update profile!']);
    }
    exit;
}

if (isset($_POST['action']) && $_POST['action'] === 'manage_data') {
    header('Content-Type: application/json');
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Unauthorized access!']);
        exit;
    }

    $type = $_POST['type']; 
    $operation = $_POST['operation'];
    
    $sql = "SELECT * FROM admindata WHERE id = 1";
    $row = $conn->query($sql)->fetch_assoc();
    
    $colName = ($type === 'banner') ? 'bannerimage_data' : (($type === 'searched_cars') ? 'searched_cars' : 'latest_cars');
    $dataArray = json_decode($row[$colName], true) ?? [];

    if ($operation === 'add') {
        $newItem = [];
        $newItem['id'] = time(); 
        
        if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
            $folder = ($type === 'banner') ? 'uploads/banners/' : 'uploads/cars/';
            $newItem['image'] = handleFileUpload($_FILES['image'], $folder);
        } else {
            $newItem['image'] = ''; 
        }

        if ($type === 'banner') {
            $newItem['title'] = $_POST['title'];
            $newItem['subtitle'] = $_POST['subtitle'];
            $newItem['button_text'] = $_POST['button_text'];
            $newItem['link'] = '#';
            $newItem['active'] = true;
        } else {
            $newItem['name'] = $_POST['name'];
            $newItem['price'] = $_POST['price'];
            if ($type === 'searched_cars') {
                $newItem['category'] = $_POST['category'];
                $newItem['model'] = $_POST['model'];
                $newItem['fuel_type'] = 'Petrol';
                $newItem['transmission'] = 'Manual';
            }
        }
        $dataArray[] = $newItem;

    } elseif ($operation === 'update') {
        $id = $_POST['item_id'];
        foreach ($dataArray as $key => $item) {
            if ($item['id'] == $id) {
                if ($type === 'banner') {
                    $dataArray[$key]['title'] = $_POST['title'];
                    $dataArray[$key]['subtitle'] = $_POST['subtitle'];
                    $dataArray[$key]['button_text'] = $_POST['button_text'];
                } else {
                    $dataArray[$key]['name'] = $_POST['name'];
                    $dataArray[$key]['price'] = $_POST['price'];
                    if ($type === 'searched_cars') {
                        $dataArray[$key]['category'] = $_POST['category'];
                        $dataArray[$key]['model'] = $_POST['model'];
                    }
                }
                
                if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
                    $folder = ($type === 'banner') ? 'uploads/banners/' : 'uploads/cars/';
                    $uploadedImg = handleFileUpload($_FILES['image'], $folder);
                    if ($uploadedImg) {
                        $dataArray[$key]['image'] = $uploadedImg;
                    }
                }
                break;
            }
        }

    } elseif ($operation === 'delete') {
        $id = $_POST['item_id'];
        foreach ($dataArray as $key => $item) {
            if ($item['id'] == $id) {
                unset($dataArray[$key]);
                break;
            }
        }
        $dataArray = array_values($dataArray);
    }

    $jsonNew = json_encode($dataArray);
    $updateStmt = $conn->prepare("UPDATE admindata SET $colName = ? WHERE id = 1");
    $updateStmt->bind_param("s", $jsonNew);
    
    if ($updateStmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Data updated successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
    exit;
}

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit;
}

$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$userName = $_SESSION['full_name'] ?? 'Guest';
$userEmail = $_SESSION['email'] ?? '';
$userRole = $_SESSION['role'] ?? 'guest';
$userProfileImage = $_SESSION['profile_image'] ?? null;

$sql = "SELECT * FROM admindata WHERE id = 1";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $data = $result->fetch_assoc();
    $banners = json_decode($data['bannerimage_data'], true);
    $searchedCars = json_decode($data['searched_cars'], true);
    $latestCars = json_decode($data['latest_cars'], true);
} else {
    $banners = []; $searchedCars = []; $latestCars = [];
}

$allSearchNames = [];
if (!empty($searchedCars)) {
    foreach ($searchedCars as $c) {
        if (!in_array($c['name'], $allSearchNames)) {
            $allSearchNames[] = $c['name'];
        }
    }
}
if (!empty($latestCars)) {
    foreach ($latestCars as $c) {
        if (!in_array($c['name'], $allSearchNames)) {
            $allSearchNames[] = $c['name'];
        }
    }
}

function formatPrice($price) { return '₹ ' . number_format($price); }

$carsByCategory = [];
if (!empty($searchedCars)) {
    foreach ($searchedCars as $car) {
        $category = isset($car['category']) ? ucfirst($car['category']) : 'Other';
        if (!isset($carsByCategory[$category])) $carsByCategory[$category] = [];
        $carsByCategory[$category][] = $car;
    }
}
?>