<?php
// Get user info
$user_name = $_SESSION['full_name'] ?? 'User';
$user_role = $_SESSION['role'] ?? 'Participant';
$initial = strtoupper(substr($user_name, 0, 1));
?>
<!-- Participant Sidebar -->
<aside class="sidebar">
    <div class="sidebar-header">
        <div class="user-avatar">
            <span><?php echo $initial; ?></span>
        </div>
        <h3><?php echo $user_name; ?></h3>
        <p class="user-role"><?php echo ucfirst($user_role); ?></p>
    </div>
    
    <nav class="sidebar-nav">
        <ul>
            <li>
                <a href="#" class="nav-item active">
                    <i class="fas fa-user"></i>
                    <span>Profile</span>
                </a>
            </li>
            <li>
                <a href="#" class="nav-item">
                    <i class="fas fa-users"></i>
                    <span>Create Team</span>
                </a>
            </li>
            <li>
                <a href="#" class="nav-item">
                    <i class="fas fa-user-plus"></i>
                    <span>Join Team</span>
                </a>
            </li>
            <li>
                <a href="#" class="nav-item">
                    <i class="fas fa-upload"></i>
                    <span>Submit Project</span>
                </a>
            </li>
            <li>
                <a href="#" class="nav-item">
                    <i class="fas fa-comments"></i>
                    <span>Send Chat Message</span>
                </a>
            </li>
        </ul>
    </nav>
    
    <div class="sidebar-footer">
        <a href="../auth/logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i>
            <span>Log Out</span>
        </a>
    </div>
</aside>
