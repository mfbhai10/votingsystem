<?php
include 'includes/session.php';

// Helper function: প্রতিটি পজিশনের জন্য টেবিলের রো বানাবে
function generateRow($conn, $adminElectionId){
    $contents = '';

    $p = $conn->prepare("SELECT * FROM positions WHERE election_id=? ORDER BY priority ASC");
    $p->bind_param('i', $adminElectionId);
    $p->execute();
    $query = $p->get_result();

    while($row = $query->fetch_assoc()){
        $contents .= '
            <tr>
                <td colspan="2" align="center" style="font-size:15px;"><b>'.
                    htmlspecialchars($row['description']).'</b></td>
            </tr>
            <tr>
                <td width="80%"><b>Candidates</b></td>
                <td width="20%"><b>Votes</b></td>
            </tr>
        ';

        $c = $conn->prepare("SELECT * FROM candidates WHERE election_id=? AND position_id=? ORDER BY lastname ASC");
        $c->bind_param('ii', $adminElectionId, $row['id']);
        $c->execute();
        $cquery = $c->get_result();

        while($crow = $cquery->fetch_assoc()){
            $v = $conn->prepare("SELECT COUNT(*) as total FROM votes WHERE election_id=? AND candidate_id=?");
            $v->bind_param('ii', $adminElectionId, $crow['id']);
            $v->execute();
            $votes = $v->get_result()->fetch_assoc()['total'];

            $contents .= '
                <tr>
                    <td>'.htmlspecialchars($crow['lastname'].", ".$crow['firstname']).'</td>
                    <td>'.$votes.'</td>
                </tr>
            ';
        }
    }

    return $contents;
}

// নির্বাচিত ইলেকশনের নাম আনছি
$ename = '';
$es = $conn->prepare("SELECT title FROM elections WHERE id=?");
$es->bind_param('i', $adminElectionId);
$es->execute();
$ename = $es->get_result()->fetch_assoc()['title'] ?? 'Election';

require_once('../tcpdf/tcpdf.php');
$pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('Result: '.$ename);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(PDF_MARGIN_LEFT, '10', PDF_MARGIN_RIGHT);
$pdf->SetAutoPageBreak(TRUE, 10);
$pdf->SetFont('helvetica', '', 11);
$pdf->AddPage();

$content = '
    <h2 align="center">'.$ename.'</h2>
    <h4 align="center">Tally Result</h4>
    <table border="1" cellspacing="0" cellpadding="3">
';
$content .= generateRow($conn, $adminElectionId);
$content .= '</table>';

$pdf->writeHTML($content);
$pdf->Output('election_result.pdf', 'I');

?>