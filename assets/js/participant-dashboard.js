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
    loadHackathonsForCreate();
    loadMyTeams();
}

// Function to show join team section
function showJoinTeam() {
    hideAllSections();
    document.getElementById('join-team-section').style.display = 'block';
    loadHackathonsForJoin();
}

// Function to show submit project section
function showSubmitProject() {
    hideAllSections();
    document.getElementById('submit-project-section').style.display = 'block';
    loadHackathonsForProjects();
    loadMyProjects();
    initializeLeaderAutocomplete();
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

// ===========================================
// TEAM MANAGEMENT FUNCTIONS
// ===========================================

// Load hackathons for create team form
function loadHackathonsForCreate() {
    fetch('../api/teams.php?action=hackathon_teams')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('team_hackathon');
                select.innerHTML = '<option value="">Choose a hackathon...</option>';
                
                data.hackathons.forEach(hackathon => {
                    const option = document.createElement('option');
                    option.value = hackathon.id;
                    option.textContent = `${hackathon.name} (${hackathon.start_date} - ${hackathon.end_date})`;
                    select.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('Error loading hackathons:', error);
        });
}

// Load hackathons for join team form
function loadHackathonsForJoin() {
    fetch('../api/teams.php?action=hackathon_teams')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('join_hackathon');
                select.innerHTML = '<option value="">Choose a hackathon to see available teams...</option>';
                
                data.hackathons.forEach(hackathon => {
                    const option = document.createElement('option');
                    option.value = hackathon.id;
                    option.textContent = `${hackathon.name} (${hackathon.start_date} - ${hackathon.end_date})`;
                    select.appendChild(option);
                });
                
                // Add event listener for hackathon selection
                select.addEventListener('change', function() {
                    if (this.value) {
                        loadAvailableTeams(this.value);
                    } else {
                        hideAvailableTeams();
                    }
                });
            }
        })
        .catch(error => {
            console.error('Error loading hackathons:', error);
        });
}

// Load available teams for selected hackathon
function loadAvailableTeams(hackathonId) {
    fetch(`../api/teams.php?action=list&hackathon_id=${hackathonId}`)
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('available-teams-container');
            const noTeamsMessage = document.getElementById('no-teams-message');
            const teamsList = document.getElementById('available-teams-list');
            
            if (data.success && data.teams.length > 0) {
                teamsList.innerHTML = '';
                
                data.teams.forEach(team => {
                    const teamCard = createTeamCard(team, true);
                    teamsList.appendChild(teamCard);
                });
                
                container.style.display = 'block';
                noTeamsMessage.style.display = 'none';
            } else {
                container.style.display = 'none';
                noTeamsMessage.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error loading teams:', error);
            Swal.fire({
                title: 'Error!',
                text: 'Failed to load teams',
                icon: 'error'
            });
        });
}

// Hide available teams section
function hideAvailableTeams() {
    document.getElementById('available-teams-container').style.display = 'none';
    document.getElementById('no-teams-message').style.display = 'none';
}

// Load user's teams
function loadMyTeams() {
    fetch('../api/teams.php?action=my_teams')
        .then(response => response.json())
        .then(data => {
            const teamsList = document.getElementById('my-teams-list');
            
            if (data.success && data.teams.length > 0) {
                teamsList.innerHTML = '';
                
                data.teams.forEach(team => {
                    const teamCard = createTeamCard(team, false);
                    teamsList.appendChild(teamCard);
                });
            } else {
                teamsList.innerHTML = '<div class="empty-state"><i class="fas fa-users"></i><h3>No Teams Yet</h3><p>You haven\'t joined any teams yet.</p></div>';
            }
        })
        .catch(error => {
            console.error('Error loading my teams:', error);
        });
}

// Create team card element
function createTeamCard(team, showJoinButton = false) {
    const card = document.createElement('div');
    card.className = 'team-card';
    
    const memberNames = team.member_names ? team.member_names.split(',').join(', ') : 'No members yet';
    const isFull = team.current_members >= team.max_participants;
    
    card.innerHTML = `
        <div class="team-header">
            <h4>${team.name}</h4>
            <span class="team-members-count">${team.current_members}/${team.max_participants} members</span>
        </div>
        <div class="team-details">
            <p class="team-description">${team.description || 'No description provided'}</p>
            <p class="team-hackathon"><strong>Hackathon:</strong> ${team.hackathon_name}</p>
            <p class="team-members"><strong>Members:</strong> ${memberNames}</p>
        </div>
        <div class="team-actions">
            ${showJoinButton ? 
                `<button class="btn btn-primary ${isFull ? 'disabled' : ''}" 
                         onclick="joinTeam(${team.id})" 
                         ${isFull ? 'disabled' : ''}>
                    ${isFull ? 'Team Full' : 'Join Team'}
                </button>` : 
                `<button class="btn btn-danger" onclick="leaveTeam(${team.id})">Leave Team</button>`
            }
        </div>
    `;
    
    return card;
}

