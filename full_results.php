<?php
// full_results.php
include 'includes/session.php';
include 'includes/header.php';

// 1. Get IDs and validate
$electionId = (int)$_GET['election_id'] ?? 0;
$positionId = (int)$_GET['position_id'] ?? 0;

if ($electionId <= 0 || $positionId <= 0) {
    // Handle error if IDs are missing or invalid
    header('Location: home.php');
    exit;
}

// 2. Fetch Position Title for display
$pstmt = $conn->prepare('SELECT title FROM elections WHERE id = ?');
$pstmt->bind_param('i', $electionId);
$pstmt->execute();
$election_title = $pstmt->get_result()->fetch_assoc()['title'] ?? 'Unknown Election';

$pos_stmt = $conn->prepare('SELECT description FROM positions WHERE id = ?');
$pos_stmt->bind_param('i', $positionId);
$pos_stmt->execute();
$position_title = $pos_stmt->get_result()->fetch_assoc()['description'] ?? 'Unknown Position';


// 3. Fetch detailed results (all candidates and their votes)
$sql = "
    SELECT 
        c.firstname, 
        c.lastname, 
        c.photo,
        COUNT(v.candidate_id) AS vote_count
    FROM candidates c
    LEFT JOIN votes v ON c.id = v.candidate_id 
    WHERE c.election_id = ? AND c.position_id = ?
    GROUP BY c.id
    ORDER BY vote_count DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $electionId, $positionId);
$stmt->execute();
$results = $stmt->get_result();

?>

<body class="hold-transition skin-blue layout-top-nav">
<div class="wrapper">
    <?php include 'includes/navbar.php'; ?>
    <div class="content-wrapper">
        <div class="container">
            <section class="content">
                <h1 class="page-header text-center title">
                    <b>DETAILED RESULTS:</b> <?= htmlspecialchars($position_title) ?>
                </h1>
                <p class="text-center text-muted">Election: <?= htmlspecialchars($election_title) ?></p>

                <div class="row">
                    <div class="col-sm-10 col-sm-offset-1">
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title">Vote Breakdown</h3>
                            </div>
                            <div class="box-body">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Candidate</th>
                                            <th class="text-center">Votes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php 
                                    $rank = 1;
                                    while ($row = $results->fetch_assoc()): 
                                        $image = (!empty($row['photo'])) ? 'images/'.$row['photo'] : 'images/profile.jpg';
                                    ?>
                                        <tr>
                                            <td><?= $rank++ ?></td>
                                            <td>
                                                <img src="<?= $image ?>" width="30px" height="30px" style="border-radius: 50%;"> 
                                                <?= htmlspecialchars($row['firstname'] . ' ' . $row['lastname']) ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-green"><?= $row['vote_count'] ?></span>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="box-footer text-center">
                                <a href="javascript:history.back()" class="btn btn-default"><i class="fa fa-arrow-left"></i> Back to Summary</a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
<?php include 'includes/scripts.php'; ?>
</body>
</html>