<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require_once '../config/config.php';

// Set content type for JSON responses
header('Content-Type: application/json');

// Get HTTP method
$method = $_SERVER['REQUEST_METHOD'];

// Get action from URL parameter or default to fetch
$action = $_GET['action'] ?? 'fetch';

// Route the request based on method and action
switch ($method) {
    case 'GET':
        if ($action === 'fetch') {
            fetchUsers();
        } elseif ($action === 'profile') {
            fetchProfile();
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
        }
        break;
        
    case 'POST':
        if ($action === 'add') {
            addUser();
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
        }
        break;
        
    case 'PUT':
        if ($action === 'edit') {
            editUser();
        } elseif ($action === 'profile') {
            updateProfile();
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
        }
        break;
        
    case 'DELETE':
        if ($action === 'delete') {
            deleteUser();
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
        break;
}

/**
 * Fetch all users from participants, organizers, and judges tables
 */
function fetchUsers() {
    global $pdo;
    
    try {
        $users = [];
        
        // Fetch participants
        $stmt = $pdo->prepare("SELECT id, full_name, email, 'participant' as role, city_country, skills_expertise, created_at FROM participants ORDER BY created_at DESC");
        $stmt->execute();
        $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $users = array_merge($users, $participants);
        
        // Fetch organizers
        $stmt = $pdo->prepare("SELECT id, full_name, email, 'organizer' as role, organization_name, job_title_position, created_at FROM organizers ORDER BY created_at DESC");
        $stmt->execute();
        $organizers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $users = array_merge($users, $organizers);
        
        // Fetch judges
        $stmt = $pdo->prepare("SELECT id, full_name, email, 'judge' as role, professional_title, created_at FROM judges ORDER BY created_at DESC");
        $stmt->execute();
        $judges = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $users = array_merge($users, $judges);
        
        // Sort all users by full name
        usort($users, function($a, $b) {
            return strcmp($a['full_name'], $b['full_name']);
        });
        
        echo json_encode([
            'success' => true,
            'users' => $users,
            'total' => count($users)
        ]);
        
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Database error: ' . $e->getMessage()
        ]);
    }
}

/**
 * Add a new user to the specified table
 */
function addUser() {
    global $pdo;
    
    try {
        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid JSON input']);
            return;
        }
        
        $full_name = $input['full_name'] ?? '';
        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';
        $role = $input['role'] ?? '';
        
        // Validate required fields
        if (empty($full_name) || empty($email) || empty($password) || empty($role)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'All fields are required']);
            return;
        }
        
        // Validate role
        if (!in_array($role, ['participant', 'organizer', 'judge'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid role']);
            return;
        }
        
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Generate username from email
        $username = explode('@', $email)[0];
        
        // Determine table and additional fields based on role
        $table = $role . 's';
        $additional_fields = '';
        $additional_values = '';
        
        if ($role === 'organizer') {
            $additional_fields = ', organization_name, job_title_position';
            $additional_values = ', :organization_name, :job_title_position';
        } elseif ($role === 'judge') {
            $additional_fields = ', professional_title';
            $additional_values = ', :professional_title';
        } elseif ($role === 'participant') {
            $additional_fields = ', city_country, skills_expertise';
            $additional_values = ', :city_country, :skills_expertise';
        }
        
        // Insert user
        $sql = "INSERT INTO {$table} (full_name, username, email, password{$additional_fields}) 
                VALUES (:full_name, :username, :email, :password{$additional_values})";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':full_name', $full_name);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashed_password);
        
        // Bind additional fields if needed
        if ($role === 'organizer') {
            $organization_name = $input['organization_name'] ?? '';
            $job_title_position = $input['job_title_position'] ?? '';
            $stmt->bindParam(':organization_name', $organization_name);
            $stmt->bindParam(':job_title_position', $job_title_position);
        } elseif ($role === 'judge') {
            $professional_title = $input['professional_title'] ?? '';
            $stmt->bindParam(':professional_title', $professional_title);
        } elseif ($role === 'participant') {
            $city_country = $input['city_country'] ?? '';
            $skills_expertise = $input['skills_expertise'] ?? '';
            $stmt->bindParam(':city_country', $city_country);
            $stmt->bindParam(':skills_expertise', $skills_expertise);
        }
        
        $stmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => 'User added successfully',
            'user_id' => $pdo->lastInsertId()
        ]);
        
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Database error: ' . $e->getMessage()
        ]);
    }
}

