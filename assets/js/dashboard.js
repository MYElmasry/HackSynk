/**
 * Dashboard JavaScript functionality
 * Handles all dashboard interactions including:
 * - Navigation between sections
 * - User management (CRUD operations)
 * - Modal handling
 * - Form submissions
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
        
        if (navText === 'Manage Users') {
            loadUsers();
        } else if (navText === 'Profile') {
            showProfile();
        } else {
            // Hide all sections
            document.getElementById('users-section').style.display = 'none';
            document.getElementById('profile-section').style.display = 'none';
            document.getElementById('default-content').style.display = 'block';
        }
    });
});

// ===========================================
// SECTION DISPLAY FUNCTIONS
// ===========================================

// Function to show profile section
function showProfile() {
    // Show profile section and hide other sections
    document.getElementById('profile-section').style.display = 'block';
    document.getElementById('users-section').style.display = 'none';
    document.getElementById('default-content').style.display = 'none';
}

// Function to load users via AJAX
function loadUsers() {
    // Show users section and hide other sections
    document.getElementById('users-section').style.display = 'block';
    document.getElementById('profile-section').style.display = 'none';
    document.getElementById('default-content').style.display = 'none';
    
    // Show loading state
    const tableBody = document.getElementById('users-table-body');
    tableBody.innerHTML = '<tr><td colspan="4" class="loading">Loading users...</td></tr>';
    
    // Fetch users
    fetch('../api/users.php?action=fetch')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayUsers(data.users);
                document.getElementById('total-users').textContent = `Total Users: ${data.total}`;
            } else {
                tableBody.innerHTML = '<tr><td colspan="4" class="error">Error loading users: ' + data.error + '</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            tableBody.innerHTML = '<tr><td colspan="4" class="error">Error loading users. Please try again.</td></tr>';
        });
}

// Function to display users in the table
function displayUsers(users) {
    const tableBody = document.getElementById('users-table-body');
    
    if (users.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="4" class="no-data">No users found</td></tr>';
        return;
    }
    
    tableBody.innerHTML = users.map(user => `
        <tr>
            <td>${user.full_name}</td>
            <td>${user.email}</td>
            <td><span class="role-badge role-${user.role}">${user.role.charAt(0).toUpperCase() + user.role.slice(1)}</span></td>
            <td class="actions">
                <button class="btn-action btn-edit" onclick="openEditUserModal(${user.id}, '${user.role}', '${user.full_name}', '${user.email}')" title="Edit User">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn-action btn-delete" onclick="deleteUser(${user.id}, '${user.role}', '${user.full_name}')" title="Delete User">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

// ===========================================
// MODAL FUNCTIONS
// ===========================================

// Modal functions
function openAddUserModal() {
    document.getElementById('addUserModal').style.display = 'block';
    document.getElementById('addUserForm').reset();
    hideAllRoleFields('add');
}

function openEditUserModal(userId, role, fullName, email) {
    document.getElementById('editUserModal').style.display = 'block';
    document.getElementById('edit_user_id').value = userId;
    document.getElementById('edit_role').value = role;
    document.getElementById('edit_full_name').value = fullName;
    document.getElementById('edit_email').value = email;
    
    // Show role-specific fields
    hideAllRoleFields('edit');
    document.getElementById(`edit_${role}_fields`).style.display = 'block';
    
    // Load additional user data for role-specific fields
    loadUserDetails(userId, role);
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function toggleRoleFields(type) {
    const role = document.getElementById(`${type}_role`).value;
    hideAllRoleFields(type);
    
    if (role) {
        document.getElementById(`${type}_${role}_fields`).style.display = 'block';
    }
}

function hideAllRoleFields(type) {
    const roles = ['participant', 'organizer', 'judge'];
    roles.forEach(role => {
        document.getElementById(`${type}_${role}_fields`).style.display = 'none';
    });
}

// Load additional user details for editing
function loadUserDetails(userId, role) {
    // This would typically fetch additional user data from the API
    // For now, we'll just show the fields based on role
    console.log(`Loading details for ${role} user ${userId}`);
}

// ===========================================
// FORM SUBMISSION HANDLERS
// ===========================================

// Add user function
document.getElementById('addUserForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const userData = Object.fromEntries(formData.entries());
    console.log(userData);
    
    fetch('../api/users.php?action=add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(userData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: 'Success!',
                text: 'User added successfully',
                icon: 'success',
                timer: 2000
            });
            closeModal('addUserModal');
            loadUsers(); // Refresh the users list
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
            text: 'An error occurred while adding the user',
            icon: 'error'
        });
    });
});

// Edit user function
document.getElementById('editUserForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const userData = Object.fromEntries(formData.entries());
    
    fetch('../api/users.php?action=edit', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(userData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: 'Success!',
                text: 'User updated successfully',
                icon: 'success',
                timer: 2000
            });
            closeModal('editUserModal');
            loadUsers(); // Refresh the users list
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
            text: 'An error occurred while updating the user',
            icon: 'error'
        });
    });
});

// ===========================================
// USER MANAGEMENT FUNCTIONS
// ===========================================

// Delete user function
function deleteUser(userId, role, userName) {
    Swal.fire({
        title: 'Are you sure?',
        text: `You are about to delete user "${userName}". This action cannot be undone!`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('../api/users.php?action=delete', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    user_id: userId,
                    role: role
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'User has been deleted successfully',
                        icon: 'success',
                        timer: 2000
                    });
                    loadUsers(); // Refresh the users list
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
                    text: 'An error occurred while deleting the user',
                    icon: 'error'
                });
            });
        }
    });
}

// ===========================================
// EVENT LISTENERS & INITIALIZATION
// ===========================================

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
});
