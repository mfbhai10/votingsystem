<?php 
include 'includes/session.php'; // Ensures only logged-in voters can see this
include 'includes/header.php'; 
?>
<body class="hold-transition skin-blue layout-top-nav">
<div class="wrapper">

    <?php include 'includes/navbar.php'; ?>

    <div class="content-wrapper">
        <div class="container">
            <section class="content">
                <h1 class="page-header text-center title"><b>LIVE ELECTION RESULTS</b></h1>
                <div class="row">
                    <div class="col-sm-10 col-sm-offset-1">
                        <div id="live-results-container">
                            <p class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading results...</p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

<?php include 'includes/scripts.php'; ?>

<script>
$(document).ready(function(){
    // Function to fetch and display live results
function fetchAllLiveResults() {
    $.ajax({
        url: 'get_all_live_results.php', // The PHP script that provides the data
        method: 'GET',
        dataType: 'json', 
        success: function(data) {
            var resultsHtml = '';
            var totalVotes = 0;

            if (data.length === 0) {
                resultsHtml = '<div class="alert alert-info text-center">No live elections or results to display at the moment.</div>';
            } else {
                data.forEach(function(election) {
                    // Start of Box for Election
                    resultsHtml += '<div class="box box-solid box-default">';
                    resultsHtml += '<div class="box-header with-border bg-gray-light">';
                    resultsHtml += '<h3 class="box-title"><i class="fa fa-line-chart"></i> <b>LIVE: ' + election.election_title + '</b></h3>';
                    resultsHtml += '<div class="box-tools pull-right"><span class="badge bg-red">LIVE VOTE COUNT</span></div>';
                    resultsHtml += '</div>';
                    
                    resultsHtml += '<div class="box-body">';
                    
                    // Loop through positions
                    election.positions.forEach(function(position) {
                        
                        // Calculate total votes for the current position
                        totalVotes = position.candidates.reduce(function(sum, candidate) {
                            return sum + parseInt(candidate.vote_count);
                        }, 0);
                        
                        // Start of Position Panel
                        resultsHtml += '<div class="panel panel-info">';
                        resultsHtml += '<div class="panel-heading"><h4 class="panel-title">' + position.position + '</h4></div>';
                        resultsHtml += '<div class="panel-body">';
                        
                        // Loop through candidates
                        position.candidates.forEach(function(candidate, index) {
                            var voteCount = parseInt(candidate.vote_count);
                            // Calculate percentage for progress bar
                            var percentage = totalVotes > 0 ? ((voteCount / totalVotes) * 100).toFixed(1) : 0;
                            
                            // Use different colors for the leader
                            var barColor = index === 0 ? 'progress-bar-success' : 'progress-bar-primary';
                            var textClass = index === 0 ? 'text-green' : 'text-blue';

                            resultsHtml += '<p class="text-sm"><strong>' + candidate.candidate + '</strong> (' + voteCount + ' Votes)</p>';
                            
                            // Progress Bar UI
                            resultsHtml += '<div class="progress progress-sm active">';
                            resultsHtml += '<div class="progress-bar ' + barColor + '" role="progressbar" aria-valuenow="' + percentage + '" aria-valuemin="0" aria-valuemax="100" style="width: ' + percentage + '%">';
                            resultsHtml += '</div>';
                            resultsHtml += '</div>';
                            resultsHtml += '<p class="pull-right ' + textClass + '" style="margin-top: -15px;">' + percentage + '%</p>';
                        });
                        
                        resultsHtml += '</div></div>'; // End of Panel
                    });

                    resultsHtml += '</div></div>'; // End of Box
                });
            }
            
            // Update the page with the generated HTML
            $('#live-results-container').html(resultsHtml);
        },
        error: function() {
            $('#live-results-container').html('<p class="alert alert-danger">Could not fetch live results. Please try again later.</p>');
        }
    });
}

// Ensure polling is still active
setInterval(fetchAllLiveResults, 5000);
fetchAllLiveResults();
});
</script>

</body>
</html>