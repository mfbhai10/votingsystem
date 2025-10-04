<?php
// admin/includes/positions_modal.php
?>
<!-- Add Position -->
<div class="modal fade" id="addnew">
  <div class="modal-dialog"><div class="modal-content">
    <form method="post" action="positions_add.php">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Add Position</h4>
      </div>
      <div class="modal-body">
        <div class="form-group"><label>Description</label>
          <input class="form-control" name="description" required>
        </div>
        <div class="form-group"><label>Max Vote</label>
          <input type="number" class="form-control" name="max_vote" min="1" value="1" required>
          <p class="help-block">এক পজিশনে একজন ভোটার সর্বোচ্চ কতজন প্রার্থীকে বাছাই করতে পারবে</p>
        </div>
        
        <!-- Election Selection Dropdown -->
        <div class="form-group"><label>Election</label>
          <select class="form-control" name="election_id" required>
            <option value="">Select Election</option>
            <?php
              // Fetch open elections
              $stmt = $conn->prepare("SELECT * FROM elections WHERE status = 'open'"); // Only open elections
              $stmt->execute();
              $elections = $stmt->get_result();
              while ($election = $elections->fetch_assoc()) {
                echo "<option value='" . $election['id'] . "'>" . htmlspecialchars($election['title']) . "</option>";
              }
            ?>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary" name="add">Create</button>
      </div>
    </form>
  </div></div>
</div>

<!-- Edit Position -->
<div class="modal fade" id="edit">
  <div class="modal-dialog"><div class="modal-content">
    <form method="post" action="positions_edit.php">
      <input type="hidden" name="id" class="id">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Edit Position</h4>
      </div>
      <div class="modal-body">
        <div class="form-group"><label>Description</label>
          <input class="form-control" name="description" id="edit_description" required>
        </div>
        <div class="form-group"><label>Max Vote</label>
          <input type="number" class="form-control" name="max_vote" id="edit_max_vote" min="1" required>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-success" name="edit">Save</button>
      </div>
    </form>
  </div></div>
</div>

<!-- Delete Position -->
<div class="modal fade" id="delete">
  <div class="modal-dialog"><div class="modal-content">
    <form method="post" action="positions_delete.php">
      <input type="hidden" name="id" class="id">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Delete Position</h4>
      </div>
      <div class="modal-body">
        <p>আপনি কি নিশ্চিতভাবে <b class="description"></b> পজিশনটি ডিলিট করতে চান?</p>
      </div>
      <div class="modal-footer">
        <button class="btn btn-danger" name="delete">Delete</button>
      </div>
    </form>
  </div></div>
</div>