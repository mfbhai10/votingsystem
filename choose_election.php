<?php
include 'includes/session.php';
include 'includes/header.php';

// This is the crucial change: Fetch only the open elections this specific voter is registered for.
$voter_id = $voter['id'];

$sql = "
    SELECT e.id, e.title, e.description, e.ends_at
    FROM elections e
    INNER JOIN voter_elections ve ON e.id = ve.election_id
    WHERE ve.voter_id = ? 
      AND e.status = 'open' 
      AND e.starts_at <= NOW() 
      AND e.ends_at >= NOW()
    ORDER BY e.ends_at ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $voter_id);
$stmt->execute();
$result = $stmt->get_result();

?>
<body class="hold-transition skin-blue layout-top-nav">
<div class="wrapper">

    <?php include 'includes/navbar.php'; ?>

    <div class="content-wrapper">
        <div class="container">
            <section class="content">
                <h1 class="page-header text-center title"><b>CHOOSE YOUR ELECTION</b></h1>
                <div class="row">
                    <div class="col-sm-10 col-sm-offset-1">
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($result->num_rows < 1): ?>
                            <div class="box box-info">
                                <div class="box-body">
                                    <div class="text-center">
                                        <h3><i class="fa fa-info-circle"></i> No Open Elections</h3>
                                        <p>You are not currently registered for any open elections.</p>
                                        <p>Please check back later or contact an administrator if you believe this is an error.</p>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="row">
                                <?php while ($election = $result->fetch_assoc()): ?>
                                    <div class="col-md-6">
                                        <div class="box box-solid box-primary">
                                            <div class="box-header with-border">
                                                <h3 class="box-title"><?= htmlspecialchars($election['title']) ?></h3>
                                                <div class="box-tools pull-right">
                                                    <span class="badge bg-green">OPEN</span>
                                                </div>
                                            </div>
                                            <div class="box-body">
                                                <p><i class="fa fa-calendar-times-o"></i> **Ends:** <?= date('M j, Y, g:i a', strtotime($election['ends_at'])) ?></p>
                                                <p style="margin-top: 10px;">
                                                    <?= htmlspecialchars(substr($election['description'], 0, 150)) . (strlen($election['description']) > 150 ? '...' : '') ?>
                                                </p>
                                            </div>
                                            <div class="box-footer text-center">
                                                <a href="select_election.php?id=<?= $election['id'] ?>" class="btn btn-lg btn-block btn-success">
                                                    <i class="fa fa-check-square-o"></i> Select and Vote
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                            <?php endif; ?>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
<?php include 'includes/scripts.php'; ?>
</body>
</html>