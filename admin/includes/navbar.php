<header class="main-header">
  <a href="#" class="logo"><span class="logo-mini"><b>V</b>S</span><span class="logo-lg"><b>Voting</b>System</span></a>
  <nav class="navbar navbar-static-top" role="navigation">
    <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button"><span class="sr-only">Toggle navigation</span></a>
    <div class="navbar-custom-menu">
      <ul class="nav navbar-nav">
        <?php if (!empty($admin_election['title'])): ?>
          <li><a><span class="label label-info" style="font-size:12px;">Election: <?= htmlspecialchars($admin_election['title']) ?></span></a></li>
        <?php endif; ?>
        <li class="dropdown user user-menu">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown">
            <img src="<?= (!empty($user['photo'])) ? '../images/'.$user['photo'] : '../images/profile.jpg' ?>" class="user-image" alt="User Image">
            <span class="hidden-xs"><?= htmlspecialchars($user['firstname'].' '.$user['lastname']) ?></span>
          </a>
          <ul class="dropdown-menu">
            <li class="user-header">
              <img src="<?= (!empty($user['photo'])) ? '../images/'.$user['photo'] : '../images/profile.jpg' ?>" class="img-circle" alt="User Image">
              <p><?= htmlspecialchars($user['firstname'].' '.$user['lastname']) ?><small>Administrator</small></p>
            </li>
            <li class="user-footer">
              <div class="pull-left"><a href="#profile" data-toggle="modal" class="btn btn-default btn-flat">Profile</a></div>
              <div class="pull-right"><a href="logout.php" class="btn btn-default btn-flat">Sign out</a></div>
            </li>
          </ul>
        </li>
      </ul>
    </div>
  </nav>
</header>