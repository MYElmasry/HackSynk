<?php
session_start();

// إذا كان مسجل دخول نجيب أول حرف من الاسم
$initial = '';
if (isset($_SESSION['full_name'])) {
    $initial = strtoupper(substr($_SESSION['full_name'], 0, 1));
}
?>
<link rel="stylesheet" href="../assets/css/style1.css">

<header>
  <div class="logo">
    <img src="../assets/images/logo.png" alt="HackSynk Logo">
    <h2 class="brand">HackSynk</h2>
  </div>

  <nav>
    <a href="../pages/home.php">Home</a>
    <a href="../pages/hackathons.php">Hackathons</a>
    <a href="../pages/about.php">About Us</a>
  </nav>
  <div class="auth-buttons">
    <?php if ($initial): ?>
      <div class="user-menu">
        <div class="user-icon" onclick="toggleDropdown()"><?php echo $initial; ?></div>
        <div class="dropdown" id="userDropdown">
          <a href="../pages/profile.php">Profile</a>
          <a href="../auth/logout.php">Logout</a>
        </div>
      </div>
    <?php else: ?>
      <a href="../auth/login.php" class="signup">Log in</a>
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

