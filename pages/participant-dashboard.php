<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

if ($_SESSION['role'] !== 'participant') {
    // Redirect to appropriate dashboard based on role
    $user_role = $_SESSION['role'] ?? 'admin';
    if ($user_role === 'admin') {
        header('Location: dashboard.php');
    } elseif ($user_role === 'organizer') {
        header('Location: organizer-dashboard.php');
    } elseif ($user_role === 'Judge') {
        header('Location: judge-dashboard.php');
    } else {
        header('Location: ../pages/home.php');
    }
    exit();
}

// Get user info
$user_name = $_SESSION['full_name'] ?? 'User';
$user_role = $_SESSION['role'] ?? 'Participant';
$initial = strtoupper(substr($user_name, 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HackSynk - Participant Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style1.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="dashboard-page">
    <div class="wrapper">
        <!-- Header -->
        <?php include '../includes/header.php'; ?>
        
        <div class="dashboard-container">
            <!-- Participant Sidebar -->
            <?php include '../includes/participant-sidebar.php'; ?>

            <!-- Main Content -->
            <main class="main-content">
                <div class="content-header">
                    <h1>Participant Dashboard</h1>
                    <p>Welcome back, <?php echo $user_name; ?>! Manage your hackathon participation.</p>
                </div>
                
                <div class="content-body">
                    <!-- Profile Section -->
                    <div id="profile-section" class="profile-section">
                        <div class="section-header">
                            <h2>Profile</h2>
                        </div>
                        
                        <div class="profile-content">
                            <div class="profile-info">
                                <div class="profile-avatar">
                                    <span><?php echo $initial; ?></span>
                                </div>
                                <div class="profile-details">
                                    <h3><?php echo $user_name; ?></h3>
                                    <p class="profile-role"><?php echo ucfirst($user_role); ?></p>
                                    <p class="profile-email"><?php echo $_SESSION['email'] ?? 'No email available'; ?></p>
                                </div>
                            </div>
                            
                            <div class="profile-actions">
                                <button class="btn btn-primary" onclick="openEditProfileModal()">Edit Profile</button>
                            </div>
                        </div>
                    </div>

                    <!-- Create Team Section -->
                    <div id="create-team-section" class="section" style="display: none;">
                        <div class="section-header">
                            <h2>Create Team</h2>
                        </div>
                        <div class="empty-state">
                            <i class="fas fa-users"></i>
                            <h3>Create Team</h3>
                            <p>This section is coming soon.</p>
                        </div>
                    </div>

                    <!-- Join Team Section -->
                    <div id="join-team-section" class="section" style="display: none;">
                        <div class="section-header">
                            <h2>Join Team</h2>
                        </div>
                        <div class="empty-state">
                            <i class="fas fa-user-plus"></i>
                            <h3>Join Team</h3>
                            <p>This section is coming soon.</p>
                        </div>
                    </div>

                    <!-- Submit Project Section -->
                    <div id="submit-project-section" class="section" style="display: none;">
                        <div class="section-header">
                            <h2>Submit Project</h2>
                        </div>
                        <div class="empty-state">
                            <i class="fas fa-upload"></i>
                            <h3>Submit Project</h3>
                            <p>This section is coming soon.</p>
                        </div>
                    </div>

                    <!-- Chat Messages Section -->
                    <div id="chat-messages-section" class="section" style="display: none;">
                        <div class="section-header">
                            <h2>Send Chat Message</h2>
                        </div>
                        <div class="empty-state">
                            <i class="fas fa-comments"></i>
                            <h3>Send Chat Message</h3>
                            <p>This section is coming soon.</p>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div id="editProfileModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Profile</h3>
                <span class="close" onclick="closeModal('editProfileModal')">&times;</span>
            </div>
            <form id="editProfileForm">
                <div class="form-group">
                    <label for="profile_full_name">Full Name *</label>
                    <input type="text" id="profile_full_name" name="full_name" required>
                </div>
                
                <div class="form-group">
                    <label for="profile_email">Email *</label>
                    <input type="email" id="profile_email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="profile_username">Username *</label>
                    <input type="text" id="profile_username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="profile_password">New Password (leave blank to keep current)</label>
                    <input type="password" id="profile_password" name="password">
                </div>
                
                <div class="form-group">
                    <label for="profile_confirm_password">Confirm New Password</label>
                    <input type="password" id="profile_confirm_password" name="confirm_password">
                </div>
                
                <!-- Participant Specific Fields -->
                <div class="form-group">
                    <label for="profile_skills">Skills</label>
                    <input type="text" id="profile_skills" name="skills" placeholder="e.g., JavaScript, Python, React">
                </div>
                
                <div class="form-group">
                    <label for="profile_experience">Experience Level</label>
                    <select id="profile_experience" name="experience">
                        <option value="beginner">Beginner</option>
                        <option value="intermediate">Intermediate</option>
                        <option value="advanced">Advanced</option>
                        <option value="expert">Expert</option>
                    </select>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editProfileModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </div>
            </form>
        </div>
    </div>


    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>

    <script src="../assets/js/participant-dashboard.js"></script>
</body>
</html>
