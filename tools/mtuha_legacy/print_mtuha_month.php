<style type="text/css">

body {
  font-family: verdana;
  font-size: 9px !important;
}
table, th, td {
  border: 0.25px solid black;
 
}
table {
  border-spacing: 0px;
}
</style>
<?php
set_time_limit(900); 

$year = $_GET['mwaka'];
$month = $_GET['mwezi'];
require_once('function.php'); ?>
<table>
   <thead>
        <tr>
            <td colspan="24"><strong>Taarifa ya Mwezi kutoka OPD (MTUHA Monthly Report)</strong></td>

        </tr>
 
        <tr>
            <td colspan="6">Jina la Kituo: <b><?php echo $row['description']; ?></b></td>
            <td colspan="3">Wilaya: <b><?php echo $row['wilaya']; ?></b></td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td colspan="3">Mkoa: <b><?php echo $row['mkoa']; ?></b></td>
            <td>&nbsp;</td>
            <td>Mwezi:</td>
            <td>&nbsp;</td>
            <td colspan="3"><?php echo $monthName = date('F', mktime(0, 0, 0, $month, 10)); ?></td>
            <td>Mwaka:</td>
            <td>&nbsp;</td>
            <td colspan="2"><?php echo $year; ?></td>
        </tr>
        <tr style="background-color: grey;">
            <td rowspan="2"><strong>NA</strong></td>
            <td colspan="5" rowspan="2">Maelezo</td>
            <td colspan="3">Umri chini ya mwezi 1</td>
            <td colspan="3">Umri mwezi 1 hadi umri chini ya mwaka 1</td>
            <td colspan="3">Umri mwaka 1 hadi umri chini ya miaka 5</td>
            <td colspan="3">Umri miaka 5 hadi umri chini ya miaka 60</td>
            <td colspan="3">Umri miaka 60 na kuendelea</td>
            <td colspan="3">Jumla Kuu</td>
        </tr>
        <tr style=" background-color: grey;">
            <td>ME</td>
            <td>MKE</td>
            <td>JUMLA</td>
            <td>ME</td>
            <td>MKE</td>
            <td>JUMLA</td>
            <td>ME</td>
            <td>MKE</td>
            <td>JUMLA</td>
            <td>ME</td>
            <td>MKE</td>
            <td>JUMLA</td>
            <td>ME</td>
            <td>MKE</td>
            <td>JUMLA</td>
            <td>ME</td>
            <td>MKE</td>
            <td>JUMLA</td>
        </tr>
             </thead>
     <tbody>
        <tr>
            <td>&nbsp;1</td>
            <td colspan="5">Wagonjwa waliohudhuria kwa mara ya kwanza mwaka huo (*) kituo chochote nchini</td>
            <td><?php echo $a1 = $row1['Male'] + 0; ?></td>
            <td><?php echo $b1 = $row1['Female'] + 0; ?></td>
            <td><?php echo $a1 + $b1; ?></td>

            <td><?php echo $a2 = $rowa1['Male'] + 0; ?></td>
            <td><?php echo $b2 = $rowa1['Female'] + 0; ?></td>
            <td><?php echo $a2 + $b2; ?></td>

            <td><?php echo $a3 = $rowa2['Male'] + 0; ?></td>
            <td><?php echo $b3 = $rowa2['Female'] + 0; ?></td>
            <td><?php echo $a3 + $b3; ?></td>

            <td><?php echo $a4 = $rowa4['Male'] + 0; ?></td>
            <td><?php echo $b4 = $rowa4['Female'] + 0; ?></td>
            <td><?php echo $a4 + $b4; ?></td>

            <td><?php echo $a5 = $rowa5['Male'] + 0; ?></td>
            <td><?php echo $b5 = $rowa5['Female'] + 0; ?></td>
            <td><?php echo $a5 + $b5; ?></td>

            <td><?php echo $a6 = $a1 + $a2 + $a3 + $a4 + $a5 ?></td>
            <td><?php echo $b6 = $b1 + $b2 + $b3 + $b4 + $b5 ?></td>
            <td><?php echo $a6 + $b6; ?></td>
        </tr>
        <tr>
            <td>2</td>
            <td colspan="5">Mahudhurio ya kwanza/ wagonjwa wapya [kwenye kituo husika kwa tatizo fulani la kiafya]</td>
            <td><?php echo $dm1 = $rowd['Male'] + 0; ?></td>
            <td><?php echo $df1 = $rowd['Female'] + 0; ?></td>
            <td><?php echo $df1 + $dm1; ?></td>

            <td><?php echo $dm2 = $rowd1['Male'] + 0; ?></td>
            <td><?php echo $df2 = $rowd1['Female'] + 0; ?></td>
            <td><?php echo $df2 + $dm2; ?></td>

            <td><?php echo $dm3 = $rowd2['Male'] + 0; ?></td>
            <td><?php echo $df3 = $rowd2['Female'] + 0; ?></td>
            <td><?php echo $dm3 + $df3; ?></td>

            <td><?php echo $dm4 = $rowd4['Male'] + 0; ?></td>
            <td><?php echo $df4 = $rowd4['Female'] + 0; ?></td>
            <td><?php echo $dm4 + $df4; ?></td>

            <td><?php echo $dm5 = $rowd5['Male'] + 0; ?></td>
            <td><?php echo $df5 = $rowd5['Female'] + 0; ?></td>
            <td><?php echo $dm5 + $df5; ?></td>

            <td><?php echo $dm6 = $dm1 + $dm2 + $dm3 + $dm4 + $dm5 ?></td>
            <td><?php echo $df6 = $df1 + $df2 + $df3 + $df4 + $df5 ?></td>
            <td><?php echo $dm6 + $df6; ?></td>
        </tr>
        <tr>
            <td>3</td>
            <td colspan="5">Mahudhurio ya marudio</td>
            <td><?php echo $cm1 = $rowc['Male'] + 0; ?></td>
            <td><?php echo $cf1 = $rowc['Female'] + 0; ?></td>
            <td><?php echo $cf1 + $cm1; ?></td>

            <td><?php echo $cm2 = $rowc1['Male'] + 0; ?></td>
            <td><?php echo $cf2 = $rowc1['Female'] + 0; ?></td>
            <td><?php echo $cf2 + $cm2; ?></td>

            <td><?php echo $cm3 = $rowc2['Male'] + 0; ?></td>
            <td><?php echo $cf3 = $rowc2['Female'] + 0; ?></td>
            <td><?php echo $cm3 + $cf3; ?></td>

            <td><?php echo $cm4 = $rowc4['Male'] + 0; ?></td>
            <td><?php echo $cf4 = $rowc4['Female'] + 0; ?></td>
            <td><?php echo $cm4 + $cf4; ?></td>

            <td><?php echo $cm5 = $rowc5['Male'] + 0; ?></td>
            <td><?php echo $cf5 = $rowc5['Female'] + 0; ?></td>
            <td><?php echo $cm5 + $cf5; ?></td>

            <td><?php echo $cm6 = $cm1 + $cm2 + $cm3 + $cm4 + $cm5 ?></td>
            <td><?php echo $cf6 = $cf1 + $cf2 + $cf3 + $cf4 + $cf5 ?></td>
            <td><?php echo $cm6 + $cf6; ?></td>
        </tr>
        <tr>
            <td></td>
            <td colspan="5">Mahudhurio ya OPD (2+3)</td>
            <td><?php echo $aa1 = $cm1+$dm1; ?></td>
            <td><?php echo $bb1 = $cf1+$df1; ?></td>
            <td><?php echo $aa1 + $bb1; ?></td>

            <td><?php echo $aa2 = $cm2+$dm2; ?></td>
            <td><?php echo $bb2 = $cf2+$df2;; ?></td>
            <td><?php echo $aa2 + $bb2; ?></td>

            <td><?php echo $aa3 = $cm3+$dm3; ?></td>
            <td><?php echo $bb3 = $cf3+$df3;; ?></td>
            <td><?php echo $aa3 + $bb3; ?></td>

            <td><?php echo $aa4 = $cm4+$dm4; ?></td>
            <td><?php echo $bb4 = $cf4+$df4; ?></td>
            <td><?php echo $aa4 + $bb4; ?></td>

            <td><?php echo $aa5 = $cm5+$dm5; ?></td>
            <td><?php echo $bb5 = $cf5+$df5; ?></td>
            <td><?php echo $aa5 + $bb5; ?></td>

            <td><?php echo $aa6 = $aa1 + $aa2 + $aa3 + $aa4 + $aa5 ?></td>
            <td><?php echo $bb6 = $bb1 + $bb2 + $bb3 + $bb4 + $bb5 ?></td>
            <td><?php echo $aa6 + $bb6; ?></td>
        </tr>

        <tr style=" background-color: grey;">
            <td>&nbsp;</td>
            <td colspan="23">Diagnosis za OPD</td>
        </tr>
        <tr style="background-color: grey;">
            <td>&nbsp;I</td>
            <td colspan="23">Infections and Parasitic diseases</td>
        </tr>
    <?php while($row_diagnoses = mysqli_fetch_array($infections1)){?>
        <tr>
            <td><?php echo $id=$row_diagnoses['id'];?></td>
            <td colspan="5"><?php echo $row_diagnoses['description'];?></td>
            <td><?php echo $i=diagnoses(1,$year,$month,$id,0,30);?></td>
            <td><?php echo $j=diagnoses(2,$year,$month,$id,0,30);?></td>
            <td><?php echo $i+$j; ?></td>
            <td><?php echo $k=diagnoses(1,$year,$month,$id,30,365);?></td>
            <td><?php echo $l=diagnoses(2,$year,$month,$id,0,365);?></td>
            <td><?php echo $k+$l; ?></td>
            <td><?php echo $m=diagnoses(1,$year,$month,$id,365,1825);?></td>
            <td><?php echo $n=diagnoses(2,$year,$month,$id,365,1825);?></td>
            <td><?php echo $m+$n; ?></td>
            <td><?php echo $o=diagnoses(1,$year,$month,$id,1825,21900);?></td>
            <td><?php echo $p=diagnoses(2,$year,$month,$id,1825,21900);?></td>
            <td><?php echo $o+$p; ?></td>
            <td><?php echo $q=diagnoses(1,$year,$month,$id,21900,36500);?></td>
            <td><?php echo $r=diagnoses(2,$year,$month,$id,21900,36500);?></td>
            <td><?php echo $q+$r; ?></td>
            <td><?php echo $s=($i+$k+$m+$o+$q);?></td>
            <td><?php echo $t=($j+$l+$n+$p+$r);?></td>
            <td><?php echo $s+$t;?></td>
        </tr>
        <?php }?>
        <tr>
            <td rowspan="4">14</td>
            <td rowspan="4">Malaria</td>
            <td colspan="4">Malaria blood slide positive</td>
            <td><?php echo $gm1 = $rowg['Male'] + 0; ?></td>
            <td><?php echo $gf1 = $rowg['Female'] + 0; ?></td>
            <td><?php echo $gm1 + $gf1; ?></td>

            <td><?php echo $gm2 = $rowg1['Male'] + 0; ?></td>
            <td><?php echo $gf2 = $rowg1['Female'] + 0; ?></td>
            <td><?php echo $gm2 + $gf2; ?></td>

            <td><?php echo $gm3 = $rowg2['Male'] + 0; ?></td>
            <td><?php echo $gf3 = $rowg2['Female'] + 0; ?></td>
            <td><?php echo $gm3 + $gf3; ?></td>

            <td><?php echo $gm4 = $rowg4['Male'] + 0; ?></td>
            <td><?php echo $gf4 = $rowg4['Female'] + 0; ?></td>
            <td><?php echo $gm4 + $gf4; ?></td>

            <td><?php echo $gm5 = $rowg5['Male'] + 0; ?></td>
            <td><?php echo $gf5 = $rowg5['Female'] + 0; ?></td>
            <td><?php echo $gm5 + $gf5; ?></td>

            <td><?php echo $gm6 = $gm1 + $gm2 + $gm3 + $gm4 + $gm5 ?></td>
            <td><?php echo $gf6 = $gf1 + $gf2 + $gf3 + $gf4 + $gf5 ?></td>
            <td><?php echo $gm6 + $gf6; ?></td>
        </tr>
        <tr>
            <td colspan="4">Malaria mRDT positive</td>
            <td><?php echo $hm1 = $rowh['Male'] + 0; ?></td>
            <td><?php echo $hf1 = $rowh['Female'] + 0; ?></td>
            <td><?php echo $hm1 + $hf1; ?></td>

            <td><?php echo $hm2 = $rowh1['Male'] + 0; ?></td>
            <td><?php echo $hf2 = $rowh1['Female'] + 0; ?></td>
            <td><?php echo $hm2 + $hf2; ?></td>

            <td><?php echo $hm3 = $rowh2['Male'] + 0; ?></td>
            <td><?php echo $hf3 = $rowh2['Female'] + 0; ?></td>
            <td><?php echo $hm3 + $hf3; ?></td>

            <td><?php echo $hm4 = $rowh4['Male'] + 0; ?></td>
            <td><?php echo $hf4 = $rowh4['Female'] + 0; ?></td>
            <td><?php echo $hm4 + $hf4; ?></td>

            <td><?php echo $hm5 = $rowh5['Male'] + 0; ?></td>
            <td><?php echo $hf5 = $rowh5['Female'] + 0; ?></td>
            <td><?php echo $hm5 + $hf5; ?></td>

            <td><?php echo $hm6 = $hm1 + $hm2 + $hm3 + $hm4 + $hm5 ?></td>
            <td><?php echo $hf6 = $hf1 + $hf2 + $hf3 + $hf4 + $hf5 ?></td>
            <td><?php echo $hm6 + $hf6; ?></td>
        </tr>
        </tr>
        <tr>
            <td colspan="4">Malaria clinical [No Test]</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
        </tr>
        <tr>
            <td colspan="4">Cases (Referral in)</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
        </tr>
    <?php while($row_diagnoses = mysqli_fetch_array($infections2)){?>
        <tr>
            <td><?php echo $id=$row_diagnoses['id'];?></td>
            <td colspan="5"><?php echo $row_diagnoses['description'];?></td>
            <td><?php echo $i=diagnoses(1,$year,$month,$id,0,30);?></td>
            <td><?php echo $j=diagnoses(2,$year,$month,$id,0,30);?></td>
            <td><?php echo $i+$j; ?></td>
            <td><?php echo $k=diagnoses(1,$year,$month,$id,30,365);?></td>
            <td><?php echo $l=diagnoses(2,$year,$month,$id,0,365);?></td>
            <td><?php echo $k+$l; ?></td>
            <td><?php echo $m=diagnoses(1,$year,$month,$id,365,1825);?></td>
            <td><?php echo $n=diagnoses(2,$year,$month,$id,365,1825);?></td>
            <td><?php echo $m+$n; ?></td>
            <td><?php echo $o=diagnoses(1,$year,$month,$id,1825,21900);?></td>
            <td><?php echo $p=diagnoses(2,$year,$month,$id,1825,21900);?></td>
            <td><?php echo $o+$p; ?></td>
            <td><?php echo $q=diagnoses(1,$year,$month,$id,21900,36500);?></td>
            <td><?php echo $r=diagnoses(2,$year,$month,$id,21900,36500);?></td>
            <td><?php echo $q+$r; ?></td>
            <td><?php echo $s=($i+$k+$m+$o+$q);?></td>
            <td><?php echo $t=($j+$l+$n+$p+$r);?></td>
            <td><?php echo $s+$t;?></td>
        </tr>
        <?php }?>
        <tr style="background-color: grey;">
            <td>&nbsp;II</td>
            <td colspan="23">Neoplasms</td>
        </tr>
    <?php while($row_diagnoses = mysqli_fetch_array($neoplasm)){?>
        <tr>
            <td><?php echo $id=$row_diagnoses['id'];?></td>
            <td colspan="5"><?php echo $row_diagnoses['description'];?></td>
            <td><?php echo $i=diagnoses(1,$year,$month,$id,0,30);?></td>
            <td><?php echo $j=diagnoses(2,$year,$month,$id,0,30);?></td>
            <td><?php echo $i+$j; ?></td>
            <td><?php echo $k=diagnoses(1,$year,$month,$id,30,365);?></td>
            <td><?php echo $l=diagnoses(2,$year,$month,$id,0,365);?></td>
            <td><?php echo $k+$l; ?></td>
            <td><?php echo $m=diagnoses(1,$year,$month,$id,365,1825);?></td>
            <td><?php echo $n=diagnoses(2,$year,$month,$id,365,1825);?></td>
            <td><?php echo $m+$n; ?></td>
            <td><?php echo $o=diagnoses(1,$year,$month,$id,1825,21900);?></td>
            <td><?php echo $p=diagnoses(2,$year,$month,$id,1825,21900);?></td>
            <td><?php echo $o+$p; ?></td>
            <td><?php echo $q=diagnoses(1,$year,$month,$id,21900,36500);?></td>
            <td><?php echo $r=diagnoses(2,$year,$month,$id,21900,36500);?></td>
            <td><?php echo $q+$r; ?></td>
            <td><?php echo $s=($i+$k+$m+$o+$q);?></td>
            <td><?php echo $t=($j+$l+$n+$p+$r);?></td>
            <td><?php echo $s+$t;?></td>
        </tr>
        <?php }?>
        <tr style="background-color: grey;">
            <td>&nbsp;III</td>
            <td colspan="23">Diseases of Blood and blood forming Organs</td>
        </tr>
    <?php while($row_diagnoses = mysqli_fetch_array($blood)){?>
        <tr>
            <td><?php echo $id=$row_diagnoses['id'];?></td>
            <td colspan="5"><?php echo $row_diagnoses['description'];?></td>
            <td><?php echo $i=diagnoses(1,$year,$month,$id,0,30);?></td>
            <td><?php echo $j=diagnoses(2,$year,$month,$id,0,30);?></td>
            <td><?php echo $i+$j; ?></td>
            <td><?php echo $k=diagnoses(1,$year,$month,$id,30,365);?></td>
            <td><?php echo $l=diagnoses(2,$year,$month,$id,0,365);?></td>
            <td><?php echo $k+$l; ?></td>
            <td><?php echo $m=diagnoses(1,$year,$month,$id,365,1825);?></td>
            <td><?php echo $n=diagnoses(2,$year,$month,$id,365,1825);?></td>
            <td><?php echo $m+$n; ?></td>
            <td><?php echo $o=diagnoses(1,$year,$month,$id,1825,21900);?></td>
            <td><?php echo $p=diagnoses(2,$year,$month,$id,1825,21900);?></td>
            <td><?php echo $o+$p; ?></td>
            <td><?php echo $q=diagnoses(1,$year,$month,$id,21900,36500);?></td>
            <td><?php echo $r=diagnoses(2,$year,$month,$id,21900,36500);?></td>
            <td><?php echo $q+$r; ?></td>
            <td><?php echo $s=($i+$k+$m+$o+$q);?></td>
            <td><?php echo $t=($j+$l+$n+$p+$r);?></td>
            <td><?php echo $s+$t;?></td>
        </tr>
        <?php }?>
        <tr style="background-color: grey;">
            <td>&nbsp;IV</td>
            <td colspan="23">Endocrine, Nutritional and Metabolic Diseases</td>
        </tr>
    <?php while($row_diagnoses = mysqli_fetch_array($endocrine)){?>
        <tr>
            <td><?php echo $id=$row_diagnoses['id'];?></td>
            <td colspan="5"><?php echo $row_diagnoses['description'];?></td>
            <td><?php echo $i=diagnoses(1,$year,$month,$id,0,30);?></td>
            <td><?php echo $j=diagnoses(2,$year,$month,$id,0,30);?></td>
            <td><?php echo $i+$j; ?></td>
            <td><?php echo $k=diagnoses(1,$year,$month,$id,30,365);?></td>
            <td><?php echo $l=diagnoses(2,$year,$month,$id,0,365);?></td>
            <td><?php echo $k+$l; ?></td>
            <td><?php echo $m=diagnoses(1,$year,$month,$id,365,1825);?></td>
            <td><?php echo $n=diagnoses(2,$year,$month,$id,365,1825);?></td>
            <td><?php echo $m+$n; ?></td>
            <td><?php echo $o=diagnoses(1,$year,$month,$id,1825,21900);?></td>
            <td><?php echo $p=diagnoses(2,$year,$month,$id,1825,21900);?></td>
            <td><?php echo $o+$p; ?></td>
            <td><?php echo $q=diagnoses(1,$year,$month,$id,21900,36500);?></td>
            <td><?php echo $r=diagnoses(2,$year,$month,$id,21900,36500);?></td>
            <td><?php echo $q+$r; ?></td>
            <td><?php echo $s=($i+$k+$m+$o+$q);?></td>
            <td><?php echo $t=($j+$l+$n+$p+$r);?></td>
            <td><?php echo $s+$t;?></td>
        </tr>
        <?php }?>
        <tr style="background-color: grey;">
            <td>&nbsp;V</td>
            <td colspan="23">Mental and Behavioral Disorders</td>
        </tr>
    <?php while($row_diagnoses = mysqli_fetch_array($mental)){?>
        <tr>
            <td><?php echo $id=$row_diagnoses['id'];?></td>
            <td colspan="5"><?php echo $row_diagnoses['description'];?></td>
            <td><?php echo $i=diagnoses(1,$year,$month,$id,0,30);?></td>
            <td><?php echo $j=diagnoses(2,$year,$month,$id,0,30);?></td>
            <td><?php echo $i+$j; ?></td>
            <td><?php echo $k=diagnoses(1,$year,$month,$id,30,365);?></td>
            <td><?php echo $l=diagnoses(2,$year,$month,$id,0,365);?></td>
            <td><?php echo $k+$l; ?></td>
            <td><?php echo $m=diagnoses(1,$year,$month,$id,365,1825);?></td>
            <td><?php echo $n=diagnoses(2,$year,$month,$id,365,1825);?></td>
            <td><?php echo $m+$n; ?></td>
            <td><?php echo $o=diagnoses(1,$year,$month,$id,1825,21900);?></td>
            <td><?php echo $p=diagnoses(2,$year,$month,$id,1825,21900);?></td>
            <td><?php echo $o+$p; ?></td>
            <td><?php echo $q=diagnoses(1,$year,$month,$id,21900,36500);?></td>
            <td><?php echo $r=diagnoses(2,$year,$month,$id,21900,36500);?></td>
            <td><?php echo $q+$r; ?></td>
            <td><?php echo $s=($i+$k+$m+$o+$q);?></td>
            <td><?php echo $t=($j+$l+$n+$p+$r);?></td>
            <td><?php echo $s+$t;?></td>
        </tr>
        <?php }?>
        <tr style="background-color: grey;">
            <td>&nbsp;VI</td>
            <td colspan="23">Diseases of the Nervous System</td>
        </tr>
        <?php while($row_diagnoses = mysqli_fetch_array($nervous)){?>
        <tr>
            <td><?php echo $id=$row_diagnoses['id'];?></td>
            <td colspan="5"><?php echo $row_diagnoses['description'];?></td>
            <td><?php echo $i=diagnoses(1,$year,$month,$id,0,30);?></td>
            <td><?php echo $j=diagnoses(2,$year,$month,$id,0,30);?></td>
            <td><?php echo $i+$j; ?></td>
            <td><?php echo $k=diagnoses(1,$year,$month,$id,30,365);?></td>
            <td><?php echo $l=diagnoses(2,$year,$month,$id,0,365);?></td>
            <td><?php echo $k+$l; ?></td>
            <td><?php echo $m=diagnoses(1,$year,$month,$id,365,1825);?></td>
            <td><?php echo $n=diagnoses(2,$year,$month,$id,365,1825);?></td>
            <td><?php echo $m+$n; ?></td>
            <td><?php echo $o=diagnoses(1,$year,$month,$id,1825,21900);?></td>
            <td><?php echo $p=diagnoses(2,$year,$month,$id,1825,21900);?></td>
            <td><?php echo $o+$p; ?></td>
            <td><?php echo $q=diagnoses(1,$year,$month,$id,21900,36500);?></td>
            <td><?php echo $r=diagnoses(2,$year,$month,$id,21900,36500);?></td>
            <td><?php echo $q+$r; ?></td>
            <td><?php echo $s=($i+$k+$m+$o+$q);?></td>
            <td><?php echo $t=($j+$l+$n+$p+$r);?></td>
            <td><?php echo $s+$t;?></td>
        </tr>
        <?php }?>
        <tr style="background-color: grey;">
            <td>&nbsp;VII</td>
            <td colspan="23">Diseases of the Eye</td>
        </tr>
        <?php while($row_diagnoses = mysqli_fetch_array($eye)){?>
        <tr>
            <td><?php echo $id=$row_diagnoses['id'];?></td>
            <td colspan="5"><?php echo $row_diagnoses['catname'];?></td>
            <td><?php echo $i=diagnoses(1,$year,$month,$id,0,30);?></td>
            <td><?php echo $j=diagnoses(2,$year,$month,$id,0,30);?></td>
            <td><?php echo $i+$j; ?></td>
            <td><?php echo $k=diagnoses(1,$year,$month,$id,30,365);?></td>
            <td><?php echo $l=diagnoses(2,$year,$month,$id,0,365);?></td>
            <td><?php echo $k+$l; ?></td>
            <td><?php echo $m=diagnoses(1,$year,$month,$id,365,1825);?></td>
            <td><?php echo $n=diagnoses(2,$year,$month,$id,365,1825);?></td>
            <td><?php echo $m+$n; ?></td>
            <td><?php echo $o=diagnoses(1,$year,$month,$id,1825,21900);?></td>
            <td><?php echo $p=diagnoses(2,$year,$month,$id,1825,21900);?></td>
            <td><?php echo $o+$p; ?></td>
            <td><?php echo $q=diagnoses(1,$year,$month,$id,21900,36500);?></td>
            <td><?php echo $r=diagnoses(2,$year,$month,$id,21900,36500);?></td>
            <td><?php echo $q+$r; ?></td>
            <td><?php echo $s=($i+$k+$m+$o+$q);?></td>
            <td><?php echo $t=($j+$l+$n+$p+$r);?></td>
            <td><?php echo $s+$t;?></td>
        </tr>
        <?php }?>
        <tr style="background-color: grey;">
            <td>&nbsp;VIII</td>
            <td colspan="23">Diseases of the Ear and Mastoid Process</td>
        </tr>
        <?php while($row_diagnoses = mysqli_fetch_array($ear)){?>
        <tr>
            <td><?php echo $id=$row_diagnoses['id'];?></td>
            <td colspan="5"><?php echo $row_diagnoses['catname'];?></td>
            <td><?php echo $i=diagnoses(1,$year,$month,$id,0,30);?></td>
            <td><?php echo $j=diagnoses(2,$year,$month,$id,0,30);?></td>
            <td><?php echo $i+$j; ?></td>
            <td><?php echo $k=diagnoses(1,$year,$month,$id,30,365);?></td>
            <td><?php echo $l=diagnoses(2,$year,$month,$id,0,365);?></td>
            <td><?php echo $k+$l; ?></td>
            <td><?php echo $m=diagnoses(1,$year,$month,$id,365,1825);?></td>
            <td><?php echo $n=diagnoses(2,$year,$month,$id,365,1825);?></td>
            <td><?php echo $m+$n; ?></td>
            <td><?php echo $o=diagnoses(1,$year,$month,$id,1825,21900);?></td>
            <td><?php echo $p=diagnoses(2,$year,$month,$id,1825,21900);?></td>
            <td><?php echo $o+$p; ?></td>
            <td><?php echo $q=diagnoses(1,$year,$month,$id,21900,36500);?></td>
            <td><?php echo $r=diagnoses(2,$year,$month,$id,21900,36500);?></td>
            <td><?php echo $q+$r; ?></td>
            <td><?php echo $s=($i+$k+$m+$o+$q);?></td>
            <td><?php echo $t=($j+$l+$n+$p+$r);?></td>
            <td><?php echo $s+$t;?></td>
        </tr>
        <?php }?>
        <tr style="background-color: grey;">
            <td>&nbsp;IX</td>
            <td colspan="23">Diseases of the Circulatory System</td>
        </tr>
        <?php while($row_diagnoses = mysqli_fetch_array($circulatory)){?>
        <tr>
            <td><?php echo $id=$row_diagnoses['id'];?></td>
            <td colspan="5"><?php echo $row_diagnoses['catname'];?></td>
            <td><?php echo $i=diagnoses(1,$year,$month,$id,0,30);?></td>
            <td><?php echo $j=diagnoses(2,$year,$month,$id,0,30);?></td>
            <td><?php echo $i+$j; ?></td>
            <td><?php echo $k=diagnoses(1,$year,$month,$id,30,365);?></td>
            <td><?php echo $l=diagnoses(2,$year,$month,$id,0,365);?></td>
            <td><?php echo $k+$l; ?></td>
            <td><?php echo $m=diagnoses(1,$year,$month,$id,365,1825);?></td>
            <td><?php echo $n=diagnoses(2,$year,$month,$id,365,1825);?></td>
            <td><?php echo $m+$n; ?></td>
            <td><?php echo $o=diagnoses(1,$year,$month,$id,1825,21900);?></td>
            <td><?php echo $p=diagnoses(2,$year,$month,$id,1825,21900);?></td>
            <td><?php echo $o+$p; ?></td>
            <td><?php echo $q=diagnoses(1,$year,$month,$id,21900,36500);?></td>
            <td><?php echo $r=diagnoses(2,$year,$month,$id,21900,36500);?></td>
            <td><?php echo $q+$r; ?></td>
            <td><?php echo $s=($i+$k+$m+$o+$q);?></td>
            <td><?php echo $t=($j+$l+$n+$p+$r);?></td>
            <td><?php echo $s+$t;?></td>
        </tr>
        <?php }?>
        <tr style="background-color: grey;">
            <td>&nbsp;X</td>
            <td colspan="23">Diseases of the Respiratory System</td>
        </tr>
        <?php while($row_diagnoses = mysqli_fetch_array($respiratory)){?>
        <tr>
            <td><?php echo $id=$row_diagnoses['id'];?></td>
            <td colspan="5"><?php echo $row_diagnoses['catname'];?></td>
            <td><?php echo $i=diagnoses(1,$year,$month,$id,0,30);?></td>
            <td><?php echo $j=diagnoses(2,$year,$month,$id,0,30);?></td>
            <td><?php echo $i+$j; ?></td>
            <td><?php echo $k=diagnoses(1,$year,$month,$id,30,365);?></td>
            <td><?php echo $l=diagnoses(2,$year,$month,$id,0,365);?></td>
            <td><?php echo $k+$l; ?></td>
            <td><?php echo $m=diagnoses(1,$year,$month,$id,365,1825);?></td>
            <td><?php echo $n=diagnoses(2,$year,$month,$id,365,1825);?></td>
            <td><?php echo $m+$n; ?></td>
            <td><?php echo $o=diagnoses(1,$year,$month,$id,1825,21900);?></td>
            <td><?php echo $p=diagnoses(2,$year,$month,$id,1825,21900);?></td>
            <td><?php echo $o+$p; ?></td>
            <td><?php echo $q=diagnoses(1,$year,$month,$id,21900,36500);?></td>
            <td><?php echo $r=diagnoses(2,$year,$month,$id,21900,36500);?></td>
            <td><?php echo $q+$r; ?></td>
            <td><?php echo $s=($i+$k+$m+$o+$q);?></td>
            <td><?php echo $t=($j+$l+$n+$p+$r);?></td>
            <td><?php echo $s+$t;?></td>
        </tr>
        <?php }?>
        <tr style="background-color: grey;">
            <td>&nbsp;XI</td>
            <td colspan="23">Diseases of the Digestive System</td>
        </tr>
        <?php while($row_diagnoses = mysqli_fetch_array($digestive)){?>
        <tr>
            <td><?php echo $id=$row_diagnoses['id'];?></td>
            <td colspan="5"><?php echo $row_diagnoses['catname'];?></td>
            <td><?php echo $i=diagnoses(1,$year,$month,$id,0,30);?></td>
            <td><?php echo $j=diagnoses(2,$year,$month,$id,0,30);?></td>
            <td><?php echo $i+$j; ?></td>
            <td><?php echo $k=diagnoses(1,$year,$month,$id,30,365);?></td>
            <td><?php echo $l=diagnoses(2,$year,$month,$id,0,365);?></td>
            <td><?php echo $k+$l; ?></td>
            <td><?php echo $m=diagnoses(1,$year,$month,$id,365,1825);?></td>
            <td><?php echo $n=diagnoses(2,$year,$month,$id,365,1825);?></td>
            <td><?php echo $m+$n; ?></td>
            <td><?php echo $o=diagnoses(1,$year,$month,$id,1825,21900);?></td>
            <td><?php echo $p=diagnoses(2,$year,$month,$id,1825,21900);?></td>
            <td><?php echo $o+$p; ?></td>
            <td><?php echo $q=diagnoses(1,$year,$month,$id,21900,36500);?></td>
            <td><?php echo $r=diagnoses(2,$year,$month,$id,21900,36500);?></td>
            <td><?php echo $q+$r; ?></td>
            <td><?php echo $s=($i+$k+$m+$o+$q);?></td>
            <td><?php echo $t=($j+$l+$n+$p+$r);?></td>
            <td><?php echo $s+$t;?></td>
        </tr>
        <?php }?>
        <tr style="background-color: grey;">
            <td>&nbsp;XII</td>
            <td colspan="23">Diseases of the Skin and Subcutaneous Tissue</td>
        </tr>
        <?php while($row_diagnoses = mysqli_fetch_array($skin)){?>
        <tr>
            <td><?php echo $id=$row_diagnoses['id'];?></td>
            <td colspan="5"><?php echo $row_diagnoses['catname'];?></td>
            <td><?php echo $i=diagnoses(1,$year,$month,$id,0,30);?></td>
            <td><?php echo $j=diagnoses(2,$year,$month,$id,0,30);?></td>
            <td><?php echo $i+$j; ?></td>
            <td><?php echo $k=diagnoses(1,$year,$month,$id,30,365);?></td>
            <td><?php echo $l=diagnoses(2,$year,$month,$id,0,365);?></td>
            <td><?php echo $k+$l; ?></td>
            <td><?php echo $m=diagnoses(1,$year,$month,$id,365,1825);?></td>
            <td><?php echo $n=diagnoses(2,$year,$month,$id,365,1825);?></td>
            <td><?php echo $m+$n; ?></td>
            <td><?php echo $o=diagnoses(1,$year,$month,$id,1825,21900);?></td>
            <td><?php echo $p=diagnoses(2,$year,$month,$id,1825,21900);?></td>
            <td><?php echo $o+$p; ?></td>
            <td><?php echo $q=diagnoses(1,$year,$month,$id,21900,36500);?></td>
            <td><?php echo $r=diagnoses(2,$year,$month,$id,21900,36500);?></td>
            <td><?php echo $q+$r; ?></td>
            <td><?php echo $s=($i+$k+$m+$o+$q);?></td>
            <td><?php echo $t=($j+$l+$n+$p+$r);?></td>
            <td><?php echo $s+$t;?></td>
        </tr>
        <?php }?>
        <tr style="background-color: grey;">
            <td>&nbsp;XIII</td>
            <td colspan="23">Diseases of the Musculoskeletal System and Connective Tissue</td>
        </tr>
        <?php while($row_diagnoses = mysqli_fetch_array($musculoskeletal)){?>
        <tr>
            <td><?php echo $id=$row_diagnoses['id'];?></td>
            <td colspan="5"><?php echo $row_diagnoses['catname'];?></td>
            <td><?php echo $i=diagnoses(1,$year,$month,$id,0,30);?></td>
            <td><?php echo $j=diagnoses(2,$year,$month,$id,0,30);?></td>
            <td><?php echo $i+$j; ?></td>
            <td><?php echo $k=diagnoses(1,$year,$month,$id,30,365);?></td>
            <td><?php echo $l=diagnoses(2,$year,$month,$id,0,365);?></td>
            <td><?php echo $k+$l; ?></td>
            <td><?php echo $m=diagnoses(1,$year,$month,$id,365,1825);?></td>
            <td><?php echo $n=diagnoses(2,$year,$month,$id,365,1825);?></td>
            <td><?php echo $m+$n; ?></td>
            <td><?php echo $o=diagnoses(1,$year,$month,$id,1825,21900);?></td>
            <td><?php echo $p=diagnoses(2,$year,$month,$id,1825,21900);?></td>
            <td><?php echo $o+$p; ?></td>
            <td><?php echo $q=diagnoses(1,$year,$month,$id,21900,36500);?></td>
            <td><?php echo $r=diagnoses(2,$year,$month,$id,21900,36500);?></td>
            <td><?php echo $q+$r; ?></td>
            <td><?php echo $s=($i+$k+$m+$o+$q);?></td>
            <td><?php echo $t=($j+$l+$n+$p+$r);?></td>
            <td><?php echo $s+$t;?></td>
        </tr>
        <?php }?>
        <tr style="background-color: grey;">
            <td>&nbsp;XIV</td>
            <td colspan="23">Diseases of the Genitourinary System and Pelvic Infalammatory diseases</td>
        </tr>
        <?php while($row_diagnoses = mysqli_fetch_array($genitourinary)){?>
        <tr>
            <td><?php echo $id=$row_diagnoses['id'];?></td>
            <td colspan="5"><?php echo $row_diagnoses['catname'];?></td>
            <td><?php echo $i=diagnoses(1,$year,$month,$id,0,30);?></td>
            <td><?php echo $j=diagnoses(2,$year,$month,$id,0,30);?></td>
            <td><?php echo $i+$j; ?></td>
            <td><?php echo $k=diagnoses(1,$year,$month,$id,30,365);?></td>
            <td><?php echo $l=diagnoses(2,$year,$month,$id,0,365);?></td>
            <td><?php echo $k+$l; ?></td>
            <td><?php echo $m=diagnoses(1,$year,$month,$id,365,1825);?></td>
            <td><?php echo $n=diagnoses(2,$year,$month,$id,365,1825);?></td>
            <td><?php echo $m+$n; ?></td>
            <td><?php echo $o=diagnoses(1,$year,$month,$id,1825,21900);?></td>
            <td><?php echo $p=diagnoses(2,$year,$month,$id,1825,21900);?></td>
            <td><?php echo $o+$p; ?></td>
            <td><?php echo $q=diagnoses(1,$year,$month,$id,21900,36500);?></td>
            <td><?php echo $r=diagnoses(2,$year,$month,$id,21900,36500);?></td>
            <td><?php echo $q+$r; ?></td>
            <td><?php echo $s=($i+$k+$m+$o+$q);?></td>
            <td><?php echo $t=($j+$l+$n+$p+$r);?></td>
            <td><?php echo $s+$t;?></td>
        </tr>
        <?php }?>
        <tr style="background-color: grey;">
            <td>&nbsp;XV</td>
            <td colspan="23">Pregnancy, Childbirth and the Puerperium</td>
        </tr>
        <?php while($row_diagnoses = mysqli_fetch_array($pregnancy)){?>
        <tr>
            <td><?php echo $id=$row_diagnoses['id'];?></td>
            <td colspan="5"><?php echo $row_diagnoses['catname'];?></td>
            <td><?php echo $i=diagnoses(1,$year,$month,$id,0,30);?></td>
            <td><?php echo $j=diagnoses(2,$year,$month,$id,0,30);?></td>
            <td><?php echo $i+$j; ?></td>
            <td><?php echo $k=diagnoses(1,$year,$month,$id,30,365);?></td>
            <td><?php echo $l=diagnoses(2,$year,$month,$id,0,365);?></td>
            <td><?php echo $k+$l; ?></td>
            <td><?php echo $m=diagnoses(1,$year,$month,$id,365,1825);?></td>
            <td><?php echo $n=diagnoses(2,$year,$month,$id,365,1825);?></td>
            <td><?php echo $m+$n; ?></td>
            <td><?php echo $o=diagnoses(1,$year,$month,$id,1825,21900);?></td>
            <td><?php echo $p=diagnoses(2,$year,$month,$id,1825,21900);?></td>
            <td><?php echo $o+$p; ?></td>
            <td><?php echo $q=diagnoses(1,$year,$month,$id,21900,36500);?></td>
            <td><?php echo $r=diagnoses(2,$year,$month,$id,21900,36500);?></td>
            <td><?php echo $q+$r; ?></td>
            <td><?php echo $s=($i+$k+$m+$o+$q);?></td>
            <td><?php echo $t=($j+$l+$n+$p+$r);?></td>
            <td><?php echo $s+$t;?></td>
        </tr>
        <?php }?>
        <tr style="background-color: grey;">
            <td>&nbsp;XVI</td>
            <td colspan="23">Certain Conditions Originating in the Perinatal Period</td>
        </tr>
        <?php while($row_diagnoses = mysqli_fetch_array($perinatal)){?>
        <tr>
            <td><?php echo $id=$row_diagnoses['id'];?></td>
            <td colspan="5"><?php echo $row_diagnoses['catname'];?></td>
            <td><?php echo $i=diagnoses(1,$year,$month,$id,0,30);?></td>
            <td><?php echo $j=diagnoses(2,$year,$month,$id,0,30);?></td>
            <td><?php echo $i+$j; ?></td>
            <td><?php echo $k=diagnoses(1,$year,$month,$id,30,365);?></td>
            <td><?php echo $l=diagnoses(2,$year,$month,$id,0,365);?></td>
            <td><?php echo $k+$l; ?></td>
            <td><?php echo $m=diagnoses(1,$year,$month,$id,365,1825);?></td>
            <td><?php echo $n=diagnoses(2,$year,$month,$id,365,1825);?></td>
            <td><?php echo $m+$n; ?></td>
            <td><?php echo $o=diagnoses(1,$year,$month,$id,1825,21900);?></td>
            <td><?php echo $p=diagnoses(2,$year,$month,$id,1825,21900);?></td>
            <td><?php echo $o+$p; ?></td>
            <td><?php echo $q=diagnoses(1,$year,$month,$id,21900,36500);?></td>
            <td><?php echo $r=diagnoses(2,$year,$month,$id,21900,36500);?></td>
            <td><?php echo $q+$r; ?></td>
            <td><?php echo $s=($i+$k+$m+$o+$q);?></td>
            <td><?php echo $t=($j+$l+$n+$p+$r);?></td>
            <td><?php echo $s+$t;?></td>
        </tr>
        <?php }?>
        <tr style="background-color: grey;">
            <td>&nbsp;XVII</td>
            <td colspan="23">Congenital Malformations, Deformations and Chromosomal Abnormalities</td>
        </tr>
        <?php while($row_diagnoses = mysqli_fetch_array($congenital)){?>
        <tr>
            <td><?php echo $id=$row_diagnoses['id'];?></td>
            <td colspan="5"><?php echo $row_diagnoses['catname'];?></td>
            <td><?php echo $i=diagnoses(1,$year,$month,$id,0,30);?></td>
            <td><?php echo $j=diagnoses(2,$year,$month,$id,0,30);?></td>
            <td><?php echo $i+$j; ?></td>
            <td><?php echo $k=diagnoses(1,$year,$month,$id,30,365);?></td>
            <td><?php echo $l=diagnoses(2,$year,$month,$id,0,365);?></td>
            <td><?php echo $k+$l; ?></td>
            <td><?php echo $m=diagnoses(1,$year,$month,$id,365,1825);?></td>
            <td><?php echo $n=diagnoses(2,$year,$month,$id,365,1825);?></td>
            <td><?php echo $m+$n; ?></td>
            <td><?php echo $o=diagnoses(1,$year,$month,$id,1825,21900);?></td>
            <td><?php echo $p=diagnoses(2,$year,$month,$id,1825,21900);?></td>
            <td><?php echo $o+$p; ?></td>
            <td><?php echo $q=diagnoses(1,$year,$month,$id,21900,36500);?></td>
            <td><?php echo $r=diagnoses(2,$year,$month,$id,21900,36500);?></td>
            <td><?php echo $q+$r; ?></td>
            <td><?php echo $s=($i+$k+$m+$o+$q);?></td>
            <td><?php echo $t=($j+$l+$n+$p+$r);?></td>
            <td><?php echo $s+$t;?></td>
        </tr>
        <?php }?>
        <tr style="background-color: grey;">
            <td>&nbsp;XIX</td>
            <td colspan="23">Injury, Poisoning and Certain Other Consequences of External Causes</td>
        </tr>
        <?php while($row_diagnoses = mysqli_fetch_array($injury)){?>
        <tr>
            <td><?php echo $id=$row_diagnoses['id'];?></td>
            <td colspan="5"><?php echo $row_diagnoses['catname'];?></td>
            <td><?php echo $i=diagnoses(1,$year,$month,$id,0,30);?></td>
            <td><?php echo $j=diagnoses(2,$year,$month,$id,0,30);?></td>
            <td><?php echo $i+$j; ?></td>
            <td><?php echo $k=diagnoses(1,$year,$month,$id,30,365);?></td>
            <td><?php echo $l=diagnoses(2,$year,$month,$id,0,365);?></td>
            <td><?php echo $k+$l; ?></td>
            <td><?php echo $m=diagnoses(1,$year,$month,$id,365,1825);?></td>
            <td><?php echo $n=diagnoses(2,$year,$month,$id,365,1825);?></td>
            <td><?php echo $m+$n; ?></td>
            <td><?php echo $o=diagnoses(1,$year,$month,$id,1825,21900);?></td>
            <td><?php echo $p=diagnoses(2,$year,$month,$id,1825,21900);?></td>
            <td><?php echo $o+$p; ?></td>
            <td><?php echo $q=diagnoses(1,$year,$month,$id,21900,36500);?></td>
            <td><?php echo $r=diagnoses(2,$year,$month,$id,21900,36500);?></td>
            <td><?php echo $q+$r; ?></td>
            <td><?php echo $s=($i+$k+$m+$o+$q);?></td>
            <td><?php echo $t=($j+$l+$n+$p+$r);?></td>
            <td><?php echo $s+$t;?></td>
        </tr>
        <?php }?>
        <tr style="background-color: grey;">
            <td>&nbsp;XX</td>
            <td colspan="23">External Causes of Morbidity and Mortality</td>
        </tr>
        <?php while($row_diagnoses = mysqli_fetch_array($external)){?>
        <tr>
            <td><?php echo $id=$row_diagnoses['id'];?></td>
            <td colspan="5"><?php echo $row_diagnoses['catname'];?></td>
            <td><?php echo $i=diagnoses(1,$year,$month,$id,0,30);?></td>
            <td><?php echo $j=diagnoses(2,$year,$month,$id,0,30);?></td>
            <td><?php echo $i+$j; ?></td>
            <td><?php echo $k=diagnoses(1,$year,$month,$id,30,365);?></td>
            <td><?php echo $l=diagnoses(2,$year,$month,$id,0,365);?></td>
            <td><?php echo $k+$l; ?></td>
            <td><?php echo $m=diagnoses(1,$year,$month,$id,365,1825);?></td>
            <td><?php echo $n=diagnoses(2,$year,$month,$id,365,1825);?></td>
            <td><?php echo $m+$n; ?></td>
            <td><?php echo $o=diagnoses(1,$year,$month,$id,1825,21900);?></td>
            <td><?php echo $p=diagnoses(2,$year,$month,$id,1825,21900);?></td>
            <td><?php echo $o+$p; ?></td>
            <td><?php echo $q=diagnoses(1,$year,$month,$id,21900,36500);?></td>
            <td><?php echo $r=diagnoses(2,$year,$month,$id,21900,36500);?></td>
            <td><?php echo $q+$r; ?></td>
            <td><?php echo $s=($i+$k+$m+$o+$q);?></td>
            <td><?php echo $t=($j+$l+$n+$p+$r);?></td>
            <td><?php echo $s+$t;?></td>
        </tr>
        <?php }?>
        <tr style="background-color: grey;">
            <td>&nbsp;</td>
            <td colspan="23">Matokeo</td>    
        </tr>
        <tr>
            <td>116</td>
            <td colspan="5">Waliopewa rufaa</td>
            <td><?php echo $rm30=$rufaam['below30'];?></td>
            <td><?php echo $rf30=$rufaaf['below30'];?></td>
            <td><?php echo  $r30=$rf30+$rm30;?></td>
            <td><?php echo $rm1=$rufaam['below1'];?></td>
            <td><?php echo $rf1=$rufaaf['below1'];?></td>
            <td><?php echo $r1=$rf1+$rm1;?></td>
            <td><?php echo $rm5=$rufaam['below5'];?></td>
            <td><?php echo $rf5=$rufaaf['below5'];?></td>
            <td><?php echo $r5=$rf5+$rm5;?></td>
            <td><?php echo $rm60=$rufaam['below60'];?></td>
            <td><?php echo $rf60=$rufaaf['below60'];?></td>
            <td><?php echo $r60=$rf60+$rm60;?></td>
            <td><?php echo $rm600=$rufaam['above60'];?></td>
            <td><?php echo $rf600=$rufaaf['above60'];?></td>
            <td><?php echo $r600=$rf600+$rm600;?></td>

            <td><?php echo $rmt=($rm30+$rm1+$rm5+$rm60+$rm600); ?></td>
            <td><?php echo $rft=($rf30+$rf1+$rf5+$rf60+$rf600); ?></td>
            <td><?php echo $rt=($rmt+$rft); ?></td>
        </tr>
        <tr>
            <td>117</td>
            <td colspan="5">Waliofariki OPD</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
        </tr>
        <tr style="background-color: grey;">
            <td>&nbsp;</td>
            <td colspan="23">Ugharamiaji wa Matibabu</td>    
        </tr>
        <tr>
            <td>118</td>
            <td colspan="5">Waliotibiwa kwa Bima ya Afya</td>
            <td><?php echo $em1 = $rowe['Male'] + 0; ?></td>
            <td><?php echo $ef1 = $rowe['Female'] + 0; ?></td>
            <td><?php echo $ef1 + $em1; ?></td>

            <td><?php echo $em2 = $rowe1['Male'] + 0; ?></td>
            <td><?php echo $ef2 = $rowe1['Female'] + 0; ?></td>
            <td><?php echo $ef2 + $em2; ?></td>

            <td><?php echo $em3 = $rowe2['Male'] + 0; ?></td>
            <td><?php echo $ef3 = $rowe2['Female'] + 0; ?></td>
            <td><?php echo $em3 + $ef3; ?></td>

            <td><?php echo $em4 = $rowe4['Male'] + 0; ?></td>
            <td><?php echo $ef4 = $rowe4['Female'] + 0; ?></td>
            <td><?php echo $em4 + $ef4; ?></td>

            <td><?php echo $em5 = $rowe5['Male'] + 0; ?></td>
            <td><?php echo $ef5 = $rowe5['Female'] + 0; ?></td>
            <td><?php echo $em5 + $ef5; ?></td>

            <td><?php echo $em6 = $em1 + $em2 + $em3 + $em4 + $em5 ?></td>
            <td><?php echo $ef6 = $ef1 + $ef2 + $ef3 + $ef4 + $ef5 ?></td>
            <td><?php echo $em6 + $ef6; ?></td>
        </tr>
        <tr>
            <td>119</td>
            <td colspan="5">Waliotibiwa kwa Bima ya CHF</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
        </tr>
        <tr>
            <td>120</td>
            <td colspan="5">Waliotibiwa kwa Bima ya Nyingine</td>
            <td><?php echo $nm1 = $rowef['Male'] + 0; ?></td>
            <td><?php echo $nf1 = $rowef['Female'] + 0; ?></td>
            <td><?php echo $nf1 + $nm1; ?></td>

            <td><?php echo $nm2 = $rowef1['Male'] + 0; ?></td>
            <td><?php echo $nf2 = $rowef1['Female'] + 0; ?></td>
            <td><?php echo $nf2 + $nm2; ?></td>

            <td><?php echo $nm3 = $rowef2['Male'] + 0; ?></td>
            <td><?php echo $nf3 = $rowef2['Female'] + 0; ?></td>
            <td><?php echo $nm3 + $nf3; ?></td>

            <td><?php echo $nm4 = $rowef4['Male'] + 0; ?></td>
            <td><?php echo $nf4 = $rowef4['Female'] + 0; ?></td>
            <td><?php echo $nm4 + $nf4; ?></td>

            <td><?php echo $nm5 = $rowef5['Male'] + 0; ?></td>
            <td><?php echo $nf5 = $rowef5['Female'] + 0; ?></td>
            <td><?php echo $nm5 + $nf5; ?></td>

            <td><?php echo $nm6 = $nm1 + $nm2 + $nm3 + $nm4 + $nm5 ?></td>
            <td><?php echo $nf6 = $nf1 + $nf2 + $nf3 + $nf4 + $nf5 ?></td>
            <td><?php echo $nm6 + $nf6; ?></td>
        </tr>
        <tr>
            <td>121</td>
            <td colspan="5">Waliotibiwa kwa Pesa taslimu (Cash).</td>
            <td><?php echo $fm1 = $rowf['Male'] + 0; ?></td>
            <td><?php echo $ff1 = $rowf['Female'] + 0; ?></td>
            <td><?php echo $ff1 + $fm1; ?></td>

            <td><?php echo $fm2 = $rowf1['Male'] + 0; ?></td>
            <td><?php echo $ff2 = $rowf1['Female'] + 0; ?></td>
            <td><?php echo $ff2 + $fm2; ?></td>

            <td><?php echo $fm3 = $rowf2['Male'] + 0; ?></td>
            <td><?php echo $ff3 = $rowf2['Female'] + 0; ?></td>
            <td><?php echo $fm3 + $ff3; ?></td>

            <td><?php echo $fm4 = $rowf4['Male'] + 0; ?></td>
            <td><?php echo $ff4 = $rowf4['Female'] + 0; ?></td>
            <td><?php echo $fm4 + $ff4; ?></td>

            <td><?php echo $fm5 = $rowf5['Male'] + 0; ?></td>
            <td><?php echo $ff5 = $rowf5['Female'] + 0; ?></td>
            <td><?php echo $fm5 + $ff5; ?></td>

            <td><?php echo $fm6 = $fm1 + $fm2 + $fm3 + $fm4 + $fm5 ?></td>
            <td><?php echo $ff6 = $ff1 + $ff2 + $ff3 + $ff4 + $ff5 ?></td>
            <td><?php echo $fm6 + $ff6; ?></td>
        </tr>
        <tr>
            <td>122</td>
            <td colspan="5">Waliotibiwa kwa Msamaha</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
        </tr>
    </tbody>
</table>
<p>Jina la Mtayarishaji wa Ripoti:.............................................................Cheo:......................................... Wadhifa:.......................................................</p>
<p>Tarehe ya kuandaa:............................................................... Imepitiwa na:......................................................................</p>
<p>Namba ya Simu ya Kituo................................................................. Taarifa imepokelewa wilayani tarehe:.....................................................</p>
<p>&nbsp;</p>