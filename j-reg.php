<?php
// Import and run database setup
require_once 'db_setup.php';
setupDatabase();

// Connect to the database for registration
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $professional_title = trim($_POST['professional_title']);
    
    if (empty($full_name) || empty($username) || empty($email) || empty($password) || empty($professional_title)) {
        $error = 'Please fill in all required fields';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM judges WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $error = 'Username already exists';
            } else {
                $stmt = $pdo->prepare("SELECT id FROM judges WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $error = 'Email already exists';
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    $stmt = $pdo->prepare("INSERT INTO judges (full_name, username, email, password, professional_title) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$full_name, $username, $email, $hashed_password, $professional_title]);
                    
                    $success = 'Registration successful! You can now login.';
                    $_POST = array();
                }
            }
        } catch(PDOException $e) {
            $error = 'Registration error: ' . $e->getMessage();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Your Account - Judge</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
     <?php include 'header.php'; ?>
    <div class="container">
        <div class="left-section">
            <img src="teamphoto.png" alt="Registration Illustration" class="illustration register-image">
        </div>
        
        <div class="right-section">
            <h1 class="form-title">Create Your Account</h1>
            
            
            <?php if ($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <input type="text" id="full_name" name="full_name" placeholder="Full Name" value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <input type="text" id="username" name="username" placeholder="User Name" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <input type="email" id="email" name="email" placeholder="Email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <div class="password-field">
                        <input type="password" id="password" name="password" placeholder="Password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword()">👁️</button>
                    </div>
                </div>
                
                <div class="form-group">
                   <input type="text" id="professional_title" name="professional_title" placeholder="Professional Title"
                   value="<?php echo isset($_POST['professional_title']) ? htmlspecialchars($_POST['professional_title']) : '';?>" required>
            </div>
            <p class="form-subtitle">Already have an account? <a href="login.php">Login</a></p>
                <button type="submit" class="submit-btn">Register</button>
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
    
    <?php include 'footer.php'; ?>
</body>
</html>
