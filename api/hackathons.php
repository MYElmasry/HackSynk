<?php
session_start();
require_once '../config/config.php';
require_once '../config/db_setup.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Setup database (creates tables if they don't exist)
setupDatabase();

// Get the request method and action
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    $pdo = new PDO($dsn, $username, $password, $options);
    
    switch ($action) {
        case 'create':
            if ($method === 'POST') {
                createHackathon($pdo);
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;
            
        case 'list':
            if ($method === 'GET') {
                listHackathons($pdo);
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;
            
        case 'get':
            if ($method === 'GET') {
                getHackathon($pdo);
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;
            
        case 'update':
            if ($method === 'POST') {
                updateHackathon($pdo);
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;
            
        case 'delete':
            if ($method === 'POST') {
                deleteHackathon($pdo);
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            }
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

function createHackathon($pdo) {
    // Validate required fields
    $required_fields = ['name', 'description', 'start_date', 'end_date', 'location'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['success' => false, 'message' => ucfirst($field) . ' is required']);
            return;
        }
    }
    
    // Validate dates
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    
    if (strtotime($start_date) >= strtotime($end_date)) {
        echo json_encode(['success' => false, 'message' => 'End date must be after start date']);
        return;
    }
    
    // Handle image upload
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/hackathons/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array(strtolower($file_extension), $allowed_extensions)) {
            $filename = uniqid() . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image_path = 'uploads/hackathons/' . $filename;
            }
        }
    }
    
    // Insert hackathon
    $sql = "INSERT INTO hackathons (name, description, start_date, end_date, location, rules, prizes, image_path) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        $_POST['name'],
        $_POST['description'],
        $start_date,
        $end_date,
        $_POST['location'],
        $_POST['rules'] ?? null,
        $_POST['prizes'] ?? null,
        $image_path
    ]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Hackathon created successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create hackathon']);
    }
}

function listHackathons($pdo) {
    $sql = "SELECT * FROM hackathons ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $hackathons = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $hackathons]);
}

function getHackathon($pdo) {
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Hackathon ID is required']);
        return;
    }
    
    $sql = "SELECT * FROM hackathons WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $hackathon = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($hackathon) {
        echo json_encode(['success' => true, 'data' => $hackathon]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Hackathon not found']);
    }
}

function updateHackathon($pdo) {
    $id = $_POST['id'] ?? null;
    
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Hackathon ID is required']);
        return;
    }
    
    // Validate required fields
    $required_fields = ['name', 'description', 'start_date', 'end_date', 'location'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['success' => false, 'message' => ucfirst($field) . ' is required']);
            return;
        }
    }
    
    // Validate dates
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    
    if (strtotime($start_date) >= strtotime($end_date)) {
        echo json_encode(['success' => false, 'message' => 'End date must be after start date']);
        return;
    }
    
    // Get current hackathon data
    $sql = "SELECT image_path FROM hackathons WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $current_hackathon = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $image_path = $current_hackathon['image_path'];
    
    // Handle new image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/hackathons/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array(strtolower($file_extension), $allowed_extensions)) {
            $filename = uniqid() . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // Delete old image if it exists
                if ($image_path && file_exists('../' . $image_path)) {
                    unlink('../' . $image_path);
                }
                $image_path = 'uploads/hackathons/' . $filename;
            }
        }
    }
    
    // Update hackathon
    $sql = "UPDATE hackathons SET name = ?, description = ?, start_date = ?, end_date = ?, 
            location = ?, rules = ?, prizes = ?, image_path = ? WHERE id = ?";
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        $_POST['name'],
        $_POST['description'],
        $start_date,
        $end_date,
        $_POST['location'],
        $_POST['rules'] ?? null,
        $_POST['prizes'] ?? null,
        $image_path,
        $id
    ]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Hackathon updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update hackathon']);
    }
}

function deleteHackathon($pdo) {
    $id = $_POST['id'] ?? null;
    
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Hackathon ID is required']);
        return;
    }
    
    // Get hackathon image path before deletion
    $sql = "SELECT image_path FROM hackathons WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $hackathon = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Delete hackathon
    $sql = "DELETE FROM hackathons WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([$id]);
    
    if ($result) {
        // Delete associated image file
        if ($hackathon && $hackathon['image_path'] && file_exists('../' . $hackathon['image_path'])) {
            unlink('../' . $hackathon['image_path']);
        }
        
        echo json_encode(['success' => true, 'message' => 'Hackathon deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete hackathon']);
    }
}
?>
