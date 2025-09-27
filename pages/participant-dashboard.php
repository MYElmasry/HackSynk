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
                        
                        <div class="team-form-container">
                            <form id="createTeamForm" class="team-form">
                                <div class="form-group">
                                    <label for="team_hackathon">Select Hackathon *</label>
                                    <select id="team_hackathon" name="hackathon_id" required>
                                        <option value="">Choose a hackathon...</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="team_name">Team Name *</label>
                                    <input type="text" id="team_name" name="name" required 
                                           placeholder="Enter your team name" maxlength="200">
                                </div>
                                
                                <div class="form-group">
                                    <label for="team_description">Team Description</label>
                                    <textarea id="team_description" name="description" 
                                              placeholder="Describe your team's goals and skills..." 
                                              rows="4"></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="team_max_participants">Maximum Participants *</label>
                                    <select id="team_max_participants" name="max_participants" required>
                                        <option value="2">2 members</option>
                                        <option value="3">3 members</option>
                                        <option value="4">4 members</option>
                                        <option value="5" selected>5 members</option>
                                        <option value="6">6 members</option>
                                        <option value="7">7 members</option>
                                        <option value="8">8 members</option>
                                        <option value="9">9 members</option>
                                        <option value="10">10 members</option>
                                    </select>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="button" class="btn btn-secondary" onclick="resetCreateTeamForm()">Reset</button>
                                    <button type="submit" class="btn btn-primary">Create Team</button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- My Teams Section -->
                        <div class="my-teams-section">
                            <h3>My Teams</h3>
                            <div id="my-teams-list" class="teams-list">
                                <!-- Teams will be loaded here -->
                            </div>
                        </div>
                    </div>

                    <!-- Join Team Section -->
                    <div id="join-team-section" class="section" style="display: none;">
                        <div class="section-header">
                            <h2>Join Team</h2>
                        </div>
                        
                        <div class="join-team-container">
                            <div class="hackathon-selector">
                                <div class="form-group">
                                    <label for="join_hackathon">Select Hackathon</label>
                                    <select id="join_hackathon" name="hackathon_id">
                                        <option value="">Choose a hackathon to see available teams...</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div id="available-teams-container" class="available-teams" style="display: none;">
                                <h3>Available Teams</h3>
                                <div id="available-teams-list" class="teams-list">
                                    <!-- Teams will be loaded here -->
                                </div>
                            </div>
                            
                            <div id="no-teams-message" class="empty-state" style="display: none;">
                                <i class="fas fa-users"></i>
                                <h3>No Teams Available</h3>
                                <p>No teams are available for this hackathon yet.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Project Section -->
                    <div id="submit-project-section" class="section" style="display: none;">
                        <div class="section-header">
                            <h2>Submit Project</h2>
                        </div>
                        
                        <div class="project-form-container">
                            <form id="submitProjectForm" class="project-form" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="project_hackathon">Select Hackathon *</label>
                                    <select id="project_hackathon" name="hackathon_id" required>
                                        <option value="">Choose a hackathon...</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="project_title">Project Title *</label>
                                    <input type="text" id="project_title" name="title" required 
                                           placeholder="Enter your project title" maxlength="200">
                                </div>
                                
                                <div class="form-group">
                                    <label for="project_leader_name">Leader Name *</label>
                                    <div class="autocomplete-container">
                                        <input type="text" id="project_leader_name" name="leader_name" required 
                                               placeholder="Search for a participant..." maxlength="100" autocomplete="off">
                                        <div id="leader_suggestions" class="autocomplete-suggestions"></div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="project_team_size">Team Size *</label>
                                    <select id="project_team_size" name="team_size" required>
                                        <option value="1">1 member</option>
                                        <option value="2">2 members</option>
                                        <option value="3">3 members</option>
                                        <option value="4">4 members</option>
                                        <option value="5" selected>5 members</option>
                                        <option value="6">6 members</option>
                                        <option value="7">7 members</option>
                                        <option value="8">8 members</option>
                                        <option value="9">9 members</option>
                                        <option value="10">10 members</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="project_file">Project File</label>
                                    <input type="file" id="project_file" name="project_file" 
                                           accept=".pdf">
                                    <small class="form-text">Upload your project file (PDF format only)</small>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="button" class="btn btn-secondary" onclick="resetProjectForm()">Reset</button>
                                    <button type="submit" class="btn btn-primary">Submit Project</button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- My Projects Section -->
                        <div class="my-projects-section">
                            <h3>My Projects</h3>
                            <div id="my-projects-list" class="projects-list">
                                <!-- Projects will be loaded here -->
                            </div>
                        </div>
                    </div>

                    <!-- Chat Messages Section -->
                    <div id="chat-messages-section" class="section" style="display: none;">
                        <div class="section-header">
                            <h2>Chat Messages</h2>
                            <div class="chat-controls">
                                <button id="refresh-participants" class="btn btn-secondary">
                                    <i class="fas fa-sync-alt"></i> Refresh
                                </button>
                            </div>
                        </div>
                        
                        <div class="chat-container">
                            <!-- Participants List (Right Side) -->
                            <div class="participants-panel">
                                <div class="participants-header">
                                    <h3>Participants</h3>
                                </div>
                                <div class="participants-search">
                                    <input type="text" id="participant-search" placeholder="Search participants...">
                                </div>
                                <div class="participants-list" id="participants-list">
                                    <!-- Participants will be loaded here -->
                                </div>
                            </div>
                            
                            <!-- Chat Area (Left Side) -->
                            <div class="chat-area">
                                <div class="chat-header" id="chat-header" style="display: none;">
                                    <div class="chat-user-info">
                                        <div class="user-avatar">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div class="user-details">
                                            <h4 id="chat-user-name">User Name</h4>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="chat-messages" id="chat-messages">
                                    <div class="no-conversation">
                                        <i class="fas fa-comments"></i>
                                        <h3>Select a participant to start chatting</h3>
                                        <p>Choose someone from the participants list to begin a conversation</p>
                                    </div>
                                </div>
                                
                                <div class="chat-input-container" id="chat-input-container" style="display: none;">
                                    <div class="chat-input-wrapper">
                                        <input type="text" id="message-input" placeholder="Type your message..." maxlength="1000">
                                        <button id="send-message-btn" class="btn btn-primary">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                    </div>
                                    <div class="chat-input-footer">
                                        <small>Press Enter to send, Shift+Enter for new line</small>
                                    </div>
                                </div>
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

    <!-- Data for JavaScript -->
    <script id="user-data" type="application/json">
        <?php
        echo json_encode([
            'id' => $_SESSION['user_id'],
            'full_name' => $_SESSION['full_name'],
            'username' => $_SESSION['username'],
            'email' => $_SESSION['email']
        ]);
        ?>
    </script>
    

    <script src="../assets/js/participant-dashboard.js"></script>
    <script src="../assets/js/chat.js"></script>
</body>
</html>
