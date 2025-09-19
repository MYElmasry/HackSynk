<?php
session_start();
require_once '../config/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            listJudges();
            break;
        case 'search':
            searchJudges();
            break;
        case 'assign':
            assignJudgeToHackathon();
            break;
        case 'assignments':
            getJudgeAssignments();
            break;
        case 'remove_assignment':
            removeJudgeAssignment();
            break;
        case 'create':
            createJudge();
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function listJudges() {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT id, full_name, email, professional_title FROM judges ORDER BY full_name");
    $stmt->execute();
    $judges = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $judges]);
}

function searchJudges() {
    global $pdo;
    
    $search = $_GET['q'] ?? '';
    
    if (empty($search)) {
        echo json_encode(['success' => true, 'data' => []]);
        return;
    }
    
    $stmt = $pdo->prepare("SELECT id, full_name, email, professional_title FROM judges WHERE full_name LIKE ? OR email LIKE ? ORDER BY full_name LIMIT 10");
    $searchTerm = "%$search%";
    $stmt->execute([$searchTerm, $searchTerm]);
    $judges = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $judges]);
}

function createJudge() {
    global $pdo;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    $full_name = trim($input['full_name'] ?? '');
    $email = trim($input['email'] ?? '');
    $professional_title = trim($input['professional_title'] ?? '');
    
    if (empty($full_name) || empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Full name and email are required']);
        return;
    }
    
    // Check if judge already exists
    $stmt = $pdo->prepare("SELECT id FROM judges WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Judge with this email already exists']);
        return;
    }
    
    // Generate username from email
    $username = explode('@', $email)[0];
    $original_username = $username;
    $counter = 1;
    
    // Ensure username is unique
    while (true) {
        $stmt = $pdo->prepare("SELECT id FROM judges WHERE username = ?");
        $stmt->execute([$username]);
        if (!$stmt->fetch()) {
            break;
        }
        $username = $original_username . $counter;
        $counter++;
    }
    
    // Create judge with default password
    $password = password_hash('judge123', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO judges (full_name, username, email, password, professional_title) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$full_name, $username, $email, $password, $professional_title]);
    
    $judge_id = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Judge created successfully',
        'data' => [
            'id' => $judge_id,
            'full_name' => $full_name,
            'email' => $email,
            'professional_title' => $professional_title
        ]
    ]);
}

function assignJudgeToHackathon() {
    global $pdo;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    $judge_id = $input['judge_id'] ?? null;
    $hackathon_id = $input['hackathon_id'] ?? null;
    
    if (!$judge_id || !$hackathon_id) {
        echo json_encode(['success' => false, 'message' => 'Judge ID and Hackathon ID are required']);
        return;
    }
    
    // Check if assignment already exists
    $stmt = $pdo->prepare("SELECT id FROM judge_hackathons WHERE judge_id = ? AND hackathon_id = ?");
    $stmt->execute([$judge_id, $hackathon_id]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Judge is already assigned to this hackathon']);
        return;
    }
    
    // Create assignment
    $stmt = $pdo->prepare("INSERT INTO judge_hackathons (judge_id, hackathon_id) VALUES (?, ?)");
    $stmt->execute([$judge_id, $hackathon_id]);
    
    echo json_encode(['success' => true, 'message' => 'Judge assigned successfully']);
}

function getJudgeAssignments() {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT 
            jh.id as assignment_id,
            j.id as judge_id,
            j.full_name as judge_name,
            j.email as judge_email,
            h.id as hackathon_id,
            h.name as hackathon_name,
            jh.assigned_at
        FROM judge_hackathons jh
        JOIN judges j ON jh.judge_id = j.id
        JOIN hackathons h ON jh.hackathon_id = h.id
        ORDER BY jh.assigned_at DESC
    ");
    $stmt->execute();
    $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $assignments]);
}

function removeJudgeAssignment() {
    global $pdo;
    
    $assignment_id = $_POST['assignment_id'] ?? null;
    
    if (!$assignment_id) {
        echo json_encode(['success' => false, 'message' => 'Assignment ID is required']);
        return;
    }
    
    $stmt = $pdo->prepare("DELETE FROM judge_hackathons WHERE id = ?");
    $stmt->execute([$assignment_id]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Assignment removed successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Assignment not found']);
    }
}
?>
