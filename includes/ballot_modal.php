<!-- View Ballot -->
<div class="modal fade" id="view">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">Your Votes</h4>
    </div>
    <div class="modal-body">
      <?php
        $id = $voter['id'];
        $electionId = (int)$current_election['id'];
        $sql = "SELECT positions.description, c.firstname AS canfirst, c.lastname AS canlast
                FROM votes v
                JOIN candidates c ON c.id=v.candidate_id
                JOIN positions  p ON p.id=v.position_id
                WHERE v.voters_id=? AND v.election_id=?
                ORDER BY p.priority ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ii', $id, $electionId);
        $stmt->execute();
        $q = $stmt->get_result();
        while($row = $q->fetch_assoc()){
          echo "<div class='row votelist'><span class='col-sm-4'><span class='pull-right'><b>".htmlspecialchars($row['description'])." :</b></span></span><span class='col-sm-8'>".htmlspecialchars($row['canfirst'].' '.$row['canlast'])."</span></div>";
        }
      ?>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
    </div>
  </div></div>
</div>