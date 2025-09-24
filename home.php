<?php require 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>
<body class="hold-transition skin-blue layout-top-nav">
<div class="wrapper">
  <?php include 'includes/navbar.php'; ?>
  <div class="content-wrapper"><div class="container">
    <section class="content">
      <h1 class="page-header text-center title"><b><?= strtoupper($current_election['title']) ?></b></h1>

      <div class="row">
        <div class="col-sm-10 col-sm-offset-1">
        <?php if(isset($_SESSION['error'])): ?>
          <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <ul><?php foreach((array)$_SESSION['error'] as $e) echo '<li>'.htmlspecialchars($e).'</li>'; ?></ul>
          </div>
          <?php unset($_SESSION['error']); endif; ?>
        <?php if(isset($_SESSION['success'])): ?>
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
          $electionId = (int)$current_election['id'];
          $stmt = $conn->prepare('SELECT 1 FROM votes WHERE election_id=? AND voters_id=? LIMIT 1');
          $stmt->bind_param('ii', $electionId, $voter['id']);
          $stmt->execute();
          $already = $stmt->get_result()->num_rows > 0;
          if ($already): ?>
            <div class="text-center">
              <h3>You have already voted in this election.</h3>
              <a href="#view" data-toggle="modal" class="btn btn-flat btn-primary btn-lg">View Ballot</a>
            </div>
        <?php else: ?>
          <form method="POST" id="ballotForm" action="submit_ballot.php">
          <?php
            require 'includes/slugify.php';
            $pstmt = $conn->prepare('SELECT * FROM positions WHERE election_id=? ORDER BY priority ASC');
            $pstmt->bind_param('i', $electionId);
            $pstmt->execute();
            $positions = $pstmt->get_result();
            while($row = $positions->fetch_assoc()):
              $cstmt = $conn->prepare('SELECT * FROM candidates WHERE election_id=? AND position_id=?');
              $cstmt->bind_param('ii', $electionId, $row['id']);
              $cstmt->execute();
              $cands = $cstmt->get_result();
              $candidateHtml = '';
              while($crow = $cands->fetch_assoc()):
                $slug = slugify($row['description']);
                $checked = '';
                if(isset($_SESSION['post'][$slug])){
                  $value = $_SESSION['post'][$slug];
                  if(is_array($value)) foreach($value as $val){ if((int)$val === (int)$crow['id']) $checked='checked'; }
                  else if((int)$value === (int)$crow['id']) $checked='checked';
                }
                $input = ($row['max_vote'] > 1)
                  ? '<input type="checkbox" class="flat-red '.$slug.'" name="'.$slug.'[]" value="'.$crow['id'].'" '.$checked.'>'
                  : '<input type="radio" class="flat-red '.$slug.'" name="'.slugify($row['description']).'" value="'.$crow['id'].'" '.$checked.'>';
                $image = (!empty($crow['photo'])) ? 'images/'.$crow['photo'] : 'images/profile.jpg';
                $candidateHtml .= '<li>'.$input.'<button type="button" class="btn btn-primary btn-sm btn-flat clist platform" data-platform="'.htmlspecialchars($crow['platform']).'" data-fullname="'.htmlspecialchars($crow['firstname'].' '.$crow['lastname']).'"><i class="fa fa-search"></i> Platform</button><img src="'.$image.'" height="100" width="100" class="clist"><span class="cname clist">'.htmlspecialchars($crow['firstname'].' '.$crow['lastname']).'</span></li>';
              endwhile;
              $instruct = ($row['max_vote']>1) ? 'You may select up to '.$row['max_vote'].' candidates' : 'Select only one candidate';
          ?>
              <div class="row"><div class="col-xs-12">
                <div class="box box-solid" id="<?= $row['id'] ?>">
                  <div class="box-header with-border"><h3 class="box-title"><b><?= htmlspecialchars($row['description']) ?></b></h3></div>
                  <div class="box-body">
                    <p><?= $instruct ?><span class="pull-right"><button type="button" class="btn btn-success btn-sm btn-flat reset" data-desc="<?= slugify($row['description']) ?>"><i class="fa fa-refresh"></i> Reset</button></span></p>
                    <div id="candidate_list"><ul><?= $candidateHtml ?></ul></div>
                  </div>
                </div>
              </div></div>
          <?php endwhile; ?>
            <div class="text-center">
              <button type="button" class="btn btn-success btn-flat" id="preview"><i class="fa fa-file-text"></i> Preview</button>
              <button type="submit" class="btn btn-primary btn-flat" name="vote"><i class="fa fa-check-square-o"></i> Submit</button>
            </div>
          </form>
        <?php endif; ?>
      </div></div>
    </section>
  </div></div>
  <?php include 'includes/footer.php'; ?>
  <?php include 'includes/ballot_modal.php'; ?>
</div>
<?php include 'includes/scripts.php'; ?>
<script>
$(function(){
  $('.content').iCheck({ checkboxClass:'icheckbox_flat-green', radioClass:'iradio_flat-green' });
  $(document).on('click','.reset',function(e){ e.preventDefault(); var d=$(this).data('desc'); $('.'+d).iCheck('uncheck');});
  $(document).on('click','.platform',function(e){ e.preventDefault(); $('#platform').modal('show'); $('#plat_view').text($(this).data('platform')); $('.candidate').text($(this).data('fullname'));});
  $('#preview').click(function(e){ e.preventDefault(); var form=$('#ballotForm').serialize(); if(!form){ $('.message').html('You must vote at least one candidate'); $('#alert').show(); } else { $.post('preview.php', form, function(resp){ if(resp.error){ var msg=''; for (i in resp.message) { msg += resp.message[i]; } $('.message').html(msg); $('#alert').show(); } else { $('#preview_modal').modal('show'); $('#preview_body').html(resp.list);} }, 'json'); }});
});
</script>
</body>
</html>