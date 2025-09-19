/**
 * Organizer Dashboard JavaScript functionality
 * Handles all organizer dashboard interactions including:
 * - Navigation between sections
 * - Hackathon management
 * - Judge assignment
 * - Profile management
 * - Quick actions
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
        } else if (navText === 'Create/Manage Hackathons') {
            showHackathonManagement();
        } else if (navText === 'Assign Judges') {
            showJudgeAssignment();
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

// Function to show hackathon management section
function showHackathonManagement() {
    hideAllSections();
    document.getElementById('hackathons-section').style.display = 'block';
}

// Function to show judge assignment section
function showJudgeAssignment() {
    hideAllSections();
    showPlaceholder('Judge Assignment', 'Assign judges to your hackathons here.');
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

// Helper function to show placeholder content
function showPlaceholder(title, message) {
    const contentBody = document.querySelector('.content-body');
    
    // Create placeholder if it doesn't exist
    let placeholder = document.getElementById('placeholder-section');
    if (!placeholder) {
        placeholder = document.createElement('div');
        placeholder.id = 'placeholder-section';
        placeholder.className = 'section';
        contentBody.appendChild(placeholder);
    }
    
    placeholder.innerHTML = `
        <div class="section-header">
            <h2>${title}</h2>
        </div>
        <div class="placeholder-content">
            <div class="placeholder-icon">
                <i class="fas fa-cog fa-spin"></i>
            </div>
            <h3>Coming Soon</h3>
            <p>${message}</p>
        </div>
    `;
    placeholder.style.display = 'block';
}

// ===========================================
// QUICK ACTION FUNCTIONS
// ===========================================

// Create hackathon function
function createHackathon() {
    Swal.fire({
        title: 'Create New Hackathon',
        text: 'This feature will be implemented soon!',
        icon: 'info',
        confirmButtonText: 'OK'
    });
}

// Manage hackathons function
function manageHackathons() {
    showHackathonManagement();
}

// Assign judges function
function assignJudges() {
    showJudgeAssignment();
}

// View profile function
function viewProfile() {
    showProfile();
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
                document.getElementById('profile_organization_name').value = profile.organization_name || '';
                document.getElementById('profile_job_title_position').value = profile.job_title_position || '';
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
// HACKATHON CARD INTERACTIONS
// ===========================================

// Add click handlers to hackathon cards
document.addEventListener('DOMContentLoaded', function() {
    const hackathonCards = document.querySelectorAll('.hackathon-card');
    hackathonCards.forEach(card => {
        const viewBtn = card.querySelector('.btn-secondary');
        const manageBtn = card.querySelector('.btn-primary');
        
        if (viewBtn) {
            viewBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                const hackathonTitle = card.querySelector('h3').textContent;
                Swal.fire({
                    title: 'Hackathon Details',
                    text: `Viewing details for: ${hackathonTitle}`,
                    icon: 'info',
                    confirmButtonText: 'OK'
                });
            });
        }
        
        if (manageBtn) {
            manageBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                const hackathonTitle = card.querySelector('h3').textContent;
                const status = card.querySelector('.status-badge').textContent;
                
                if (status === 'Completed') {
                    Swal.fire({
                        title: 'View Results',
                        text: `Viewing results for: ${hackathonTitle}`,
                        icon: 'info',
                        confirmButtonText: 'OK'
                    });
                } else {
                    Swal.fire({
                        title: 'Manage Hackathon',
                        text: `Managing: ${hackathonTitle}`,
                        icon: 'info',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }
    });
});

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
// INITIALIZATION
// ===========================================

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
