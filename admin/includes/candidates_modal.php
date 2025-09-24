<?php
// admin/includes/candidates_modal.php
// নীচের সিলেক্টে পজিশন লোডের জন্য current election context দরকার
$adminElectionId = (int)($_SESSION['admin_election_id'] ?? 0);
$pos_stmt = $conn->prepare('SELECT id, description FROM positions WHERE election_id=? ORDER BY priority ASC');
$pos_stmt->bind_param('i', $adminElectionId);
$pos_stmt->execute();
$positions_rs = $pos_stmt->get_result();
$positions = [];
while($r = $positions_rs->fetch_assoc()){ $positions[] = $r; }
?>

<!-- Add Candidate -->
<div class="modal fade" id="addnew">
  <div class="modal-dialog"><div class="modal-content">
    <form method="post" action="candidates_add.php" enctype="multipart/form-data">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Add Candidate</h4>
      </div>
      <div class="modal-body">
        <div class="form-group"><label>Position</label>
          <select class="form-control" name="position" required>
            <option value="">-- select --</option>
            <?php foreach($positions as $p): ?>
              <option value="<?= (int)$p['id'] ?>"><?= htmlspecialchars($p['description']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group"><label>Firstname</label>
          <input class="form-control" name="firstname" required>
        </div>
        <div class="form-group"><label>Lastname</label>
          <input class="form-control" name="lastname" required>
        </div>
        <div class="form-group"><label>Platform</label>
          <textarea class="form-control" name="platform" rows="3" placeholder="Manifesto / Platform (optional)"></textarea>
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

<!-- Edit Candidate -->
<div class="modal fade" id="edit">
  <div class="modal-dialog"><div class="modal-content">
    <form method="post" action="candidates_edit.php">
      <input type="hidden" name="id" class="id">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Edit Candidate</h4>
      </div>
      <div class="modal-body">
        <div class="form-group"><label>Position</label>
          <select class="form-control" name="position" id="edit_position" required>
            <?php foreach($positions as $p): ?>
              <option value="<?= (int)$p['id'] ?>"><?= htmlspecialchars($p['description']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group"><label>Firstname</label>
          <input class="form-control" name="firstname" id="edit_firstname" required>
        </div>
        <div class="form-group"><label>Lastname</label>
          <input class="form-control" name="lastname" id="edit_lastname" required>
        </div>
        <div class="form-group"><label>Platform</label>
          <textarea class="form-control" name="platform" id="edit_platform" rows="3"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-success" name="edit">Save</button>
      </div>
    </form>
  </div></div>
</div>

<!-- Update Photo -->
<div class="modal fade" id="edit_photo">
  <div class="modal-dialog"><div class="modal-content">
    <form method="post" action="candidates_photo.php" enctype="multipart/form-data">
      <input type="hidden" name="id" class="id">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Update Photo</h4>
      </div>
      <div class="modal-body">
        <div class="form-group"><label>Photo</label>
          <input type="file" class="form-control" name="photo" accept="image/*" required>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary" name="upload">Upload</button>
      </div>
    </form>
  </div></div>
</div>

<!-- View Platform -->
<div class="modal fade" id="platform">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal">&times;</button>
      <h4 class="modal-title"><span class="fullname"></span> — Platform</h4>
    </div>
    <div class="modal-body">
      <p id="plat_view" style="white-space:pre-line;"></p>
    </div>
  </div></div>
</div>

<!-- Delete Candidate -->
<div class="modal fade" id="delete">
  <div class="modal-dialog"><div class="modal-content">
    <form method="post" action="candidates_delete.php">
      <input type="hidden" name="id" class="id">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Delete Candidate</h4>
      </div>
      <div class="modal-body">
        <p>আপনি কি নিশ্চিতভাবে <b class="fullname"></b> কে ডিলিট করতে চান?</p>
      </div>
      <div class="modal-footer">
        <button class="btn btn-danger" name="delete">Delete</button>
      </div>
    </form>
  </div></div>
</div>