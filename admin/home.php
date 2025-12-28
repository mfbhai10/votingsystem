<?php include 'includes/session.php'; ?>
<?php include 'includes/slugify.php'; ?>
<?php include 'includes/header.php'; ?>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

  <?php include 'includes/navbar.php'; ?>
  <?php include 'includes/menubar.php'; ?>

  <div class="content-wrapper">
    <section class="content-header">
      <h1>Dashboard</h1>
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

      <div class="row">
        <!-- Positions count -->
        <div class="col-lg-3 col-xs-6">
          <div class="small-box bg-aqua">
            <div class="inner">
              <?php
                $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM positions WHERE election_id=?");
                $stmt->bind_param("i", $adminElectionId);
                $stmt->execute(); $count = $stmt->get_result()->fetch_assoc()['cnt'];
                echo "<h3>".$count."</h3>";
              ?>
              <p>No. of Positions</p>
            </div>
            <div class="icon"><i class="fa fa-tasks"></i></div>
            <a href="positions.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>

        <!-- Candidates count -->
        <div class="col-lg-3 col-xs-6">
          <div class="small-box bg-green">
            <div class="inner">
              <?php
                $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM candidates WHERE election_id=?");
                $stmt->bind_param("i", $adminElectionId);
                $stmt->execute(); $count = $stmt->get_result()->fetch_assoc()['cnt'];
                echo "<h3>".$count."</h3>";
              ?>
              <p>No. of Candidates</p>
            </div>
            <div class="icon"><i class="fa fa-black-tie"></i></div>
            <a href="candidates.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>

        <!-- Voters count -->
        <div class="col-lg-3 col-xs-6">
          <div class="small-box bg-yellow">
            <div class="inner">
              <?php
                $res = $conn->query("SELECT COUNT(*) as cnt FROM voters");
                echo "<h3>".$res->fetch_assoc()['cnt']."</h3>";
              ?>
              <p>Total Voters</p>
            </div>
            <div class="icon"><i class="fa fa-users"></i></div>
            <a href="voters.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>

        <!-- Voters who voted (this election only) -->
        <div class="col-lg-3 col-xs-6">
          <div class="small-box bg-red">
            <div class="inner">
              <?php
                $stmt = $conn->prepare("SELECT COUNT(DISTINCT voters_id) as cnt FROM votes WHERE election_id=?");
                $stmt->bind_param("i", $adminElectionId);
                $stmt->execute(); $count = $stmt->get_result()->fetch_assoc()['cnt'];
                echo "<h3>".$count."</h3>";
              ?>
              <p>Voters Voted</p>
            </div>
            <div class="icon"><i class="fa fa-edit"></i></div>
            <a href="votes.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
      </div>

      <!-- Tally charts -->
      <div class="row">
        <div class="col-xs-12">
          <h3>Votes Tally
            <!-- <span class="pull-right">
              <a href="print.php" class="btn btn-success btn-sm btn-flat"><span class="glyphicon glyphicon-print"></span> Print</a>
            </span> -->
          </h3>
        </div>
      </div>

      <?php
        $stmt = $conn->prepare("SELECT * FROM positions WHERE election_id=? ORDER BY priority ASC");
        $stmt->bind_param("i", $adminElectionId);
        $stmt->execute(); $positions = $stmt->get_result();
        $inc = 2;
        while($row = $positions->fetch_assoc()){
          $inc = ($inc == 2) ? 1 : $inc+1;
          if($inc == 1) echo "<div class='row'>";
          echo "
            <div class='col-sm-6'>
              <div class='box box-solid'>
                <div class='box-header with-border'>
                  <h4 class='box-title'><b>".htmlspecialchars($row['description'])."</b></h4>
                </div>
                <div class='box-body'>
                  <div class='chart'>
                    <canvas id='".slugify($row['description'])."' style='height:200px'></canvas>
                  </div>
                </div>
              </div>
            </div>";
          if($inc == 2) echo "</div>";
        }
        if($inc == 1) echo "<div class='col-sm-6'></div></div>";
      ?>
    </section>
  </div>
  <?php include 'includes/footer.php'; ?>
</div>

<?php include 'includes/scripts.php'; ?>
<?php
// চার্ট ডেটা জেনারেট
$stmt = $conn->prepare("SELECT * FROM positions WHERE election_id=? ORDER BY priority ASC");
$stmt->bind_param("i", $adminElectionId);
$stmt->execute(); $positions = $stmt->get_result();
while($row = $positions->fetch_assoc()){
  $carray=[]; $varray=[];
  $c = $conn->prepare("SELECT id, lastname FROM candidates WHERE election_id=? AND position_id=?");
  $c->bind_param("ii", $adminElectionId, $row['id']);
  $c->execute(); $cquery=$c->get_result();
  while($crow=$cquery->fetch_assoc()){
    $carray[]=$crow['lastname'];
    $v=$conn->prepare("SELECT COUNT(*) as cnt FROM votes WHERE election_id=? AND candidate_id=?");
    $v->bind_param("ii", $adminElectionId, $crow['id']);
    $v->execute(); $varray[]=$v->get_result()->fetch_assoc()['cnt'];
  }
  $carray=json_encode($carray);
  $varray=json_encode($varray);
?>
<script>
$(function(){
  var barChartCanvas = $('#<?= slugify($row['description']); ?>').get(0).getContext('2d');
  var barChart = new Chart(barChartCanvas);
  var barChartData = {
    labels: <?= $carray ?>,
    datasets: [{
      label: 'Votes',
      fillColor: 'rgba(60,141,188,0.9)',
      strokeColor: 'rgba(60,141,188,0.8)',
      pointColor: '#3b8bba',
      pointStrokeColor: 'rgba(60,141,188,1)',
      pointHighlightFill: '#fff',
      pointHighlightStroke: 'rgba(60,141,188,1)',
      data: <?= $varray ?>
    }]
  };
  var barChartOptions = {
    scaleBeginAtZero: true,
    scaleShowGridLines: true,
    scaleGridLineColor: 'rgba(0,0,0,.05)',
    scaleGridLineWidth: 1,
    scaleShowHorizontalLines: true,
    scaleShowVerticalLines: true,
    barShowStroke: true,
    barStrokeWidth: 2,
    barValueSpacing: 5,
    barDatasetSpacing: 1,
    responsive: true,
    maintainAspectRatio: true
  };
  barChartOptions.datasetFill = false;
  barChart.HorizontalBar(barChartData, barChartOptions);
});
</script>
<?php } ?>
</body>
</html>