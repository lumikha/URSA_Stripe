<div class="tab-pane fade" id="account">
<form  action="" method="POST" id="cust_account_form">
        <div class="grid_9" style="margin-top:-35px;">
            <div class="grid_5 alpha">
                <label class="<?php echo $check; ?>">Business Name<label class="error rem-bor" for="acc_b_name" generated="true"></label></label>
                <input type="text" name="acc_b_name" class="form-control" placeholder="Business Name" value="<?php echo $business_name; ?>">
            </div>
            <div class="grid_4 omega">
                <label class="<?php echo $check; ?>">Business Category</label>
                <select class="form-control" name="acc_category">
                <?php 
                if(isset($_GET['id'])) {
                    echo "<optgroup label='Current'>";
                    if($business_category=="null") { 
                        echo "<option value='null'>None</option>";
                    } else {
                        echo "<option value='".$business_category."'>".$business_category."</option>"; 
                    }
                    echo "</optgroup>";
                ?>
                <optgroup label="Categories">
                    <option value="Automotive Services">Automotive Services</option>
                    <option value="Business Services">Business Services</option>
                    <option value="Food &amp; Beverage">Food &amp; Beverage</option>
                    <option value="Healthcare">Healthcare</option>
                    <option value="Household Services">Household Services</option>
                    <option value="Lawn &amp; Garden Services">Lawn &amp; Garden Services</option>
                    <option value="Mobile Services">Mobile Services</option>
                    <option value="Personal Services">Personal Services</option>
                    <option value="Retail Establishment">Retail Establishment</option>
                </optgroup>
                <?php 
                } else {
                    echo "<option value='' disabled selected>Business Category</option>";
                }
                ?>
                </select>
            </div>
        </div>
        <div class="grid_9">
            <div class="grid_2 alpha">
                <label class="<?php echo $check; ?>">Salutation</label>
                <select name="acc_salut" class="form-control">
                <?php
                    $arr_sltn = array('Mr','Mrs','Ms','Miss','Dr','Herr','Monsieur','Hr','Frau','A V M','Admiraal','Admiral','Air Cdre','Air Commodore','Air Marshal','Air Vice Marshal','Alderman','Alhaji','Ambassador','Baron','Barones','Brig','Brig Gen','Brig General','Brigadier','Brigadier General','Brother','Canon','Capt','Captain','Cardinal','Cdr','Chief','Cik','Cmdr','Col','Col Dr','Colonel','Commandant','Commander','Commissioner','Commodore','Comte','Comtessa','Congressman','Conseiller','Consul','Conte','Contessa','Corporal','Councillor','Count','Countess','Crown Prince','Crown Princess','Dame','Datin','Dato','Datuk','Datuk Seri','Deacon','Deaconess','Dean','Dhr','Dipl Ing','Doctor','Dott','Dott sa','Dr','Dr Ing','Dra','Drs','Embajador','Embajadora','En','Encik','Eng','Eur Ing','Exma Sra','Exmo Sr','F O','Father','First Lieutient','First Officer','Flt Lieut','Flying Officer','Fr','Frau','Fraulein','Fru','Gen','Generaal','General','Governor','Graaf','Gravin','Group Captain','Grp Capt','H E Dr','H H','H M','H R H','Hajah','Haji','Hajim','Her Highness','Her Majesty','Herr','High Chief','His Highness','His Holiness','His Majesty','Hon','Hr','Hra','Ing','Ir','Jonkheer','Judge','Justice','Khun Ying','Kolonel','Lady','Lcda','Lic','Lieut','Lieut Cdr','Lieut Col','Lieut Gen','Lord','M','M L','M R','Madame','Mademoiselle','Maj Gen','Major','Master','Mevrouw','Miss','Mlle','Mme','Monsieur','Monsignor','Mr','Mrs','Ms','Mstr','Nti','Pastor','President','Prince','Princess','Princesse','Prinses','Prof','Prof Dr','Prof Sir','Professor','Puan','Puan Sri','Rabbi','Rear Admiral','Rev','Rev Canon','Rev Dr','Rev Mother','Reverend','Rva','Senator','Sergeant','Sheikh','Sheikha','Sig','Sig na','Sig ra','Sir','Sister','Sqn Ldr','Sr','Sr D','Sra','Srta','Sultan','Tan Sri','Tan Sri Dato','Tengku','Teuku','Than Puying','The Hon Dr','The Hon Justice','The Hon Miss','The Hon Mr','The Hon Mrs','The Hon Ms','The Hon Sir','The Very Rev','Toh Puan','Tun','Vice Admiral','Viscount','Viscountess','Wg Cdr');

                    if(isset($_GET['id'])) { ?> 
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
                        <?php
                    } else {
                        echo "<option value='' disabled selected>Salutation</option>";
                    }
                ?>
                </select>
            </div>
            <div class="grid_4">
                <label class="<?php echo $check; ?>">First Name<label class="error rem-bor" for="acc_fname" generated="true"></label></label>
                <input type="text" name="acc_fname" class="form-control" placeholder="First Name" value="<?php echo $fname; ?>">
            </div>
            <div class="grid_3 omega">
                <label class="<?php echo $check; ?>">Last Name<label class="error rem-bor" for="acc_lname" generated="true"></label></label>
                <input type="text" name="acc_lname" class="form-control" placeholder="Last Name" value="<?php echo $lname; ?>">
            </div>
        </div>
        <div class="grid_9">
            <div class="grid_9 alpha">
                <label class="<?php echo $check; ?>">Title</label>
                <select name="acc_title" class="form-control">
                <?php
                    $arr_ttl = array('Accountant','Accountant Systems','Acquisition Management Intern','Actuarial Analyst','Actuary','Administrative Generalist/Specialist','Affordable Housing Specialist','Analyst','Appraiser','Archaeologist','Area Systems Coordinator','Asylum or Immigration Officer','Attorney/Law Clerk','Audience Analyst','Audit Resolution Follow Up','Auditor','Behavioral Scientist','Biologist, Fishery','Biologist, Marine','Biologist, Wildlife','Budget Analyst','Budget Specialist','Business Administration Officer','Chemical Engineer','Chemist','Citizen Services Specialist','Civil Engineer','Civil Penalties Specialist','Civil/Mechanical/Structural','Engineer','Communications Specialist','Community and Intergovernmental','Program Specialist','Community Planner','Community Planning\Development','Specialist','Community Services Program','Specialist','Compliance Specialist','Computer Engineer','Computer Programmer/Analyst','Computer Scientist','Computer Specialist','Consumer Safety Officer','Contract Specialist','Contract Specialist/Grants','Management Specialist','Corporate Management Analyst','Cost Account','Criminal Enforcement Analyst','Criminal Investigator','Customer Account Manager','Customer Acct Mgr\Specialist','Democracy Specialist','Desk Officer','Disaster Operations Specialist','Disbursing Specialist','Ecologist','Economist','Economist, Financial','Education Specialist','Electrical Engineer','Electronics Engineer','Emergency Management Specialist','Employee and Management','Development Specialist','Employee Development Specialist','Employee Relations Specialist','Energy and Environmental Policy','Analyst','Energy Program Specialist','Engineer (General)','Environmental Engineer','Environmental Planning and Policy','Specialist','Environmental Protection Specialist','Environmental Specialist','Epidemiologist','Equal Employment Opportunity','Specialist','Equal Opportunity Specialist','Ethics Program Specialist','Evaluation and Technical Services Generalist','Evaluator','Executive Analyst','Facilities Analyst','Federal Retirement Benefits Specialist','Field Management Assistant','Field Office Supervisor','Financial Management Specialist','Financial Legislative Specialist','Financial Specialist','Financial Systems Analyst','Financial Transactions Examination Officer','Food Safety Coordinator','Food Technologist','Foreign Affairs Officer','Foreign Affairs Specialist','Foreign Assets Control Intelligence Analyst','Foreign Assets Control Terrorist Program Analyst','Functional Area Analyst','General Engineer','Geographer','Geographical Information Systems/Computer Aided','Geophysicist','Grants Program Specialist','Grants Specialist','Hazard Mitigation Specialist','Hazardous Waste Generator Initiative Specialist','Health Communications Specialist','Health Educator','Health Insurance Specialist','Health Scientist','Health Systems Specialist','Hospital Finance Associate','Housing Program Specialist','Housing Project Manager','Human Resources Advisor\Consultant','Human Resources Consultant','Human Resources Development','Human Resources Evaluator','Human Resources Representative','Human Resources Specialist','Hydraulic Engineer','Immigration Officer','Import Policy Analyst','Industrial Hygienist','Information Management Specialist','Information Research Specialist','Information Resource Management Specialist','Information Technology Policy Analyst','Information Technology Security Assistant','Information Technology Specialist','Inspector','Instructional Systems Design Specialist','Instructions Methods Specialist','Insurance Marketing Specialist','Insurance Specialist','Intelligence Analyst','Intelligence Operations Specialist','Intelligence Research Specialist','Intelligence Specialist','Internal Program Specialist','Internal Revenue Agent','International Affairs Specialist','International Aviation Operations Specialist','International Cooperation Specialist','International Economist','International Project Manager','International Relations Specialist','International Trade Litigation Electronic Database C','International Trade Specialist','International Transportation Specialist','Investigator','Junior Foreign Affairs Officer','Labor Relations Specialist','Labor Relations Specialist','Learning Specialist','Legislative Assistant','Legislative Analyst','Legislative Specialist','Lender Approval Analyst','Lender Monitoring Analyst','Licensing Examining Specialist/Offices','Logistics Management Specialist','Managed Care Specialist','Management Analyst','Management and Budget Analyst','Management and Program Analyst','Management Intern','Management Support Analyst ','Management Support Specialist','Manpower Analyst','Manpower Development Specialist','Marketing Analyst','Marketing Specialist','Mass Communications Producer','Mathematical Statistician','Media Relations Assistant','Meteorologist','Microbiologist','Mitigation Program Specialist','National Security Training Technology','Natural Resources Specialist','Naval Architect','Operations Officer','Operations Planner','Operations Research Analyst','Operations Supervisor','Outdoor Recreation Planner','Paralegal Specialis','Passport/Visa Specialist','Personnel Management Specialist','Personnel Staffing and Classification Specialist','Petroleum Engineer','Physical Science Officer','Physical Scientist, General','Physical Security Specialist','Policy Advisor to the Director','Policy Analyst','Policy and Procedure Analyzt','Policy and Regulatory Analyst','Policy Coordinator','Policy/Program Analyst','Population/Family Planning Specialist','Position Classification Specialist','Presidential Management Fellow','Procurement Analyst','Procurement Specialist','Professional Relations Outreach','Program Administrator','Program Analyst','Program and Policy Analyst','Program Evaluation and Risk Analyst','Program Evolution Team Leader','Program Examiner','Program Manager','Program Operations Specialist','Program Specialist','Program Support Specialist','Program/Public Health Analyst','Project Analyst','Project Manager','Prototype Activities Coordinator','Psychologist (General)','Public Affairs Assistant','Public Affairs Intern','Public Affairs Specialist','Public Health Advisor','Public Health Analyst','Public Health Specialist','Public Liaison/Outreach Specialist','Public Policy Analyst','Quantitative Analyst','Real Estate Appraiser','Realty Specialist','Regional Management Analyst','Regional Technician','Regulatory Analyst','Regulatory Specialist','Research Analyst','Restructuring Analyst','Risk Analyst','Safety and Occupational Health Manager','Safety and Occupational Health Specialist','Safety Engineer/Industrial Hygienist','Science Program Analyst','Securities Compliance Examiner','Security Specialist','SeniorManagement Information Specialist','Social Insurance Analyst','Social Insurance Policy Specialist','Social Insurance Specialist','Social Science Analyst','Social Science Research Analyst','Social Scientist','South Asia Desk Officer','Special Assistant','Special Assistant for Foreign Policy Strategy','Special Assistant to the Associate Director','Special Assistant to the Chief Information Office','Special Assistant to the Chief, FBI National Security', 'Special Assistant to the Director','Special Emphasis Program Manager','Special Projects Analyst','Specialist','Staff Associate','Statistician','Supply Systems Analyst','Survey or Mathematical Statistician','Survey Statistician','Systems Accountant','Systems Analyst','Tax Law Specialist','Team Leader','Technical Writer/Editor','Telecommunications Policy Analyst','Telecommunications Specialist','Traffic Management Specialist','Training and Technical Assistant','Training Specialist','Transportation Analyst','Transportation Industry Analyst','Transportation Program Specialist','Urban Development Specialist','Usability Researcher','Veterans Employment Specialist','Video Production Specialist','Visa Specialist','Work Incentives Coordinator','Workers Compensation Specialist','Workforce Development Specialist','Worklife Wellness Specialist','Writer','Writer/Editor');

                    if(isset($_GET['id'])) { ?> 
                        <optgroup label="Current"> 
                        <?php 
                            if(!empty($title)) {
                                echo "<option value='".$title."'>".$title."</option>";
                            } else {
                                echo "<option value='null'>None</option>";
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
                        <?php
                    } else {
                        echo "<option value='' disabled selected>Title</option>";
                    }
                ?>
                </select>
            </div>
        </div>
        <br/>
        <div class="grid_9">
            <div class="grid_3 alpha">
                <label class="<?php echo $check; ?>">Primary Phone<label class="error rem-bor" for="acc_phone" generated="true"></label></label>
                <input type="text" name="acc_phone" class="form-control" placeholder="Primary Phone" value="<?php echo $phone; ?>">
            </div>
            <div class="grid_3">
                <label class="<?php echo $check; ?>">Alternate Phone<label class="error rem-bor" for="acc_alter_phone" generated="true"></label></label>
                <input type="text" name="acc_alter_phone" class="form-control" placeholder="Alternate Phone" value="<?php if($alt_phone!="null"){ echo $alt_phone; }?>">
            </div>
            <div class="grid_3 omega">
                <label class="<?php echo $check; ?>">Mobile Phone<label class="error rem-bor" for="acc_mobile_phone" generated="true"></label></label>
                <input type="text" name="acc_mobile_phone" class="form-control" placeholder="Mobile Phone" value="<?php if($mobile!="null"){ echo $mobile; }?>">
            </div>
        </div>
        <div class="grid_9">
            <div class="grid_5 alpha">
                <label class="<?php echo $check; ?>">Primary Email<label class="error rem-bor" for="acc_email" generated="true"></label></label>
                <input type="text" name="acc_email" class="form-control" placeholder="Primary Email" value="<?php echo $email; ?>">
            </div>
            <div class="grid_4 omega">
                <label class="<?php echo $check; ?>">Alternate Email<label class="error rem-bor" for="acc_alter_email" generated="true"></label></label>
                <input type="text" name="acc_alter_email" class="form-control" placeholder="Alternate Email" value="<?php if($alt_email!="null"){ echo $alt_email; }?>">
            </div>
        </div>
        <br/>
        <div class="grid_9">
            <div class="grid_5 alpha">
                <label class="<?php echo $check; ?>">Billing Address 1<label class="error rem-bor" for="acc_bill_add_1" generated="true"></label></label>
                <input type="text" name="acc_bill_add_1" class="form-control" placeholder="Billing Address 1" value="<?php echo $bill_address; ?>">
            </div>
            <div class="grid_4 omega">
                <label class="<?php echo $check; ?>">Billing Address 2<label class="error rem-bor" for="acc_bill_add_2" generated="true"></label></label>
                <input type="text" name="acc_bill_add_2" class="form-control" placeholder="Billing Address 2" value="<?php echo $bill_address_2; ?>">
            </div>
        </div>
        <div class="grid_9">
             <div class="grid_4 alpha">
                <label class="<?php echo $check; ?>">Billing City<label class="error rem-bor" for="acc_bill_city" generated="true"></label></label>
                <input type="text" name="acc_bill_city" class="form-control" placeholder="Billing City" value="<?php echo $bill_city; ?>">
            </div>
            <div class="grid_2">
                <label class="<?php echo $check; ?>">Office State<label class="error rem-bor" for="acc_bill_state" generated="true"></label></label>
                <select class="form-control" name="acc_bill_state">
                <?php if(isset($_GET['id'])) {
                    echo "<optgroup label='Current'>
                        <option value='".$bill_state."'>".$bill_state."</option>
                        </optgroup>";
                ?>
                    <optgroup label='States'>
                        <option value="AL">AL</option> 
                        <option value="AK">AK</option>
                        <option value="AZ">AZ</option> 
                        <option value="AR">AR</option> 
                        <option value="CA">CA</option> 
                        <option value="CO">CO</option> 
                        <option value="CT">CT</option> 
                        <option value="DE">DE</option> 
                        <option value="DC">DC</option> 
                        <option value="FL">FL</option> 
                        <option value="GA">GA</option> 
                        <option value="HI">HI</option> 
                        <option value="ID">ID</option> 
                        <option value="IL">IL</option> 
                        <option value="IN">IN</option> 
                        <option value="IA">IA</option> 
                        <option value="KS">KS</option> 
                        <option value="KY">KY</option> 
                        <option value="LA">LA</option> 
                        <option value="ME">ME</option> 
                        <option value="MD">MD</option> 
                        <option value="MA">MA</option> 
                        <option value="MI">MI</option> 
                        <option value="MN">MN</option> 
                        <option value="MS">MS</option> 
                        <option value="MO">MO</option> 
                        <option value="MT">MT</option> 
                        <option value="NE">NE</option> 
                        <option value="NV">NV</option> 
                        <option value="NH">NH</option> 
                        <option value="NJ">NJ</option> 
                        <option value="NM">NM</option> 
                        <option value="NY">NY</option> 
                        <option value="NC">NC</option> 
                        <option value="ND">ND</option> 
                        <option value="OH">OH</option> 
                        <option value="OK">OK</option> 
                        <option value="OR">OR</option> 
                        <option value="PA">PA</option> 
                        <option value="RI">RI</option> 
                        <option value="SC">SC</option> 
                        <option value="SD">SD</option> 
                        <option value="TN">TN</option> 
                        <option value="TX">TX</option> 
                        <option value="UT">UT</option> 
                        <option value="VT">VT</option> 
                        <option value="VA">VA</option> 
                        <option value="WA">WA</option> 
                        <option value="WV">WV</option> 
                        <option value="WI">WI</option> 
                        <option value="WY">WY</option>
                    </optgroup>
                <?php } else {
                    echo "<option value='' disabled selected>Office State</option>";
                } ?>
                </select>
            </div>
            <div class="grid_3 omega">
                <label class="<?php echo $check; ?>">Postal Code<label class="error rem-bor" for="acc_bill_zip" generated="true"></label></label>
                <input type="text" name="acc_bill_zip" class="form-control" placeholder="Postal Code" value="<?php echo $bill_zip; ?>">
            </div>
        </div>
        <br/>
        <div class="grid_9">
            <div class="grid_5 alpha">
                <label class="<?php echo $check; ?>">Sales Center<label class="error rem-bor" for="acc_sales_center" generated="true"></label></label>
                <input type="text" name="acc_sales_center" class="form-control" placeholder="Sales Center" value="<?php echo $sales_center; ?>" readonly>
            </div>
            <div class="grid_4 omega">
                <label class="<?php echo $check; ?>">Sales Agent<label class="error rem-bor" for="acc_sales_agent" generated="true"></label></label>
                <input type="text" name="acc_sales_agent" class="form-control" placeholder="Sales Agent" value="<?php echo $sales_agent; ?>" readonly>
            </div>
        </div>
        <div class="grid_9">
            <div class="grid_3 alpha">
                <label class="<?php echo $check; ?>">Product<label class="error rem-bor" for="acc_product" generated="true"></label></label>
                <select class="form-control" name="acc_product">
                    <?php
                    if(isset($_GET['id'])) {
                        echo "<optgroup label='Current'>
                            <option value='".$product_handle."'>".$product_name."</option>
                        </optgroup>";
                    ?>
                    <optgroup label="Available Plans">
                        <option value="prod_001">Basic Plan</option>
                        <option value="plan_002">Start-up Plan</option>
                        <option value="plan_005">Upgrade to Start-up Plan</option>
                        <option value="plan_003">Business Plan</option>
                        <option value="plan_006">Upgrade to Business Plan</option>
                        <option value="plan_004">Enterprise Plan</option>
                        <option value="plan_007">Upgrade Enterprise Plan</option>
                    </optgroup>
                    <?php
                    } else {
                        echo "<option value='' disabled selected>Product</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="grid_3">
                <label class="<?php echo $check; ?>">Component<label class="error rem-bor" for="acc_component" generated="true"></label></label>
                <select class="form-control" name="acc_component">
                    <?php
                    if(isset($_GET['id'])) {
                        echo "<optgroup label='Current'>";
                        if($product_component_quantity=="null") { 
                            echo "<option value=''>None</option>";
                        } else {
                            echo "<option value='".$product_component_quantity."'>".$product_component_quantity."</option>";
                        }
                        echo "</optgroup>";
                    ?>
                    <optgroup label="Quantity">
                        <?php
                            $count_comp=0;
                            while($count_comp != 10) {
                                echo "<option value='".$count_comp."'>".$count_comp."</option>";
                                $count_comp++;
                            }
                        ?>
                    </optgroup>
                    <?php
                    } else {
                        echo "<option value='' disabled selected>Component</option>";
                    }
                    ?>  
                </select>
            </div>
            <div class="grid_3 omega">
                <label class="<?php echo $check; ?>">Coupon<label class="error rem-bor" for="acc_coupon" generated="true"></label></label>
                <select class="form-control" name="acc_coupon">
                    <?php
                    if(isset($_GET['id'])) {
                        echo "<optgroup label='Current'>";
                        if($product_coupon_code == "null") { 
                            echo "<option value='null'>None</option>";
                        } else {
                            echo "<option value='".$product_coupon_code."'>".$product_coupon_name."</option>
                                  <option value='REMOVE'>REMOVE</option>"; 
                        }
                         echo "</optgroup>";
                    ?>    
                    </optgroup>
                    <optgroup label="Available Coupons">
                        <option value="SAVE50">Discount Coupon</option>
                        <option value="FREDOM">Domain Coupon</option>
                        <option value="REFER">Referral Coupon</option>
                    </optgroup>
                     <?php
                    } else {
                        echo "<option value='' disabled selected>Coupon</option>";
                    }
                    ?>  
                </select>
            </div>
        </div>
        <?php if(isset($_GET['id'])) { ?>
        <div class="row">
            <div class="col-md-1 col-md-offset-5 col-sm-1 col-sm-offset-5 col-xs-1 col-xs-offset-4">
                <button class="btn btn-danger" type="submit" name="upd_acc">Update</button>
            </div>
        </div>
        <?php } ?>
    </form>

    <?php if(!isset($_GET['id'])) { ?>
        <form  action="" method="POST" id="noid_cust_account_form">
            <input type="text" name="cID" value="" hidden>
            <div class="row">
                <div class="col-md-1 col-md-offset-5 col-sm-1 col-sm-offset-5 col-xs-1 col-xs-offset-4">
                    <button class="btn btn-danger" type="submit">Update</button>
                </div>
            </div>
        </form>
        <?php } ?>
</div>