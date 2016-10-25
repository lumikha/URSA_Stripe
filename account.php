<?php
    require 'header.php';

    if(isset($_POST['upd_acc'])) {
        $business_name = stripslashes($_POST['acc_b_name']);
        $prod = $_POST['acc_prod'];
        $salutation = $_POST['acc_salut'];
        if(isset($_POST['acc_title'])) {
            $title = $_POST['acc_title'];
        }else{
            $title = null;
        }
        $fname = $_POST['acc_fname'];
        $lname = $_POST['acc_lname'];

        $test = true;
        $customer = new ChargifyCustomer(NULL, $test);
        $upd_subscription = new ChargifySubscription(NULL, $test);

        $customer->id = $chargifyID;
        $customer->organization = $business_name;
        $customer->first_name = $fname;
        $customer->last_name = $lname;

        if($prod == 'prod_001') {
            $prodID = 3881312;
            $prodName = "Basic Plan";
        } else if($prod == 'plan_002') {
            $prodID = 3881313;
            $prodName = "Start-up Plan";
        } else if($prod == 'plan_005') {
            $prodID = 3881318;
            $prodName = "Upgrade to Start-up Plan";
        } else if($prod == 'plan_003') {
            $prodID = 3881314;
            $prodName = "Business Plan";
        } else if($prod == 'plan_006') {
            $prodID = 3881319;
            $prodName = "Upgrade to Business Plan";
        } else if($prod == 'plan_004') {
            $prodID = 3881316;
            $prodName = "Enterprise Plan";
        } else {
            $prodID = 3881320;
            $prodName = "Upgrade to Enterprise Plan";
        }  


        $upd_subscription->id = @$result_customer_id[0]->id; //chargify subscriptionID
        $sub_prod = new stdClass();
        $sub_prod->handle = @$prod;
        $sub_prod->id = @$prodID;
        $upd_subscription->product = $sub_prod;

        try {
            $result_upd_cus = $customer->update();
            $result_upd_sub = $upd_subscription->updateProduct();

            try {
                $doc = $client_customers->getDoc($_SESSION['user_now_db_customer_id']);
                ?>
                    <script type="text/javascript">
                        window.location = window.location
                    </script>
                <?php
            } catch (Exception $e) {
                echo "ERROR: ".$e->getMessage()." (".$e->getCode().")<br>\n";
            }

            $doc->business_name = @$business_name;
            $doc->customer_salutation = @$salutation;
            $doc->customer_title = @$title;
            $doc->customer_first_name = @$fname;
            $doc->customer_last_name = @$lname;
            $doc->product_id = @$prodID;
            $doc->product_handle = @$prod;
            $doc->product_name = @$prodName;

            try {
                $client_customers->storeDoc($doc);
                $product_handle = $prod;
                $product_name = $prodName;
            } catch (Exception $e) {
                echo "ERROR: ".$e->getMessage()." (".$e->getCode().")<br>\n";
            }

        } catch (ChargifyValidationException $cve) {
            echo $cve->getMessage();
        }
    }
?>

