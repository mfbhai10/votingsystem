<?php
require 'includes/session.php';

// Fetch open elections (display all open elections)
$stmt = $conn->prepare("SELECT id, title, starts_at, ends_at 
                        FROM elections 
                        WHERE status='open' AND starts_at <= NOW() AND ends_at >= NOW() 
                        ORDER BY ends_at ASC");
$stmt->execute();
$res = $stmt->get_result();

include 'includes/header.php';
?>
<body class="hold-transition skin-blue layout-top-nav">
<div class="wrapper">
  <?php include 'includes/navbar.php'; ?>
  <div class="content-wrapper">
    <div class="container">
      <section class="content">
        <h1 class="page-header text-center title"><b>Select an Election</b></h1>
        <div class="row">
          <div class="col-sm-8 col-sm-offset-2">
            <?php if (isset($_SESSION['error'])): ?>
              <div class="alert alert-danger">
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
              </div>
            <?php endif; ?>
            <?php if ($res->num_rows === 0): ?>
              <div class="alert alert-info">No active elections available at the moment.</div>
            <?php else: ?>
              <div class="list-group">
                <?php while ($e = $res->fetch_assoc()): ?>
                  <a class="list-group-item" href="set_election.php?id=<?= (int)$e['id'] ?>">
                    <h4 class="list-group-item-heading"><?= htmlspecialchars($e['title']) ?></h4>
                    <p class="list-group-item-text">Open until <?= htmlspecialchars($e['ends_at']) ?></p>
                  </a>
                <?php endwhile; ?>
              </div>
            <?php endif; ?>
            <!-- Display All Elections Live Results -->
            <div id="live-elections-results"></div>
          </div>
        </div>
      </section>
    </div>
  </div>
</div>
<?php include 'includes/scripts.php'; ?>

<script>
// Fetch and display live results for all elections
function fetchAllLiveResults() {
    $.ajax({
        url: 'get_all_live_results.php',  // PHP script that fetches live vote counts for all elections
        method: 'GET',
        success: function(response) {
            var data = JSON.parse(response);
            var resultsHtml = '';
            
            data.forEach(function(election) {
                resultsHtml += '<div class="card mb-4">';
                resultsHtml += '<div class="card-header" style="background-color: #3498db; color: white; border-radius: 15px 15px 0 0;">';
                resultsHtml += '<h5 class="card-title text-center">' + election.election_title + '</h5>';
                resultsHtml += '</div>';
                resultsHtml += '<div class="card-body">';
                
                election.positions.forEach(function(position) {
                    resultsHtml += '<h6>' + position.position + ':</h6>';
                    resultsHtml += '<ul>';
                    position.candidates.forEach(function(candidate) {
                        resultsHtml += '<li>' + candidate.candidate + ': ' + candidate.vote_count + ' votes</li>';
                    });
                    resultsHtml += '</ul>';
                });

                resultsHtml += '</div></div>';
            });

            // Update the page with the election results
            $('#live-elections-results').html(resultsHtml);
        },
        error: function() {
            $('#live-elections-results').html('<p>Error fetching live results.</p>');
        }
    });
}

// Optionally, set up polling to fetch results every 5 seconds (you can adjust the interval)
setInterval(fetchAllLiveResults, 5000);

// Call it once initially to populate the results immediately
fetchAllLiveResults();
</script>
</body>
</html>