/**
 * Participant Dashboard JavaScript functionality
 * Handles all participant dashboard interactions including:
 * - Navigation between sections
 * - Team management
 * - Project submission
 * - Chat messaging
 * - Profile management
 */

// ===========================================
// NAVIGATION FUNCTIONALITY
// ===========================================

// Add active class to clicked nav items
document.querySelectorAll('.nav-item').forEach(item => {
    item.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Remove active class from all items
        document.querySelectorAll('.nav-item').forEach(nav => {
            nav.classList.remove('active');
        });
        
        // Add active class to clicked item
        this.classList.add('active');
        
        // Handle specific navigation items
        const navText = this.querySelector('span').textContent;
        
        if (navText === 'Profile') {
            showProfile();
        } else if (navText === 'Create Team') {
            showCreateTeam();
        } else if (navText === 'Join Team') {
            showJoinTeam();
        } else if (navText === 'Submit Project') {
            showSubmitProject();
        } else if (navText === 'Send Chat Message') {
            showChatMessages();
        }
    });
});

// ===========================================
// SECTION DISPLAY FUNCTIONS
// ===========================================

// Function to show profile section
function showProfile() {
    hideAllSections();
    document.getElementById('profile-section').style.display = 'block';
}

// Function to show create team section
function showCreateTeam() {
    hideAllSections();
    document.getElementById('create-team-section').style.display = 'block';
}

// Function to show join team section
function showJoinTeam() {
    hideAllSections();
    document.getElementById('join-team-section').style.display = 'block';
}

// Function to show submit project section
function showSubmitProject() {
    hideAllSections();
    document.getElementById('submit-project-section').style.display = 'block';
}

// Function to show chat messages section
function showChatMessages() {
    hideAllSections();
    document.getElementById('chat-messages-section').style.display = 'block';
}


// Helper function to hide all sections
function hideAllSections() {
    const sections = document.querySelectorAll('.section');
    sections.forEach(section => {
        section.style.display = 'none';
    });
    
    // Also hide the profile section
    const profileSection = document.getElementById('profile-section');
    if (profileSection) {
        profileSection.style.display = 'none';
    }
    
    // Hide dashboard overview
    const dashboardOverview = document.getElementById('dashboard-overview');
    if (dashboardOverview) {
        dashboardOverview.style.display = 'none';
    }
}

// ===========================================
// PROFILE MANAGEMENT FUNCTIONS
// ===========================================

// Open edit profile modal
function openEditProfileModal() {
    document.getElementById('editProfileModal').style.display = 'block';
    loadProfileData();
}

// Close modal function
function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Load current profile data
function loadProfileData() {
    fetch('../api/users.php?action=profile')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const profile = data.profile;
                document.getElementById('profile_full_name').value = profile.full_name;
                document.getElementById('profile_email').value = profile.email;
                document.getElementById('profile_username').value = profile.username;
                document.getElementById('profile_skills').value = profile.skills || '';
                document.getElementById('profile_experience').value = profile.experience || 'beginner';
                // Clear password fields
                document.getElementById('profile_password').value = '';
                document.getElementById('profile_confirm_password').value = '';
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: data.error,
                    icon: 'error'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                title: 'Error!',
                text: 'An error occurred while loading profile data',
                icon: 'error'
            });
        });
}


// ===========================================
// STATS ANIMATION
// ===========================================

// Animate stats on page load
document.addEventListener('DOMContentLoaded', function() {
    const statNumbers = document.querySelectorAll('.stat-content h3');
    
    statNumbers.forEach(stat => {
        const finalValue = parseFloat(stat.textContent);
        let currentValue = 0;
        const increment = finalValue / 50; // 50 steps
        
        const timer = setInterval(() => {
            currentValue += increment;
            if (currentValue >= finalValue) {
                stat.textContent = finalValue;
                clearInterval(timer);
            } else {
                stat.textContent = Math.floor(currentValue);
            }
        }, 30);
    });
});

// ===========================================
// RESPONSIVE HANDLING
// ===========================================

// Handle mobile menu toggle
function toggleMobileMenu() {
    const sidebar = document.querySelector('.sidebar');
    sidebar.classList.toggle('open');
}

// Close mobile menu when clicking outside
document.addEventListener('click', function(event) {
    const sidebar = document.querySelector('.sidebar');
    const mobileToggle = document.querySelector('.mobile-menu-toggle');
    
    if (mobileToggle && !mobileToggle.contains(event.target) && 
        sidebar && !sidebar.contains(event.target)) {
        sidebar.classList.remove('open');
    }
});

// ===========================================
// FORM SUBMISSION HANDLERS
// ===========================================

// Edit profile form submission
document.addEventListener('DOMContentLoaded', function() {
    const editProfileForm = document.getElementById('editProfileForm');
    if (editProfileForm) {
        editProfileForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const profileData = Object.fromEntries(formData.entries());
            
            // Validate password confirmation
            if (profileData.password && profileData.password !== profileData.confirm_password) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Passwords do not match',
                    icon: 'error'
                });
                return;
            }
            
            // Remove confirm_password from data sent to server
            delete profileData.confirm_password;
            
            fetch('../api/users.php?action=profile', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(profileData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Profile updated successfully',
                        icon: 'success',
                        timer: 2000
                    });
                    closeModal('editProfileModal');
                    // Refresh the page to update the profile display
                    location.reload();
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: data.error,
                        icon: 'error'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred while updating the profile',
                    icon: 'error'
                });
            });
        });
    }
});


// Close modal when clicking outside
window.onclick = function(event) {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
}

// ===========================================
// INITIALIZATION
// ===========================================

// Initialize dashboard on page load
document.addEventListener('DOMContentLoaded', function() {
    // Show profile section by default
    showProfile();
    
    // Add mobile menu toggle if on mobile
    if (window.innerWidth <= 768) {
        const header = document.querySelector('header');
        const mobileToggle = document.createElement('button');
        mobileToggle.className = 'mobile-menu-toggle';
        mobileToggle.innerHTML = '<i class="fas fa-bars"></i>';
        mobileToggle.onclick = toggleMobileMenu;
        header.appendChild(mobileToggle);
    }
});
