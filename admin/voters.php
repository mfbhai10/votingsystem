<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
  <?php include 'includes/navbar.php'; ?>
  <?php include 'includes/menubar.php'; ?>
  <div class="content-wrapper">
    <section class="content-header"><h1>Voters</h1></section>
    <section class="content">
      <?php if(isset($_SESSION['error'])){ echo '<div class="alert alert-danger">'.$_SESSION['error'].'</div>'; unset($_SESSION['error']); } ?>
      <?php if(isset($_SESSION['success'])){ echo '<div class="alert alert-success">'.$_SESSION['success'].'</div>'; unset($_SESSION['success']); } ?>
      <div class="box">
        <div class="box-header with-border">
          <a href="#addnew" data-toggle="modal" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-plus"></i> New</a>
        </div>
        <div class="box-body">
          <table id="example1" class="table table-bordered">
            <thead>
              <tr><th>Photo</th><th>Voter ID</th><th>Firstname</th><th>Lastname</th><th>Election</th><th>Tools</th></tr>
            </thead>
            <tbody>
            <?php
              // Fetch voters along with their associated election
              $rs = $conn->query('SELECT v.*, e.title AS election_title FROM voters v LEFT JOIN elections e ON v.election_id = e.id ORDER BY v.lastname, v.firstname');
              while($row = $rs->fetch_assoc()):
                $image = (!empty($row['photo'])) ? '../images/'.$row['photo'] : '../images/profile.jpg'; ?>
                <tr>
                  <td><img src="<?= $image ?>" width="30" height="30"> <a href="#edit_photo" data-toggle="modal" class="pull-right photo" data-id="<?= (int)$row['id'] ?>"><span class="fa fa-edit"></span></a></td>
                  <td><?= htmlspecialchars($row['voters_id']) ?></td>
                  <td><?= htmlspecialchars($row['firstname']) ?></td>
                  <td><?= htmlspecialchars($row['lastname']) ?></td>
                  <td><?= htmlspecialchars($row['election_title']) ?></td> <!-- Display Election Title -->
                  <td>
                    <button class="btn btn-success btn-sm edit btn-flat" data-id="<?= (int)$row['id'] ?>"><i class="fa fa-edit"></i> Edit</button>
                    <button class="btn btn-danger btn-sm delete btn-flat" data-id="<?= (int)$row['id'] ?>"><i class="fa fa-trash"></i> Delete</button>
                  </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </div>
  <?php include 'includes/footer.php'; ?>

  <!-- Modals -->
  <div class="modal fade" id="addnew"><div class="modal-dialog"><div class="modal-content">
    <form method="post" action="voters_add.php" enctype="multipart/form-data">
      <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">Add Voter</h4></div>
      <div class="modal-body">
        <div class="form-group"><label>Voter ID</label><input class="form-control" name="voters_id" required></div>
        <div class="form-group"><label>Firstname</label><input class="form-control" name="firstname" required></div>
        <div class="form-group"><label>Lastname</label><input class="form-control" name="lastname" required></div>
        <div class="form-group"><label>Password</label><input type="password" class="form-control" name="password" required></div>

        <!-- Election Selection Dropdown -->
        <div class="form-group">
          <label for="election_id">Election</label>
          <select class="form-control" name="election_id" id="election_id" required>
            <option value="">Select Election</option>
            <?php
            // Fetch open elections
            $election_query = $conn->query("SELECT * FROM elections WHERE status = 'open'");
            while ($election = $election_query->fetch_assoc()): ?>
                <option value="<?= $election['id'] ?>"><?= htmlspecialchars($election['title']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>

        <div class="form-group"><label>Photo</label><input type="file" class="form-control" name="photo" accept="image/*"></div>
      </div>
      <div class="modal-footer"><button class="btn btn-primary" name="add">Create</button></div>
    </form>
  </div></div></div>

  <!-- Edit Voter -->
  <div class="modal fade" id="edit"><div class="modal-dialog"><div class="modal-content">
    <form method="post" action="voters_edit.php">
      <input type="hidden" name="id" class="id">
      <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">Edit Voter</h4></div>
      <div class="modal-body">
        <div class="form-group"><label>Voter ID</label><input class="form-control" name="voters_id" id="edit_voters_id" required></div>
        <div class="form-group"><label>Firstname</label><input class="form-control" name="firstname" id="edit_firstname" required></div>
        <div class="form-group"><label>Lastname</label><input class="form-control" name="lastname" id="edit_lastname" required></div>
        <div class="form-group"><label>New Password (optional)</label><input type="password" class="form-control" name="password"></div>
      </div>
      <div class="modal-footer"><button class="btn btn-success" name="edit">Save</button></div>
    </form>
  </div></div></div>

  <!-- Update Photo -->
  <div class="modal fade" id="edit_photo"><div class="modal-dialog"><div class="modal-content">
    <form method="post" action="voters_photo.php" enctype="multipart/form-data">
      <input type="hidden" name="id" class="id">
      <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">Update Photo</h4></div>
      <div class="modal-body">
        <div class="form-group"><label>Photo</label><input type="file" class="form-control" name="photo" accept="image/*" required></div>
      </div>
      <div class="modal-footer"><button class="btn btn-primary" name="upload">Upload</button></div>
    </form>
  </div></div></div>

  <!-- Delete Voter -->
  <div class="modal fade" id="delete"><div class="modal-dialog"><div class="modal-content">
    <form method="post" action="voters_delete.php">
      <input type="hidden" name="id" class="id">
      <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">Delete Voter</h4></div>
      <div class="modal-body"><p>Delete <b class="fullname"></b>?</p></div>
      <div class="modal-footer"><button class="btn btn-danger" name="delete">Delete</button></div>
    </form>
  </div></div></div>

</div>
<?php include 'includes/scripts.php'; ?>
<script>
$(function(){
  $(document).on('click','.edit',function(e){ e.preventDefault(); $('#edit').modal('show'); getRow($(this).data('id')); });
  $(document).on('click','.delete',function(e){ e.preventDefault(); $('#delete').modal('show'); getRow($(this).data('id')); });
  $(document).on('click','.photo',function(e){ e.preventDefault(); $('#edit_photo').modal('show'); getRow($(this).data('id')); });
});
function getRow(id){ $.post('voters_row.php',{id:id},function(r){ $('.id').val(r.id); $('#edit_voters_id').val(r.voters_id); $('#edit_firstname').val(r.firstname); $('#edit_lastname').val(r.lastname); $('.fullname').text(r.firstname+' '+r.lastname); },'json'); }
</script>
</body>
</html>