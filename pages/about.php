<?php

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
  <title>HackSynk - About Us</title>
  <link rel="stylesheet" href="../assets/css/style1.css" />
</head>
<body>

  <?php include '../includes/header.php'; ?>

  <!-- About Section -->
  <section class="about">
    <div class="about-left">
      <h3>Who We Are?</h3>
      <p>
        HackSynk is a dynamic platform designed to connect innovators, developers, 
        and creators through hackathons. We aim to foster collaboration, creativity, 
        and impactful solutions.
      </p>

      <h3>Our Vision</h3>
      <p>
        To be the leading digital space for meaningful hackathon experiences 
        and team building in the region.
      </p>

      <h3>Our Mission</h3>
      <p>
        To empower participants by providing seamless tools for team formation, 
        hackathon creation, and judging — all in one place.
      </p>

      <h3>What Makes Us Unique?</h3>
      <ul>
        <li>Smart team-matching system</li>
        <li>Organizers can launch hackathons easily</li>
        <li>Built-in communication tools</li>
        <li>User-friendly interface</li>
      </ul>
    </div>

    <div class="about-right">
      <img src="../assets/images/teamphoto.png" alt="Hackathon Illustration">
    </div>
  </section>

  <?php include '../includes/footer.php'; ?>

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
