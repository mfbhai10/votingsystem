<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
  <?php include 'includes/navbar.php'; ?>
  <?php include 'includes/menubar.php'; ?>

  <div class="content-wrapper">
    <section class="content-header">
      <h1>Votes</h1>
    </section>

    <section class="content">
      <?php
        if(isset($_SESSION['error'])){
          echo "<div class='alert alert-danger'>".$_SESSION['error']."</div>";
          unset($_SESSION['error']);
        }
        if(isset($_SESSION['success'])){
          echo "<div class='alert alert-success'>".$_SESSION['success']."</div>";
          unset($_SESSION['success']);
        }
      ?>

      <div class="box">
        <div class="box-header with-border">
          <!-- Reset form -->
          <form action="votes_reset.php" method="post" style="display:inline;" onsubmit="return confirm('Reset all votes for this election?');">
            <button type="submit" class="btn btn-danger btn-sm btn-flat">
              <i class="fa fa-refresh"></i> Reset (this election only)
            </button>
          </form>
        </div>

        <div class="box-body">
          <table id="example1" class="table table-bordered">
            <thead>
              <tr>
                <th>Position</th>
                <th>Candidate</th>
                <th>Voter</th>
              </tr>
            </thead>
            <tbody>
              <?php
                $sql = "SELECT p.description AS position,
                               c.firstname AS canfirst, c.lastname AS canlast,
                               v2.firstname AS votfirst, v2.lastname AS votlast
                        FROM votes v
                        JOIN positions p ON p.id = v.position_id
                        JOIN candidates c ON c.id = v.candidate_id
                        JOIN voters v2 ON v2.id = v.voters_id
                        WHERE v.election_id = ? 
                          AND p.election_id = ? 
                          AND c.election_id = ?
                        ORDER BY p.priority ASC";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iii", $adminElectionId, $adminElectionId, $adminElectionId);
                $stmt->execute();
                $res = $stmt->get_result();

                while($row = $res->fetch_assoc()){
                  echo "<tr>
                          <td>".htmlspecialchars($row['position'])."</td>
                          <td>".htmlspecialchars($row['canfirst'].' '.$row['canlast'])."</td>
                          <td>".htmlspecialchars($row['votfirst'].' '.$row['votlast'])."</td>
                        </tr>";
                }
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </div>

  <?php include 'includes/footer.php'; ?>
</div>
<?php include 'includes/scripts.php'; ?>
</body>
</html>