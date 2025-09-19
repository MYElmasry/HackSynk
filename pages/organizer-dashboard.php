<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

// Check if user is an organizer
// if ($_SESSION['role'] !== 'Organizer') {
//     // Redirect to appropriate dashboard based on role
//     $user_role = $_SESSION['role'] ?? 'admin';
//     if ($user_role === 'admin') {
//         header('Location: dashboard.php');
//     } elseif ($user_role === 'participant') {
//         header('Location: participant-dashboard.php');
//     } elseif ($user_role === 'Judge') {
//         header('Location: judge-dashboard.php');
//     } else {
//         header('Location: ../pages/home.php');
//     }
//     exit();
// }

// Get user info
$user_name = $_SESSION['full_name'] ?? 'User';
$user_role = $_SESSION['role'] ?? 'Organizer';
$initial = strtoupper(substr($user_name, 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HackSynk - Organizer Dashboard</title>
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
            <!-- Organizer Sidebar -->
            <?php include '../includes/organizer-sidebar.php'; ?>

            <!-- Main Content -->
            <main class="main-content">
                <div class="content-header">
                    <h1>Organizer Dashboard</h1>
                    <p>Welcome back, <?php echo $user_name; ?>! Manage your hackathons and events.</p>
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

                    <!-- Dashboard Overview -->
                    <div id="dashboard-overview" class="dashboard-overview" style="display: none;">
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div class="stat-content">
                                    <h3>5</h3>
                                    <p>Active Hackathons</p>
                                </div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="stat-content">
                                    <h3>127</h3>
                                    <p>Total Participants</p>
                                </div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-trophy"></i>
                                </div>
                                <div class="stat-content">
                                    <h3>23</h3>
                                    <p>Completed Events</p>
                                </div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-star"></i>
                                </div>
                                <div class="stat-content">
                                    <h3>4.8</h3>
                                    <p>Average Rating</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Create/Manage Hackathons -->
                    <div id="hackathons-section" class="section" style="display: none;">
                        <div class="section-header">
                            <h2>Create/Manage Hackathons</h2>
                        </div>
                        
                        <div class="placeholder-content">
                            <div class="placeholder-icon">
                                <i class="fas fa-calendar-plus"></i>
                            </div>
                            <h3>Coming Soon</h3>
                            <p>Hackathon creation and management interface will be implemented here.</p>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div id="quick-actions-section" class="section" style="display: none;">
                        <div class="section-header">
                            <h2>Quick Actions</h2>
                        </div>
                        
                        <div class="quick-actions-grid">
                            <div class="action-card" onclick="createHackathon()">
                                <div class="action-icon">
                                    <i class="fas fa-plus-circle"></i>
                                </div>
                                <h3>Create Hackathon</h3>
                                <p>Start a new hackathon event</p>
                            </div>
                            
                            <div class="action-card" onclick="manageHackathons()">
                                <div class="action-icon">
                                    <i class="fas fa-list"></i>
                                </div>
                                <h3>Manage Hackathons</h3>
                                <p>View and manage your events</p>
                            </div>
                            
                            <div class="action-card" onclick="assignJudges()">
                                <div class="action-icon">
                                    <i class="fas fa-gavel"></i>
                                </div>
                                <h3>Assign Judges</h3>
                                <p>Assign judges to hackathons</p>
                            </div>
                            
                            <div class="action-card" onclick="viewProfile()">
                                <div class="action-icon">
                                    <i class="fas fa-user"></i>
                                </div>
                                <h3>Update Profile</h3>
                                <p>Manage your information</p>
                            </div>
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
                
                <!-- Organizer Specific Fields -->
                <div class="form-group">
                    <label for="profile_organization_name">Organization Name</label>
                    <input type="text" id="profile_organization_name" name="organization_name">
                </div>
                
                <div class="form-group">
                    <label for="profile_job_title_position">Job Title/Position</label>
                    <input type="text" id="profile_job_title_position" name="job_title_position">
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

    <script src="../assets/js/organizer-dashboard.js"></script>
</body>
</html>
