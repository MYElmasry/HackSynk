// Hackathons functionality
// Handles loading and displaying hackathons in a grid layout

// Fetch and display hackathons
async function loadHackathons() {
  try {
    const response = await fetch('../api/hackathons.php?action=list');
    const data = await response.json();
    
    const loading = document.getElementById('loading');
    const grid = document.getElementById('hackathons-grid');
    const noHackathons = document.getElementById('no-hackathons');
    
    loading.style.display = 'none';
    
    if (data.success && data.data.length > 0) {
      grid.innerHTML = '';
      data.data.forEach(hackathon => {
        const card = createHackathonCard(hackathon);
        grid.appendChild(card);
      });
    } else {
      noHackathons.style.display = 'block';
    }
  } catch (error) {
    console.error('Error loading hackathons:', error);
    document.getElementById('loading').innerHTML = 'Error loading hackathons. Please try again.';
  }
}

function createHackathonCard(hackathon) {
  const card = document.createElement('div');
  card.className = 'hackathon-card';
  
  const startDate = new Date(hackathon.start_date);
  const endDate = new Date(hackathon.end_date);
  const now = new Date();
  
  let status = 'upcoming';
  let statusClass = 'status-upcoming';
  if (now >= startDate && now <= endDate) {
    status = 'active';
    statusClass = 'status-active';
  } else if (now > endDate) {
    status = 'ended';
    statusClass = 'status-ended';
  }
  
  card.innerHTML = `
    <div class="card-image">
      ${hackathon.image_path ? 
        `<img src="../${hackathon.image_path}" alt="${hackathon.name}" onerror="this.style.display='none'">` : 
        '<div class="no-image">No Image</div>'
      }
    </div>
    <div class="card-content">
      <div class="card-header">
        <h3>${hackathon.name}</h3>
        <span class="status ${statusClass}">${status}</span>
      </div>
      <p class="card-description">${hackathon.description.substring(0, 100)}${hackathon.description.length > 100 ? '...' : ''}</p>
      <div class="card-details">
        <div class="detail-item">
          <strong>Location:</strong> ${hackathon.location}
        </div>
        <div class="detail-item">
          <strong>Start:</strong> ${startDate.toLocaleDateString()}
        </div>
        <div class="detail-item">
          <strong>End:</strong> ${endDate.toLocaleDateString()}
        </div>
      </div>
    </div>
  `;
  
  return card;
}

// Load hackathons when page loads
document.addEventListener('DOMContentLoaded', loadHackathons);
