<?php
include 'includes/session.php';

// 1. Check if an election has been chosen from the selection page
if (empty($_SESSION['election_id'])) {
    header('Location: choose_election.php'); 
    exit;
}

$electionId = (int)$_SESSION['election_id'];
$voterId = (int)$voter['id'];

// 2. Security Check: Verify the logged-in voter is actually registered for the selected election
$stmt = $conn->prepare("SELECT 1 FROM voter_elections WHERE voter_id = ? AND election_id = ?");
$stmt->bind_param('ii', $voterId, $electionId);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
    $_SESSION['error'] = 'You are not registered for this election.';
    unset($_SESSION['election_id']); // Clear invalid session data
    header('Location: choose_election.php');
    exit;
}

// 3. Fetch the current election details and check if it's open
$chk = $conn->prepare("SELECT id, title FROM elections WHERE id=? AND status='open' AND starts_at <= NOW() AND ends_at >= NOW()");
$chk->bind_param('i', $electionId);
$chk->execute();
$current_election = $chk->get_result()->fetch_assoc();

if (!$current_election) {
    $_SESSION['error'] = 'This election is not open for voting right now.';
    header('Location: choose_election.php'); 
    exit;
}

include 'includes/header.php'; 
?>

<body class="hold-transition skin-blue layout-top-nav">
<div class="wrapper">
  <?php include 'includes/navbar.php'; ?>
  <div class="content-wrapper">
    <div class="container">
      <section class="content">
        <h1 class="page-header text-center title"><b><?= strtoupper(htmlspecialchars($current_election['title'])) ?></b></h1>
        <div class="text-center"><a href="choose_election.php" class="btn btn-default btn-sm">Change Election</a></div>
        <br>

        <div class="row">
          <div class="col-sm-10 col-sm-offset-1">
            <?php if (isset($_SESSION['error'])): ?>
              <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <ul><?php foreach((array)$_SESSION['error'] as $e) echo '<li>'.htmlspecialchars($e).'</li>'; ?></ul>
              </div>
              <?php unset($_SESSION['error']); endif; ?>
            <?php if (isset($_SESSION['success'])): ?>
              <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <h4><i class="icon fa fa-check"></i> Success!</h4>
                <?= $_SESSION['success']; unset($_SESSION['success']); ?>
              </div>
            <?php endif; ?>

            <div class="alert alert-danger alert-dismissible" id="alert" style="display:none;">
              <button type="button" class="close" data-dismiss="alert">&times;</button>
              <span class="message"></span>
            </div>

            <?php
              $stmt_voted = $conn->prepare('SELECT 1 FROM votes WHERE election_id=? AND voters_id=? LIMIT 1');
              $stmt_voted->bind_param('ii', $electionId, $voter['id']);
              $stmt_voted->execute();
              $alreadyVoted = $stmt_voted->get_result()->num_rows > 0;

              if ($alreadyVoted): ?>
                <div class="text-center">
                  <h3>You have already voted in this election.</h3>
                  <a href="#view" data-toggle="modal" class="btn btn-flat btn-primary btn-lg">View Ballot</a>
                  <button id="showResultBtn" class="btn btn-info btn-lg">Show Results</button>
                </div>
              <?php else: ?>
                <form method="POST" id="ballotForm" action="submit_ballot.php">
                  <input type="hidden" name="election_id" value="<?= $electionId ?>">
                  <?php
                    include 'includes/slugify.php';
                    $pstmt = $conn->prepare('SELECT * FROM positions WHERE election_id=? ORDER BY priority ASC');
                    $pstmt->bind_param('i', $electionId);
                    $pstmt->execute();
                    $positions = $pstmt->get_result();

                    while ($row = $positions->fetch_assoc()):
                      $cstmt = $conn->prepare('SELECT * FROM candidates WHERE position_id=?');
                      $cstmt->bind_param('i', $row['id']);
                      $cstmt->execute();
                      $cands = $cstmt->get_result();
                      $candidateHtml = '';

                      while ($crow = $cands->fetch_assoc()):
                        $slug = slugify($row['description']);
                        $checked = '';
                        $input = ($row['max_vote'] > 1)
                          ? '<input type="checkbox" class="flat-red '.$slug.'" name="'.$slug.'[]" value="'.$crow['id'].'" '.$checked.'>'
                          : '<input type="radio" class="flat-red '.$slug.'" name="'.slugify($row['description']).'" value="'.$crow['id'].'" '.$checked.'>';
                        $image = (!empty($crow['photo'])) ? 'images/'.$crow['photo'] : 'images/profile.jpg';
                        $candidateHtml .= '<li>'.$input.'<button type="button" class="btn btn-primary btn-sm btn-flat clist platform" data-platform="'.htmlspecialchars($crow['platform']).'" data-fullname="'.htmlspecialchars($crow['firstname'].' '.$crow['lastname']).'"><i class="fa fa-search"></i> Platform</button><img src="'.$image.'" height="100" width="100" class="clist"><span class="cname clist">'.htmlspecialchars($crow['firstname'].' '.$crow['lastname']).'</span></li>';
                      endwhile;
                      $instruct = ($row['max_vote']>1) ? 'You may select up to '.$row['max_vote'].' candidates' : 'Select only one candidate';
                  ?>
                    <div class="row">
                      <div class="col-xs-12">
                        <div class="box box-solid" id="<?= $row['id'] ?>">
                          <div class="box-header with-border"><h3 class="box-title"><b><?= htmlspecialchars($row['description']) ?></b></h3></div>
                          <div class="box-body">
                            <p><?= $instruct ?>
                              <span class="pull-right"><button type="button" class="btn btn-success btn-sm btn-flat reset" data-desc="<?= slugify($row['description']) ?>"><i class="fa fa-refresh"></i> Reset</button></span>
                            </p>
                            <div id="candidate_list"><ul><?= $candidateHtml ?></ul></div>
                          </div>
                        </div>
                      </div>
                    </div>
                  <?php endwhile; ?>

                  <div class="text-center">
                    <button type="button" class="btn btn-success btn-flat" id="preview"><i class="fa fa-file-text"></i> Preview</button>
                    <button type="submit" class="btn btn-primary btn-flat" name="vote"><i class="fa fa-check-square-o"></i> Submit</button>
                  </div>
                </form>
              <?php endif; ?>
              
              <div id="resultsSection" style="display:none; margin-top: 30px;">
                <h2 class="text-center">Election Results</h2>
                <div id="results"></div>
              </div>
          </div>
        </div>
      </section>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
