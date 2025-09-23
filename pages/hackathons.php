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
  <title>HackSynk - Hackathons</title>
  <link rel="stylesheet" href="../assets/css/style1.css" />
</head>
<body>
  <div class="wrapper">

  <!-- Header -->
 <?php include '../includes/header.php'; ?>

  <!-- Hackathons Section -->
  <section class="hackathons">
    <h2>Active and Upcoming Hackathons</h2>
    <div id="loading" class="loading">Loading hackathons...</div>
    <div id="hackathons-grid" class="hackathons-grid">
      <!-- Hackathons will be loaded here -->
    </div>
    <div id="no-hackathons" class="no-hackathons" style="display: none;">
      <p>No hackathons available at the moment.</p>
    </div>
  </section>
  </div>

  <?php include '../includes/footer.php'; ?>

  <script src="../assets/js/hackathons.js"></script>
</body>
</html>
