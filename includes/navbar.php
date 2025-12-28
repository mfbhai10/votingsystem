<header class="main-header">
  <nav class="navbar navbar-static-top">
    <div class="container">
      <div class="navbar-header">
        <a href="choose_election.php" class="navbar-brand"><b>Vote</b>Xpress</a>
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse">
          <i class="fa fa-bars"></i>
        </button>
      </div>
      <div class="collapse navbar-collapse pull-left" id="navbar-collapse"></div>
      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">

        <li><a href='choose_election.php'>ELECTIONS</a></li>
        <li><a href='results_dashboard.php'>LIVE RESULTS</a></li>
        <li><a href="contact.php">CONTACT US</a></li>

          <li class="user user-menu">
            <a href="#">
              <img src="<?= (!empty($voter['photo'])) ? 'images/'.$voter['photo'] : 'images/profile.jpg' ?>" class="user-image" alt="User Image">
              <span class="hidden-xs">
                <?= htmlspecialchars($voter['firstname'].' '.$voter['lastname']) ?>
                <?php if (!empty($current_election['title'])): ?>
                  <small class="label label-info" style="margin-left:6px;"><?= htmlspecialchars($current_election['title']) ?></small>
                <?php endif; ?>
              </span>
            </a>
          </li>
          <li><a href="logout.php"><i class="fa fa-sign-out"></i> LOGOUT</a></li>
        </ul>
      </div>
    </div>
  </nav>
</header>