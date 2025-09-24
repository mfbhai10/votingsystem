<?php
// admin/includes/votes_modal.php
?>
<!-- Reset Votes (এই ইলেকশনের জন্য) -->
<div class="modal fade" id="reset">
  <div class="modal-dialog"><div class="modal-content">
    <form action="votes_reset.php" method="post">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Reset Votes</h4>
      </div>
      <div class="modal-body">
        <p>আপনি কি নিশ্চিত? এই অ্যাকশন করলে <b>শুধুমাত্র বর্তমান নির্বাচিত ইলেকশনের</b> সব ভোট মুছে যাবে।</p>
      </div>
      <div class="modal-footer">
        <button class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button class="btn btn-danger" type="submit">Reset</button>
      </div>
    </form>
  </div></div>
</div>