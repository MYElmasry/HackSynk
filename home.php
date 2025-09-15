<?php

// إذا كان مسجل دخول نجيب أول حرف من الاسم
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
  <title>HackSynk - Home</title>
  <link rel="stylesheet" href="style1.css" />
  
</head>
<body>

  <div class="wrapper">
    <!-- Header -->
    <?php include 'header.php'; ?>


    
    <section class="hero">
      <div class="hero-text">
        <h1>The Home for Hackathons</h1>
        <p>Join, Form Your Team, and Start Innovating in Hackathons.</p>
      </div>
      <div class="hero-image">
        <img src="teamphoto.png" alt="Hackathon Illustration">
      </div>
    </section>

    
    <section class="hackathons">
      <h2>Active and Upcoming Hackathons</h2>
    </section>
  </div>

  <?php include 'footer.php'; ?>

  <script>
      console.log('Session user_id:', '<?php echo $_SESSION['user_id'] ?? 'not set'; ?>');
        console.log('Session username:', '<?php echo $_SESSION['username'] ?? 'not set'; ?>');
        console.log('Session full_name:', '<?php echo $_SESSION['full_name'] ?? 'not set'; ?>');
        console.log('Session role:', '<?php echo $_SESSION['role'] ?? 'not set'; ?>');
        
    function toggleDropdown() {
      document.getElementById("userDropdown").classList.toggle("show");
    }

    // اغلاق القائمة إذا ضغطت خارجها
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

