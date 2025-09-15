<?php
session_start();

// إذا كان مسجل دخول نجيب أول حرف من الاسم
$initial = '';
if (isset($_SESSION['full_name'])) {
    $initial = strtoupper(substr($_SESSION['full_name'], 0, 1));
}
?>
<link rel="stylesheet" href="style1.css">

<header>
  <div class="logo">
    <img src="logo.png" alt="HackSynk Logo">
    <h2 class="brand">HackSynk</h2>
  </div>

  <nav>
    <a href="home.php">Home</a>
    <a href="Hackathons.php">Hackathons</a>
    <a href="about.php">About Us</a>
  </nav>
  <div class="auth-buttons">
    <?php if ($initial): ?>
      <div class="user-menu">
        <div class="user-icon" onclick="toggleDropdown()"><?php echo $initial; ?></div>
        <div class="dropdown" id="userDropdown">
          <a href="profile.php">Profile</a>
          <a href="logout.php">Logout</a>
        </div>
      </div>
    <?php else: ?>
      <a href="login.php" class="signup">Log in</a>
    <?php endif; ?>
  </div>
  <script>
    function toggleDropdown() {
      document.getElementById("userDropdown").classList.toggle("show");
    }
    window.onclick = function(event) {
      if (!event.target.matches('.user-icon')) {
        let dropdown = document.getElementById("userDropdown");
        if (dropdown && dropdown.classList.contains('show')) {
          dropdown.classList.remove('show');
        }
      }
    }
  </script>
</header>