<?php include 'includes/scripts.php'; ?>

<script>
$(function(){
  // Your existing JS code...
  $('.content').iCheck({
    checkboxClass: 'icheckbox_flat-green',
    radioClass: 'iradio_flat-green'
  });
  $(document).on('click', '.reset', function(e){ e.preventDefault(); var desc = $(this).data('desc'); $('.'+desc).iCheck('uncheck'); });
  $(document).on('click', '.platform', function(e){ e.preventDefault(); $('#platform').modal('show'); var platform = $(this).data('platform'); var fullname = $(this).data('fullname'); $('.candidate').html(fullname); $('#plat_view').html(platform); });
  $('#preview').click(function(e){
    e.preventDefault();
    var form = $('#ballotForm').serialize();
    if(form == ''){
      $('.message').html('You must vote for at least one candidate');
      $('#alert').show();
    } else {
      $.ajax({
        type: 'POST', url: 'preview.php', data: form, dataType: 'json',
        success: function(response){
          if(response.error){
            $('.message').html(response.message.join(''));
            $('#alert').show();
          } else {
            $('#preview_modal').modal('show');
            $('#preview_body').html(response.list);
          }
        }
      });
    }
  });

  // Handle click on "Show Results" button
  $('#showResultBtn').click(function(){
    $('#resultsSection').slideToggle();
    $.ajax({
      url: 'get_results.php',
      type: 'GET',
      data: { election_id: <?= $electionId ?> },
      success: function(data) { $('#results').html(data); },
      error: function() { $('#results').html('<p class="text-danger">Error fetching results.</p>');}
    });
  });
});
</script>
<?php include 'includes/ballot_modal.php'; ?>
</body>
</html>