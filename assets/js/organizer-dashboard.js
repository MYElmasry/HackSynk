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
    loadHackathons();
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
    openCreateHackathonModal();
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

// ===========================================
// HACKATHON MANAGEMENT FUNCTIONS
// ===========================================

// Open create hackathon modal
function openCreateHackathonModal() {
    document.getElementById('createHackathonModal').style.display = 'block';
    // Reset form
    document.getElementById('createHackathonForm').reset();
}

// Load hackathons from API
function loadHackathons() {
    const hackathonsList = document.getElementById('hackathons-list');
    
    // Show loading state
    hackathonsList.innerHTML = `
        <div class="loading-state">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Loading hackathons...</p>
        </div>
    `;
    
    fetch('../api/hackathons.php?action=list')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayHackathons(data.data);
            } else {
                hackathonsList.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <h3>No Hackathons Found</h3>
                        <p>You haven't created any hackathons yet.</p>
                        <button class="btn btn-primary" onclick="openCreateHackathonModal()">
                            <i class="fas fa-plus"></i> Create Your First Hackathon
                        </button>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            hackathonsList.innerHTML = `
                <div class="error-state">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Error Loading Hackathons</h3>
                    <p>There was an error loading your hackathons. Please try again.</p>
                    <button class="btn btn-primary" onclick="loadHackathons()">Retry</button>
                </div>
            `;
        });
}

// Display hackathons in the UI
function displayHackathons(hackathons) {
    const hackathonsList = document.getElementById('hackathons-list');
    
    if (hackathons.length === 0) {
        hackathonsList.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-calendar-times"></i>
                <h3>No Hackathons Found</h3>
                <p>You haven't created any hackathons yet.</p>
                <button class="btn btn-primary" onclick="openCreateHackathonModal()">
                    <i class="fas fa-plus"></i> Create Your First Hackathon
                </button>
            </div>
        `;
        return;
    }
    
    const hackathonsHTML = hackathons.map(hackathon => {
        const startDate = new Date(hackathon.start_date).toLocaleDateString();
        const endDate = new Date(hackathon.end_date).toLocaleDateString();
        const currentDate = new Date();
        const isUpcoming = new Date(hackathon.start_date) > currentDate;
        const isOngoing = new Date(hackathon.start_date) <= currentDate && new Date(hackathon.end_date) >= currentDate;
        const isCompleted = new Date(hackathon.end_date) < currentDate;
        
        let statusClass = 'status-upcoming';
        let statusText = 'Upcoming';
        
        if (isOngoing) {
            statusClass = 'status-ongoing';
            statusText = 'Ongoing';
        } else if (isCompleted) {
            statusClass = 'status-completed';
            statusText = 'Completed';
        }
        
        return `
            <div class="hackathon-card">
                <div class="hackathon-image">
                    ${hackathon.image_path ? 
                        `<img src="../${hackathon.image_path}" alt="${hackathon.name}" onerror="this.style.display='none'">` : 
                        `<div class="default-image"><i class="fas fa-calendar-alt"></i></div>`
                    }
                </div>
                <div class="hackathon-content">
                    <div class="hackathon-header">
                        <h3>${hackathon.name}</h3>
                        <span class="status-badge ${statusClass}">${statusText}</span>
                    </div>
                    <p class="hackathon-description">${hackathon.description.substring(0, 150)}${hackathon.description.length > 150 ? '...' : ''}</p>
                    <div class="hackathon-details">
                        <div class="detail-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>${hackathon.location}</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-calendar"></i>
                            <span>${startDate} - ${endDate}</span>
                        </div>
                    </div>
                    <div class="hackathon-actions">
                        <button class="btn btn-secondary" onclick="viewHackathon(${hackathon.id})">
                            <i class="fas fa-eye"></i> View
                        </button>
                        <button class="btn btn-primary" onclick="editHackathon(${hackathon.id})">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-danger" onclick="deleteHackathon(${hackathon.id}, '${hackathon.name}')">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        `;
    }).join('');
    
    hackathonsList.innerHTML = hackathonsHTML;
}

