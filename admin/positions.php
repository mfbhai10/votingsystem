<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
  <?php include 'includes/navbar.php'; ?>
  <?php include 'includes/menubar.php'; ?>
  <div class="content-wrapper">
    <section class="content-header"><h1>Positions</h1></section>
    <section class="content">
      <?php if(isset($_SESSION['error'])){ echo '<div class="alert alert-danger">'.$_SESSION['error'].'</div>'; unset($_SESSION['error']); } ?>
      <?php if(isset($_SESSION['success'])){ echo '<div class="alert alert-success">'.$_SESSION['success'].'</div>'; unset($_SESSION['success']); } ?>
      <div class="box">
        <div class="box-header with-border">
          <a href="#addnew" data-toggle="modal" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-plus"></i> New</a>
        </div>
        <div class="box-body">
          <table id="example1" class="table table-bordered">
            <thead><tr><th>Description</th><th>Max Vote</th><th>Priority</th><th>Tools</th></tr></thead>
            <tbody>
            <?php
              $stmt = $conn->prepare('SELECT * FROM positions WHERE election_id=? ORDER BY priority ASC');
              $stmt->bind_param('i', $adminElectionId);
              $stmt->execute();
              $res = $stmt->get_result();
              while($row = $res->fetch_assoc()): ?>
                <tr>
                  <td><?= htmlspecialchars($row['description']) ?></td>
                  <td><?= (int)$row['max_vote'] ?></td>
                  <td><?= (int)$row['priority'] ?></td>
                  <td>
                    <button class="btn btn-success btn-sm edit btn-flat" data-id="<?= (int)$row['id'] ?>"><i class="fa fa-edit"></i> Edit</button>
                    <button class="btn btn-danger btn-sm delete btn-flat" data-id="<?= (int)$row['id'] ?>"><i class="fa fa-trash"></i> Delete</button>
                  </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </div>
  <?php include 'includes/footer.php'; ?>
  <?php include 'includes/positions_modal.php'; ?>
</div>
<?php include 'includes/scripts.php'; ?>
<script>
$(function(){
  $(document).on('click','.edit',function(e){ e.preventDefault(); $('#edit').modal('show'); getRow($(this).data('id')); });
  $(document).on('click','.delete',function(e){ e.preventDefault(); $('#delete').modal('show'); getRow($(this).data('id')); });
});
function getRow(id){ $.post('positions_row.php',{id:id},function(r){ $('.id').val(r.id); $('#edit_description').val(r.description); $('#edit_max_vote').val(r.max_vote); $('.description').html(r.description); },'json'); }
</script>
</body>
</html>