<?php
session_start();
require_once '../config/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

// Only participants can submit projects
if ($_SESSION['role'] !== 'participant') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Only participants can submit projects']);
    exit();
}

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'submit':
            submitProject();
            break;
        case 'list':
            listUserProjects();
            break;
        case 'update':
            updateProject();
            break;
        case 'delete':
            deleteProject();
            break;
        case 'hackathons':
            getHackathonsForProjects();
            break;
        case 'participants':
            getParticipantsForAutocomplete();
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

function submitProject() {
    global $pdo;
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
        return;
    }
    
    // Get form data from $_POST since we're using multipart/form-data
    $title = trim($_POST['title'] ?? '');
    $leader_name = trim($_POST['leader_name'] ?? '');
    $team_size = intval($_POST['team_size'] ?? 1);
    $hackathon_id = intval($_POST['hackathon_id'] ?? 0);
    
    // Validate required fields
    if (empty($title) || empty($leader_name) || empty($hackathon_id)) {
        echo json_encode(['success' => false, 'error' => 'Title, leader name, and hackathon are required']);
        return;
    }
    
    $project_file_path = null;
    
    // Handle file upload if provided
    if (isset($_FILES['project_file']) && $_FILES['project_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/projects/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['project_file']['name'], PATHINFO_EXTENSION));
        
        // Validate file type - only PDF allowed
        if ($file_extension !== 'pdf') {
            echo json_encode(['success' => false, 'error' => 'Only PDF files are allowed']);
            return;
        }
        
        $file_name = uniqid() . '_' . time() . '.pdf';
        $file_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['project_file']['tmp_name'], $file_path)) {
            $project_file_path = 'uploads/projects/' . $file_name;
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to upload file']);
            return;
        }
    }
    
    // Verify hackathon exists
    $stmt = $pdo->prepare("SELECT id FROM hackathons WHERE id = ?");
    $stmt->execute([$hackathon_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Invalid hackathon selected']);
        return;
    }
    
    // Insert project
    $stmt = $pdo->prepare("
        INSERT INTO projects (title, leader_name, project_file_path, team_size, hackathon_id) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    if ($stmt->execute([$title, $leader_name, $project_file_path, $team_size, $hackathon_id])) {
        echo json_encode([
            'success' => true, 
            'message' => 'Project submitted successfully',
            'project_id' => $pdo->lastInsertId()
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to submit project']);
    }
}

function listUserProjects() {
    global $pdo;
    
    $user_id = $_SESSION['user_id'];
    
    // Get projects where user is the leader (based on leader_name matching user's full_name)
    $stmt = $pdo->prepare("
        SELECT p.*, h.name as hackathon_name, h.start_date, h.end_date
        FROM projects p
        JOIN hackathons h ON p.hackathon_id = h.id
        WHERE p.leader_name = (
            SELECT full_name FROM participants WHERE id = ?
        )
        ORDER BY p.created_at DESC
    ");
    
    $stmt->execute([$user_id]);
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'projects' => $projects]);
}

function updateProject() {
    global $pdo;
    
    if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $project_id = intval($input['project_id'] ?? 0);
    
    if (!$project_id) {
        echo json_encode(['success' => false, 'error' => 'Project ID is required']);
        return;
    }
    
    // Verify project belongs to user
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("
        SELECT id FROM projects 
        WHERE id = ? AND leader_name = (
            SELECT full_name FROM participants WHERE id = ?
        )
    ");
    $stmt->execute([$project_id, $user_id]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Project not found or access denied']);
        return;
    }
    
    // Update project
    $update_fields = [];
    $values = [];
    
    if (isset($input['title'])) {
        $update_fields[] = "title = ?";
        $values[] = trim($input['title']);
    }
    
    if (isset($input['leader_name'])) {
        $update_fields[] = "leader_name = ?";
        $values[] = trim($input['leader_name']);
    }
    
    if (isset($input['team_size'])) {
        $update_fields[] = "team_size = ?";
        $values[] = intval($input['team_size']);
    }
    
    if (isset($input['hackathon_id'])) {
        $update_fields[] = "hackathon_id = ?";
        $values[] = intval($input['hackathon_id']);
    }
    
    if (empty($update_fields)) {
        echo json_encode(['success' => false, 'error' => 'No fields to update']);
        return;
    }
    
    $values[] = $project_id;
    $sql = "UPDATE projects SET " . implode(', ', $update_fields) . " WHERE id = ?";
    
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute($values)) {
        echo json_encode(['success' => true, 'message' => 'Project updated successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update project']);
    }
}

function deleteProject() {
    global $pdo;
    
    if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $project_id = intval($input['project_id'] ?? 0);
    
    if (!$project_id) {
        echo json_encode(['success' => false, 'error' => 'Project ID is required']);
        return;
    }
    
    // Verify project belongs to user
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("
        SELECT id, project_file_path FROM projects 
        WHERE id = ? AND leader_name = (
            SELECT full_name FROM participants WHERE id = ?
        )
    ");
    $stmt->execute([$project_id, $user_id]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$project) {
        echo json_encode(['success' => false, 'error' => 'Project not found or access denied']);
        return;
    }
    
    // Delete project file if exists
    if ($project['project_file_path'] && file_exists('../' . $project['project_file_path'])) {
        unlink('../' . $project['project_file_path']);
    }
    
    // Delete project
    $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
    if ($stmt->execute([$project_id])) {
        echo json_encode(['success' => true, 'message' => 'Project deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to delete project']);
    }
}

function getHackathonsForProjects() {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT id, name, start_date, end_date, location
        FROM hackathons 
        WHERE end_date >= CURDATE()
        ORDER BY start_date ASC
    ");
    
    $stmt->execute();
    $hackathons = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'hackathons' => $hackathons]);
}

function getParticipantsForAutocomplete() {
    global $pdo;
    
    $search = $_GET['search'] ?? '';
    
    $stmt = $pdo->prepare("
        SELECT id, full_name, username, email
        FROM participants 
        WHERE full_name LIKE ? OR username LIKE ? OR email LIKE ?
        ORDER BY full_name ASC
        LIMIT 20
    ");
    
    $searchTerm = '%' . $search . '%';
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
    $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'participants' => $participants]);
}
?>
