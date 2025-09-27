<?php
session_start();
require_once '../config/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'create':
            createTeam();
            break;
        case 'join':
            joinTeam();
            break;
        case 'leave':
            leaveTeam();
            break;
        case 'list':
            listTeams();
            break;
        case 'my_teams':
            getMyTeams();
            break;
        case 'hackathon_teams':
            getHackathonTeams();
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

function createTeam() {
    global $pdo;
    
    if ($_SESSION['role'] !== 'participant') {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Only participants can create teams']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    $name = trim($input['name'] ?? '');
    $description = trim($input['description'] ?? '');
    $hackathon_id = (int)($input['hackathon_id'] ?? 0);
    $max_participants = (int)($input['max_participants'] ?? 5);
    
    if (empty($name) || $hackathon_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Team name and hackathon are required']);
        return;
    }
    
    if ($max_participants < 2 || $max_participants > 10) {
        echo json_encode(['success' => false, 'error' => 'Max participants must be between 2 and 10']);
        return;
    }
    
    // Check if hackathon exists
    $stmt = $pdo->prepare("SELECT id FROM hackathons WHERE id = ?");
    $stmt->execute([$hackathon_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Hackathon not found']);
        return;
    }
    
    // Check if user is already in a team for this hackathon
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM team_members tm 
        JOIN teams t ON tm.team_id = t.id 
        WHERE tm.participant_id = ? AND t.hackathon_id = ?
    ");
    $stmt->execute([$_SESSION['user_id'], $hackathon_id]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'error' => 'You are already in a team for this hackathon']);
        return;
    }
    
    // Check if team name already exists for this hackathon
    $stmt = $pdo->prepare("SELECT id FROM teams WHERE name = ? AND hackathon_id = ?");
    $stmt->execute([$name, $hackathon_id]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Team name already exists for this hackathon']);
        return;
    }
    
    $pdo->beginTransaction();
    
    try {
        // Create team
        $stmt = $pdo->prepare("
            INSERT INTO teams (name, description, hackathon_id, max_participants) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$name, $description, $hackathon_id, $max_participants]);
        $team_id = $pdo->lastInsertId();
        
        // Add creator as team member
        $stmt = $pdo->prepare("
            INSERT INTO team_members (team_id, participant_id) 
            VALUES (?, ?)
        ");
        $stmt->execute([$team_id, $_SESSION['user_id']]);
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Team created successfully',
            'team_id' => $team_id
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function joinTeam() {
    global $pdo;
    
    if ($_SESSION['role'] !== 'participant') {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Only participants can join teams']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $team_id = (int)($input['team_id'] ?? 0);
    
    if ($team_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid team ID']);
        return;
    }
    
    // Get team details
    $stmt = $pdo->prepare("
        SELECT t.*, h.name as hackathon_name 
        FROM teams t 
        JOIN hackathons h ON t.hackathon_id = h.id 
        WHERE t.id = ?
    ");
    $stmt->execute([$team_id]);
    $team = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$team) {
        echo json_encode(['success' => false, 'error' => 'Team not found']);
        return;
    }
    
    // Check if user is already in a team for this hackathon
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM team_members tm 
        JOIN teams t ON tm.team_id = t.id 
        WHERE tm.participant_id = ? AND t.hackathon_id = ?
    ");
    $stmt->execute([$_SESSION['user_id'], $team['hackathon_id']]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'error' => 'You are already in a team for this hackathon']);
        return;
    }
    
    // Check if team is full
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM team_members WHERE team_id = ?
    ");
    $stmt->execute([$team_id]);
    $current_members = $stmt->fetchColumn();
    
    if ($current_members >= $team['max_participants']) {
        echo json_encode(['success' => false, 'error' => 'Team is full']);
        return;
    }
    
    // Add user to team
    $stmt = $pdo->prepare("
        INSERT INTO team_members (team_id, participant_id) 
        VALUES (?, ?)
    ");
    $stmt->execute([$team_id, $_SESSION['user_id']]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Successfully joined team',
        'team_name' => $team['name']
    ]);
}

function leaveTeam() {
    global $pdo;
    
    if ($_SESSION['role'] !== 'participant') {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Only participants can leave teams']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $team_id = (int)($input['team_id'] ?? 0);
    
    if ($team_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid team ID']);
        return;
    }
    
    // Check if user is in this team
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM team_members 
        WHERE team_id = ? AND participant_id = ?
    ");
    $stmt->execute([$team_id, $_SESSION['user_id']]);
    
    if ($stmt->fetchColumn() == 0) {
        echo json_encode(['success' => false, 'error' => 'You are not a member of this team']);
        return;
    }
    
    // Remove user from team
    $stmt = $pdo->prepare("
        DELETE FROM team_members 
        WHERE team_id = ? AND participant_id = ?
    ");
    $stmt->execute([$team_id, $_SESSION['user_id']]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Successfully left team'
    ]);
}

function listTeams() {
    global $pdo;
    
    $hackathon_id = (int)($_GET['hackathon_id'] ?? 0);
    
    if ($hackathon_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Hackathon ID is required']);
        return;
    }
    
    // Get teams for hackathon with member count
    $stmt = $pdo->prepare("
        SELECT 
            t.*,
            h.name as hackathon_name,
            COUNT(tm.participant_id) as current_members,
            GROUP_CONCAT(p.full_name) as member_names
        FROM teams t
        LEFT JOIN team_members tm ON t.id = tm.team_id
        LEFT JOIN participants p ON tm.participant_id = p.id
        LEFT JOIN hackathons h ON t.hackathon_id = h.id
        WHERE t.hackathon_id = ?
        GROUP BY t.id
        ORDER BY t.created_at DESC
    ");
    $stmt->execute([$hackathon_id]);
    $teams = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'teams' => $teams
    ]);
}

function getMyTeams() {
    global $pdo;
    
    if ($_SESSION['role'] !== 'participant') {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Only participants can view their teams']);
        return;
    }
    
    // Get teams where user is a member
    $stmt = $pdo->prepare("
        SELECT 
            t.*,
            h.name as hackathon_name,
            h.start_date,
            h.end_date,
            COUNT(tm2.participant_id) as current_members,
            GROUP_CONCAT(p.full_name) as member_names
        FROM teams t
        JOIN team_members tm ON t.id = tm.team_id
        JOIN hackathons h ON t.hackathon_id = h.id
        LEFT JOIN team_members tm2 ON t.id = tm2.team_id
        LEFT JOIN participants p ON tm2.participant_id = p.id
        WHERE tm.participant_id = ?
        GROUP BY t.id
        ORDER BY t.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $teams = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'teams' => $teams
    ]);
}

function getHackathonTeams() {
    global $pdo;
    
    // Get all hackathons for dropdown
    $stmt = $pdo->prepare("
        SELECT id, name, start_date, end_date 
        FROM hackathons 
        WHERE end_date >= CURDATE()
        ORDER BY start_date ASC
    ");
    $stmt->execute();
    $hackathons = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'hackathons' => $hackathons
    ]);
}
?>
