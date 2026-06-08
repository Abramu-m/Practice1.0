<?php 
//$conn = new mysqli('localhost', 'root', '', 'hassanali');
require_once("../connect.php");
require_once('../function.php');

                        $a=$_POST['d_id'];
                        $result = $conn->query("
                        SELECT stype,result, svdescription from pat_lab as pl
   INNER JOIN services as s ON pl.svcode=s.svsvid AND
   pl.id = '$a'
                        ");
$row= mysqli_fetch_array($result);
$type=$row['stype'];
$result=$row['result'];
if($type=='3' or $type=='2'){
        if($result==16){
            $result_sterility_div = $conn->query("SELECT pid, 
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
            FROM procedures where pl_id='{$_POST['d_id']}'
            GROUP BY pl_id ");
            
            $row_sterility_div = mysqli_fetch_array($result_sterility_div);
            ?>
                                <div class="row">
                                    <div class="col-sm-12" style="text-align: center;">
                                        <img src="img/brigita_official.jpg" alt="logo ya hospitali" />
                                            <p><h2><u><strong>Anamnesis for Sterility Patients</strong></u></h2></p>
                                    </div>
                                </div>
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="col-sm-2">
                                                <div class="col-sm-6">
                                                    <span  class="pull-right"><strong>Para:</strong></span>
                                                </div>
                                                <div class="col-sm-6">
                                                    <span class="pull-left">
                                                        <?php if(!empty($row_sterility_div['para'])){ echo $row_sterility_div['para']; } ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-sm-3">
                                                <div class="col-sm-6">
                                                    <span  class="pull-right"><strong>Years of delivery:</strong></span>
                                                </div>
                                                <div class="col-sm-6">
                                                    <span class="pull-left">
                                                        <?php if(!empty($row_sterility_div['delivery_year'])){ echo $row_sterility_div['delivery_year']; } ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-sm-3">
                                                <div class="col-sm-6">
                                                    <span  class="pull-right"><strong>Operations:</strong></span>
                                                </div>
                                                <div class="col-sm-6">
                                                    <?php if(!empty($row_sterility_div['operation_hx'])){ echo $row_sterility_div['operation_hx']; }  ?>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="col-sm-6">
                                                    <span class="pull-right"><strong>Hysterosalpingography:</strong></span>
                                                </div>
                                                <div class="col-sm-6">
                                                    <span class="pull-left">
                                                        <?php if(!empty($row_sterility_div['hsg'])){ echo $row_sterility_div['hsg']; }  ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-12">
                                            <div class="col-sm-2">
                                                <div class="col-sm-6">
                                                    <span class="pull-right"><strong>Abortions:</strong></span>
                                                </div>
                                                <div class="col-sm-6">
                                                    <span class="pull-left">
                                                        <?php if(!empty($row_sterility_div['abortion'])){ echo $row_sterility_div['abortion']; } ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-sm-3">
                                                <div class="col-sm-6">
                                                    <span class="pull-right"><strong>Years of abortion:</strong></span>
                                                </div>
                                                <div class="col-sm-6">
                                                    <span class="pull-left">
                                                        <?php if(!empty($row_sterility_div['abortion_year'])){ echo $row_sterility_div['abortion_year']; } ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-sm-3">
                                                <div class="col-sm-6">
                                                    <span class="pull-right"><strong>Which:</strong></span>
                                                </div>
                                                <div class="col-sm-6">
                                                    <span class="pull-left">
                                                        <?php if(!empty($row_sterility_div['operations'])){ echo $row_sterility_div['operations']; } ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="col-sm-6">
                                                    <span class="pull-right"><strong>HIV/AIDS/ART:</strong></span>
                                                </div>
                                                <div class="col-sm-6">
                                                    <span class="pull-left">
                                                                <?php if(!empty($row_sterility_div['hiv'])){ echo $row_sterility_div['hiv']; }  ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-12">
                                            <div class="col-sm-2">
                                                <div class="col-sm-6">
                                                    <span class="pull-right"><strong>Alive:</strong></span>
                                                </div>
                                                <div class="col-sm-6">
                                                    <span class="pull-left">
                                                        <?php if(!empty($row_sterility_div['alive'])){ echo $row_sterility_div['alive']; } ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-sm-3">
                                                <div class="col-sm-6">
                                                    <span class="pull-right"><strong>D+C or EVA:</strong></span>
                                                </div>
                                                <div class="col-sm-6">
                                                    <span class="pull-left">
                                                        <?php if(!empty($row_sterility_div['d_c_eva'])){ echo $row_sterility_div['d_c_eva']; }  ?>
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
                                                    <span><strong>CD4:</strong> <?php if(!empty($row_sterility_div['cd4'])){ echo $row_sterility_div['cd4']; } ?></span> 
                                                </div>
                                                <div class="col-sm-6">
                                                    <span><strong>HVL:</strong> <?php if(!empty($row_sterility_div['hvl'])){ echo $row_sterility_div['hvl']; } ?></span> 
                                                </div>
                                            </div>
                                        </div>
                                </div>
                                        <hr>
                                <div class="row">
                                        <div class="col-sm-12">
                                                <div class="col-sm-6">
                                                    <div class="col-sm-6">
                                                        <span class="pull-right"><strong>1st/2nd/3rd.. Husband:</strong></span>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <span class="pull-left">
                                                            <?php if(!empty($row_sterility_div['husband_number'])){ echo $row_sterility_div['husband_number']; }  ?>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="col-sm-6">
                                                        <span class="pull-right"><strong>History of Orchitis:</strong></span>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <span class="pull-left">
                                                            <?php if(!empty($row_sterility_div['orchitis'])){ echo $row_sterility_div['orchitis']; }  ?>
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
                                                        <span class="pull-right"><strong>Husband is the father of (some of) her children:</strong></span>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <span class="pull-left">
                                                            <?php if(!empty($row_sterility_div['father'])){ echo $row_sterility_div['father']; }  ?>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="col-sm-6">
                                                        <span class="pull-right"><strong>PITC:</strong></span>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <span class="pull-left">
                                                            <?php if(!empty($row_sterility_div['pitc'])){ echo $row_sterility_div['pitc']; }  ?>
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
                                                        <span class="pull-right"><strong>How many wives does the husband have:</strong></span>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <span class="pull-left">
                                                            <?php if(!empty($row_sterility_div['wives'])){ echo $row_sterility_div['wives']; } ?>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="col-sm-6">
                                                        <span class="pull-right"><strong>Regular drug intake:</strong></span>
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <span class="pull-left">
                                                            <?php if(!empty($row_sterility_div['drug_intake'])){ echo $row_sterility_div['drug_intake']; }  ?>
                                                        </span>
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <span class="pull-left">
                                                            <?php if(!empty($row_sterility_div['drug_name'])){ echo $row_sterility_div['drug_name']; } ?>
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
                                                        <span class="pull-right"><strong>She is the 1st/2nd/3rd.. wife:</strong></span>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <span class="pull-left">
                                                            <?php if(!empty($row_sterility_div['wife_number'])){ echo $row_sterility_div['wife_number']; }  ?>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="col-sm-6">
                                                        <span class="pull-right"><strong>Number of children of husband:</strong></span>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <span class="pull-left">
                                                            <?php if(!empty($row_sterility_div['children_number'])){ echo $row_sterility_div['children_number']; } ?>
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
                                                        <span class="pull-right"><strong>For how many years has she stayed with her partner:</strong></span>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <span class="pull-left">
                                                            <?php if(!empty($row_sterility_div['relationship_duration'])){ echo $row_sterility_div['relationship_duration']; } ?>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="col-sm-6">
                                                        <span class="pull-right"><strong>Age of husband's last born child:</strong></span>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <span class="pull-left">
                                                            <?php if(!empty($row_sterility_div['lastborn_age'])){ echo $row_sterility_div['lastborn_age']; } ?>
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
                                                        <span class="pull-right"><strong>Husband Operations:</strong></span>
                                                    </div>
                                                    <div class="col-sm-2">
                                                        <span class="pull-left">
                                                            <?php if(!empty($row_sterility_div['husband_operation'])){ echo $row_sterility_div['husband_operation']; }  ?>
                                                        </span>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <span class="pull-left">
                                                        <strong>Type of Operations:</strong><?php if(!empty($row_sterility_div['operation_type'])){ echo $row_sterility_div['operation_type']; } ?>
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
                                                <strong>1:</strong>
                                                <?php if(!empty($row_sterility_div['contraceptive_method_1'])){ echo $row_sterility_div['contraceptive_method_1']; } ?>
                                            </p>
                                            <p class="d-inline-flex align-items-center">
                                                <strong>2:</strong>
                                                <?php if(!empty($row_sterility_div['contraceptive_method_2'])){ echo $row_sterility_div['contraceptive_method_2']; } ?>
                                            </p>
                                            <p class="d-inline-flex align-items-center">
                                                <strong>3:</strong>
                                                <?php if(!empty($row_sterility_div['contraceptive_method_3'])){ echo $row_sterility_div['contraceptive_method_3']; } ?>
                                            </p>
                                        </div>
                                        <div class="col-md-3">
                                            <p><i>Years of using the contraceptives</i></p>
                                            <p>
                                                <?php if(!empty($row_sterility_div['contraceptive_method_1_duration'])){ echo $row_sterility_div['contraceptive_method_1_duration']; } ?>
                                            </p>
                                            <p>
                                                <?php if(!empty($row_sterility_div['contraceptive_method_2_duration'])){ echo $row_sterility_div['contraceptive_method_2_duration']; } ?>
                                            </p>
                                            <p>
                                                <?php if(!empty($row_sterility_div['contraceptive_method_3_duration'])){ echo $row_sterility_div['contraceptive_method_3_duration']; } ?>
                                            </p>
                                        </div>
                                        <div class="col-md-2"></div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-3"><strong>Menstrual Cycle:</strong></div>
                                        <div class="col-md-4">
                                            <p class="d-inline-flex align-items-center">
                                            <strong>Length ~:</strong> <?php if(!empty($row_sterility_div['cycle_length'])){ echo $row_sterility_div['cycle_length']; } ?>
                                            </p>
                                            <p>
                                            <strong>Menstrual Bleeding:</strong> <?php if(!empty($row_sterility_div['num_of_days'])){ echo $row_sterility_div['num_of_days']; } ?>
                                            </p>
                                            <p class="d-inline-flex align-items-center">
                                                <label>Amenorrhea at the moment:</label>
                                                        <?php if(!empty($row_sterility_div['current_amenorrhea'])){ echo $row_sterility_div['current_amenorrhea']; } ?>
                                                </select>
                                            </p>
                                        </div>
                                        <div class="col-md-5">
                                            <p class="d-inline-flex align-items-center">
                                                <label>Duration of cycle changing</label>
                                                <?php if(!empty($row_sterility_div['cycle_changing'])){ echo $row_sterility_div['cycle_changing']; }  ?>
                                            </p>
                                            <p class="d-inline-flex align-items-center">
                                                <label>Intermeidate Bleeding</label>
                                                <?php if(!empty($row_sterility_div['intermediate_bleeding'])){ echo $row_sterility_div['intermediate_bleeding']; }  ?>
                                            </p>
                                            <p class="d-inline-flex align-items-center">
                                                <label>Bleeding Intensity</label>
                                                <?php if(!empty($row_sterility_div['bleeding_intensity'])){ echo $row_sterility_div['bleeding_intensity']; }  ?>
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
                                                <label>Milk Discharge</label>
                                                <?php if(!empty($row_sterility_div['milk_discharge'])){ echo $row_sterility_div['milk_discharge']; }  ?>   
                                            </p>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                    
                                        <div class="col-md-4">
                                            <p><strong>PID</strong></p>
                                            <p class="d-inline-flex align-items-center">
                                                <label>Previous PID?</label>
                                                <?php if(!empty($row_sterility_div['previous_std'])){ echo $row_sterility_div['previous_std']; }  ?>
                                            </p>
                                            <p class="d-inline-flex align-items-center">
                                                <label>When:</label>
                                                <?php if(!empty($row_sterility_div['previous_std_year'])){ echo $row_sterility_div['previous_std_year']; } ?>
                                            </p>
                                            <p class="d-inline-flex align-items-center">
                                                <label>Dyspareunie:</label>
                                                <?php if(!empty($row_sterility_div['Dyspareunie'])){ echo $row_sterility_div['Dyspareunie']; }  ?>
                                            </p>
                                            <p class="d-inline-flex align-items-center">
                                                <label>Dysmenstruation:</label>
                                                <?php if(!empty($row_sterility_div['Dysmenstruation'])){ echo $row_sterility_div['Dysmenstruation']; }  ?>
                                            </p>
                                            <p class="d-inline-flex align-items-center">
                                                <label>Genital itching:</label>
                                                <?php if(!empty($row_sterility_div['genital_itching'])){ echo $row_sterility_div['genital_itching']; }  ?>
                                            </p>
                                        </div>
        
                                        <div class="col-md-4">
                                        <p><strong>STD</strong></p>
                                            <p class="d-inline-flex align-items-center">
                                                <label>Wife?</label>
                                                <?php if(!empty($row_sterility_div['wife_std'])){ echo $row_sterility_div['wife_std']; }  ?>
                                            </p>
        
                                            <p class="d-inline-flex align-items-center">
                                                <label>Which disease:</label>
                                                <?php if(!empty($row_sterility_div['wife_std_disease'])){ echo $row_sterility_div['wife_std_disease']; } ?>
                                            </p>
        
                                            <p class="d-inline-flex align-items-center">
                                                <label>When:</label>
                                                <?php if(!empty($row_sterility_div['wife_std_year'])){ echo $row_sterility_div['wife_std_year']; } ?>
                                            </p>
                                        </div>
        
                                        <div class="col-md-4">
                                        <p><strong>STD</strong></p>
                                            <p class="d-inline-flex align-items-center">
                                                <label>Husband?</label>
                                                <?php if(!empty($row_sterility_div['husband_std'])){ echo $row_sterility_div['husband_std']; }  ?>
                                            </p>
        
                                            <p class="d-inline-flex align-items-center">
                                                <label>Which disease:</label>
                                                <?php if(!empty($row_sterility_div['husband_std_disease'])){ echo $row_sterility_div['husband_std_disease']; } ?>
                                            </p>
        
                                            <p class="d-inline-flex align-items-center">
                                                <label>When:</label>
                                                <?php if(!empty($row_sterility_div['husband_std_year'])){ echo $row_sterility_div['husband_std_year']; } ?>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-12">
                                                <div class="col-sm-6">
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="col-sm-6">
                                                        <span class="pull-right"><strong>Spermoigram:</strong></span>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <span class="pull-left">
                                                                    <?php if(!empty($row_sterility_div['spermiogram'])){ echo $row_sterility_div['spermiogram']; }  ?>
                                                        </span>
                                                    </div>
                                                </div>
        
                                        </div>
                                    </div> 
        <?php }elseif($result==11){

            $resultin = $conn->query("SELECT * FROM bedrest_observation as b 
            INNER JOIN user AS u ON b.cby=u.user_id
            WHERE pl_id='$a' 
            ORDER BY b.id ASC ");
    if($resultin->num_rows < 1){ echo " <h3 class='form-signin-heading alert-warning'>No data entered yet</h3>"; } else{?>          
            <table class="table table-striped table-bordered" style="width: 100%; color:black;">
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
                <th>Dehydration</th>
                <th>Weight</th>
                <th>Fluid Input</th>
                <th>Fluid Output</th>
                <th>Sugar</th>
                <th>Ketones</th>
                <th>Comments</th>
            </tr>
        </thead>
        <tbody><?php
        while($rowin= mysqli_fetch_array($resultin)){ ?>
            <tr>
                <td><?php echo $rowin['firstname'];?></td>
                <td><?php echo date('H:i', strtotime($rowin['time'])).'hrs';?></td>
                <td><?php if($rowin['diastolic']!==""){echo $rowin['diastolic'].' mmHg';}else{echo $rowin['diastolic'];}?></td>
                <td><?php if($rowin['systolic']!==""){echo $rowin['systolic'].' mmHg';}else{echo $rowin['systolic'];}?></td>
                <td><?php if($rowin['resp_rate']!==""){echo $rowin['resp_rate'].' breaths/min';}else{echo $rowin['resp_rate'];}?></td>
                <td><?php if($rowin['pulse_rate']!==""){echo $rowin['pulse_rate'].' beats/min';}else{echo $rowin['pulse_rate'];}?></td>
                <td><?php if($rowin['temp']!==""){echo $rowin['temp'].' &#8451;';}else{echo $rowin['temp'];}?></td>
                <td><?php if($rowin['spo2']!==""){echo $rowin['spo2'].'%';}else{echo $rowin['spo2'];}?></td>
                <td><?php echo $rowin['dehydration'];?></td>
                <td><?php if($rowin['weight']!==""){echo $rowin['weight'].' kg';}else{echo $rowin['weight'];}?></td>
                <td><?php if($rowin['fluid_input']!==""){echo $rowin['fluid_input'].' mL';}else{echo $rowin['fluid_input'];}?></td>
                <td><?php if($rowin['fluid_output']!==""){echo $rowin['fluid_output'].' mL';}else{echo $rowin['fluid_output'];}?></td>
                <td><?php if($rowin['blood_sugar']!==""){echo $rowin['blood_sugar'].' mmol/L';}else{echo $rowin['blood_sugar'];}?></td>
                <td><?php echo $rowin['urine_ketones'];?></td>
                <td><?php echo nl2br($rowin['comments']);?></td>
            </tr>
                <?php  }?> 
                
                </tbody>  
                </table> 
       <?php  }

        }else{
    $resultf = $conn->query("
                        SELECT * FROM procedures WHERE pl_id= '$a'
                        ");
     while ($rowtf = mysqli_fetch_array($resultf)) { $urlpicha =$rowtf['pimage']; $picha = strstr(strrev($rowtf['pimage']),".",true);?>
                        <table class="table table-hover">
                            <thead>
                                <tr><th colspan="2"><?php  echo ucfirst($row['svdescription']);?></th></tr>
                                
                        
                            </thead>
                            <tbody><?php if($urlpicha!==""){ if($picha!==""){?>
                                <tr><td><img id="picha" height="100%" width="100%" src="<?php  echo $rowtf['pimage'];?>"/></td></tr>
                                <?php }}?>
                                <tr><td><?php  echo str_replace(array('*', '#'), array('<u><em><strong>', '</em></strong></u>'),  nl2br($rowtf['preports']));?></td></tr>
                                
                            </tbody>
                        </table>
                        <?php }

}}
else if($type=='1'){


   if($result==9) { /// for stool nanalysis 

    $resultf = $conn->query("SELECT pid, 
        max(CASE WHEN perimeter = 'Pus cells' THEN preports END) 'Pus cells',
        max(CASE WHEN perimeter = 'RBC' THEN preports END) 'RBC',
        max(CASE WHEN perimeter = 'Hookworms' THEN preports END) 'Hookworms',
        max(CASE WHEN perimeter = 'Ascaris' THEN preports END) 'Ascaris',
        max(CASE WHEN perimeter = 'Amoeba' THEN preports END) 'Amoeba',
        max(CASE WHEN perimeter = 'Enterobius' THEN preports END) 'Enterobius',
        max(CASE WHEN perimeter = 'hominis' THEN preports END) 'hominis',
        max(CASE WHEN perimeter = 'Giardia' THEN preports END) 'Giardia',
        max(CASE WHEN perimeter = 'mansoni' THEN preports END) 'mansoni',
        max(CASE WHEN perimeter = 'Taenia' THEN preports END) 'Taenia',
        max(CASE WHEN perimeter = 'Trichuris' THEN preports END) 'Trichuris'
        FROM procedures where pl_id='$a'
        GROUP BY pl_id ");
             while ($rowtf = mysqli_fetch_array($resultf)) { ?>
             <table class="table">
                        <tr>
                            <td>Pus cells</td>
                            <td><?php echo $rowtf['Pus cells'];?></td>
                        </tr>
                        <tr>
                            <td>RBC</td>
                            <td><?php echo $rowtf['RBC'];?></td>
                        </tr>
                        <tr>
                            <td>Hookworms</td>
                            <td><?php echo $rowtf['Hookworms'];?></td>
                        </tr>
                        <tr>
                            <td>Ascaris lumbricoides</td>
                            <td><?php echo $rowtf['Ascaris'];?></td>
                        </tr>
                        <tr>
                            <td>Amoeba cysts</td>
                            <td><?php echo $rowtf['Amoeba'];?></td>
                        </tr>
                        <tr>
                            <td>Enterobius vermicularis</td>
                            <td><?php echo $rowtf['Enterobius'];?></td>
                        </tr>
                            <td>Trichomonas hominis</td>
                            <td><?php echo $rowtf['hominis'];?></td>
                        </tr>
                        <tr>
                            <td>Giardia lamblia</td>
                            <td><?php echo $rowtf['Giardia'];?></td>
                        </tr>
                        <tr>
                            <td>Schistosomiasis mansoni</td>
                            <td><?php echo $rowtf['mansoni'];?></td>
                        </tr>  
                        <tr>
                            <td>Taenia solium</td>
                            <td><?php echo $rowtf['Taenia'];?></td>
                        </tr>
                        <tr>
                            <td>Trichuris trichiura</td>
                            <td><?php echo $rowtf['Trichuris'];?></td>
                        </tr>
                        </table>
                        <?php   }}

    else  if($result==7){//urinalyses
      $resultf = $conn->query("
                   
                   SELECT pid, 
max(CASE WHEN perimeter = 'Leucos/HPF' THEN preports END) 'Leucos/HPF',
max(CASE WHEN perimeter = 'Erys/HPF' THEN preports END) 'Erys/HPF',
max(CASE WHEN perimeter = 'epith' THEN preports END) 'epith',
max(CASE WHEN perimeter = 'Calcium Oxalate' THEN preports END) 'Calcium Oxalate',
max(CASE WHEN perimeter = 'Granular Cast' THEN preports END) 'Granular Cast',
max(CASE WHEN perimeter = 'Sch haem' THEN preports END) 'Sch haem',
max(CASE WHEN perimeter = 'Blood' THEN preports END) 'Blood',
max(CASE WHEN perimeter = 'Sperms' THEN preports END) 'Sperms',
max(CASE WHEN perimeter = 'T vaginalis' THEN preports END) 'T vaginalis',
max(CASE WHEN perimeter = 'Urobilinogen' THEN preports END) 'Urobilinogen',
max(CASE WHEN perimeter = 'Glucose' THEN preports END) 'Glucose',
max(CASE WHEN perimeter = 'Keton' THEN preports END) 'Keton',
max(CASE WHEN perimeter = 'Bilirubin' THEN preports END) 'Bilirubin',
max(CASE WHEN perimeter = 'Protein' THEN preports END) 'Protein',
max(CASE WHEN perimeter = 'Nitrite' THEN preports END) 'Nitrite',
max(CASE WHEN perimeter = 'Leucocytes' THEN preports END) 'Leucocytes',
max(CASE WHEN perimeter = 'PH' THEN preports END) 'PH',
max(CASE WHEN perimeter = 'Specific Gravidity' THEN preports END) 'Specific Gravidity'

FROM procedures where pl_id='$a'
GROUP BY pl_id
                        ");
    ?>
                        <table style="width:100%">
<?php 
     while ($rowtf = mysqli_fetch_array($resultf)) { ?>
                            <thead>
                                <tr>
                                    <td colspan="2"  style ="align:center; font-size: large;"><b>Wet Preparation</b></td>
                                      
                                    
                                    <td colspan="6" style ="align:center; font-size: large;"><b>Multistix</b></td>
                                      
                                </tr>
                                <tr> <td colspan="2"><br></td></tr>
                                <tr></tr>
                                <tr></tr>
                                <tr></tr>
                                <tr></tr>
                                <tr></tr>
                               </thead>
                                    <tbody>
                                <tr>
                                    <td colspan="2"><b>Leucos/HPF: </b><?php echo " ".$rowtf['Leucos/HPF'];?> </td>
                                    <td colspan="2"><b>Urobilinogen: </b><?php echo " ".$rowtf['Urobilinogen']." ";?> </td>
                                    <td colspan="2"><b>Nitrite: </b><?php echo $rowtf['Nitrite']." ";?> </td>
                                    <td colspan="2"><b>Epithelia cells: </b><?php echo $rowtf['epith']." ";?></b></td>
                                    
                                    
                                </tr>
                                 <tr>
                                     <td colspan="2"><b>Sch. haematobium: </b><?php echo " ".$rowtf['Sch haem'];?> </td>
                                     <td colspan="2"><b>Glucose: </b><?php echo " ".$rowtf['Glucose']." ";?> </td>
                                     <td colspan="2"><b>Leucocytes: </b><?php echo $rowtf['Leucocytes']." ";?> </td>
                                       <td colspan="2"></td>
                                     
                                </tr>
                                 <tr>
                                     <td colspan="2"><b>Erys/HPF: </b><?php echo  " ".$rowtf['Erys/HPF'];?> </td>
                                     <td colspan="2"><b>Ketone: </b><?php echo " ".$rowtf['Keton']." ";?> </td>
                                     <td colspan="2"><b>Blood: </b><?php echo $rowtf['Blood']." ";?> </td>
                                          <td colspan="2"></td>  
                                </tr>
                                        
                                            
                                            
                                 <tr>
                                     <td colspan="2"><b>T. vaginalis: </b><?php echo " ".$rowtf['T vaginalis'];?> </td>
                                     <td colspan="2"><b>Bilirubin: </b><?php echo " ".$rowtf['Bilirubin']." ";?> </td>
                                     <td colspan="2"><b>Ph: </b><?php echo " ".$rowtf['PH']." ";?> </td>
                                       <td colspan="2"></td>
                                </tr>
                                <tr>
                                    <td colspan="2"><b>Calcium Oxalate: </b><?php echo  " ".$rowtf['Calcium Oxalate'];?> </td>
                                            <td colspan="2"><b>Protein: </b><?php echo $rowtf['Protein']." ";?> </td>
                                    <td colspan="2"><b>Specific Gravity: </b><?php echo " ".$rowtf['Specific Gravidity']." ";?> </td>
                                      <td colspan="2"></td>
                                            
                                </tr> 
                                 <tr>
                                     <td colspan="2"><b>Sperms: </b><?php echo " ".$rowtf['Sperms'];?> </td>
                                      <td colspan="6"></td>
                                </tr>
                                        <tr>
                                <td colspan="2"><b>Granular Cast: </b><?php echo  " ".$rowtf['Granular Cast'];?> </td>
                                  <td colspan="6"></td>
                                </tr>
                               

                               </tbody>                  
                        
                        <?php } 
                        echo "</table>"; 
     }
                        else if($result==12){
           
$resultf = $conn->query("
                   
SELECT pid, 
max(CASE WHEN perimeter = 'wbc' THEN preports END) 'wbc',
max(CASE WHEN perimeter = 'lymnum' THEN preports END) 'lymnum',
max(CASE WHEN perimeter = 'grannum' THEN preports END) 'grannum',
max(CASE WHEN perimeter = 'midnum' THEN preports END) 'midnum',
max(CASE WHEN perimeter = 'lymperc' THEN preports END) 'lymperc',
max(CASE WHEN perimeter = 'granperc' THEN preports END) 'granperc',
max(CASE WHEN perimeter = 'midperc' THEN preports END) 'midperc',
max(CASE WHEN perimeter = 'rbc' THEN preports END) 'rbc',
max(CASE WHEN perimeter = 'hb' THEN preports END) 'hb',
max(CASE WHEN perimeter = 'hct' THEN preports END) 'hct',
max(CASE WHEN perimeter = 'mcv' THEN preports END) 'mcv',
max(CASE WHEN perimeter = 'mch' THEN preports END) 'mch',
max(CASE WHEN perimeter = 'mchc' THEN preports END) 'mchc',
max(CASE WHEN perimeter = 'rdwcv' THEN preports END) 'rdwcv',
max(CASE WHEN perimeter = 'rdwsd' THEN preports END) 'rdwsd',
max(CASE WHEN perimeter = 'plt' THEN preports END) 'plt',
max(CASE WHEN perimeter = 'mpv' THEN preports END) 'mpv',
max(CASE WHEN perimeter = 'pdw' THEN preports END) 'pdw',
max(CASE WHEN perimeter = 'pct' THEN preports END) 'pct',
max(CASE WHEN perimeter = 'plcr' THEN preports END) 'plcr',
max(CASE WHEN perimeter = 'plcc' THEN preports END) 'plcc',
max(CASE WHEN perimeter = 'flags' THEN preports END) 'flags'

FROM procedures where pl_id='$a'
GROUP BY pl_id
     ");
     while ($rowtf = mysqli_fetch_array($resultf)) { 

?>
<table class="table table-striped table-bordered table-sm" id="fbp" style="color:black;">
<thead>
<tr>
<th><strong>Parameter</strong></th>
<th><strong>Result</strong></th>
<th><strong>Unit</strong></th>
<th><strong>Ref. Range</strong></th>
<th><strong>Flag</strong></th>
</tr>
</thead>
<tbody>
<div class="col-xs-6">
<tr>
<td><strong>WBC</strong></td>
<td><strong><?php echo $rowtf['wbc'];?></strong></td>
<td>10*3/uL</td>
<td>3.50 - 11.50</td>
<td><?php echo comparer(3.50, 11.50, $rowtf['wbc'])?></td>
</tr>
<tr>
<td>Lym%</td>
<td><?php echo $rowtf['lymperc'];?></td>
<td>%</td>
<td>20.0 - 50.0</td>
<td><?php echo comparer(20.0, 50.0, $rowtf['lymperc'])?></td>
</tr>
<tr>
<td>Gran%</td>
<td><?php echo $rowtf['granperc'];?></td>
<td>%</td>
<td>50.0 - 70.0</td>
<td><?php echo comparer(50.0, 70.0, $rowtf['granperc'])?></td>
</tr>
<tr>
<td>Mid%</td>
<td><?php echo $rowtf['midperc'];?></td>
<td>%</td>
<td>3.0 - 9.0</td>
<td><?php echo comparer(3.0, 9.0, $rowtf['midperc'])?></td>
</tr>
<tr>
<td>Lym#</td>
<td><?php echo $rowtf['lymnum'];?></td>
<td>10*3/uL</td>
<td>1.10 - 3.20</td>
<td><?php echo comparer(1.10, 3.20, $rowtf['lymnum'])?></td>
</tr>
<tr>
<td>Gran#</td>
<td><?php echo $rowtf['grannum'];?></td>
<td>10*3/uL</td>
<td>2.00 - 7.00</td>
<td><?php echo comparer(2.00, 7.00, $rowtf['grannum'])?></td>
</tr>
<tr>
<td>Mid#</td>
<td><?php echo $rowtf['midnum'];?></td>
<td>10*3/uL</td>
<td>0.10 - 0.90</td>
<td><?php echo comparer(0.10, 0.90, $rowtf['midnum'])?></td>
</tr>
<tr>
<td><strong>RBC</strong></td>
<td><strong><?php echo $rowtf['rbc'];?></strong></td>
<td>10*6/uL</td>
<td>4.30 - 5.80</td>
<td><?php echo comparer(4.30, 5.80, $rowtf['rbc'])?></td>
</tr>
<tr>
<td><strong>HGB</strong></td>
<td><strong><?php echo $rowtf['hb'];?></strong></td>
<td>g/dL</td>
<td>13.0 - 17.0</td>
<td><?php echo comparer(13.0, 17.0, $rowtf['hb'])?></td>
</tr>
<tr>
<td>HCT</td>
<td><?php echo $rowtf['hct'];?></td>
<td>%</td>
<td>40.0 - 50.0</td>
<td><?php echo comparer(40.0, 50.0, $rowtf['hct'])?></td>
</tr>
<tr>
<td>MCV</td>
<td><?php echo $rowtf['mcv'];?></td>
<td>fL</td>
<td>82.0 - 100.0</td>
<td><?php echo comparer(82.0, 100.0, $rowtf['mcv'])?></td>
</tr>
<tr>
<td>MCH</td>
<td><?php echo $rowtf['mch'];?></td>
<td>pg</td>
<td>27.0 - 34.0</td>
<td><?php echo comparer(27.0, 34.0, $rowtf['mch'])?></td>
</tr>
<tr>
<td>MCHC</td>
<td><?php echo $rowtf['mchc'];?></td>
<td>g/dL</td>
<td>31.6 - 35.4</td>
<td><?php echo comparer(31.6, 35.4, $rowtf['mchc'])?></td>
</tr>
<tr>
<td>RDW-CV</td>
<td><?php echo $rowtf['rdwcv'];?></td>
<td>%</td>
<td>11.5 - 14.5</td>
<td><?php echo comparer(11.5, 14.5, $rowtf['rdwcv'])?></td>
</tr>
<tr>
<td>RDW-SD</td>
<td><?php echo $rowtf['rdwsd'];?></td>
<td>fL</td>
<td>35.0 - 56.0</td>
<td><?php echo comparer(35.0, 56.0, $rowtf['rdwsd'])?></td>
</tr>
<tr>
<td><strong>PLT</strong></td>
<td><strong><?php echo $rowtf['plt'];?></strong></td>
<td>10*3/uL</td>
<td>150 - 400</td>
<td><?php echo comparer(150, 400, $rowtf['plt'])?></td>
</tr>
<tr>
<td>MPV</td>
<td><?php echo $rowtf['mpv'];?></td>
<td>fL</td>
<td>7.0 - 11.0</td>
<td><?php echo comparer(7.0, 11.0, $rowtf['mpv'])?></td>
</tr>
<tr>
<td>PDW</td>
<td><?php echo $rowtf['pdw'];?></td>
<td>fL</td>
<td>9.0 - 17.0</td>
<td><?php echo comparer(9.0, 17.0, $rowtf['pdw'])?></td>
</tr>
<tr>
<td>PCT</td>
<td><?php echo $rowtf['pct'];?></td>
<td>%</td>
<td>0.106 - 0.282</td>
<td><?php echo comparer(0.106, 0.282, $rowtf['pct'])?></td>
</tr>
<tr>
<td>P-LCR</td>
<td><?php echo $rowtf['plcr'];?></td>
<td>%</td>
<td>11.0 - 45.0</td>
<td><?php echo comparer(11.0, 45.0, $rowtf['plcr'])?></td>
</tr>
<tr>
<td>P-LCC</td>
<td><?php echo $rowtf['plcc'];?></td>
<td>10*9/L</td>
<td>30 - 90</td>
<td><?php echo comparer(30, 90, $rowtf['plcc'])?></td>
</tr>
<tr>
<td>Comments</td>
<td colspan = "4"><?php echo  $rowtf['flags'];?></td>
</tr>
</tbody>
</table>
        
  <?php  }}else if($result==13){

    $resultf = $conn->query(" SELECT pid, 
 max(CASE WHEN perimeter = 'sp_quality' THEN preports END) 'sp_quality',
 max(CASE WHEN perimeter = 'reason' THEN preports END) 'reason',
 max(CASE WHEN perimeter = 'color' THEN preports END) 'color',
 max(CASE WHEN perimeter = 'volume' THEN preports END) 'volume',
 max(CASE WHEN perimeter = 'viscocity' THEN preports END) 'viscocity',
 max(CASE WHEN perimeter = 'ph' THEN preports END) 'ph',
 max(CASE WHEN perimeter = 'spermcount' THEN preports END) 'spermcount',
 max(CASE WHEN perimeter = 'morphology' THEN preports END) 'morphology',
 max(CASE WHEN perimeter = 'motility' THEN preports END) 'motility',
 max(CASE WHEN perimeter = 'progressive' THEN preports END) 'progressive',
 max(CASE WHEN perimeter = 'pus' THEN preports END) 'pus',
 max(CASE WHEN perimeter = 'spermiogram' THEN preports END) 'spermiogram'
 FROM procedures where pl_id='$a'
 GROUP BY pl_id ");
                       while ($rowtf = mysqli_fetch_array($resultf)) { 
                         ?>
                                <div class="card-box table-responsive">

 <table class="table">
 <thead>
     <tr>
         <th colspan="4">Spermiogram</th>
     </tr>
 </thead>
 <tbody>
     <tr style="border: 1px solid; box-shadow: 0 2px;">
         <td>Specimen Quality</td>
         <td><?php echo $rowtf['sp_quality'];?></td>
         <td>Reason</td>
         <td> <?php echo $rowtf['reason'];?></td>
     </tr>
     <tr style="border: 1px solid;">
         <td>Color</td>
         <td> <?php echo $rowtf['color'];?></td>
         <td>Volume</td>
         <td><?php echo $rowtf['volume'];?> mL (normal: 1.5 - 4 mL)</td>
     </tr>
    <tr style="border: 1px solid; box-shadow: 0 1px;">
         <td>Viscocity after 20 minutes</td>
         <td><?php echo $rowtf['viscocity'];?></td>
         <td>pH</td>
         <td><?php echo $rowtf['ph'];?> (normal: 7.2 - 7.8)</td>
     </tr>
     <tr style="border: 1px solid;">
         <td>Sperm Count</td>
         <td><?php echo $rowtf['spermcount'];?> million spermatozoa/mL seen (normal: 20-60 million/mL)</td>
         <td rowspan="5" colspan="2" style="border: 1px solid; box-shadow: 0 1px;">
         <h4><u>Conclusion</u></h4>
         <?php if(strstr($rowtf['spermiogram'], 'Normo')){ ?>
            <span style="color:green;"> <?php echo $rowtf['spermiogram']; ?> </span>
            <?php } else{ ?>
            <span style="color:red;"> <?php echo $rowtf['spermiogram']; ?> </span>
        <?php }
         ?></td>
     </tr>
     <tr style="border: 1px solid;">
         <td>Morphology</td>
         <td><?php echo $rowtf['morphology'];?> % (normal: >80%)</td>
     </tr>
     <tr style="border: 1px solid;">
         <td>Motility</td>
         <td><?php echo $rowtf['motility'];?> % (normal: >70%)</td>
     </tr>
     <tr style="border: 1px solid;">
         <td>Progressive</td>
         <td><?php echo $rowtf['progressive'];?> % (normal: >60%)</td>
     </tr>
     <tr style="border: 1px solid; box-shadow: 0 1px;">
         <td>Pus Cells</td>
         <td><?php echo $rowtf['pus'];?></td>
     </tr>
 </tbody>
 </table> 
 
 </div> 

  <?php } }
    else if ($result==14) { 
        $resultf = $conn->query(" SELECT pid, 
        max(CASE WHEN perimeter = 'wbctot' THEN preports END) 'wbctot',
        max(CASE WHEN perimeter = 'neutrophil' THEN preports END) 'neutrophil',
        max(CASE WHEN perimeter = 'basophil' THEN preports END) 'basophil',
        max(CASE WHEN perimeter = 'eosinophil' THEN preports END) 'eosinophil',
        max(CASE WHEN perimeter = 'monocyte' THEN preports END) 'monocyte',
        max(CASE WHEN perimeter = 'lymphocyte' THEN preports END) 'lymphocyte'
        FROM procedures where pl_id='$a'
        GROUP BY pl_id ");
                              while ($rowtf = mysqli_fetch_array($resultf)) { 
                                
        
        ?>
       <div class="card-box table-responsive">

<table id="fbp" class="table table-striped table-bordered" style="height:20px; color:black;">
<thead>
<tr>
    <th>Parameter</th>
    <th>Result</th>
    <th>Unit</th>
    <th>Ref. Range</th>
    <th>Flag</th>
</tr>
</thead>
<tbody>
<div class="col-xs-6">
<tr>
    <td><strong>WBC Total</strong></td>
    <td><?php echo $rowtf['wbctot'];?></td>
    <td>/mm3</td>
    <td>3500 - 11500</td>
    <td><?php echo comparer(3500, 11500, $rowtf['wbctot'])?></td>
</tr>
<tr>
    <td>Neutrophil</td>
    <td><?php echo $rowtf['neutrophil'];?></td>
    <td>%</td>
    <td>40.0 - 70.0</td>
    <td><?php echo comparer(40.0, 70.0, $rowtf['neutrophil'])?></td>
</tr>
<tr>
    <td>Basophil</td>
    <td><?php echo $rowtf['basophil'];?></td>
    <td>%</td>
    <td>1</td>
    <td><?php echo comparer(0, 1.0, $rowtf['basophil'])?></td>
</tr>
<tr>
    <td>Eosinophil</td>
    <td><?php echo $rowtf['eosinophil'];?></td>
    <td>%</td>
    <td>1.0 - 6.0</td>
    <td><?php echo comparer(1.0, 6.0, $rowtf['eosinophil'])?></td>
</tr>
<tr>
    <td>Monocytes</td>
    <td><?php echo $rowtf['monocyte'];?></td>
    <td>%</td>
    <td>1.0 - 10.0</td>
    <td><?php echo comparer(1.0, 10.0, $rowtf['monocyte'])?></td>
</tr>
<tr>
    <td>Lymphocytes</td>
    <td><?php echo $rowtf['lymphocyte'];?></td>
    <td>%</td>
    <td>25.0 - 45.0</td>
    <td><?php echo comparer(25.0, 45.0, $rowtf['lymphocyte'])?></td>
</tr>
    </tbody>
    </table>
    </div>
    <?php    }}
  
  else{
        
          $resultf = $conn->query("
                        SELECT * FROM `procedures` WHERE pl_id= '$a'
                        ");
    ?>
                        <table class="table table-hover" >
<?php 
     while ($rowtf = mysqli_fetch_array($resultf)) { ?>
                            <thead>
                                <tr><th><?php  echo ucfirst($rowtf['perimeter']);?></th>
                                    <td><?php  echo ucfirst($rowtf['preports']);?></td></tr>
                               </thead>
                          
                        
                        <?php }
echo "</table>";
        
    }
   
    
}else{ echo "No results found";}

  ?>                        
                        