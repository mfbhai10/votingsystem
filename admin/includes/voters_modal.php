<?php
// admin/includes/voters_modal.php
// Make sure $conn, session, etc., are included before this (included in your admin pages)

// Fetch available elections (only open elections)
$stmt = $conn->prepare("SELECT * FROM elections WHERE status = 'open'"); // Only open elections
$stmt->execute();
$elections = $stmt->get_result();
?>
<!-- Add Voter -->
<div class="modal fade" id="addnew">
  <div class="modal-dialog"><div class="modal-content">
    <form method="post" action="voters_add.php" enctype="multipart/form-data">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Add Voter</h4>
      </div>
      <div class="modal-body">
        <div class="form-group"><label>Voter ID</label>
          <input class="form-control" name="voters_id" required>
        </div>
        <div class="form-group"><label>Firstname</label>
          <input class="form-control" name="firstname" required>
        </div>
        <div class="form-group"><label>Lastname</label>
          <input class="form-control" name="lastname" required>
        </div>
        <div class="form-group"><label>Password</label>
          <input type="password" class="form-control" name="password" required>
        </div>
        
        <!-- Election Selection Dropdown -->
        <div class="form-group">
          <label for="election_id">Election</label>
          <select class="form-control" name="election_id" id="election_id" required>
            <option value="">Select Election</option>
            <?php while ($election = $elections->fetch_assoc()): ?>
                <option value="<?= $election['id'] ?>"><?= htmlspecialchars($election['title']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>

        <div class="form-group"><label>Photo (optional)</label>
          <input type="file" class="form-control" name="photo" accept="image/*">
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary" name="add">Create</button>
      </div>
    </form>
  </div></div>
</div>

<!-- Edit Voter -->
<div class="modal fade" id="edit">
  <div class="modal-dialog"><div class="modal-content">
    <form method="post" action="voters_edit.php">
      <input type="hidden" name="id" class="id">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Edit Voter</h4>
      </div>
      <div class="modal-body">
        <div class="form-group"><label>Voter ID</label>
          <input class="form-control" name="voters_id" id="edit_voters_id" required>
        </div>
        <div class="form-group"><label>Firstname</label>
          <input class="form-control" name="firstname" id="edit_firstname" required>
        </div>
        <div class="form-group"><label>Lastname</label>
          <input class="form-control" name="lastname" id="edit_lastname" required>
        </div>
        <div class="form-group"><label>New Password (optional)</label>
          <input type="password" class="form-control" name="password" id="edit_password">
          <p class="help-block">Leave blank to keep the current password.</p>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-success" name="edit">Save</button>
      </div>
    </form>
  </div></div>
</div>

<!-- Edit Photo Modal -->
<div class="modal fade" id="edit_photo">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" action="voters_photo.php" enctype="multipart/form-data">
        <input type="hidden" name="id" class="id">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Update Photo</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Photo</label>
            <input type="file" class="form-control" name="photo" accept="image/*" required>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-primary" name="upload">Upload</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Delete Voter -->
<div class="modal fade" id="delete">
  <div class="modal-dialog"><div class="modal-content">
    <form method="post" action="voters_delete.php">
      <input type="hidden" name="id" class="id">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Delete Voter</h4>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to delete <b class="fullname"></b>?</p>
      </div>
      <div class="modal-footer">
        <button class="btn btn-danger" name="delete">Delete</button>
      </div>
    </form>
  </div></div>
</div>