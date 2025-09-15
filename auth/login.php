<?php
include '../includes/header.php';

// Check if user is already logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['username'])) {
    header('Location: ../pages/home.php');
    exit();
}

// Import and run database setup
require_once '../config/db_setup.php';
setupDatabase();

// Connect to the database for login
require_once '../config/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        try {
            $tables = [
                'participants' => 'participant',
                'organizers'   => 'Organizer',
                'judges'       => 'Judge'
            ];
            
            $user = null;
            $role = null;

            foreach ($tables as $table => $roleName) {
                $stmt = $pdo->prepare("SELECT * FROM $table WHERE username = ?");
                $stmt->execute([$username]);
                $userData = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($userData && password_verify($password, $userData['password'])) {
                    $user = $userData;
                    $role = $roleName;
                    break;
                }
            }

            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $role;

                // تحويل بناءً على نوع الحساب
                if ($role === 'participant') {
                    header('Location: ../pages/home.php');
                } elseif ($role === 'Organizer') {
                    header('Location: ../pages/home.php');
                } elseif ($role === 'Judge') {
                    header('Location: ../pages/home.php');
                }
                exit();
            } else {
                $error = 'Invalid username or password';
            }
        } catch(PDOException $e) {
            $error = 'Login error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../assets/css/login.css">
</head>
<body>
    <div class="container">
        <div class="left-section">
            <img src="../assets/images/teamphoto.png" alt="Login Illustration" class="illustration">
        </div>
        
        <div class="right-section">
            <h1 class="form-title">Log in!</h1>
            
            
            <?php if ($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">User Name</label>
                    <input type="text" id="username" name="username" 
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-field">
                        <input type="password" id="password" name="password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword()">👁️</button>
                    </div>
                </div>
             <p class="form-subtitle">Don't have an account? <a href="signup.php">Sign Up</a></p>   
                <button type="submit" class="submit-btn">Login</button>
            </form>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const toggleBtn = document.querySelector('.password-toggle');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleBtn.textContent = '🙈';
            } else {
                passwordField.type = 'password';
                toggleBtn.textContent = '👁️';
            }
        }
    </script>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>
