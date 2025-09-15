<?php
session_start();

// أول حرف من الاسم إذا مسجل
$initial = '';
if (isset($_SESSION['full_name'])) {
  $initial = strtoupper(substr($_SESSION['full_name'], 0, 1));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>HackSynk - Hackathons</title>
  <link rel="stylesheet" href="style1.css" />
</head>
<body>
  <div class="wrapper">

  <!-- Header -->
  <header>
    <div class="logo">
      <img src="logo.png" alt="HackSynk Logo">
      <span class="brand">HackSynk</span>
    </div>
    <nav>
      <a href="home.php">Home</a>
      <a href="hackathons.php" class="active">Hackathons</a>
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
  </header>

  <!-- Hackathons Section -->
  <section class="about">
    <div class="about-left">
      <h3>Active and Upcoming Hackathons</h3>
      <!-- هنا تقدر تضيف كروت hackathons -->
    </div>
  </section>
  </div>

  <?php include 'footer.php'; ?>

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
</body>
</html>