<div class="col-md-10 col-sm-12" style="margin-top:-90px;">
    <div class="row text-center">
        <div class="cust_id col-lg-2 col-md-2 col-sm-3 col-xs-12 col-md-offset-1 col-sm-offset-1" style="padding: 1em;margin-right:1em;margin-bottom:1em;border:solid black 2px;"><strong><?php if($chargifyID){ echo $chargifyID; }else{ echo "Chargify ID"; }?></strong></div>
        <div class="bill_sum col-lg-2 col-md-2 col-sm-4 col-xs-12" style="padding: 1em;margin-right:1em;margin-bottom:1em;border:solid black 2px;"><strong><?php if($billing_sum){ echo $billing_sum; }else{ echo "Billing Sum"; }?></strong></div>
        <div class="last_activity col-lg-2 col-md-2 col-sm-3 col-xs-12" style="padding: 1em;margin-right:1em;margin-bottom:1em;border:solid black 2px;"><strong><?php if($char_upd_at){ echo $char_upd_at; }else{ echo "Last Activity"; }?></strong></div>
        <div class="col-lg-2 col-md-1 col-sm-5 col-xs-12 col-sm-offset-1 col-md-offset-0" style="padding: 1em;margin-right:1em;margin-bottom:1em;border:solid #A60800 2px;color:#A60800"><strong>Ticket</strong></div>
        <div class="col-lg-2 col-md-2 col-sm-5 col-xs-12" style="padding: 1em;margin-right:1em;margin-bottom:1em;border:solid #340570 2px;color:#340570"><strong>Talkdesk</strong></div>
        <div class="row">
        </div>
    </div>
    <div class="row">
        <div class="col-lg-11 col-md-11 col-sm-12 col-xs-12 col-md-offset-1" style="padding: 1em;">

    <style>
    .error {
        border:1px solid #ff4d4d;
        box-shadow: 0 0 5px #ff4d4d;
        margin-top: 0px;
    }
    </style>

    <div class="tab-content">
    <div class="tab-pane fade" id="account">
    <form id="acc_account_form" action="" method="POST">
        <div class="row">
            <div class="col-md-6">
                <input type="text" name="acc_b_name" class="form-control" placeholder="Business Name" value="<?php echo $business_name; ?>">
            </div>
            <div class="col-md-6">
                <select class="form-control" name="acc_prod" placeholder="Product">
                    <optgroup label="Current">
                    <?php 
                        echo "<option value='".$product_handle."'>".$product_name."</option>"; 
                    ?>
                    </optgroup>
                    <optgroup label="Available Plans">
                        <option value="prod_001">Basic Plan</option>
                        <option value="plan_002">Start-up Plan</option>
                        <option value="plan_005">Upgrade to Start-up Plan</option>
                        <option value="plan_003">Business Plan</option>
                        <option value="plan_006">Upgrade to Business Plan</option>
                        <option value="plan_004">Enterprise Plan</option>
                        <option value="plan_007">Upgrade Enterprise Plan</option>
                    </optgroup>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col-md-2">
                <select name="acc_salut" class="form-control">
                <?php
                    $arr_sltn = array('Mr','Mrs','Ms','Miss','Dr','Herr','Monsieur','Hr','Frau','A V M','Admiraal','Admiral','Air Cdre','Air Commodore','Air Marshal','Air Vice Marshal','Alderman','Alhaji','Ambassador','Baron','Barones','Brig','Brig Gen','Brig General','Brigadier','Brigadier General','Brother','Canon','Capt','Captain','Cardinal','Cdr','Chief','Cik','Cmdr','Col','Col Dr','Colonel','Commandant','Commander','Commissioner','Commodore','Comte','Comtessa','Congressman','Conseiller','Consul','Conte','Contessa','Corporal','Councillor','Count','Countess','Crown Prince','Crown Princess','Dame','Datin','Dato','Datuk','Datuk Seri','Deacon','Deaconess','Dean','Dhr','Dipl Ing','Doctor','Dott','Dott sa','Dr','Dr Ing','Dra','Drs','Embajador','Embajadora','En','Encik','Eng','Eur Ing','Exma Sra','Exmo Sr','F O','Father','First Lieutient','First Officer','Flt Lieut','Flying Officer','Fr','Frau','Fraulein','Fru','Gen','Generaal','General','Governor','Graaf','Gravin','Group Captain','Grp Capt','H E Dr','H H','H M','H R H','Hajah','Haji','Hajim','Her Highness','Her Majesty','Herr','High Chief','His Highness','His Holiness','His Majesty','Hon','Hr','Hra','Ing','Ir','Jonkheer','Judge','Justice','Khun Ying','Kolonel','Lady','Lcda','Lic','Lieut','Lieut Cdr','Lieut Col','Lieut Gen','Lord','M','M L','M R','Madame','Mademoiselle','Maj Gen','Major','Master','Mevrouw','Miss','Mlle','Mme','Monsieur','Monsignor','Mr','Mrs','Ms','Mstr','Nti','Pastor','President','Prince','Princess','Princesse','Prinses','Prof','Prof Dr','Prof Sir','Professor','Puan','Puan Sri','Rabbi','Rear Admiral','Rev','Rev Canon','Rev Dr','Rev Mother','Reverend','Rva','Senator','Sergeant','Sheikh','Sheikha','Sig','Sig na','Sig ra','Sir','Sister','Sqn Ldr','Sr','Sr D','Sra','Srta','Sultan','Tan Sri','Tan Sri Dato','Tengku','Teuku','Than Puying','The Hon Dr','The Hon Justice','The Hon Miss','The Hon Mr','The Hon Mrs','The Hon Ms','The Hon Sir','The Very Rev','Toh Puan','Tun','Vice Admiral','Viscount','Viscountess','Wg Cdr');