/**
 * Edit an existing user
 */
function editUser() {
    global $pdo;
    
    try {
        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid JSON input']);
            return;
        }
        
        $user_id = $input['user_id'] ?? '';
        $role = $input['role'] ?? '';
        $full_name = $input['full_name'] ?? '';
        $email = $input['email'] ?? '';
        
        // Validate required fields
        if (empty($user_id) || empty($role) || empty($full_name) || empty($email)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'User ID, role, full name, and email are required']);
            return;
        }
        
        // Validate role
        if (!in_array($role, ['participant', 'organizer', 'judge'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid role']);
            return;
        }
        
        $table = $role . 's';
        
        // Build update query
        $sql = "UPDATE {$table} SET full_name = :full_name, email = :email";
        $params = [
            ':full_name' => $full_name,
            ':email' => $email,
            ':user_id' => $user_id
        ];
        
        // Add password update if provided
        if (!empty($input['password'])) {
            $sql .= ", password = :password";
            $params[':password'] = password_hash($input['password'], PASSWORD_DEFAULT);
        }
        
        // Add role-specific fields
        if ($role === 'organizer') {
            if (isset($input['organization_name'])) {
                $sql .= ", organization_name = :organization_name";
                $params[':organization_name'] = $input['organization_name'];
            }
            if (isset($input['job_title_position'])) {
                $sql .= ", job_title_position = :job_title_position";
                $params[':job_title_position'] = $input['job_title_position'];
            }
        } elseif ($role === 'judge') {
            if (isset($input['professional_title'])) {
                $sql .= ", professional_title = :professional_title";
                $params[':professional_title'] = $input['professional_title'];
            }
        } elseif ($role === 'participant') {
            if (isset($input['city_country'])) {
                $sql .= ", city_country = :city_country";
                $params[':city_country'] = $input['city_country'];
            }
            if (isset($input['skills_expertise'])) {
                $sql .= ", skills_expertise = :skills_expertise";
                $params[':skills_expertise'] = $input['skills_expertise'];
            }
        }
        
        $sql .= " WHERE id = :user_id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'User updated successfully'
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'User not found'
            ]);
        }
        
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Database error: ' . $e->getMessage()
        ]);
    }
}

/**
 * Delete a user from the specified table
 */
function deleteUser() {
    global $pdo;
    
    try {
        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid JSON input']);
            return;
        }
        
        $user_id = $input['user_id'] ?? '';
        $role = $input['role'] ?? '';
        
        // Validate required fields
        if (empty($user_id) || empty($role)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'User ID and role are required']);
            return;
        }
        
        // Validate role
        if (!in_array($role, ['participant', 'organizer', 'judge'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid role']);
            return;
        }
        
        $table = $role . 's';
        
        // Delete user
        $sql = "DELETE FROM {$table} WHERE id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'User not found'
            ]);
        }
        
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Database error: ' . $e->getMessage()
        ]);
    }
}

/**
 * Fetch current admin profile data
 */
