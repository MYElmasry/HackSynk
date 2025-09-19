<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

// Get user info
$user_name = $_SESSION['full_name'] ?? 'User';
$user_role = $_SESSION['role'] ?? 'participant';
$initial = strtoupper(substr($user_name, 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HackSynk - Admin Dashboard</title>
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
            <!-- Sidebar -->
            <?php include '../includes/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            
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

                <!-- Users Management Section -->
                <div id="users-section" class="users-section" style="display: none;">
                    <div class="section-header">
                        <h2>Manage Users</h2>
                        <div class="user-actions">
                            <div class="search-filter-container">
                                <div class="search-box">
                                    <i class="fas fa-search"></i>
                                    <input type="text" id="user-search" placeholder="Search by name or email..." onkeyup="filterUsers()">
                                </div>
                                <div class="filter-box">
                                    <select id="role-filter" onchange="filterUsers()">
                                        <option value="">All Roles</option>
                                        <option value="participant">Participant</option>
                                        <option value="organizer">Organizer</option>
                                        <option value="judge">Judge</option>
                                    </select>
                                </div>
                            </div>
                            <span id="total-users">Total Users: 0</span>
                            <button class="btn btn-primary" onclick="openAddUserModal()">
                                <i class="fas fa-plus"></i> Add User
                            </button>
                        </div>
                    </div>
                    
                    <div class="table-container">
                        <table class="users-table">
                            <thead>
                                <tr>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="users-table-body">
                                <!-- Users will be loaded here via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Default Dashboard Content -->
                <div id="default-content" class="default-content" style="display: none;">
                    <!-- Content will be loaded based on selected nav item -->
                </div>
            </div>
        </main>
        </div>
    </div>

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>

    <!-- Add User Modal -->
    <div id="addUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New User</h3>
                <span class="close" onclick="closeModal('addUserModal')">&times;</span>
            </div>
            <form id="addUserForm">
                <div class="form-group">
                    <label for="add_role">Role *</label>
                    <select id="add_role" name="role" required onchange="toggleRoleFields('add')">
                        <option value="">Select Role</option>
                        <option value="participant">Participant</option>
                        <option value="organizer">Organizer</option>
                        <option value="judge">Judge</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="add_full_name">Full Name *</label>
                    <input type="text" id="add_full_name" name="full_name" required>
                </div>
                
                <div class="form-group">
                    <label for="add_email">Email *</label>
                    <input type="email" id="add_email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="add_password">Password *</label>
                    <input type="password" id="add_password" name="password" required>
                </div>
                
                <!-- Participant Fields -->
                <div id="add_participant_fields" class="role-fields" style="display: none;">
                    <div class="form-group">
                        <label for="add_city_country">City, Country</label>
                        <input type="text" id="add_city_country" name="city_country">
                    </div>
                    <div class="form-group">
                        <label for="add_skills_expertise">Skills & Expertise</label>
                        <input type="text" id="add_skills_expertise" name="skills_expertise">
                    </div>
                </div>
                
                <!-- Organizer Fields -->
                <div id="add_organizer_fields" class="role-fields" style="display: none;">
                    <div class="form-group">
                        <label for="add_organization_name">Organization Name</label>
                        <input type="text" id="add_organization_name" name="organization_name">
                    </div>
                    <div class="form-group">
                        <label for="add_job_title_position">Job Title/Position</label>
                        <input type="text" id="add_job_title_position" name="job_title_position">
                    </div>
                </div>
                
                <!-- Judge Fields -->
                <div id="add_judge_fields" class="role-fields" style="display: none;">
                    <div class="form-group">
                        <label for="add_professional_title">Professional Title</label>
                        <input type="text" id="add_professional_title" name="professional_title">
                    </div>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addUserModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add User</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit User</h3>
                <span class="close" onclick="closeModal('editUserModal')">&times;</span>
            </div>
            <form id="editUserForm">
                <input type="hidden" id="edit_user_id" name="user_id">
                <input type="hidden" id="edit_role" name="role">
                
                <div class="form-group">
                    <label for="edit_full_name">Full Name *</label>
                    <input type="text" id="edit_full_name" name="full_name" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_email">Email *</label>
                    <input type="email" id="edit_email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_password">New Password (leave blank to keep current)</label>
                    <input type="password" id="edit_password" name="password">
                </div>
                
                <!-- Participant Fields -->
                <div id="edit_participant_fields" class="role-fields" style="display: none;">
                    <div class="form-group">
                        <label for="edit_city_country">City, Country</label>
                        <input type="text" id="edit_city_country" name="city_country">
                    </div>
                    <div class="form-group">
                        <label for="edit_skills_expertise">Skills & Expertise</label>
                        <input type="text" id="edit_skills_expertise" name="skills_expertise">
                    </div>
                </div>
                
                <!-- Organizer Fields -->
                <div id="edit_organizer_fields" class="role-fields" style="display: none;">
                    <div class="form-group">
                        <label for="edit_organization_name">Organization Name</label>
                        <input type="text" id="edit_organization_name" name="organization_name">
                    </div>
                    <div class="form-group">
                        <label for="edit_job_title_position">Job Title/Position</label>
                        <input type="text" id="edit_job_title_position" name="job_title_position">
                    </div>
                </div>
                
                <!-- Judge Fields -->
                <div id="edit_judge_fields" class="role-fields" style="display: none;">
                    <div class="form-group">
                        <label for="edit_professional_title">Professional Title</label>
                        <input type="text" id="edit_professional_title" name="professional_title">
                    </div>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editUserModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update User</button>
                </div>
            </form>
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
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editProfileModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/dashboard.js"></script>
</body>
</html>