// View hackathon details
function viewHackathon(id) {
    fetch(`../api/hackathons.php?action=get&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const hackathon = data.data;
                const startDate = new Date(hackathon.start_date).toLocaleDateString();
                const endDate = new Date(hackathon.end_date).toLocaleDateString();
                
                Swal.fire({
                    title: hackathon.name,
                    html: `
                        <div style="text-align: left;">
                            <p><strong>Description:</strong><br>${hackathon.description}</p>
                            <p><strong>Location:</strong> ${hackathon.location}</p>
                            <p><strong>Dates:</strong> ${startDate} - ${endDate}</p>
                            ${hackathon.rules ? `<p><strong>Rules:</strong><br>${hackathon.rules}</p>` : ''}
                            ${hackathon.prizes ? `<p><strong>Prizes:</strong><br>${hackathon.prizes}</p>` : ''}
                        </div>
                    `,
                    imageUrl: hackathon.image_path ? `../${hackathon.image_path}` : undefined,
                    imageWidth: 300,
                    imageHeight: 200,
                    showConfirmButton: true,
                    showCancelButton: true,
                    confirmButtonText: 'Edit',
                    cancelButtonText: 'Close'
                }).then((result) => {
                    if (result.isConfirmed) {
                        editHackathon(id);
                    }
                });
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: data.message,
                    icon: 'error'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                title: 'Error!',
                text: 'An error occurred while loading hackathon details',
                icon: 'error'
            });
        });
}

// Edit hackathon
function editHackathon(id) {
    fetch(`../api/hackathons.php?action=get&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const hackathon = data.data;
                
                // Populate edit form
                document.getElementById('edit_hackathon_id').value = hackathon.id;
                document.getElementById('edit_hackathon_name').value = hackathon.name;
                document.getElementById('edit_hackathon_description').value = hackathon.description;
                document.getElementById('edit_hackathon_start_date').value = hackathon.start_date;
                document.getElementById('edit_hackathon_end_date').value = hackathon.end_date;
                document.getElementById('edit_hackathon_location').value = hackathon.location;
                document.getElementById('edit_hackathon_rules').value = hackathon.rules || '';
                document.getElementById('edit_hackathon_prizes').value = hackathon.prizes || '';
                
                // Show current image if exists
                const currentImagePreview = document.getElementById('current_image_preview');
                const currentImage = document.getElementById('current_image');
                if (hackathon.image_path) {
                    currentImage.src = `../${hackathon.image_path}`;
                    currentImagePreview.style.display = 'block';
                } else {
                    currentImagePreview.style.display = 'none';
                }
                
                // Open edit modal
                document.getElementById('editHackathonModal').style.display = 'block';
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: data.message,
                    icon: 'error'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                title: 'Error!',
                text: 'An error occurred while loading hackathon data',
                icon: 'error'
            });
        });
}

// Delete hackathon
function deleteHackathon(id, name) {
    Swal.fire({
        title: 'Delete Hackathon',
        text: `Are you sure you want to delete "${name}"? This action cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('id', id);
            
            fetch('../api/hackathons.php?action=delete', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'Hackathon has been deleted successfully.',
                        icon: 'success',
                        timer: 2000
                    });
                    loadHackathons(); // Reload the list
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: data.message,
                        icon: 'error'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred while deleting the hackathon',
                    icon: 'error'
                });
            });
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
    
    // Add hackathon form handlers
    setupHackathonFormHandlers();
});

// Setup hackathon form handlers
function setupHackathonFormHandlers() {
    // Create hackathon form
    const createForm = document.getElementById('createHackathonForm');
    if (createForm) {
        createForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('../api/hackathons.php?action=create', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Hackathon created successfully',
                        icon: 'success',
                        timer: 2000
                    });
                    closeModal('createHackathonModal');
                    loadHackathons(); // Reload the list
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: data.message,
                        icon: 'error'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred while creating the hackathon',
                    icon: 'error'
                });
            });
        });
    }
    
    // Edit hackathon form
    const editForm = document.getElementById('editHackathonForm');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('../api/hackathons.php?action=update', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Hackathon updated successfully',
                        icon: 'success',
                        timer: 2000
                    });
                    closeModal('editHackathonModal');
                    loadHackathons(); // Reload the list
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: data.message,
                        icon: 'error'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred while updating the hackathon',
                    icon: 'error'
                });
            });
        });
    }
}
