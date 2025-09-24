<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>

<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
  <?php include 'includes/navbar.php'; ?>
  <?php include 'includes/menubar.php'; ?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>Ballot Position</h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Ballot Preview</li>
      </ol>
    </section>
    <!-- Main content -->
    <section class="content">
      <?php
        if(isset($_SESSION['error'])){
          echo "
            <div class='alert alert-danger alert-dismissible'>
              <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
              <h4><i class='icon fa fa-warning'></i> Error!</h4>
              ".$_SESSION['error']."
            </div>
          ";
          unset($_SESSION['error']);
        }
        if(isset($_SESSION['success'])){
          echo "
            <div class='alert alert-success alert-dismissible'>
              <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
              <h4><i class='icon fa fa-check'></i> Success!</h4>
              ".$_SESSION['success']."
            </div>
          ";
          unset($_SESSION['success']);
        }
      ?>

      <div class="row">
        <div class="col-xs-10 col-xs-offset-1" id="content">
          <!-- Ballot Content will be loaded here by AJAX -->
        </div>
      </div>

    </section>
  </div>

  <?php include 'includes/footer.php'; ?>
</div>

<?php include 'includes/scripts.php'; ?>

<script>
$(function(){
  fetch(); // Fetch ballot data on page load

  // Event listener for reset button (to reset votes for a position)
  $(document).on('click', '.reset', function(e){
    e.preventDefault();
    var desc = $(this).data('desc');
    $('.'+desc).iCheck('uncheck');
  });

  // Move up the ballot position
  $(document).on('click', '.moveup', function(e){
    e.preventDefault();
    var id = $(this).data('id');
    $('#'+id).animate({
      'marginTop' : "-300px"
    });
    $.ajax({
      type: 'POST',
      url: 'ballot_up.php',
      data: {id: id},
      dataType: 'json',
      success: function(response){
        if (!response.error) {
          fetch();  // Reload ballot data after position change
        }
      }
    });
  });

  // Move down the ballot position
  $(document).on('click', '.movedown', function(e){
    e.preventDefault();
    var id = $(this).data('id');
    $('#'+id).animate({
      'marginTop' : "+300px"
    });
    $.ajax({
      type: 'POST',
      url: 'ballot_down.php',
      data: {id: id},
      dataType: 'json',
      success: function(response){
        if (!response.error) {
          fetch();  // Reload ballot data after position change
        }
      }
    });
  });

  // When the Platform button is clicked
  $(document).on('click', '.platform', function(e){
    e.preventDefault();
    
    // Get candidate's full name and platform from data attributes
    var fullname = $(this).data('fullname');  // Candidate full name
    var platform = $(this).data('platform');  // Candidate platform message
    
    // Set the candidate's name and platform in the modal
    $('.candidate').text(fullname);           // Candidate's full name
    $('#plat_view').text(platform || 'No platform available'); // Set platform content or fallback message
    
    // Show the modal
    $('#platform').modal('show');
  });
});

// Fetch ballot data (positions and candidates)
function fetch(){
  $.ajax({
    type: 'POST',
    url: 'ballot_fetch.php',  // Fetch the ballot data
    dataType: 'json',
    success: function(response){
      $('#content').html(response).iCheck({checkboxClass: 'icheckbox_flat-green', radioClass: 'iradio_flat-green'});
    }
  });
}
</script>

</body>
</html>