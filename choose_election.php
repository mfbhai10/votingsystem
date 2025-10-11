<?php
require 'includes/session.php';

// Fetch open elections (display all open elections)
$stmt = $conn->prepare("SELECT id, title, starts_at, ends_at 
                        FROM elections 
                        WHERE status='open' AND starts_at <= NOW() AND ends_at >= NOW() 
                        ORDER BY ends_at ASC");
$stmt->execute();
$res = $stmt->get_result();

include 'includes/header.php';
?>
<body class="hold-transition skin-blue layout-top-nav">
<div class="wrapper">
  <?php include 'includes/navbar.php'; ?>
  <div class="content-wrapper">
    <div class="container">
      <section class="content">
        <h1 class="page-header text-center title"><b>Select an Election</b></h1>
        <div class="row">
          <div class="col-sm-8 col-sm-offset-2">
            <?php if (isset($_SESSION['error'])): ?>
              <div class="alert alert-danger">
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
              </div>
            <?php endif; ?>
            <?php if ($res->num_rows === 0): ?>
              <div class="alert alert-info">No active elections available at the moment.</div>
            <?php else: ?>
              <div class="list-group">
                <?php while ($e = $res->fetch_assoc()): ?>
                  <a class="list-group-item" href="set_election.php?id=<?= (int)$e['id'] ?>">
                    <h4 class="list-group-item-heading"><?= htmlspecialchars($e['title']) ?></h4>
                    <p class="list-group-item-text">Open until <?= htmlspecialchars($e['ends_at']) ?></p>
                  </a>
                <?php endwhile; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </section>
    </div>
  </div>
</div>
<?php include 'includes/scripts.php'; ?>
</body>
</html>