?> 
                    <optgroup label="Current">
                    <?php if(!empty($salutation)) {
                        echo "<option value='".$salutation."'>".$salutation."</option>";
                    } else {
                        echo "<option value='' disabled selected>None</option>";
                    }
                    ?>  
                    </optgroup> 
                    <optgroup label="Salutations">
                    <?php
                        $count_sltn = 0;
                        while(!empty($arr_sltn[$count_sltn])) {
                            echo "<option value='".$arr_sltn[$count_sltn]."'>".$arr_sltn[$count_sltn]."</option>";
                            $count_sltn++;
                        } ?>
                    </optgroup>
                </select>
            </div>
            <div class="col-md-5">
                <input type="text" name="acc_fname" class="form-control" placeholder="First Name" value="<?php echo $fname; ?>">
            </div>
            <div class="col-md-5">
                <input type="text" name="acc_lname" class="form-control" placeholder="Last Name" value="<?php echo $lname; ?>">
            </div>
        </div>
        <div class="row">
            <div class="col-md-5">
                <select name="acc_title" class="form-control">
                <?php
                    $arr_ttl = array('Accountant','Accountant Systems','Acquisition Management Intern','Actuarial Analyst','Actuary','Administrative Generalist/Specialist','Affordable Housing Specialist','Analyst','Appraiser','Archaeologist','Area Systems Coordinator','Asylum or Immigration Officer','Attorney/Law Clerk','Audience Analyst','Audit Resolution Follow Up','Auditor','Behavioral Scientist','Biologist, Fishery','Biologist, Marine','Biologist, Wildlife','Budget Analyst','Budget Specialist','Business Administration Officer','Chemical Engineer','Chemist','Citizen Services Specialist','Civil Engineer','Civil Penalties Specialist','Civil/Mechanical/Structural','Engineer','Communications Specialist','Community and Intergovernmental','Program Specialist','Community Planner','Community Planning\Development','Specialist','Community Services Program','Specialist','Compliance Specialist','Computer Engineer','Computer Programmer/Analyst','Computer Scientist','Computer Specialist','Consumer Safety Officer','Contract Specialist','Contract Specialist/Grants','Management Specialist','Corporate Management Analyst','Cost Account','Criminal Enforcement Analyst','Criminal Investigator','Customer Account Manager','Customer Acct Mgr\Specialist','Democracy Specialist','Desk Officer','Disaster Operations Specialist','Disbursing Specialist','Ecologist','Economist','Economist, Financial','Education Specialist','Electrical Engineer','Electronics Engineer','Emergency Management Specialist','Employee and Management','Development Specialist','Employee Development Specialist','Employee Relations Specialist','Energy and Environmental Policy','Analyst','Energy Program Specialist','Engineer (General)','Environmental Engineer','Environmental Planning and Policy','Specialist','Environmental Protection Specialist','Environmental Specialist','Epidemiologist','Equal Employment Opportunity','Specialist','Equal Opportunity Specialist','Ethics Program Specialist','Evaluation and Technical Services Generalist','Evaluator','Executive Analyst','Facilities Analyst','Federal Retirement Benefits Specialist','Field Management Assistant','Field Office Supervisor','Financial Management Specialist','Financial Legislative Specialist','Financial Specialist','Financial Systems Analyst','Financial Transactions Examination Officer','Food Safety Coordinator','Food Technologist','Foreign Affairs Officer','Foreign Affairs Specialist','Foreign Assets Control Intelligence Analyst','Foreign Assets Control Terrorist Program Analyst','Functional Area Analyst','General Engineer','Geographer','Geographical Information Systems/Computer Aided','Geophysicist','Grants Program Specialist','Grants Specialist','Hazard Mitigation Specialist','Hazardous Waste Generator Initiative Specialist','Health Communications Specialist','Health Educator','Health Insurance Specialist','Health Scientist','Health Systems Specialist','Hospital Finance Associate','Housing Program Specialist','Housing Project Manager','Human Resources Advisor\Consultant','Human Resources Consultant','Human Resources Development','Human Resources Evaluator','Human Resources Representative','Human Resources Specialist','Hydraulic Engineer','Immigration Officer','Import Policy Analyst','Industrial Hygienist','Information Management Specialist','Information Research Specialist','Information Resource Management Specialist','Information Technology Policy Analyst','Information Technology Security Assistant','Information Technology Specialist','Inspector','Instructional Systems Design Specialist','Instructions Methods Specialist','Insurance Marketing Specialist','Insurance Specialist','Intelligence Analyst','Intelligence Operations Specialist','Intelligence Research Specialist','Intelligence Specialist','Internal Program Specialist','Internal Revenue Agent','International Affairs Specialist','International Aviation Operations Specialist','International Cooperation Specialist','International Economist','International Project Manager','International Relations Specialist','International Trade Litigation Electronic Database C','International Trade Specialist','International Transportation Specialist','Investigator','Junior Foreign Affairs Officer','Labor Relations Specialist','Labor Relations Specialist','Learning Specialist','Legislative Assistant','Legislative Analyst','Legislative Specialist','Lender Approval Analyst','Lender Monitoring Analyst','Licensing Examining Specialist/Offices','Logistics Management Specialist','Managed Care Specialist','Management Analyst','Management and Budget Analyst','Management and Program Analyst','Management Intern','Management Support Analyst ','Management Support Specialist','Manpower Analyst','Manpower Development Specialist','Marketing Analyst','Marketing Specialist','Mass Communications Producer','Mathematical Statistician','Media Relations Assistant','Meteorologist','Microbiologist','Mitigation Program Specialist','National Security Training Technology','Natural Resources Specialist','Naval Architect','Operations Officer','Operations Planner','Operations Research Analyst','Operations Supervisor','Outdoor Recreation Planner','Paralegal Specialis','Passport/Visa Specialist','Personnel Management Specialist','Personnel Staffing and Classification Specialist','Petroleum Engineer','Physical Science Officer','Physical Scientist, General','Physical Security Specialist','Policy Advisor to the Director','Policy Analyst','Policy and Procedure Analyzt','Policy and Regulatory Analyst','Policy Coordinator','Policy/Program Analyst','Population/Family Planning Specialist','Position Classification Specialist','Presidential Management Fellow','Procurement Analyst','Procurement Specialist','Professional Relations Outreach','Program Administrator','Program Analyst','Program and Policy Analyst','Program Evaluation and Risk Analyst','Program Evolution Team Leader','Program Examiner','Program Manager','Program Operations Specialist','Program Specialist','Program Support Specialist','Program/Public Health Analyst','Project Analyst','Project Manager','Prototype Activities Coordinator','Psychologist (General)','Public Affairs Assistant','Public Affairs Intern','Public Affairs Specialist','Public Health Advisor','Public Health Analyst','Public Health Specialist','Public Liaison/Outreach Specialist','Public Policy Analyst','Quantitative Analyst','Real Estate Appraiser','Realty Specialist','Regional Management Analyst','Regional Technician','Regulatory Analyst','Regulatory Specialist','Research Analyst','Restructuring Analyst','Risk Analyst','Safety and Occupational Health Manager','Safety and Occupational Health Specialist','Safety Engineer/Industrial Hygienist','Science Program Analyst','Securities Compliance Examiner','Security Specialist','SeniorManagement Information Specialist','Social Insurance Analyst','Social Insurance Policy Specialist','Social Insurance Specialist','Social Science Analyst','Social Science Research Analyst','Social Scientist','South Asia Desk Officer','Special Assistant','Special Assistant for Foreign Policy Strategy','Special Assistant to the Associate Director','Special Assistant to the Chief Information Office','Special Assistant to the Chief, FBI National Security', 'Special Assistant to the Director','Special Emphasis Program Manager','Special Projects Analyst','Specialist','Staff Associate','Statistician','Supply Systems Analyst','Survey or Mathematical Statistician','Survey Statistician','Systems Accountant','Systems Analyst','Tax Law Specialist','Team Leader','Technical Writer/Editor','Telecommunications Policy Analyst','Telecommunications Specialist','Traffic Management Specialist','Training and Technical Assistant','Training Specialist','Transportation Analyst','Transportation Industry Analyst','Transportation Program Specialist','Urban Development Specialist','Usability Researcher','Veterans Employment Specialist','Video Production Specialist','Visa Specialist','Work Incentives Coordinator','Workers Compensation Specialist','Workforce Development Specialist','Worklife Wellness Specialist','Writer','Writer/Editor');
