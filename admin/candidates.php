<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
  <?php include 'includes/navbar.php'; ?>
  <?php include 'includes/menubar.php'; ?>
  <div class="content-wrapper">
    <section class="content-header"><h1>Candidates</h1></section>
    <section class="content">
      <div class="box">
        <div class="box-header with-border"><a href="#addnew" data-toggle="modal" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-plus"></i> New</a></div>
        <div class="box-body">
          <table id="example1" class="table table-bordered">
            <thead><tr><th>Position</th><th>Photo</th><th>Firstname</th><th>Lastname</th><th>Platform</th><th>Tools</th></tr></thead>
            <tbody>
            <?php
              $sql = 'SELECT c.*, p.description FROM candidates c JOIN positions p ON p.id=c.position_id WHERE c.election_id=? AND p.election_id=? ORDER BY p.priority ASC';
              $st = $conn->prepare($sql); $st->bind_param('ii',$adminElectionId,$adminElectionId); $st->execute(); $rs=$st->get_result();
              while($row=$rs->fetch_assoc()):
                $image = (!empty($row['photo'])) ? '../images/'.$row['photo'] : '../images/profile.jpg'; ?>
                <tr>
                  <td><?= htmlspecialchars($row['description']) ?></td>
                  <td><img src="<?= $image ?>" width="30" height="30"> <a href="#edit_photo" data-toggle="modal" class="pull-right photo" data-id="<?= (int)$row['id'] ?>"><span class="fa fa-edit"></span></a></td>
                  <td><?= htmlspecialchars($row['firstname']) ?></td>
                  <td><?= htmlspecialchars($row['lastname']) ?></td>
                  <td><a href="#platform" data-toggle="modal" class="btn btn-info btn-sm btn-flat platform" data-id="<?= (int)$row['id'] ?>"><i class="fa fa-search"></i> View</a></td>
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
  <?php include 'includes/candidates_modal.php'; ?>
</div>
<?php include 'includes/scripts.php'; ?>
<script>
$(function(){
  $(document).on('click','.edit',function(e){ e.preventDefault(); $('#edit').modal('show'); getRow($(this).data('id')); });
  $(document).on('click','.delete',function(e){ e.preventDefault(); $('#delete').modal('show'); getRow($(this).data('id')); });
  $(document).on('click','.photo',function(e){ e.preventDefault(); getRow($(this).data('id')); });
  $(document).on('click','.platform',function(e){ e.preventDefault(); var id=$(this).data('id'); $.post('candidates_row.php',{id:id},function(r){ $('#plat_view').text(r.platform); $('.fullname').text(r.firstname+' '+r.lastname); },'json'); });
});
function getRow(id){ $.post('candidates_row.php',{id:id},function(r){ $('.id').val(r.id); $('#edit_firstname').val(r.firstname); $('#edit_lastname').val(r.lastname); $('#edit_position').val(r.position_id); $('#edit_platform').val(r.platform); $('.fullname').text(r.firstname+' '+r.lastname); },'json'); }
</script>
</body>
</html>