<?php 

require_once("../connect.php");
require_once("../function.php");


use Dompdf\Dompdf;

if(isset($_POST['print_lab_blood_transfusion'])){
    $mwaka=$_POST['mwakam'];
    $mwezi=$_POST['mwezim'];
    $data='mwaka='.$mwaka;
     $data.='&mwezi='.$mwezi;
    $url='http://localhost/medcom1.0/production/blood_transfusion_months.php?';
 $salim=print_file($data,$url);
}
if(isset($_POST['print_mtuha'])){
    $mwaka=$_POST['mwakam'];
    $mwezi=$_POST['mwezim'];
    $data='mwaka='.$mwaka;
     $data.='&mwezi='.$mwezi;
    $url='http://localhost/medcom1.0/production/print_mtuha_month.php?';
 $salim=print_file($data,$url);
}
if(isset($_POST['print_std_sti'])){
    $mwaka=$_POST['mwakam'];
    $mwezi=$_POST['mwezim'];
    $data='mwaka='.$mwaka;
     $data.='&mwezi='.$mwezi;
    $url='http://localhost/medcom1.0/production/print_std_report.php?';
 $salim=print_file($data,$url);
}
if(isset($_POST['print_mtuha_malaria'])){
    $mwaka=$_POST['mwakam'];
    $mwezi=$_POST['mwezim'];
    $data='mwaka='.$mwaka;
     $data.='&mwezi='.$mwezi;
    $url='http://localhost/medcom1.0/production/print_mtuha_malaria_month.php?';
 $salim=print_file($data,$url);
}
if(isset($_POST['print_other_medicines_monthly'])){
    $fdate=$_POST['date1'];
    $tdate=$_POST['date2'];
    $data='date1='.$fdate;
     $data.='&date2='.$tdate;
    $url='http://localhost/medcom1.0/production/print_other_medicines_monthly.php?';
 $salim=print_file($data,$url);
}
if(isset($_POST['print_alu_monthly'])){
    $fdate=$_POST['date1'];
    $tdate=$_POST['date2'];
    $data='date1='.$fdate;
     $data.='&date2='.$tdate;
    $url='http://localhost/medcom1.0/production/print_alu_monthly.php?';
 $salim=print_file($data,$url);
}
if(isset($_POST['print_tb_register'])){
    $fdate=$_POST['date1'];
    $tdate=$_POST['date2'];
    $data='date1='.$fdate;
     $data.='&date2='.$tdate;
    $url='http://localhost/medcom1.0/production/print_tb_leprosy_register.php?';
 $salim=print_file($data,$url);
}
if(isset($_POST['print_mtuha_week_malaria'])){
    $fdate=$_POST['date1'];
    $tdate=$_POST['date2'];
    $data='date1='.$fdate;
     $data.='&date2='.$tdate;
    $url='http://localhost/medcom1.0/production/print_mtuha_weekly_malaria.php?';
 $salim=print_file($data,$url);
}
if(isset($_POST['print_malaria_register'])){
    $fdate=$_POST['date1'];
    $tdate=$_POST['date2'];
    $data='date1='.$fdate;
     $data.='&date2='.$tdate;
    $url='http://localhost/medcom1.0/production/print_malaria_register.php?';
 $salim=print_file($data,$url);
}
if(isset($_POST['print_idsr_old_register'])){
    $fdate=$_POST['date1'];
    $tdate=$_POST['date2'];
    $data='date1='.$fdate;
     $data.='&date2='.$tdate;
    $url='http://localhost/medcom1.0/production/print_idsr_chart_month.php?';
 $salim=print_file($data,$url);
}
if(isset($_POST['print_idsr_report_weekly'])){
    $fdate=$_POST['date1'];
    $tdate=$_POST['date2'];
    $data='date1='.$fdate;
     $data.='&date2='.$tdate;
    $url='http://localhost/medcom1.0/production/print_report_weekly_idsr.php?';
 $salim=print_file($data,$url);
}
if(isset($_GET['claimfile'])){
    $data=$_GET['claimfile'];
    $url='http://localhost/medcom1.0/production/claimfile.php?id=';
 $salim=print_file_landscape($data,$url);
}
if(isset($_GET['gene_xpert_form'])){
    $data=$_GET['gene_xpert_form'];
    $url='http://localhost/medcom1.0/production/print_gen_xpert_report_form.php?id=';
 $salim=print_file_landscape($data,$url);
}
if(isset($_GET['gene_xpert'])){
    $data=$_GET['gene_xpert'];
    $url='http://localhost/medcom1.0/production/print_gen_xpert_report.php?id=';
 $salim=print_file_landscape($data,$url);
}
if(isset($_GET['patientfile'])){
    $data=$_GET['patientfile'];
    $url='http://localhost/medcom1.0/production/patientfile.php?id=';
 $salim=print_file($data,$url);
}
if(isset($_POST['print'])){
    $data=$_POST['tranno'];
    $url='http://localhost/medcom1.0/production/print_cash_sell_drugs.php?id=';
 $salim=print_file($data,$url);
}
if(isset($_GET['print_pharm_cons'])){
    $data=$_GET['visit'];
    $url='http://localhost/muhali/production/print_visit_drugs.php?id=';
 $salim=print_file($data,$url);
}
if(isset($_POST['print_tracermedicine'])){
    $data=$_POST['tracer'];
    $url='http://localhost/medcom1.0/production/print_tracermedicine.php?id=';
 $salim=print_file($data,$url);
}
if(isset($_POST['printservicesonly'])){
    $data=$_POST['tranno'];
    $url='http://localhost/medcom1.0/production/print_services.php?id=';
 $salim=print_file($data,$url);
}
if(isset($_POST['print_tb_form'])){
    $data=$_POST['form_id'];
    $url='http://localhost/medcom1.0/production/print_tb_leprosy_form.php?id=';
 $salim=print_file($data,$url);
}
if(isset($_POST['print_xray_form'])){
    $data=$_POST['form_id'];
    $url='http://localhost/medcom1.0/production/print_xray_form.php?id=';
 $salim=print_file($data,$url);
}
if(isset($_POST['casesheet'])){
    $data=$_POST['tranno'];
    $url='http://localhost/medcom1.0/production/print_case_sheet.php?id=';
 $salim=print_file($data,$url);
}
if(isset($_POST['refferal'])){
    $visit=$_POST['tranno'];
    $dept=$_POST['department'];
    $hosp=$_POST['hospital'];
    $refdr=$_POST['ref_dr_id'];

    $data='id='.$visit;
        $data.='&ref_dr='.$refdr;

    $notes = mysqli_real_escape_string($conn,$_POST['r_notes']);
    $query0 = mysqli_query($conn, $query = "DELETE from cons_ref where visit='$visit'"); 
    $query = mysqli_query($conn, $query = "
    INSERT INTO cons_ref (ref_id, visit, hospital, department, notes, createdon, createdby, status) 
    VALUES (NULL, '$visit', '$hosp', '$dept', '$notes', CURRENT_TIMESTAMP, '$refdr', '1')
    ");
    $url='http://localhost/medcom1.0/production/referral_letter.php?';
 $salim=print_file($data,$url);
} 
if(isset($_POST['jibu_moja_tu'])){
    $data=$_POST['pl_id'];
    $url='http://localhost/medcom1.0/production/print_results_1.php?id=';
 $salim=print_file($data,$url);
}
if(isset($_POST['printmajibu'])){
    $p=$_POST['pat_id'];
    $d1=$_POST['rfrom'];
    $d2=$_POST['rto'];
    $url='http://localhost/medcom1.0/production/print_results.php?id=';
 $salim=print_majibu($p,$d1,$d2,$url);
}
   
    
    
function print_file_landscape($autho,$url){
//$visit=4642;
require_once 'dompdf/autoload.inc.php';

$dompdf = new Dompdf();
$dompdf->set_option('isRemoteEnabled', TRUE);
//$dompdf->load_html_file('http://localhost/medcom1.0/production/verification_data_pdf.php?id=4631');

$file=$url;
$file .=$autho;
    $dompdf->set_paper('letter', 'landscape');
   // $dompdf->setPaper('A4', 'portrait');
$dompdf->load_html_file($file);
$dompdf->render();
//$dompdf->stream();
$output=$dompdf->output();
    $dompdf->stream();
//return  $b64Doc = base64_encode($output);
}
function print_file($autho,$url){
//$visit=4642;
require_once 'dompdf/autoload.inc.php';

$dompdf = new Dompdf();
$dompdf->set_option('isRemoteEnabled', TRUE);
//$dompdf->load_html_file('http://localhost/medcom1.0/production/verification_data_pdf.php?id=4631');

$file=$url;
$file .=$autho;
    //$dompdf->set_paper('letter', 'landscape');
    $dompdf->setPaper('A4', 'portrait');
$dompdf->load_html_file($file);
$dompdf->render();
//$dompdf->stream();
$output=$dompdf->output();
    $dompdf->stream();
//return  $b64Doc = base64_encode($output);
}

function print_majibu($p,$d1,$d2,$url){
//$visit=4642;
require_once 'dompdf/autoload.inc.php';

$dompdf = new Dompdf();
$dompdf->set_option('isRemoteEnabled', TRUE);
//$dompdf->load_html_file('http://localhost/medcom1.0/production/verification_data_pdf.php?id=4631');

$file=$url.$p.'&date1='.$d1.'&date2='.$d2;
/*$file .=$p;
$file .='&date1=';
$file .=$d1;
$file .='&date2=';
$file .=$d2;*/
    //$dompdf->set_paper('letter', 'landscape');
    $dompdf->setPaper('A4', 'portrait');
$dompdf->load_html_file($file);
$dompdf->render();
//$dompdf->stream();
$output=$dompdf->output();
    $dompdf->stream();
//return  $b64Doc = base64_encode($output);
}
 



?>

