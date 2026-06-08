<?php require_once('header.php'); ?>


<!-- page content -->
        <div class="right_col" role="main">
          <!-- top tiles -->
        
          <!-- /top tiles fhfh -->

          <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
                
            
     
<div class="container">
	<?php 		 $user=$_SESSION['user_id'];
	$result0=$conn->query("select p.pat_id, p.pat_names, p.age, CONCAT(u.firstname,' ',u.lastname) as name, pl.id, pl.createdon, pl.createdat, pl.plstatus, 
    s.svdescription from pat_lab as pl 
    INNER JOIN patients as p on p.pat_id=pl.pat_id
    INNER JOIN user as u on pl.createdby=u.user_id 
    INNER JOIN services as s on s.svsvid=pl.svcode and pl.plstatus >1 and s.stype=2 and DATEDIFF(CURDATE(),pl.createdon) < 30 
    ORDER BY pl.createdon desc,pl.createdat desc ");
				if ( mysqli_num_rows($result0) == 0){
			echo " <h3 class='form-signin-heading alert-warning'>You have no any patient  yet</h3>";
		}
		else
		{ 	?>
<table id="datatable-fixed-header" class="table table-striped table-bordered" style="color:black">

        <thead>
            <tr>
                <th>SN</th>
                <th>MR Number</th>
                <th>Patient Name</th>
                <th>Age</th>
                <th>Ordered By</th>
                <th>Procedure Name</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
            </tr>
        </thead>
    <tbody>
        <?php  $n=1;
        
		while($row1=mysqli_fetch_array($result0)){ ?>
       
  <tr bgcolor="#E9F3F5">
    <td align="center"><?php  echo $n; ?>.</td>
    <td><?php  echo $row1['pat_id']; ?></td>
    <td><?php  echo ucfirst($row1['pat_names']); ?></td>
    <td><?php  echo ages($row1['age']);?></td>
    <td><?php  echo $row1['name']; ?></td>
    <td><a href="?result=<?php echo $row1['svdescription'];?>
    &procedure=<?php echo $row1['id']; ?>&name=<?php  echo ucfirst($row1['pat_names']); ?>&mrn=<?php  echo $row1['pat_id']; ?>"><?php echo $row1['svdescription'];?></a></td>
    <td><?php  echo ucfirst($row1['createdon']); ?></td>
    <td><?php  echo ucfirst($row1['createdat']); ?></td>
    <td class="conditional"><?php if($row1['plstatus'] ==2){ echo "Accepted";}else { echo "Resulted";}?></td>
  </tr><?php $n++;  }?>
         
        </tbody>
    </table>
     <?php }?>
</div>
            </div>

          </div>
          <br />

          


          
        </div>
        <!-- /page content -->

      <!-- modal for student profile -->
<form action="../process.php" method="post" enctype="multipart/form-data">
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel"><h2>Report for: <?php echo $_GET['result'];?> <br><br><?php echo $_GET['mrn'];?> <span> </span><strong><?php echo $_GET['name'];?></strong></h2></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
            <div class="modal-body">
                <div class="alert alert-info" style="color: black; font-size: 20px;">
                    <?php 
                        $resul=$conn->query("SELECT indication, l.svcode FROM pat_lab AS l 
                        LEFT JOIN procedure_indication AS p on l.visit_id=p.visit AND l.svcode= p.procedure_s 
                        WHERE l.id='{$_GET['procedure']}'");
                            if($resul->num_rows < 1){ echo " "; } 
                            else{
                                $r=mysqli_fetch_array($resul);
                    ?>
                        <strong>Indication: </strong><?php echo nl2br($r['indication']);?>
                    <?php } ?>
                </div>
            </div>
          <div class="modal-body">
            <div class="container">

<div class="row">
<?php
$resulty = $conn->query("
    SELECT pl.id as labid, pl.pat_id, pl.visit_id, pt.pat_names, pt.age, stype,result,pl.createdon, svdescription from pat_lab as pl
    INNER JOIN patients as pt ON pl.pat_id = pt.pat_id
   INNER JOIN services as s ON pl.svcode=s.svsvid AND
   pl.id = '{$_GET['procedure']}' ");
$rowy= mysqli_fetch_array($resulty);
$typey=$rowy['stype'];
$resulti=$rowy['result']; 
$visitid=$rowy['visit_id'];
$patid=$rowy['pat_id'];
$plid=$rowy['labid'];

    if($typey=='2' AND $resulti=='11'){ 
        $result1 = $conn->query("SELECT * FROM bedrest_observation WHERE pl_id='$plid' AND visit_id = '$visitid' ");
    }else{
        $result1=$conn->query("SELECT * FROM procedures WHERE pl_id='{$_GET['procedure']}'");
    }
        if($result1->num_rows < 1){ $id=0; }else{ $id=1; }

    $row2=mysqli_fetch_array($result1);


$result1234 = $conn->query("SELECT tc.qty as qty, d.ddescription as drugs, du.du_name as unit, COALESCE(SUM(t.quantity), 0) as qoh
FROM test_consumable as tc 
INNER JOIN services as s on s.svsvid=tc.t_id 
INNER JOIN drugs as d on d.did=tc.d_id
 INNER JOIN drug_unit as du on du.du_id=d.dunit
 left JOIN store_mst as t on t.material=d.did and t.section=3 
 where   s.svsvid='{$r['svcode']}' ");
              while ($row = mysqli_fetch_array($result1234)) {
                                            $qoh=$row['qoh']; $qty=$row['qty'];
                                        $remain=$qoh-$qty;
                                        if($remain<0){
                                    ?>
                                          <input type="hidden" id="sasa" value="<?php echo $remain;?>" />
                                       <?php }else{}
              }

if($typey=='2' AND $resulti=='11'){ $visitid=$rowy['visit_id'];?>

<div class="body">
<?php $resultin = $conn->query("SELECT * FROM bedrest_observation as b 
            INNER JOIN user AS u ON b.cby=u.user_id
            WHERE pl_id='$plid' AND visit_id = '$visitid' ORDER BY b.id ASC ");
    if($resultin->num_rows < 1){ echo " <h3 class='form-signin-heading alert-warning'>You have not entered any data yet</h3>"; } else{?>          
            <table class="table table-striped table-bordered">
            <thead>
            <tr>
                <th>Nurse</th>
                <th>Time</th>
                <th>DIA</th>
                <th>SYS</th>
                <th>Resp.</th>
                <th>Pulse</th>
                <th>Temp</th>
                <th>SPO2</th>
                <th>Dehy</th>
                <th>Weight</th>
                <th>Inp</th>
                <th>Out</th>
                <th>Sugar</th>
                <th>Ket</th>
                <th>Comments</th>
            </tr>
        </thead>
        <tbody><?php
        while($rowin= mysqli_fetch_array($resultin)){ ?>
            <tr>
                <td><?php echo $rowin['firstname'];?></td>
                <td><?php echo date('H:i', strtotime($rowin['time']));?></td>
                <td><?php echo $rowin['diastolic'];?></td>
                <td><?php echo $rowin['systolic'];?></td>
                <td><?php echo $rowin['resp_rate'];?></td>
                <td><?php echo $rowin['pulse_rate'];?></td>
                <td><?php echo $rowin['temp'];?></td>
                <td><?php echo $rowin['spo2'];?></td>
                <td><?php echo $rowin['dehydration'];?></td>
                <td><?php echo $rowin['weight'];?></td>
                <td><?php echo $rowin['fluid_input'];?></td>
                <td><?php echo $rowin['fluid_output'];?></td>
                <td><?php echo $rowin['blood_sugar'];?></td>
                <td><?php echo $rowin['urine_ketones'];?></td>
                <td><?php echo nl2br($rowin['comments']);?></td>
            </tr>
                <?php  }?> 
                
                </tbody>  
                </table> 
       <?php  }?> 
            </div>

      <div class="dialog lg">
        <div class="content">
          <div class="body">
             <div style="display: none;" id="loader_img_group">
              <table>
                
               <tr> 
                <td>
                 
               </td>    
             </tr>
           </table>
         </div>
              <div class="row">
        
                  <div class="col-xs-4">
           
      </div>
              </div>
              <div class="row">

<div class="col-xs-10">
    <div class="panel panel-primary" style="margin:20px;">
        <div class="panel-heading"><h3 class="panel-title">Observation</h3></div>
        <div class="panel-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Examinations</th>
                        <th>Value</th>
                        <th style="border-right: 1px solid;">Unit</th>
                        <th>Examinations</th>
                        <th>Value</th>
                        <th>Unit</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Time</td>
                        <td><input class="form-control" onchange="success()" id="report" name="entrytime" type="time"/></td>
                        <td style="border-right: 1px solid;"></td>
                        <td>Dehydration</td>
                        <td>
                            <select class="form-control" id="dehydration"  name="dehydration">
                                    <option value="mild">Mild</option>
                                    <option value="moderate">Moderate</option>
                                    <option value="severe">Severe</option>
                                    <option value="No">NO</option>
                                    <option value="" selected></option>
                            </select>
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Systolic pressure</td>
                        <td><input class="form-control"  name="systolic" type="text"/></td>
                        <td style="border-right: 1px solid;">mmHg</td>
                        <td>Weight</td>
                        <td><input class="form-control" name="weight" type="text"/></td>
                        <td>kg</td>
                    </tr>
                        <tr>
                        <td>Diastolic pressure</td>
                        <td><input class="form-control"  name="diastolic" type="text"/></td>
                        <td style="border-right: 1px solid;">mmHg</td>
                        <td>Fluid Input</td>
                        <td><input class="form-control"    name="input" type="text" /></td>
                        <td>mL</td>
                    </tr>
                    <tr>
                        <td>Pulse rate</td>
                        <td><input class="form-control"  name="pulse" type="text"  /></td>
                        <td style="border-right: 1px solid;">Beats per minute</td>
                        <td>Fluid Output</td>
                        <td><input class="form-control"    name="output" type="text" /></td>
                        <td>mL</td>
                    </tr>
                    <tr>
                        <td>Resp rate</td>
                        <td><input class="form-control"   name="resp" type="text" /></td>
                        <td style="border-right: 1px solid;">Breath/min</td>
                        <td>Blood Sugar</td>
                        <td><input class="form-control"    name="sugar" type="text" /></td>
                        <td>mmol/L</td>
                    </tr>
                    <tr>
                        <td>SpO2</td>
                        <td><input class="form-control"  name="sop" type="text" /></td>
                        <td style="border-right: 1px solid;">%</td>
                        <td>Urine Ketones</td>
                        <td>
                            <select class="form-control" id="ketone"  name="ketone">
                                    <option value="+">+</option>
                                    <option value="++">++</option>
                                    <option value="+++">+++</option>
                                    <option value="No">NO</option>
                                    <option value="" selected></option>
                            </select>
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Temp</td>
                        <td><input class="form-control" name="temp" type="text"/></td>
                        <td style="border-right: 1px solid;"><span>&#8451;</span></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Comments</td>
                        <td colspan="5"><textarea class="form-control" rows="2" name="comments" id="comment" ></textarea></td>
                    </tr>
                </tbody>
            </table>  

        </div>      
                  
              </div>        
          </div>
        </div>
      </div>

 <?php }
 else if($typey=='2' AND $resulti=='16'){ $visitid=$rowy['visit_id'];
    
    $resultpv = $conn->query("SELECT pid, 
    max(CASE WHEN perimeter = 'para' THEN preports END) 'para',
    max(CASE WHEN perimeter = 'delivery_year' THEN preports END) 'delivery_year',
    max(CASE WHEN perimeter = 'abortion' THEN preports END) 'abortion',
    max(CASE WHEN perimeter = 'abortion_year' THEN preports END) 'abortion_year',
    max(CASE WHEN perimeter = 'alive' THEN preports END) 'alive',
    max(CASE WHEN perimeter = 'd_c_eva' THEN preports END) 'd_c_eva',
    max(CASE WHEN perimeter = 'cd4' THEN preports END) 'cd4',
    max(CASE WHEN perimeter = 'hvl' THEN preports END) 'hvl',
    max(CASE WHEN perimeter = 'wives' THEN preports END) 'wives',
    max(CASE WHEN perimeter = 'drug_name' THEN preports END) 'drug_name',
    max(CASE WHEN perimeter = 'children_number' THEN preports END) 'children_number',
    max(CASE WHEN perimeter = 'relationship_duration' THEN preports END) 'relationship_duration',
    max(CASE WHEN perimeter = 'lastborn_age' THEN preports END) 'lastborn_age',
    max(CASE WHEN perimeter = 'operation_type' THEN preports END) 'operation_type',
    max(CASE WHEN perimeter = 'contraceptive_method_1' THEN preports END) 'contraceptive_method_1',
    max(CASE WHEN perimeter = 'contraceptive_method_2' THEN preports END) 'contraceptive_method_2',
    max(CASE WHEN perimeter = 'contraceptive_method_3' THEN preports END) 'contraceptive_method_3',
    max(CASE WHEN perimeter = 'contraceptive_method_1_duration' THEN preports END) 'contraceptive_method_1_duration',
    max(CASE WHEN perimeter = 'contraceptive_method_2_duration' THEN preports END) 'contraceptive_method_2_duration',
    max(CASE WHEN perimeter = 'contraceptive_method_3_duration' THEN preports END) 'contraceptive_method_3_duration',
    max(CASE WHEN perimeter = 'cycle_length' THEN preports END) 'cycle_length',
    max(CASE WHEN perimeter = 'num_of_days' THEN preports END) 'num_of_days',
    max(CASE WHEN perimeter = 'current_amenorrhea' THEN preports END) 'current_amenorrhea',
    max(CASE WHEN perimeter = 'cycle_changing' THEN preports END) 'cycle_changing',
    max(CASE WHEN perimeter = 'intermediate_bleeding' THEN preports END) 'intermediate_bleeding',
    max(CASE WHEN perimeter = 'bleeding_intensity' THEN preports END) 'bleeding_intensity',
    max(CASE WHEN perimeter = 'milk_discharge' THEN preports END) 'milk_discharge',
    max(CASE WHEN perimeter = 'previous_std' THEN preports END) 'previous_std',
    max(CASE WHEN perimeter = 'previous_std_year' THEN preports END) 'previous_std_year',
    max(CASE WHEN perimeter = 'Dyspareunie' THEN preports END) 'Dyspareunie',
    max(CASE WHEN perimeter = 'Dysmenstruation' THEN preports END) 'Dysmenstruation',
    max(CASE WHEN perimeter = 'genital_itching' THEN preports END) 'genital_itching',
    max(CASE WHEN perimeter = 'wife_std' THEN preports END) 'wife_std',
    max(CASE WHEN perimeter = 'wife_std_disease' THEN preports END) 'wife_std_disease',
    max(CASE WHEN perimeter = 'wife_std_year' THEN preports END) 'wife_std_year',
    max(CASE WHEN perimeter = 'husband_std' THEN preports END) 'husband_std',
    max(CASE WHEN perimeter = 'husband_std_disease' THEN preports END) 'husband_std_disease',
    max(CASE WHEN perimeter = 'husband_std_year' THEN preports END) 'husband_std_year',
    max(CASE WHEN perimeter = 'operation_hx' THEN preports END) 'operation_hx',
    max(CASE WHEN perimeter = 'operations' THEN preports END) 'operations',
    max(CASE WHEN perimeter = 'hsg' THEN preports END) 'hsg',
    max(CASE WHEN perimeter = 'hiv' THEN preports END) 'hiv',
    max(CASE WHEN perimeter = 'husband_number' THEN preports END) 'husband_number',
    max(CASE WHEN perimeter = 'orchitis' THEN preports END) 'orchitis',
    max(CASE WHEN perimeter = 'father' THEN preports END) 'father',
    max(CASE WHEN perimeter = 'pitc' THEN preports END) 'pitc',
    max(CASE WHEN perimeter = 'drug_intake' THEN preports END) 'drug_intake',
    max(CASE WHEN perimeter = 'wife_number' THEN preports END) 'wife_number',
    max(CASE WHEN perimeter = 'husband_operation' THEN preports END) 'husband_operation',
    max(CASE WHEN perimeter = 'spermiogram' THEN preports END) 'spermiogram'
    FROM procedures where pl_id='{$_GET['procedure']}'
    GROUP BY pl_id ");
    
    $rowpv = mysqli_fetch_array($resultpv);
     ?>
                        <div class="row">
                            <div class="col-sm-12" style="text-align: center;">
                                <img src="img/brigita_official.jpg" alt="logo ya hospitali" />
                                    <p><h2><u><strong>Anamnesis for Sterility Patients</strong></u></h2></p>
                            </div>
                        </div>
                        <div class="row">
                                <div class="col-sm-6">
                                    Patient Names: <strong><?php echo $rowy['pat_names']; ?></strong>
                                </div>
                                <div class="col-sm-1">
                                    Age: <strong><?php echo agen($rowy['age'],$rowy['createdon']); ?></strong>
                                </div>
                                <div class="col-sm-5">
                                    Date: <strong><?php echo date("d"); ?>/<?php echo date("m"); ?>/<?php echo date("Y");?></strong>

                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="col-sm-2">
                                        <div class="col-sm-6">
                                            <span class="pull-right">Para:</span>
                                        </div>
                                        <div class="col-sm-6">
                                            <span class="pull-left">
                                                <input type="text" class="form-control" value="<?php if(!empty($rowpv['para'])){ echo $rowpv['para']; } ?>" name="para" onkeyup="success()"id="report">
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="col-sm-6">
                                            <span class="pull-right">Years of delivery:</span>
                                        </div>
                                        <div class="col-sm-6">
                                            <span class="pull-left"><input type="text" class="form-control" name="delivery_year" value="<?php if(!empty($rowpv['delivery_year'])){ echo $rowpv['delivery_year']; } ?>" id="delivery_year"></span>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="col-sm-6">
                                            <span class="pull-right">Operations:</span>
                                        </div>
                                        <div class="col-sm-6">
                                            <select class="form-control" name="operation_hx" id="operation_hx">
                                                <option value="<?php if(!empty($rowpv['operation_hx'])){ echo $rowpv['operation_hx']; } ?>" selected>
                                                    <?php if(!empty($rowpv['operation_hx'])){ echo $rowpv['operation_hx']; }else{ echo "Select.."; } ?>
                                                </option>
                                                <option value="Yes">Yes</option>
                                                <option value="No">No</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="col-sm-6">
                                            <span class="pull-right">Hysterosalpingography</span>
                                        </div>
                                        <div class="col-sm-6">
                                            <span class="pull-left">
                                                <select class="form-control" name="hsg" id="hsg">
                                                    <option value="<?php if(!empty($rowpv['hsg'])){ echo $rowpv['hsg']; } ?>" selected>
                                                        <?php if(!empty($rowpv['hsg'])){ echo $rowpv['hsg']; }else{ echo "Select.."; } ?>
                                                    </option>
                                                    <option value="No">No</option>
                                                    <option value="Yes, Both tubes patent">Yes, Both tubes patent</option>
                                                    <option value="Yes, One tube patent">Yes, One tube patent</option>
                                                    <option value="Yes, Both tubes blocked">Yes, Both tubes blocked</option>
                                                </select>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="col-sm-2">
                                        <div class="col-sm-6">
                                            <span class="pull-right">Abortions:</span>
                                        </div>
                                        <div class="col-sm-6">
                                            <span class="pull-left"><input type="text" class="form-control" name="abortion" value="<?php if(!empty($rowpv['abortion'])){ echo $rowpv['abortion']; } ?>" id="abortion"></span>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="col-sm-6">
                                            <span class="pull-right">Years of abortion:</span>
                                        </div>
                                        <div class="col-sm-6">
                                            <span class="pull-left"><input type="text" class="form-control" name="abortion_year" value="<?php if(!empty($rowpv['abortion_year'])){ echo $rowpv['abortion_year']; } ?>" id="abortion_year"></span>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="col-sm-6">
                                            <span class="pull-right">Which:</span>
                                        </div>
                                        <div class="col-sm-6">
                                            <span class="pull-left"><input type="text" class="form-control" name="operations" value="<?php if(!empty($rowpv['operations'])){ echo $rowpv['operations']; } ?>" id="operations"></span>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="col-sm-6">
                                            <span class="pull-right">HIV/AIDS/ART</span>
                                        </div>
                                        <div class="col-sm-6">
                                            <span class="pull-left">
                                                <select class="form-control" name="hiv" id="hiv">
                                                    <option value="<?php if(!empty($rowpv['hiv'])){ echo $rowpv['hiv']; } ?>" selected>
                                                        <?php if(!empty($rowpv['hiv'])){ echo $rowpv['hiv']; }else{ echo "Select.."; } ?>
                                                    </option>
                                                    <option value="No">No</option>
                                                    <option value="Yes">Yes</option>
                                                </select>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="col-sm-2">
                                        <div class="col-sm-6">
                                            <span class="pull-right">Alive:</span>
                                        </div>
                                        <div class="col-sm-6">
                                            <span class="pull-left"><input type="text" class="form-control" name="alive" value="<?php if(!empty($rowpv['alive'])){ echo $rowpv['alive']; } ?>" id="alive"></span>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="col-sm-6">
                                            <span class="pull-right">D+C or EVA:</span>
                                        </div>
                                        <div class="col-sm-6">
                                            <span class="pull-left">
                                                    <select class="form-control" name="d_c_eva" id="d_c_eva">
                                                        <option value="<?php if(!empty($rowpv['d_c_eva'])){ echo $rowpv['d_c_eva']; } ?>" selected>
                                                            <?php if(!empty($rowpv['d_c_eva'])){ echo $rowpv['d_c_eva']; }else{ echo "Select.."; } ?>
                                                        </option>
                                                        <option value="No">No</option>
                                                        <option value="Yes">Yes</option>
                                                    </select>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <!-- leave empty, for spacing -->
                                        <div class="col-sm-6">
                                            <span class="pull-right"></span>
                                        </div>
                                        <div class="col-sm-6">
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="col-sm-6">
                                            <span>CD4:<input type="text" class="form-control" name="cd4" value="<?php if(!empty($rowpv['cd4'])){ echo $rowpv['cd4']; } ?>" id="cd4"></span> 
                                        </div>
                                        <div class="col-sm-6">
                                            <span>HVL:<input type="text" class="form-control" name="hvl" value="<?php if(!empty($rowpv['hvl'])){ echo $rowpv['hvl']; } ?>" id="hvl"></span> 
                                        </div>
                                    </div>
                                </div>
                        </div>
                                <hr>
                        <div class="row">
                                <div class="col-sm-12">
                                        <div class="col-sm-6">
                                            <div class="col-sm-6">
                                                <span class="pull-right">1st/2nd/3rd.. Husband:</span>
                                            </div>
                                            <div class="col-sm-6">
                                                <span class="pull-left">
                                                    <select class="form-control" name="husband_number" id="husband_number">
                                                        <option value="<?php if(!empty($rowpv['husband_number'])){ echo $rowpv['husband_number']; } ?>" selected>
                                                            <?php if(!empty($rowpv['husband_number'])){ echo $rowpv['husband_number']; }else{ echo "Select.."; } ?>
                                                        </option>
                                                        <option value="1st">1st</option>
                                                        <option value="2nd">2nd</option>
                                                        <option value="3rd">3rd</option>
                                                        <option value="4th">4th</option>
                                                        <option value="5th">5th</option>
                                                    </select>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="col-sm-6">
                                                <span class="pull-right">History of Orchitis:</span>
                                            </div>
                                            <div class="col-sm-6">
                                                <span class="pull-left">
                                                    <select class="form-control" name="orchitis" id="orchitis">
                                                        <option value="<?php if(!empty($rowpv['orchitis'])){ echo $rowpv['orchitis']; } ?>" selected>
                                                            <?php if(!empty($rowpv['orchitis'])){ echo $rowpv['orchitis']; }else{ echo "Select.."; } ?>
                                                        </option>
                                                        <option value="No">No</option>
                                                        <option value="Yes">Yes</option>
                                                    </select>
                                                </span>
                                            </div>
                                        </div>

                                </div>
                        </div>

                            <br>

                            <div class="row">
                                <div class="col-sm-12">
                                        <div class="col-sm-6">
                                            <div class="col-sm-6">
                                                <span class="pull-right">Husband is the father of (some of) her children:</span>
                                            </div>
                                            <div class="col-sm-6">
                                                <span class="pull-left">
                                                    <select class="form-control" name="father" id="father">
                                                        <option value="<?php if(!empty($rowpv['father'])){ echo $rowpv['father']; } ?>" selected >
                                                            <?php if(!empty($rowpv['father'])){ echo $rowpv['father']; }else{ echo "Select.."; } ?>
                                                        </option>
                                                        <option value="Yes">Yes</option>
                                                        <option value="No">No</option>
                                                    </select>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="col-sm-6">
                                                <span class="pull-right">PITC:</span>
                                            </div>
                                            <div class="col-sm-6">
                                                <span class="pull-left">
                                                    <select class="form-control" name="pitc" id="pitc">
                                                        <option value="<?php if(!empty($rowpv['pitc'])){ echo $rowpv['pitc']; } ?>" selected>
                                                            <?php if(!empty($rowpv['pitc'])){ echo $rowpv['pitc']; }else{ echo "Select.."; } ?>
                                                        </option>
                                                        <option value="NR">NR</option>
                                                        <option value="R">R</option>
                                                    </select>
                                                </span>
                                            </div>
                                        </div>

                                </div>
                            </div>

                            <br>
                            
                            <div class="row">
                                <div class="col-sm-12">
                                        <div class="col-sm-6">
                                            <div class="col-sm-6">
                                                <span class="pull-right">How many wives does the husband have:</span>
                                            </div>
                                            <div class="col-sm-6">
                                                <span class="pull-left">
                                                    <input type="text" class="form-control" name="wives" value="<?php if(!empty($rowpv['wives'])){ echo $rowpv['wives']; } ?>" id="wives">
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="col-sm-6">
                                                <span class="pull-right">Regular drug intake:</span>
                                            </div>
                                            <div class="col-sm-3">
                                                <span class="pull-left">
                                                    <select class="form-control" name="drug_intake" id="drug_intake">
                                                        <option value="<?php if(!empty($rowpv['drug_intake'])){ echo $rowpv['drug_intake']; } ?>" selected >
                                                            <?php if(!empty($rowpv['drug_intake'])){ echo $rowpv['drug_intake']; }else{ echo "Select.."; } ?>
                                                        </option>
                                                        <option value="No">No</option>
                                                        <option value="Yes">Yes</option>
                                                    </select>
                                                </span>
                                            </div>
                                            <div class="col-sm-3">
                                                <span class="pull-left">
                                                    <input type="text" class="form-control" placeholder="Drug name.." name="drug_name" value="<?php if(!empty($rowpv['drug_name'])){ echo $rowpv['drug_name']; } ?>" id="drug_name">
                                                </span>
                                            </div>
                                        </div>

                                </div>
                            </div>

                            <br>

                            <div class="row">
                                <div class="col-sm-12">
                                        <div class="col-sm-6">
                                            <div class="col-sm-6">
                                                <span class="pull-right">She is the 1st/2nd/3rd.. wife:</span>
                                            </div>
                                            <div class="col-sm-6">
                                                <span class="pull-left">
                                                    <select class="form-control" name="wife_number" id="wife_number">
                                                        <option value="<?php if(!empty($rowpv['wife_number'])){ echo $rowpv['wife_number']; } ?>" selected>
                                                            <?php if(!empty($rowpv['wife_number'])){ echo $rowpv['wife_number']; }else{ echo "Select.."; } ?>
                                                        </option>
                                                        <option value="1st">1st</option>
                                                        <option value="2nd">2nd</option>
                                                        <option value="3rd">3rd</option>
                                                        <option value="4th">4th</option>
                                                        <option value="5th">5th</option>
                                                        <option value="6th">6th</option>
                                                    </select>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="col-sm-6">
                                                <span class="pull-right">Number of children of husband:</span>
                                            </div>
                                            <div class="col-sm-6">
                                                <span class="pull-left">
                                                    <input type="text" class="form-control" name="children_number" value="<?php if(!empty($rowpv['children_number'])){ echo $rowpv['children_number']; } ?>" id="children_number">
                                                </span>
                                            </div>
                                        </div>

                                </div>
                        </div>

                        <br>
                        <div class="row">
                                <div class="col-sm-12">
                                        <div class="col-sm-6">
                                            <div class="col-sm-6">
                                                <span class="pull-right">For how many years has she stayed with her partner:</span>
                                            </div>
                                            <div class="col-sm-6">
                                                <span class="pull-left">
                                                    <input type="text" class="form-control" name="relationship_duration" value="<?php if(!empty($rowpv['relationship_duration'])){ echo $rowpv['relationship_duration']; } ?>" id="relationship_duration">
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="col-sm-6">
                                                <span class="pull-right">Age of husband's last born child:</span>
                                            </div>
                                            <div class="col-sm-6">
                                                <span class="pull-left">
                                                    <input type="text" class="form-control" name="lastborn_age" value="<?php if(!empty($rowpv['lastborn_age'])){ echo $rowpv['lastborn_age']; } ?>" id="lastborn_age">
                                                </span>
                                            </div>
                                        </div>

                                </div>
                        </div>

                        <br>

                        <div class="row">
                                <div class="col-sm-12">
                                        <div class="col-sm-6">
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="col-sm-4">
                                                <span class="pull-right">Husband Operations:</span>
                                            </div>
                                            <div class="col-sm-2">
                                                <span class="pull-left">
                                                    <select class="form-control" name="husband_operation" id="husband_operation">
                                                        <option value="<?php if(!empty($rowpv['husband_operation'])){ echo $rowpv['husband_operation']; } ?>" selected>
                                                            <?php if(!empty($rowpv['husband_operation'])){ echo $rowpv['husband_operation']; }else{ echo "Select.."; } ?>
                                                        </option>
                                                        <option value="No">No</option>
                                                        <option value="Yes">Yes</option>
                                                    </select>
                                                </span>
                                            </div>
                                            <div class="col-sm-6">
                                                <span class="pull-left">
                                                    <input type="text" class="form-control" placeholder="Which operation.." name="operation_type" value="<?php if(!empty($rowpv['operation_type'])){ echo $rowpv['operation_type']; } ?>" id="operation_type">
                                                </span>
                                            </div>
                                        </div>

                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-3"><strong>Contraceptives:</strong></div>
                                <div class="col-md-4">
                                    <p><i>Type/Method</i></p>
                                    <p class="d-inline-flex align-items-center">
                                        <label for="contraceptive_method_1">1:</label>
                                        <input type="text" name="contraceptive_method_1" value="<?php if(!empty($rowpv['contraceptive_method_1'])){ echo $rowpv['contraceptive_method_1']; } ?>" id="contraceptive_method_1">
                                    </p>
                                    <p class="d-inline-flex align-items-center">
                                        <label for="contraceptive_method_2">2:</label>
                                        <input type="text" name="contraceptive_method_2" value="<?php if(!empty($rowpv['contraceptive_method_2'])){ echo $rowpv['contraceptive_method_2']; } ?>" id="contraceptive_method_2">
                                    </p>
                                    <p class="d-inline-flex align-items-center">
                                        <label for="contraceptive_method_3">3:</label>
                                        <input type="text" name="contraceptive_method_3" value="<?php if(!empty($rowpv['contraceptive_method_3'])){ echo $rowpv['contraceptive_method_3']; } ?>" id="contraceptive_method_3">
                                    </p>
                                </div>
                                <div class="col-md-3">
                                    <p><i>Years of using the contraceptives</i></p>
                                    <p>
                                        <input type="text" name="contraceptive_method_1_duration" value="<?php if(!empty($rowpv['contraceptive_method_1_duration'])){ echo $rowpv['contraceptive_method_1_duration']; } ?>" id="contraceptive_method_1_duration">
                                    </p>
                                    <p>
                                        <input type="text" name="contraceptive_method_2_duration" value="<?php if(!empty($rowpv['contraceptive_method_2_duration'])){ echo $rowpv['contraceptive_method_2_duration']; } ?>" id="contraceptive_method_2_duration">
                                    </p>
                                    <p>
                                        <input type="text" name="contraceptive_method_3_duration" value="<?php if(!empty($rowpv['contraceptive_method_3_duration'])){ echo $rowpv['contraceptive_method_3_duration']; } ?>" id="contraceptive_method_3_duration">
                                    </p>
                                </div>
                                <div class="col-md-2"></div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-3"><strong>Menstrual Cycle:</strong></div>
                                <div class="col-md-4">
                                    <p class="d-inline-flex align-items-center">
                                        <label for="cycle_length">Length ~:</label>
                                        <input type="text" name="cycle_length" value="<?php if(!empty($rowpv['cycle_length'])){ echo $rowpv['cycle_length']; } ?>" id="cycle_length" placeholder="About .... days..">
                                    </p>
                                    <p>
                                        <label for="num_of_days">Menstrual Bleeding:</label>
                                        <input type="text" name="num_of_days" value="<?php if(!empty($rowpv['num_of_days'])){ echo $rowpv['num_of_days']; } ?>" id="num_of_days" placeholder="Number of days..">
                                    </p>
                                    <p class="d-inline-flex align-items-center">
                                        <label for="current_amenorrhea">Amenorrhea at the moment</label>
                                        <select name="current_amenorrhea" id="current_amenorrhea">
                                            <option value="<?php if(!empty($rowpv['current_amenorrhea'])){ echo $rowpv['current_amenorrhea']; } ?>" selected>
                                                <?php if(!empty($rowpv['current_amenorrhea'])){ echo $rowpv['current_amenorrhea']; }else{ echo "Select.."; }?>
                                            </option>
                                            <option value="No">No</option>
                                            <option value="Yes">Yes</option>
                                        </select>
                                    </p>
                                </div>
                                <div class="col-md-5">
                                    <p class="d-inline-flex align-items-center">
                                        <label for="cycle_changing">Duration of cycle changing</label>
                                        <select name="cycle_changing" id="cycle_changing">
                                            <option value="<?php if(!empty($rowpv['cycle_changing'])){ echo $rowpv['cycle_changing']; } ?>" selected>
                                                <?php if(!empty($rowpv['cycle_changing'])){ echo $rowpv['cycle_changing']; }else{ echo "Select.."; } ?>
                                            </option>
                                            <option value="No">No</option>
                                            <option value="Slightly">Slightly</option>
                                            <option value="More than 5 days">More than 5 days</option>
                                        </select>
                                    </p>
                                    <p class="d-inline-flex align-items-center">
                                        <label for="intermediate_bleeding">Intermeidate Bleeding</label>
                                        <select name="intermediate_bleeding" id="intermediate_bleeding">
                                            <option value="<?php if(!empty($rowpv['intermediate_bleeding'])){ echo $rowpv['intermediate_bleeding']; } ?>" selected>
                                                <?php if(!empty($rowpv['intermediate_bleeding'])){ echo $rowpv['intermediate_bleeding']; }else{ echo "Select.."; } ?>
                                            </option>
                                            <option value="No">No</option>
                                            <option value="Yes">Yes</option>
                                        </select>
                                    </p>
                                    <p class="d-inline-flex align-items-center">
                                        <label for="bleeding_intensity">Bleeding Intensity</label>
                                        <select name="bleeding_intensity" id="bleeding_intensity">
                                            <option value="<?php if(!empty($rowpv['bleeding_intensity'])){ echo $rowpv['bleeding_intensity']; } ?>" selected>
                                                <?php if(!empty($rowpv['bleeding_intensity'])){ echo $rowpv['bleeding_intensity']; }else{ echo "Select.."; } ?>
                                            </option>
                                            <option value="Scanty">Scanty</option>
                                            <option value="Normal">Normal</option>
                                            <option value="Strong">Strong</option>
                                            <option value="Clots">Clots</option>
                                        </select>
                                    </p>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-4">
                                    <p>Signs of Prolactinemia</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="d-inline-flex align-items-center">
                                        <label for="milk_discharge">Milk Discharge</label>
                                        <select name="milk_discharge" id="milk_discharge">
                                            <option value="<?php if(!empty($rowpv['milk_discharge'])){ echo $rowpv['milk_discharge']; } ?>" selected>
                                                <?php if(!empty($rowpv['milk_discharge'])){ echo $rowpv['milk_discharge']; }else{ echo "Select.."; } ?>   
                                            </option>
                                            <option value="No">No</option>
                                            <option value="Yes">Yes</option>
                                        </select>
                                    </p>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                               
                                <div class="col-md-4">
                                    <p><strong>PID</strong></p>
                                    <p class="d-inline-flex align-items-center">
                                        <label for="previous_std">Previous PID?</label>
                                        <select name="previous_std" id="previous_std">
                                            <option value="<?php if(!empty($rowpv['previous_std'])){ echo $rowpv['previous_std']; } ?>" selected>
                                                <?php if(!empty($rowpv['previous_std'])){ echo $rowpv['previous_std']; }else{ echo "Select.."; } ?>
                                            </option>
                                            <option value="No">No</option>
                                            <option value="Yes">Yes</option>
                                        </select>
                                    </p>
                                    <p class="d-inline-flex align-items-center">
                                        <label for="previous_std_year">When:</label>
                                        <input type="text" name="previous_std_year" value="<?php if(!empty($rowpv['previous_std_year'])){ echo $rowpv['previous_std_year']; } ?>" id="previous_std_year" placeholder="year..">
                                    </p>
                                    <p class="d-inline-flex align-items-center">
                                        <label for="Dyspareunie">Dyspareunie:</label>
                                        <select name="Dyspareunie" id="Dyspareunie">
                                            <option value="<?php if(!empty($rowpv['Dyspareunie'])){ echo $rowpv['Dyspareunie']; } ?>" selected>
                                                <?php if(!empty($rowpv['Dyspareunie'])){ echo $rowpv['Dyspareunie']; }else{ echo "Select.."; } ?>
                                            </option>
                                            <option value="No">No</option>
                                            <option value="Yes">Yes</option>
                                        </select>
                                    </p>
                                    <p class="d-inline-flex align-items-center">
                                        <label for="Dysmenstruation">Dysmenstruation:</label>
                                        <select name="Dysmenstruation" id="Dysmenstruation">
                                            <option value="<?php if(!empty($rowpv['Dysmenstruation'])){ echo $rowpv['Dysmenstruation']; } ?>" selected>
                                                <?php if(!empty($rowpv['Dysmenstruation'])){ echo $rowpv['Dysmenstruation']; }else{ echo "Select.."; } ?>
                                            </option>
                                            <option value="No">No</option>
                                            <option value="Yes">Yes</option>
                                        </select>
                                    </p>
                                    <p class="d-inline-flex align-items-center">
                                        <label for="genital_itching">Genital itching:</label>
                                        <select name="genital_itching" id="genital_itching">
                                            <option value="<?php if(!empty($rowpv['genital_itching'])){ echo $rowpv['genital_itching']; } ?>" selected>
                                                <?php if(!empty($rowpv['genital_itching'])){ echo $rowpv['genital_itching']; }else{ echo "Select.."; } ?>
                                            </option>
                                            <option value="No">No</option>
                                            <option value="Yes">Yes</option>
                                        </select>
                                    </p>
                                </div>

                                <div class="col-md-4">
                                <p><strong>STD</strong></p>
                                    <p class="d-inline-flex align-items-center">
                                        <label for="wife_std">Wife?</label>
                                        <select name="wife_std" id="wife_std">
                                            <option value="<?php if(!empty($rowpv['wife_std'])){ echo $rowpv['wife_std']; } ?>" selected>
                                                <?php if(!empty($rowpv['wife_std'])){ echo $rowpv['wife_std']; }else{ echo "Select.."; } ?>
                                            </option>
                                            <option value="No">No</option>
                                            <option value="Yes">Yes</option>
                                        </select>
                                    </p>

                                    <p class="d-inline-flex align-items-center">
                                        <label for="wife_std_disease">Which disease:</label>
                                        <input type="text" name="wife_std_disease" value="<?php if(!empty($rowpv['wife_std_disease'])){ echo $rowpv['wife_std_disease']; } ?>" id="wife_std_disease" placeholder="disease..">
                                    </p>

                                    <p class="d-inline-flex align-items-center">
                                        <label for="wife_std_year">When:</label>
                                        <input type="text" name="wife_std_year" value="<?php if(!empty($rowpv['wife_std_year'])){ echo $rowpv['wife_std_year']; } ?>" id="wife_std_year" placeholder="year..">
                                    </p>
                                </div>

                                <div class="col-md-4">
                                <p><strong>STD</strong></p>
                                    <p class="d-inline-flex align-items-center">
                                        <label for="husband_std">Husband?</label>
                                        <select name="husband_std" id="husband_std">
                                            <option value="<?php if(!empty($rowpv['husband_std'])){ echo $rowpv['husband_std']; } ?>" selected>
                                                <?php if(!empty($rowpv['husband_std'])){ echo $rowpv['husband_std']; }else{ echo "Select.."; } ?>
                                            </option>
                                            <option value="No">No</option>
                                            <option value="Yes">Yes</option>
                                        </select>
                                    </p>

                                    <p class="d-inline-flex align-items-center">
                                        <label for="husband_std_disease">Which disease:</label>
                                        <input type="text" name="husband_std_disease" value="<?php if(!empty($rowpv['husband_std_disease'])){ echo $rowpv['husband_std_disease']; } ?>" id="husband_std_disease" placeholder="disease..">
                                    </p>

                                    <p class="d-inline-flex align-items-center">
                                        <label for="husband_std_year">When:</label>
                                        <input type="text" name="husband_std_year" value="<?php if(!empty($rowpv['husband_std_year'])){ echo $rowpv['husband_std_year']; } ?>" id="husband_std_year" placeholder="year..">
                                    </p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                        <div class="col-sm-6">
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="col-sm-6">
                                                <span class="pull-right">Spermoigram:</span>
                                            </div>
                                            <div class="col-sm-6">
                                                <span class="pull-left">
                                                    <select name="spermiogram" id="spermiogram" class="form-control">
                                                        <option value="<?php if(!empty($rowpv['spermiogram'])){ echo $rowpv['spermiogram']; } ?>" selected>
                                                            <?php if(!empty($rowpv['spermiogram'])){ echo $rowpv['spermiogram']; }else{ echo "Select.."; } ?>
                                                        </option>
                                                        <option value="Yes, Normal findings">Yes, Normal findings</option>
                                                        <option value="Yes, abnormal findings">Yes, abnormal findings</option>
                                                        <option value="No">No</option>
                                                    </select>
                                                </span>
                                            </div>
                                        </div>

                                </div>
                            </div> <br>

 <?php } else {	 ?>
  
<div class="col-md-12">
<div class="form-group">
        <label>Upload Image </label>
        <div class="input-group">
            <span class="input-group-btn">
                <span class="btn btn-default btn-file">
                    Browse… <input type="file" id="fileToUpload" name="myfile"class="col-md-12">
                </span>
            </span>
            
        </div>
        <img id='img-upload'/>
    </div>
</div>
</div>
         


 <div class="col-xs-12">
 <img src="<?php echo $row2['pimage'];?>" alt="" height="100%" width="100%" />
  
</div> 
<div class="row">
    <div class="col-xs-4">
    <label for="Number">Normal Study</label>
    <select  id="normalselect" onchange="getcompany(this.value);" class="form-control" >
    <option value="">Normal Study</option>
    <?php
    $query = mysqli_query($conn, $query = "Select * from normal_study where status = 2");
    while ($row = mysqli_fetch_array($query)) {
        ?>
        <option value="<?php echo $row['ns_id']; ?>"><?php echo $row['st_type']; ?></option>
        <?php
    }
    ?>
    </select>
    </div>
</div>

                <div class="col-xs-12">
  <label for="member">Report</label>
  <textarea class="form-control" onkeyup="success()" rows="6" name="report" id="report"  placeholder="Write procedure report..."
                            required="required"><?php if(!empty($row2['preports'])){ echo $row2['preports']; } ?></textarea>
</div>  



</div>
                
             
          </div>
     <?php } ?>

           <div class="modal-footer">

            <input type="hidden" id="vst" name="vst" value="<?php echo $rowy['visit_id']; ?>" />
            <input type="hidden" id="pt" name="pt" value="<?php echo $rowy['pat_id']; ?>" />
            <input type="hidden" name="pl_id" value="<?php echo $_GET['procedure']; ?>" />
            <input type="hidden" name="r_type" value="<?php echo $rowy['result']; ?>" />
            <input type="hidden" name="page" id="page" value="procedure.php" />
            <input type="hidden" id="userid" name="procedureuserid" value="<?php echo $_SESSION['user_id']; ?>" />

                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            <?php if($id>0){?>
                <button type="submit"  name="updateprocedure" class="btn btn-primary">Update</button>
            <?php }else{?>
                <button type="submit" id="Saveprocedure" name="Saveprocedure" class="btn btn-primary" disabled>Save Report</button>
            <?php }?>
                <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
            </div> 
        </div>
      </div>
    </div>   
</form> 


<?php require_once('footer.php'); ?>



 
      

<script type="text/javascript">
    function getcompany(val){
        var normalselect = $("#normalselect").val();
       // alert(category);
        $.ajax({
            type: "POST",
            url: "getamount.php",
           // data: 'catselect='+val, 
            data:{
                            "normalselect": normalselect,
                            "category": 1
                            
                        },      
            success: function(data){
                $("#report").html(data);

            }
        });
    }

    
    $(document).ready( function() {
        var sasaElement = document.getElementById('sasa');
        if(sasaElement) {
            var sasa = sasaElement.value;
            if(sasa<0){
                document.getElementById('Saveprocedure').id = 'disabled';
                document.getElementById('disabled').innerText = 'Stock is Low';
                document.getElementById('disabled').style.background = 'red';
            }
        }


    	$(document).on('change', '.btn-file :file', function() {
		var input = $(this),
			label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
		input.trigger('fileselect', [label]);
		});

		$('.btn-file :file').on('fileselect', function(event, label) {
		    
		    var input = $(this).parents('.input-group').find(':text'),
		        log = label;
		    
		    if( input.length ) {
		        input.val(log);
		    } else {
		       // if( log ) alert(log);
		    }
	    
		});
		function readURL(input) {
		    if (input.files && input.files[0]) {
		        var reader = new FileReader();
		        
		        reader.onload = function (e) {
		            $('#img-upload').attr('src', e.target.result);
		        }
		        
		        reader.readAsDataURL(input.files[0]);
		    }
		}

		$("#fileToUpload").change(function(){
		    readURL(this);
		}); 	
	});
    
    
       //$(document).ready(function(){
     function success() {
	 var reportElement = document.getElementById("report");
	 if(reportElement && reportElement.value==="") { 
            document.getElementById('Saveprocedure').disabled = true;
        } else {  
            document.getElementById('Saveprocedure').disabled = false;
    }
    }


	// For demo to fit into DataTables site builder...
	$('#example')
		.removeClass( 'display' )
		.addClass('table table-striped table-bordered');
</script>
<?php if(isset($_GET['result'])){?>
 
<script type="text/javascript">

 $("#myModal").modal();

</script>
<?php }?>