?> 
                    <optgroup label="Current"> 
                    <?php if(!empty($title)) {
                        echo "<option value='".$title."'>".$title."</option>";
                    } else {
                        echo "<option value='' disabled selected>None</option>";
                    }
                    ?> 
                    </optgroup> 
                    <optgroup label="Titles">
                    <?php
                        $count_ttl = 0;
                        while(!empty($arr_ttl[$count_ttl])) {
                            echo "<option value='".$arr_ttl[$count_ttl]."'>".$arr_ttl[$count_ttl]."</option>";
                            $count_ttl++;
                        } ?>
                    </optgroup>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col-md-1 col-md-offset-5 col-sm-1 col-sm-offset-5 col-xs-1 col-xs-offset-4">
                <button class="btn btn-danger" type="submit" name="upd_acc">Update</button>
            </div>
        </div>
    </form>
  </div>
  <div class="tab-pane fade" id="dashboard">
    <form id="acc_dashboard_form" action="" method="POST">
        <div class="row">
            <div class="col-md-12">
                <img src="img/web_under_construction.jpg" style="margin-left: 20%;">
            </div>
        </div>
    </form>
  </div>
</div>

<?php
    require "footer.php";
?>

<script>
    $("#acc_account_form").validate({
        rules: {
            acc_b_name: { 
                required:true 
            },
            acc_fname: { 
                required:true 
            },
            acc_lname: { 
                required:true 
            }
        },
        messages: {
            acc_b_name: {
                required: false
            },
            acc_fname: { 
                required: false
            },
            acc_lname: { 
                required: false
            }
        },
        focusInvalid: false,
        invalidHandler: function() {
            $(this).find(":input.error:first").focus();
        },
        errorPlacement: function(){
           return false;
        },
        submitHandler: function(form) {
            form.submit();
        }
    });
</script>
                                    </div>
                                </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
