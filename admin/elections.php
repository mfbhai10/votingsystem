<?php require 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
  <?php include 'includes/navbar.php'; ?>
  <?php include 'includes/menubar.php'; ?>
  <div class="content-wrapper">
    <section class="content-header"><h1>Elections</h1></section>
    <section class="content">
      <div class="box">
        <div class="box-header with-border">
          <a href="#addnew" data-toggle="modal" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-plus"></i> New</a>
        </div>
        <div class="box-body">
          <table id="example1" class="table table-bordered">
            <thead><tr><th>Title</th><th>Window</th><th>Status</th><th>Context</th><th>Tools</th></tr></thead>
            <tbody>
              <?php $r=$conn->query('SELECT * FROM elections ORDER BY created_at DESC'); while($e=$r->fetch_assoc()): ?>
                <tr>
                  <td><?= htmlspecialchars($e['title']) ?></td>
                  <td><?= htmlspecialchars($e['starts_at']).' → '.htmlspecialchars($e['ends_at']) ?></td>
                  <td><span class="label label-default"><?= htmlspecialchars($e['status']) ?></span></td>
                  <td>
                    <?php if(isset($_SESSION['admin_election_id']) && (int)$_SESSION['admin_election_id']===(int)$e['id']): ?>
                      <span class="label label-info">Selected</span>
                    <?php else: ?>
                      <a class="btn btn-xs btn-default" href="set_admin_election.php?id=<?= (int)$e['id'] ?>">Use</a>
                    <?php endif; ?>
                  </td>
                  <td>
                    <a class="btn btn-xs btn-success" href="#" data-toggle="modal" data-target="#edit_<?= (int)$e['id'] ?>">Edit</a>
                    <a class="btn btn-xs btn-danger" href="elections_delete.php?id=<?= (int)$e['id'] ?>" onclick="return confirm('Delete election and all related data?')">Delete</a>
                  </td>
                </tr>

                <!-- Edit modal -->
                <div class="modal fade" id="edit_<?= (int)$e['id'] ?>"><div class="modal-dialog"><div class="modal-content">
                  <form method="post" action="elections_edit.php">
                    <input type="hidden" name="id" value="<?= (int)$e['id'] ?>">
                    <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">Edit</h4></div>
                    <div class="modal-body">
                      <div class="form-group"><label>Title</label><input class="form-control" name="title" value="<?= htmlspecialchars($e['title']) ?>" required></div>
                      <div class="form-group"><label>Description</label><textarea class="form-control" name="description"><?= htmlspecialchars($e['description']) ?></textarea></div>
                      <div class="form-group"><label>Start</label><input type="datetime-local" class="form-control" name="starts_at" value="<?= str_replace(' ','T',$e['starts_at']) ?>" required></div>
                      <div class="form-group"><label>End</label><input type="datetime-local" class="form-control" name="ends_at" value="<?= str_replace(' ','T',$e['ends_at']) ?>" required></div>
                      <div class="form-group"><label>Status</label><select class="form-control" name="status"><option <?= $e['status']=='draft'?'selected':'' ?>>draft</option><option <?= $e['status']=='scheduled'?'selected':'' ?>>scheduled</option><option <?= $e['status']=='open'?'selected':'' ?>>open</option><option <?= $e['status']=='closed'?'selected':'' ?>>closed</option><option <?= $e['status']=='archived'?'selected':'' ?>>archived</option></select></div>
                    </div>
                    <div class="modal-footer"><button class="btn btn-primary">Save</button></div>
                  </form>
                </div></div></div>

              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </div>

  <!-- Add modal -->
  <div class="modal fade" id="addnew"><div class="modal-dialog"><div class="modal-content">
    <form method="post" action="elections_add.php">
      <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">New Election</h4></div>
      <div class="modal-body">
        <div class="form-group"><label>Title</label><input class="form-control" name="title" required></div>
        <div class="form-group"><label>Description</label><textarea class="form-control" name="description"></textarea></div>
        <div class="form-group"><label>Start</label><input type="datetime-local" class="form-control" name="starts_at" required></div>
        <div class="form-group"><label>End</label><input type="datetime-local" class="form-control" name="ends_at" required></div>
        <div class="form-group"><label>Status</label><select class="form-control" name="status"><option>draft</option><option>scheduled</option><option selected>open</option><option>closed</option><option>archived</option></select></div>
      </div>
      <div class="modal-footer"><button class="btn btn-primary">Create</button></div>
    </form>
  </div></div></div>

  <?php include 'includes/footer.php'; ?>
</div>
<?php include 'includes/scripts.php'; ?>
</body>
</html>