// Join team function
function joinTeam(teamId) {
    Swal.fire({
        title: 'Join Team',
        text: 'Are you sure you want to join this team?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, join!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('../api/teams.php?action=join', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ team_id: teamId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: data.message,
                        icon: 'success',
                        timer: 2000
                    });
                    // Refresh the teams list
                    const hackathonSelect = document.getElementById('join_hackathon');
                    if (hackathonSelect.value) {
                        loadAvailableTeams(hackathonSelect.value);
                    }
                    loadMyTeams();
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
                    text: 'An error occurred while joining the team',
                    icon: 'error'
                });
            });
        }
    });
}

// Leave team function
function leaveTeam(teamId) {
    Swal.fire({
        title: 'Leave Team',
        text: 'Are you sure you want to leave this team?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, leave!',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#d33'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('../api/teams.php?action=leave', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ team_id: teamId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: data.message,
                        icon: 'success',
                        timer: 2000
                    });
                    // Refresh the teams list
                    loadMyTeams();
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
                    text: 'An error occurred while leaving the team',
                    icon: 'error'
                });
            });
        }
    });
}

// Reset create team form
function resetCreateTeamForm() {
    document.getElementById('createTeamForm').reset();
}

// ===========================================
// FORM SUBMISSION HANDLERS
// ===========================================

// Create team form submission
document.addEventListener('DOMContentLoaded', function() {
    const createTeamForm = document.getElementById('createTeamForm');
    if (createTeamForm) {
        createTeamForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const teamData = Object.fromEntries(formData.entries());
            
            // Convert max_participants to integer
            teamData.max_participants = parseInt(teamData.max_participants);
            teamData.hackathon_id = parseInt(teamData.hackathon_id);
            
            fetch('../api/teams.php?action=create', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(teamData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: data.message,
                        icon: 'success',
                        timer: 2000
                    });
                    resetCreateTeamForm();
                    loadMyTeams();
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
                    text: 'An error occurred while creating the team',
                    icon: 'error'
                });
            });
        });
    }
    
    // Submit project form submission
    const submitProjectForm = document.getElementById('submitProjectForm');
    if (submitProjectForm) {
        submitProjectForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            // Validate required fields
            if (!formData.get('hackathon_id') || !formData.get('title') || !formData.get('leader_name')) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Please fill in all required fields',
                    icon: 'error'
                });
                return;
            }
            
            fetch('../api/projects.php?action=submit', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: data.message,
                        icon: 'success',
                        timer: 2000
                    });
                    resetProjectForm();
                    loadMyProjects();
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
                    text: 'An error occurred while submitting the project',
                    icon: 'error'
                });
            });
        });
    }
});

// ===========================================
// PROJECT MANAGEMENT FUNCTIONS
// ===========================================

// Load hackathons for project submission
function loadHackathonsForProjects() {
    fetch('../api/projects.php?action=hackathons')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('project_hackathon');
                select.innerHTML = '<option value="">Choose a hackathon...</option>';
                
                data.hackathons.forEach(hackathon => {
                    const option = document.createElement('option');
                    option.value = hackathon.id;
                    option.textContent = `${hackathon.name} (${hackathon.start_date} - ${hackathon.end_date})`;
                    select.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('Error loading hackathons:', error);
        });
}

// Load user's projects
function loadMyProjects() {
    fetch('../api/projects.php?action=list')
        .then(response => response.json())
        .then(data => {
            const projectsList = document.getElementById('my-projects-list');
            
            if (data.success && data.projects.length > 0) {
                projectsList.innerHTML = '';
                
                data.projects.forEach(project => {
                    const projectCard = createProjectCard(project);
                    projectsList.appendChild(projectCard);
                });
            } else {
                projectsList.innerHTML = '<div class="empty-state"><i class="fas fa-project-diagram"></i><h3>No Projects Yet</h3><p>You haven\'t submitted any projects yet.</p></div>';
            }
        })
        .catch(error => {
            console.error('Error loading projects:', error);
        });
}

// Create project card element
function createProjectCard(project) {
    const card = document.createElement('div');
    card.className = 'project-card';
    
    const fileInfo = project.project_file_path ? 
        `<p class="project-file"><strong>File:</strong> <a href="../${project.project_file_path}" target="_blank">Download</a></p>` : 
        '<p class="project-file"><strong>File:</strong> No file uploaded</p>';
    
    card.innerHTML = `
        <div class="project-header">
            <h4>${project.title}</h4>
            <span class="project-team-size">${project.team_size} member${project.team_size > 1 ? 's' : ''}</span>
        </div>
        <div class="project-details">
            <p class="project-leader"><strong>Leader:</strong> ${project.leader_name}</p>
            <p class="project-hackathon"><strong>Hackathon:</strong> ${project.hackathon_name}</p>
            <p class="project-dates"><strong>Dates:</strong> ${project.start_date} - ${project.end_date}</p>
            ${fileInfo}
            <p class="project-created"><strong>Submitted:</strong> ${new Date(project.created_at).toLocaleDateString()}</p>
        </div>
        <div class="project-actions">
            <button class="btn btn-danger" onclick="deleteProject(${project.id})">Delete Project</button>
        </div>
    `;
    
    return card;
}

