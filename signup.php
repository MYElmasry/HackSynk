<?php
// Import and run database setup
require_once 'db_setup.php';
setupDatabase();

// Connect to the database
require_once 'config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose Your Account Type - HackSynk</title>
    <link rel="stylesheet" href="login.css">
    <style>
        .account-type-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
            max-width: 600px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        .account-type-card {
            background: white;
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .account-type-card:hover {
            border-color: #007bff;
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,123,255,0.2);
        }
        
        .account-type-card h3 {
            color: #333;
            margin-bottom: 10px;
            font-size: 24px;
        }
        
        .account-type-card p {
            color: #666;
            margin-bottom: 20px;
            line-height: 1.5;
        }
        
        .account-type-card .icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        .back-link {
            text-align: center;
            margin-top: 30px;
        }
        
        .back-link a {
            color: #007bff;
            text-decoration: none;
            font-weight: 500;
        }
        
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <div class="left-section">
            <img src="teamphoto.png" alt="Sign Up Illustration" class="illustration">
        </div>
        
        <div class="right-section">
            <h1 class="form-title">Create Your Account</h1>
            <p style="text-align: center; color: #666; margin-bottom: 30px;">Choose your account type to get started</p>
            
            <div class="account-type-container">
                <div class="account-type-card" onclick="window.location.href='p-reg.php'">
                    <div class="icon">👨‍💻</div>
                    <h3>Participant</h3>
                    <p>Join hackathons, collaborate with teams, and showcase your skills. Perfect for developers, designers, and innovators.</p>
                </div>
                
                <div class="account-type-card" onclick="window.location.href='o-reg.php'">
                    <div class="icon">🏢</div>
                    <h3>Organizer</h3>
                    <p>Create and manage hackathons, set up events, and connect with talented participants. For companies and organizations.</p>
                </div>
                
                <div class="account-type-card" onclick="window.location.href='j-reg.php'">
                    <div class="icon">⚖️</div>
                    <h3>Judge</h3>
                    <p>Evaluate projects, provide feedback, and help select winners. For industry experts and professionals.</p>
                </div>
            </div>
            
            <div class="back-link">
                <a href="login.php">← Back to Login</a>
            </div>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>
