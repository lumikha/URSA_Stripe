<?php 
    require 'functions.php';
    $current_page = basename($_SERVER['PHP_SELF']);
    if(!isset($_SESSION['user_now_id'])) { 
        header("Location: login");
    } else {
        if($_SESSION['user_now_access_level'] == "Customer") {
            if($current_page == "customer.php") {
                header("Location: noaccess");
            }
        } else {
            if($current_page == "account.php" || $current_page == "sales.php" || $current_page == "provisioning.php") {
                header("Location: noaccess");
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>URSA</title>
        <link rel="Shortcut icon" href="img/ursa_tab_logo.png"/>
       <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0" />
    </head>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
    <link href="js/dataTables/dataTables.bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css"/>
    <link rel="stylesheet" type="text/css" href="css/jquery.tagsinput.css" />
    
     <!--960 grid stylesheet links-->

    <link href="css/960.css" rel="stylesheet"/>
    <link href="css/reset.css" rel="stylesheet"/>
    <link href="css/text.css" rel="stylesheet"/>
    <link rel="stylesheet" href="style.css"/>
   
   <!--media queries-->
   <link rel="stylesheet" type="text/css" href="css/ursa/mediaquery.css">


    <body>

 <div class="full-width-div">
 <div class="container_12" style="margin-top:60px;">
                              
                            <div class="grid_2 alpha logo text-center">
                                <a id="home" class="disp_tickets" href="<?php if($current_page == 'summary.php' || $current_page == 'summary'):?>#<?php else:?>summary<?php endif;?>" <?php if($current_page == 'summary.php' || $current_page == 'summary'):?>onclick="return tickets();"<?php endif;?>> 
                                    <img src="img/ursa_logo_red.gif" height="130" align="middle" >
                                </a>
                            </div>
                        
                                    <div class="grid_3 hey" style="margin-left:2.5em;">
                                         <div class="user_opt dropdown"> 
                                            <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                             <p class="hello">Hey <?php echo @$fname; ?>
                                                <span class="caret"></span>
                                             </p>
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
                                                <li><a href="#">My Profile</a></li>
                                                <li><a href="#">Another action</a></li>
                                                <li><a href="#">Something else here</a></li>
                                                <li role="separator" class="divider"></li>
                                                <li><a href="logout.php">Logout</a></li>
                                            </ul>
                                        </div>
                                    </div>
                               
                                    <?php if(!isset($_SESSION['user_now_db_customer_id'])) { ?>
                                    <div class="search">
                                    <div class="grid_2 push_6 omega"  style="margin-left:1.5em;">
                                        <div ng-app="myapp" id="search_result_view" >
                                            <div ng-controller="newController">
                                                <div id="toggleContainer" style=" position:relative">
                                                    <form name="myForm">
                                                        <input type="text" style="width: 100%;margin-bottom:0em " class="form-control" onkeyup="return check();" id="search" name="search" ng-model="search" placeholder="Search" autocomplete="off" >
                                                    </form>
                                                    <div name="output" id="output" style="position:absolute; z-index:1;width: 200%;" ng-cloak >
                                                        <div class="list-group">
                                                            <a class="list-group-item" ng-if="search" ng-repeat="user in result = ( users | filter:search | limitTo:num)" href="customer.php?id={{ user.chargify_id.S }}">
                                                                <span style="font-size:130%" class="text-info"><span class="glyphicon glyphicon-briefcase" aria-hidden="true"></span> {{user.business_name.S}}</span><br>
                                                                <span style="font-size:75%"><span class="glyphicon glyphicon-user" aria-hidden="true"></span> {{user.customer_first_name.S}} {{user.customer_last_name.S}}, 
                                                                    <span class="glyphicon glyphicon-envelope" aria-hidden="true"></span>  {{user.business_email.S}}
                                                                </span>
                                                            </a>
                                                            <a class="list-group-item" ng-if="search" ng-hide="result.length">Opps, No Results Found ...</a>
                                                            <a class="list-group-item " ng-if="search" href="#" onclick="getSearch();">View More Results <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span></a>    
                                                        </div>
                                                    </div> 
                                                    </div>
                                                </div>
                                            </div>   
                                            </div>
                                        </div> 
                                        </div>
                                        </div>

                                    </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>

                        <div class="full-width-div">

                        <div class="container_12">
                       
                            <div class="grid_2 alpha navtabs" style="margin-bottom:90px; margin-top:20px; position:absolute;">
                                    <?php 
                                    if(isset($_SESSION['user_now_db_customer_id'])) { ?>
                                    <ul class="nav nav-pills nav-stacked text-center" id="myTab">
                                     <li><a class="hidden">Hidden</a></li> 
                                        <li id="acc_tab1"><a class="nl" href="#account" data-toggle="tab">Account</a></li>
                                        <li id="acc_tab2"><a class="nl" href="#dashboard" data-toggle="tab">Dashboard</a></li>
                                     </ul>
                                    <?php }else { ?>
                                    <ul class="nav nav-pills nav-stacked text-center" id="myTab">
                                    <?php  if($current_page=="summary.php" || $current_page=="support2.php" || $current_page=="summary2.php") { ?>
                                    <script type="text/javascript">
                                    
                                        function accounts(){
                                        $("#tickets_display").addClass('hidden');
                                        $("#support_display").addClass('hidden');
                                        $("#accounts_display").removeClass('hidden');
                                        }
                                        function tickets(){
                                        $("#accounts_display").addClass('hidden');
                                        $("#support_display").addClass('hidden');
                                        $("#accounts_tab").removeClass('active');
                                        $("#tickets_display").removeClass('hidden');
                                        }
                                        function support(){
                                        $("#accounts_display").addClass('hidden');
                                        $("#tickets_display").addClass('hidden');
                                        $("#support_display").removeClass('hidden');
                                        }
                                        
                                    </script>
                                    <li><a class="nl disabledTab hidden">Hidden</a></li>
                                    <!--<li><a class="nl" href="#account" data-toggle="tab" onclick="return remAccounts();" class="hide_tickets">Tickets</a></li>-->
                                    <li id="accounts_tab"><a  href="#" class="nl" data-toggle="tab" onclick="return accounts();">Accounts</a></li>
                                    <li><a id="btn_provisioning" class="nl disabledTab" data-toggle="tab" >Provisioning</a></li>
                                        <?php if($current_page=="support2.php") { ?>
                                            <li class="active"><a id="btn_support" class="nl" href="#" >Support</a></li>
                                        <?php } else { ?>
                                            <li><a id="btn_support" class="nl" href="#" data-toggle="tab" onclick="return support();">Support</a></li>
                                        <?php } ?>
                                    <li><a id="btn_customer" class="nl disabledTab" data-toggle="tab" >Customer</a></li>
                                    <li><a id="btn_quality" class="nl disabledTab" data-toggle="tab" >Quality</a></li>
                                    <li><a id="btn_dashboard" class="nl disabledTab" data-toggle="tab" >Dashboard</a></li>
                                    <li><a id="btn_center" class="nl disabledTab" data-toggle="tab" >Center</a></li>
                                    <?php } ?>
                                    <?php  if($current_page=="customer.php") { ?>  
                                    <li><a class="hidden">Hidden</a></li>
                                    <li><a id="btn_account" class="nl" href="#account" data-toggle="tab">Account</a></li>
                                    <li><a id="btn_provisioning" class="nl" href="#provisioning" data-toggle="tab" >Provisioning</a></li>
                                    <li><a id="btn_support" class="nl" href="support">Support</a></li>
                                    <li><a id="btn_customer" class="nl" href="#customer" data-toggle="tab" >Customer</a></li>
                                    <li><a id="btn_quality" class="nl" href="#quality" data-toggle="tab" >Quality</a></li>
                                    <li><a id="btn_dashboard" class="nl" href="#dashboard" data-toggle="tab" >Dashboard</a></li>
                                    <li><a id="btn_center" class="nl" href="#center" data-toggle="tab" >Center</a></li>
                                    <li><a id="btn_admin" href="#admin" data-toggle="tab" class="nl <?php if($_SESSION['type'] == 'agent'): ?> hidden <?php endif;?>" >Admin</a></li>
                                    <?php } ?>
                            
                                </ul>
                                    <?php if($current_page == "customer.php") { ?>
                                     <div class="container_12" hidden>
                                        <div class="grid_10 push_2 dynamic" hidden>
                                     </div>    
                                </div>
                                    <?php } ?>
                                <?php }?>
                             </div>
                            </div>
</div>

                            </div>

                            