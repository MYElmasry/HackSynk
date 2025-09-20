<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

if ($_SESSION['role'] !== 'Organizer') {
    // Redirect to appropriate dashboard based on role
    $user_role = $_SESSION['role'] ?? 'admin';
    if ($user_role === 'admin') {
        header('Location: dashboard.php');
    } elseif ($user_role === 'participant') {
        header('Location: participant-dashboard.php');
    } elseif ($user_role === 'Judge') {
        header('Location: judge-dashboard.php');
    } else {
        header('Location: ../pages/home.php');
    }
    exit();
}

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
                            <button class="btn btn-primary" onclick="openCreateHackathonModal()">
                                <i class="fas fa-plus"></i> Create New Hackathon
                            </button>
                        </div>
                        
                        <div class="hackathons-container">
                            <div id="hackathons-list" class="hackathons-list">
                                <!-- Hackathons will be loaded here dynamically -->
                                <div class="loading-state">
                                    <i class="fas fa-spinner fa-spin"></i>
                                    <p>Loading hackathons...</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Judge Assignment Section -->
                    <div id="judge-assignment-section" class="section" style="display: none;">
                        <div class="section-header">
                            <h2>Assign Judges</h2>
                            <button class="btn btn-primary" onclick="openAssignJudgeModal()">
                                <i class="fas fa-plus"></i> Assign New Judge
                            </button>
                        </div>
                        
                        <div class="judge-assignments-container">
                            <div id="judge-assignments-list" class="assignments-list">
                                <!-- Judge assignments will be loaded here dynamically -->
                                <div class="loading-state">
                                    <i class="fas fa-spinner fa-spin"></i>
                                    <p>Loading judge assignments...</p>
                                </div>
                            </div>
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

    <!-- Create Hackathon Modal -->
    <div id="createHackathonModal" class="modal">
        <div class="modal-content large-modal">
            <div class="modal-header">
                <h3>Create New Hackathon</h3>
                <span class="close" onclick="closeModal('createHackathonModal')">&times;</span>
            </div>
            <form id="createHackathonForm" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label for="hackathon_name">Hackathon Name *</label>
                        <input type="text" id="hackathon_name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="hackathon_location">Location *</label>
                        <input type="text" id="hackathon_location" name="location" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="hackathon_description">Description *</label>
                    <textarea id="hackathon_description" name="description" rows="4" required placeholder="Describe your hackathon..."></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="hackathon_start_date">Start Date *</label>
                        <input type="date" id="hackathon_start_date" name="start_date" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="hackathon_end_date">End Date *</label>
                        <input type="date" id="hackathon_end_date" name="end_date" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="hackathon_rules">Rules</label>
                    <textarea id="hackathon_rules" name="rules" rows="3" placeholder="Enter hackathon rules and guidelines..."></textarea>
                </div>
                
                <div class="form-group">
                    <label for="hackathon_prizes">Prizes</label>
                    <textarea id="hackathon_prizes" name="prizes" rows="3" placeholder="Describe prizes and rewards..."></textarea>
                </div>
                
                <div class="form-group">
                    <label for="hackathon_image">Hackathon Image</label>
                    <input type="file" id="hackathon_image" name="image" accept="image/*">
                    <small class="form-help">Upload an image for your hackathon (optional)</small>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('createHackathonModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Hackathon</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Hackathon Modal -->
    <div id="editHackathonModal" class="modal">
        <div class="modal-content large-modal">
            <div class="modal-header">
                <h3>Edit Hackathon</h3>
                <span class="close" onclick="closeModal('editHackathonModal')">&times;</span>
            </div>
            <form id="editHackathonForm" enctype="multipart/form-data">
                <input type="hidden" id="edit_hackathon_id" name="id">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_hackathon_name">Hackathon Name *</label>
                        <input type="text" id="edit_hackathon_name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_hackathon_location">Location *</label>
                        <input type="text" id="edit_hackathon_location" name="location" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="edit_hackathon_description">Description *</label>
                    <textarea id="edit_hackathon_description" name="description" rows="4" required></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_hackathon_start_date">Start Date *</label>
                        <input type="date" id="edit_hackathon_start_date" name="start_date" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_hackathon_end_date">End Date *</label>
                        <input type="date" id="edit_hackathon_end_date" name="end_date" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="edit_hackathon_rules">Rules</label>
                    <textarea id="edit_hackathon_rules" name="rules" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="edit_hackathon_prizes">Prizes</label>
                    <textarea id="edit_hackathon_prizes" name="prizes" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="edit_hackathon_image">Hackathon Image</label>
                    <input type="file" id="edit_hackathon_image" name="image" accept="image/*">
                    <small class="form-help">Upload a new image to replace the current one</small>
                    <div id="current_image_preview" class="current-image-preview" style="display: none;">
                        <p>Current image:</p>
                        <img id="current_image" src="" alt="Current hackathon image" style="max-width: 200px; max-height: 150px;">
                    </div>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editHackathonModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Hackathon</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Assign Judge Modal -->
    <div id="assignJudgeModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Assign New Judge</h3>
                <span class="close" onclick="closeModal('assignJudgeModal')">&times;</span>
            </div>
            <form id="assignJudgeForm">
                <div class="form-group">
                    <label for="judge_name">Judge Name *</label>
                    <input type="text" id="judge_name" name="judge_name" required placeholder="Type judge name or select from existing...">
                    <div id="judge_suggestions" class="suggestions-dropdown" style="display: none;"></div>
                </div>
                
                <div class="form-group">
                    <label for="judge_email">Email *</label>
                    <input type="email" id="judge_email" name="judge_email" required>
                </div>
                
                <div class="form-group">
                    <label for="hackathon_select">Select Hackathon *</label>
                    <select id="hackathon_select" name="hackathon_id" required>
                        <option value="">Choose a hackathon...</option>
                        <!-- Hackathons will be loaded here dynamically -->
                    </select>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('assignJudgeModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign Judge</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>

    <script src="../assets/js/organizer-dashboard.js"></script>
</body>
</html>
