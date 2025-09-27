<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once '../config/config.php';

class ChatAPI {
    private $pdo;
    
    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }
    
    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? '';
        
        try {
            switch ($action) {
                case 'get_participants':
                    $this->getParticipants();
                    break;
                case 'get_conversations':
                    $this->getConversations();
                    break;
                case 'get_messages':
                    $this->getMessages();
                    break;
                case 'send_message':
                    $this->sendMessage();
                    break;
                case 'create_conversation':
                    $this->createConversation();
                    break;
                case 'mark_messages_read':
                    $this->markMessagesRead();
                    break;
                default:
                    $this->sendResponse(['error' => 'Invalid action'], 400);
            }
        } catch (Exception $e) {
            $this->sendResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    private function getParticipants() {
        $current_user_id = $_GET['current_user_id'] ?? null;
        
        if (!$current_user_id) {
            $this->sendResponse(['error' => 'Missing required parameters'], 400);
            return;
        }
        
        // Get all participants, excluding current user
        $sql = "SELECT p.id, p.full_name, p.username, p.email, p.city_country, p.skills_expertise
                FROM participants p
                WHERE p.id != ?
                ORDER BY p.full_name ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$current_user_id]);
        $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->sendResponse(['participants' => $participants]);
    }
    
    private function getConversations() {
        $user_id = $_GET['user_id'] ?? null;
        
        if (!$user_id) {
            $this->sendResponse(['error' => 'Missing required parameters'], 400);
            return;
        }
        
        // Get all conversations for the user
        $sql = "SELECT c.id as conversation_id, c.created_at, c.updated_at,
                       CASE 
                           WHEN c.participant1_id = ? THEN p2.id
                           ELSE p1.id
                       END as other_participant_id,
                       CASE 
                           WHEN c.participant1_id = ? THEN p2.full_name
                           ELSE p1.full_name
                       END as other_participant_name,
                       CASE 
                           WHEN c.participant1_id = ? THEN p2.username
                           ELSE p1.username
                       END as other_participant_username,
                       (SELECT COUNT(*) FROM messages m WHERE m.conversation_id = c.id AND m.sender_id != ? AND m.is_read = FALSE) as unread_count,
                       (SELECT m.message FROM messages m WHERE m.conversation_id = c.id ORDER BY m.created_at DESC LIMIT 1) as last_message,
                       (SELECT m.created_at FROM messages m WHERE m.conversation_id = c.id ORDER BY m.created_at DESC LIMIT 1) as last_message_time
                FROM conversations c
                JOIN participants p1 ON c.participant1_id = p1.id
                JOIN participants p2 ON c.participant2_id = p2.id
                WHERE (c.participant1_id = ? OR c.participant2_id = ?)
                ORDER BY c.updated_at DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id, $user_id]);
        $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->sendResponse(['conversations' => $conversations]);
    }
    
    private function getMessages() {
        $conversation_id = $_GET['conversation_id'] ?? null;
        $user_id = $_GET['user_id'] ?? null;
        
        if (!$conversation_id || !$user_id) {
            $this->sendResponse(['error' => 'Missing required parameters'], 400);
            return;
        }
        
        // Verify user has access to this conversation
        $verifySql = "SELECT id FROM conversations WHERE id = ? AND (participant1_id = ? OR participant2_id = ?)";
        $verifyStmt = $this->pdo->prepare($verifySql);
        $verifyStmt->execute([$conversation_id, $user_id, $user_id]);
        
        if (!$verifyStmt->fetch()) {
            $this->sendResponse(['error' => 'Access denied'], 403);
            return;
        }
        
        // Get messages for the conversation
        $sql = "SELECT m.id, m.message, m.message_type, m.file_path, m.is_read, m.created_at, m.sender_id,
                       p.full_name as sender_name, p.username as sender_username
                FROM messages m
                JOIN participants p ON m.sender_id = p.id
                WHERE m.conversation_id = ?
                ORDER BY m.created_at ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$conversation_id]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->sendResponse(['messages' => $messages]);
    }
    
    private function sendMessage() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $conversation_id = $data['conversation_id'] ?? null;
        $sender_id = $data['sender_id'] ?? null;
        $message = $data['message'] ?? '';
        $message_type = $data['message_type'] ?? 'text';
        $file_path = $data['file_path'] ?? null;
        
        if (!$conversation_id || !$sender_id || empty($message)) {
            $this->sendResponse(['error' => 'Missing required parameters'], 400);
            return;
        }
        
        // Verify user has access to this conversation
        $verifySql = "SELECT id FROM conversations WHERE id = ? AND (participant1_id = ? OR participant2_id = ?)";
        $verifyStmt = $this->pdo->prepare($verifySql);
        $verifyStmt->execute([$conversation_id, $sender_id, $sender_id]);
        
        if (!$verifyStmt->fetch()) {
            $this->sendResponse(['error' => 'Access denied'], 403);
            return;
        }
        
        // Insert message
        $sql = "INSERT INTO messages (conversation_id, sender_id, message, message_type, file_path) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$conversation_id, $sender_id, $message, $message_type, $file_path]);
        
        $message_id = $this->pdo->lastInsertId();
        
        // Update conversation timestamp
        $updateSql = "UPDATE conversations SET updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        $updateStmt = $this->pdo->prepare($updateSql);
        $updateStmt->execute([$conversation_id]);
        
        $this->sendResponse(['message_id' => $message_id, 'success' => true]);
    }
    
    private function createConversation() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $participant1_id = $data['participant1_id'] ?? null;
        $participant2_id = $data['participant2_id'] ?? null;
        
        if (!$participant1_id || !$participant2_id) {
            $this->sendResponse(['error' => 'Missing required parameters'], 400);
            return;
        }
        
        // Check if conversation already exists
        $checkSql = "SELECT id FROM conversations 
                     WHERE ((participant1_id = ? AND participant2_id = ?) OR 
                            (participant1_id = ? AND participant2_id = ?))";
        $checkStmt = $this->pdo->prepare($checkSql);
        $checkStmt->execute([$participant1_id, $participant2_id, $participant2_id, $participant1_id]);
        $existing = $checkStmt->fetch();
        
        if ($existing) {
            $this->sendResponse(['conversation_id' => $existing['id'], 'success' => true]);
            return;
        }
        
        // Create new conversation
        $sql = "INSERT INTO conversations (participant1_id, participant2_id) 
                VALUES (?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$participant1_id, $participant2_id]);
        
        $conversation_id = $this->pdo->lastInsertId();
        
        $this->sendResponse(['conversation_id' => $conversation_id, 'success' => true]);
    }
    
    private function markMessagesRead() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $conversation_id = $data['conversation_id'] ?? null;
        $user_id = $data['user_id'] ?? null;
        
        if (!$conversation_id || !$user_id) {
            $this->sendResponse(['error' => 'Missing required parameters'], 400);
            return;
        }
        
        // Mark all messages in conversation as read (except user's own messages)
        $sql = "UPDATE messages SET is_read = TRUE 
                WHERE conversation_id = ? AND sender_id != ? AND is_read = FALSE";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$conversation_id, $user_id]);
        
        $this->sendResponse(['success' => true]);
    }
    
    
    private function sendResponse($data, $status_code = 200) {
        http_response_code($status_code);
        echo json_encode($data);
        exit;
    }
}

$chatAPI = new ChatAPI();
$chatAPI->handleRequest();
?>
