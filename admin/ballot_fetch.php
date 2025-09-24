<?php
include 'includes/session.php';
include 'includes/slugify.php';

$sql = "SELECT * FROM positions";
$pquery = $conn->query($sql);

$output = ''; // Clear the output variable for positions list
$candidate = ''; // Reset candidates list for each position

$sql = "SELECT * FROM positions ORDER BY priority ASC";  // Get positions ordered by priority
$query = $conn->query($sql);
$num = 1;  // Initialize the position number for priority

while($row = $query->fetch_assoc()){
    // Determine whether to show checkbox or radio button based on max_vote
    $input = ($row['max_vote'] > 1) 
             ? '<input type="checkbox" class="flat-red '.slugify($row['description']).'" name="'.slugify($row['description'])."[]".'">' 
             : '<input type="radio" class="flat-red '.slugify($row['description']).'" name="'.slugify($row['description']).'">';

    // Fetch candidates for the current position
    $sql = "SELECT * FROM candidates WHERE position_id='".$row['id']."'";
    $cquery = $conn->query($sql);
    
    // Clear previous candidate HTML for each position
    $candidate = '';

    while($crow = $cquery->fetch_assoc()){
        // If a candidate has a photo, use it; otherwise, set a default photo
        $image = (!empty($crow['photo'])) ? '../images/'.$crow['photo'] : '../images/profile.jpg';
        
        // Create the list item for the candidate
        $candidate .= '
            <li>
                '.$input.'<button class="btn btn-primary btn-sm btn-flat clist"><i class="fa fa-search"></i> Platform</button>
                <img src="'.$image.'" height="100px" width="100px" class="clist">
                <span class="cname clist">'.$crow['firstname'].' '.$crow['lastname'].'</span>
            </li>
        ';
    }

    // Define instructions based on the max_vote field
    $instruct = ($row['max_vote'] > 1) ? 'You may select up to '.$row['max_vote'].' candidates' : 'Select only one candidate';

    // Disable move up if priority is 1, and disable move down if it's the last position
    $updisable = ($row['priority'] == 1) ? 'disabled' : '';
    $downdisable = ($row['priority'] == $pquery->num_rows) ? 'disabled' : '';

    // Construct the output for this position
    $output .= '
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-solid" id="'.$row['id'].'">
                    <div class="box-header with-border">
                        <h3 class="box-title"><b>'.$row['description'].'</b></h3>
                        <div class="pull-right box-tools">
                            <button type="button" class="btn btn-default btn-sm moveup" data-id="'.$row['id'].'" '.$updisable.'><i class="fa fa-arrow-up"></i></button>
                            <button type="button" class="btn btn-default btn-sm movedown" data-id="'.$row['id'].'" '.$downdisable.'><i class="fa fa-arrow-down"></i></button>
                        </div>
                    </div>
                    <div class="box-body">
                        <p>'.$instruct.'
                            <span class="pull-right">
                                <button type="button" class="btn btn-success btn-sm btn-flat reset" data-desc="'.slugify($row['description']).'"><i class="fa fa-refresh"></i> Reset</button>
                            </span>
                        </p>
                        <div id="candidate_list">
                            <ul>
                                '.$candidate.'  <!-- Insert candidates here -->
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    ';

    // Update the position's priority in the database (to maintain order)
    $sql = "UPDATE positions SET priority = '$num' WHERE id = '".$row['id']."'";
    $conn->query($sql);

    $num++;  // Increment position number for priority
}

// Return the final HTML output as a JSON response
echo json_encode($output);
?>