// Delete project function
function deleteProject(projectId) {
    Swal.fire({
        title: 'Delete Project',
        text: 'Are you sure you want to delete this project? This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete!',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#d33'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('../api/projects.php?action=delete', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ project_id: projectId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: data.message,
                        icon: 'success',
                        timer: 2000
                    });
                    loadMyProjects();
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
                    text: 'An error occurred while deleting the project',
                    icon: 'error'
                });
            });
        }
    });
}

// Reset project form
function resetProjectForm() {
    document.getElementById('submitProjectForm').reset();
    hideLeaderSuggestions();
}

// ===========================================
// AUTCOMPLETE FUNCTIONALITY
// ===========================================

// Initialize autocomplete for leader name
function initializeLeaderAutocomplete() {
    const leaderInput = document.getElementById('project_leader_name');
    const suggestionsContainer = document.getElementById('leader_suggestions');
    
    if (!leaderInput || !suggestionsContainer) return;
    
    let searchTimeout;
    
    leaderInput.addEventListener('input', function() {
        const query = this.value.trim();
        
        // Clear previous timeout
        clearTimeout(searchTimeout);
        
        if (query.length < 2) {
            hideLeaderSuggestions();
            return;
        }
        
        // Debounce search
        searchTimeout = setTimeout(() => {
            searchParticipants(query);
        }, 300);
    });
    
    // Hide suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!leaderInput.contains(e.target) && !suggestionsContainer.contains(e.target)) {
            hideLeaderSuggestions();
        }
    });
    
    // Handle keyboard navigation
    leaderInput.addEventListener('keydown', function(e) {
        const suggestions = suggestionsContainer.querySelectorAll('.suggestion-item');
        const activeSuggestion = suggestionsContainer.querySelector('.suggestion-item.active');
        
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (activeSuggestion) {
                activeSuggestion.classList.remove('active');
                const next = activeSuggestion.nextElementSibling;
                if (next) {
                    next.classList.add('active');
                } else {
                    suggestions[0]?.classList.add('active');
                }
            } else {
                suggestions[0]?.classList.add('active');
            }
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (activeSuggestion) {
                activeSuggestion.classList.remove('active');
                const prev = activeSuggestion.previousElementSibling;
                if (prev) {
                    prev.classList.add('active');
                } else {
                    suggestions[suggestions.length - 1]?.classList.add('active');
                }
            } else {
                suggestions[suggestions.length - 1]?.classList.add('active');
            }
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (activeSuggestion) {
                selectParticipant(activeSuggestion);
            }
        } else if (e.key === 'Escape') {
            hideLeaderSuggestions();
        }
    });
}

// Search participants
function searchParticipants(query) {
    fetch(`../api/projects.php?action=participants&search=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayLeaderSuggestions(data.participants);
            } else {
                hideLeaderSuggestions();
            }
        })
        .catch(error => {
            console.error('Error searching participants:', error);
            hideLeaderSuggestions();
        });
}

// Display leader suggestions
function displayLeaderSuggestions(participants) {
    const suggestionsContainer = document.getElementById('leader_suggestions');
    
    if (!participants || participants.length === 0) {
        hideLeaderSuggestions();
        return;
    }
    
    suggestionsContainer.innerHTML = '';
    
    participants.forEach(participant => {
        const suggestionItem = document.createElement('div');
        suggestionItem.className = 'suggestion-item';
        suggestionItem.innerHTML = `
            <div class="suggestion-name">${participant.full_name}</div>
            <div class="suggestion-details">${participant.username} • ${participant.email}</div>
        `;
        
        suggestionItem.addEventListener('click', () => selectParticipant(suggestionItem, participant));
        suggestionItem.addEventListener('mouseenter', () => {
            suggestionsContainer.querySelectorAll('.suggestion-item').forEach(item => item.classList.remove('active'));
            suggestionItem.classList.add('active');
        });
        
        suggestionsContainer.appendChild(suggestionItem);
    });
    
    suggestionsContainer.style.display = 'block';
}

// Select participant
function selectParticipant(suggestionElement, participant = null) {
    const leaderInput = document.getElementById('project_leader_name');
    
    if (participant) {
        leaderInput.value = participant.full_name;
    } else {
        const nameElement = suggestionElement.querySelector('.suggestion-name');
        if (nameElement) {
            leaderInput.value = nameElement.textContent;
        }
    }
    
    hideLeaderSuggestions();
    leaderInput.focus();
}

// Hide leader suggestions
function hideLeaderSuggestions() {
    const suggestionsContainer = document.getElementById('leader_suggestions');
    if (suggestionsContainer) {
        suggestionsContainer.style.display = 'none';
        suggestionsContainer.innerHTML = '';
    }
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