function fetchProfile() {
    global $pdo;
    
    try {
        $user_id = $_SESSION['user_id'];
        $user_role = $_SESSION['role'] ?? 'admin';
        
        // Determine table based on user role
        $table = '';
        $fields = 'id, full_name, username, email';
        
        switch ($user_role) {
            case 'admin':
                $table = 'admins';
                break;
            case 'Organizer':
                $table = 'organizers';
                $fields .= ', organization_name, job_title_position';
                break;
            case 'participant':
                $table = 'participants';
                $fields .= ', city_country, skills_expertise';
                break;
            case 'Judge':
                $table = 'judges';
                $fields .= ', professional_title';
                break;
            default:
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid user role']);
                return;
        }
        
        // Fetch profile data
        $stmt = $pdo->prepare("SELECT $fields FROM $table WHERE id = :user_id");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($profile) {
            echo json_encode([
                'success' => true,
                'profile' => $profile
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Profile not found'
            ]);
        }
        
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Database error: ' . $e->getMessage()
        ]);
    }
}

/**
 * Update admin profile
 */
function updateProfile() {
    global $pdo;
    
    try {
        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid JSON input']);
            return;
        }
        
        $user_id = $_SESSION['user_id'];
        $user_role = $_SESSION['role'] ?? 'admin';
        $full_name = $input['full_name'] ?? '';
        $email = $input['email'] ?? '';
        $username = $input['username'] ?? '';
        $password = $input['password'] ?? '';
        
        // Validate required fields
        if (empty($full_name) || empty($email) || empty($username)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Full name, email, and username are required']);
            return;
        }
        
        // Validate password if provided
        if (!empty($password)) {
            if (strlen($password) < 6) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Password must be at least 6 characters long']);
                return;
            }
        }
        
        // Determine table based on user role
        $table = '';
        switch ($user_role) {
            case 'admin':
                $table = 'admins';
                break;
            case 'Organizer':
                $table = 'organizers';
                break;
            case 'participant':
                $table = 'participants';
                break;
            case 'Judge':
                $table = 'judges';
                break;
            default:
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid user role']);
                return;
        }
        
        // Check if email or username already exists (excluding current user)
        $checkStmt = $pdo->prepare("SELECT id FROM $table WHERE (email = :email OR username = :username) AND id != :user_id");
        $checkStmt->bindParam(':email', $email);
        $checkStmt->bindParam(':username', $username);
        $checkStmt->bindParam(':user_id', $user_id);
        $checkStmt->execute();
        
        if ($checkStmt->fetch()) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Email or username already exists']);
            return;
        }
        
        // Build update query based on role
        $sql = "UPDATE $table SET full_name = :full_name, email = :email, username = :username";
        $params = [
            ':full_name' => $full_name,
            ':email' => $email,
            ':username' => $username,
            ':user_id' => $user_id
        ];
        
        // Add role-specific fields
        if ($user_role === 'Organizer') {
            $organization_name = $input['organization_name'] ?? '';
            $job_title_position = $input['job_title_position'] ?? '';
            $sql .= ", organization_name = :organization_name, job_title_position = :job_title_position";
            $params[':organization_name'] = $organization_name;
            $params[':job_title_position'] = $job_title_position;
        } elseif ($user_role === 'participant') {
            $city_country = $input['city_country'] ?? '';
            $skills_expertise = $input['skills_expertise'] ?? '';
            $sql .= ", city_country = :city_country, skills_expertise = :skills_expertise";
            $params[':city_country'] = $city_country;
            $params[':skills_expertise'] = $skills_expertise;
        } elseif ($user_role === 'Judge') {
            $professional_title = $input['professional_title'] ?? '';
            $sql .= ", professional_title = :professional_title";
            $params[':professional_title'] = $professional_title;
        }
        
        // Add password update if provided
        if (!empty($password)) {
            $sql .= ", password = :password";
            $params[':password'] = password_hash($password, PASSWORD_DEFAULT);
        }
        
        $sql .= " WHERE id = :user_id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        if ($stmt->rowCount() > 0) {
            // Update session data
            $_SESSION['full_name'] = $full_name;
            $_SESSION['email'] = $email;
            $_SESSION['username'] = $username;
            
            echo json_encode([
                'success' => true,
                'message' => 'Profile updated successfully'
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Profile not found'
            ]);
        }
        
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Database error: ' . $e->getMessage()
        ]);
    }
}
?>
