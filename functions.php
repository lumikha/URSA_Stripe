<?php 
	require 'dynamoDB/dbConnect.php';
	require 'Chargify-PHP-Client/lib/Chargify.php';
	//$result_db_users = getAll user list 
	//$result_db_customers = getAll customer list
	//$client_users = new connection to user database
	//$client_customers = new connection to customer database

	session_start();

    if(isset($_SESSION['user_now_id'])) {
    	if($_SESSION['type']=="customer") {
	    	if(basename($_SERVER['PHP_SELF'])=="login.php"){
	        header("Location: account.php");
			}
		}else{
			if(basename($_SERVER['PHP_SELF'])=="login.php"){
	        header("Location: customer.php");
			}
		}
    } else {
    	if(isset($_COOKIE['URSA'])) {
			$pieces = explode(",", $_COOKIE["URSA"]);

			$i=0;
			while(isset($result_db_users['Items'][$i])) {
				if($result_db_users['Items'][$i]['user_id']['S'] == $pieces[0])
				{
					$_GET['e'] = $result_db_users['Items'][$i]['email']['S'];
					$_GET['p'] = $result_db_users['Items'][$i]['password']['S'];
				}
				$i++;
			}
		}
    }

	$found=true;
	if(isset($_POST['login_btn']) || (isset($_GET['e']) && isset($_GET['p']))) {
		if(isset($_GET['e']) && isset($_GET['p'])) {
			$email = $_GET['e'];
			$pass = $_GET['p'];
		} else {
			$email = $_POST['email'];
			$pass = $_POST['password'];
		}

		$i=0;
		while(isset($result_db_users['Items'][$i])) {
			if(($result_db_users['Items'][$i]['email']['S'] == $email) && ($result_db_users['Items'][$i]['password']['S'] == $pass)) {
				$_SESSION['user_now_id'] = $result_db_users['Items'][$i]['user_id']['S'];
				$_SESSION['user_now_email'] = $email;
				$_SESSION['user_now_access_level'] = $result_db_users['Items'][$i]['userType']['S'];
				if($result_db_users['Items'][$i]['userType']['S'] == 'Customer') {
					$_SESSION['user_now_db_customer_id'] = $result_db_users['Items'][$i]['customer_id']['S'];
					$_SESSION['type'] = "customer";
					?>
					<script>
						window.location = "account"; //User Dashboard
					</script>
					<?php
				}else if($result_db_users['Items'][$i]['userType']['S'] == 'Administrator') {
					$_SESSION['user_now_fname'] = $result_db_users['Items'][$i]['user_first_name']['S'];
					$_SESSION['user_now_lname'] = $result_db_users['Items'][$i]['user_last_name']['S'];
					$_SESSION['type'] = "admin";
					?>
					<script>
						window.location = "summary"; 
					</script>
					<?php
				} else {
					$_SESSION['user_now_fname'] = $result_db_users['Items'][$i]['user_first_name']['S'];
					$_SESSION['user_now_lname'] = $result_db_users['Items'][$i]['user_last_name']['S'];
					$_SESSION['type'] = "agent";
					?>
					<script>
						window.location = "summary"; //Admin/Agent Dashboard
					</script>
					<?php
				}

				if(isset($_POST['remember'])) {
					$cookie_name = 'URSA';
					$cookie_time = (60 * 30);
					$id = $result_db_users['Items'][$i]['user_id']['S'];
					setcookie ($cookie_name, $id. ',' .$email, time() + $cookie_time);
				} 

			} else {
				$found=false;
			}
			$i++;
		}
	}

	$reset_pass_error = 0;
	$reset_pass_success = 0;
	if(isset($_POST['reset_btn'])) {
		$r_email = $_POST['resetpass_email'];
		$newpass = $_POST['resetpass_newpass'];

		$vcode_a1 = mt_rand(0 , 1000000);
	    $vcode_a2 = mt_rand(0 , 1000000);
	    $vcode_a = $vcode_a1.$vcode_a2;

	    $vcode_b1 = 'abcdefghijklmnopqrstuvwxyz';
	    $vcode_b2 = strtoupper('abcdefghijklmnopqrstuvwxyz');
	    $vcode_b = $vcode_b1.$vcode_b2;

	    $vcode_fin = $vcode_a.$vcode_b;

	    $email_hex = bin2hex($r_email);
	    $vcode = str_shuffle($vcode_fin);
	    $date = date('Y-m-d');
	    $exp_date = new DateTime('+1 day');
		$exp_date = $exp_date->format('Y-m-d'); 

		$i=0;
		$reset_email_found = false;
		while(isset($result_db_users['Items'][$i])) {
			if($result_db_users['Items'][$i]['email']['S'] == $r_email) {
				$reset_email_found = true;
				$userid = $result_db_users['Items'][$i]['user_id']['S'];
				$userType = $result_db_users['Items'][$i]['userType']['S'];

				if($userType != "Customer") {
					$fname = $result_db_users['Items'][$i]['user_first_name'];
				} else {
					$customer_ID = $result_db_users['Items'][$i]['user_id']['S'];

					$j=0;
					while(isset($result_db_customers['Items'][$j])) {
						if($result_db_customers['Items'][$j]['customer_id']['S'] == $customer_ID) {
							$fname = $result_db_customers['Items'][$j]['customer_first_name']['S'];
						}
						$j++;
					}
				}

	            $key_np = $marshaler->marshalJson('
				    {
				        "user_id": "'.$userid.'"
				    }
			    ');
			    $eav_np = $marshaler->marshalJson('
				    {
				        ":requested_new_password": "'.@$newpass.'",
				        ":reset_verification_code": "'.@$vcode.'",
				        ":reset_date_requested": "'.@$date.'",
				        ":reset_date_request_expire": "'.@$exp_date.'"
				    }
				');
				$params_np = [
				    'TableName' => 'ursa-users',
				    'Key' => $key_np,
				    'UpdateExpression' =>
				        'set requested_new_password=:requested_new_password,
				        	reset_verification_code=:reset_verification_code,
				        	reset_date_requested=:reset_date_requested,
				        	reset_date_request_expire=:reset_date_request_expire,
				        	',
				    'ExpressionAttributeValues'=> $eav_np,
				    'ReturnValues' => 'UPDATED_NEW'
				];

				try {
				    $result_np = $dynamodb->updateItem($params_np);
				} catch (DynamoDbException $e) {
				    echo "Unable to update item:\n";
				    echo $e->getMessage() . "\n";
				}
//????????????????????????????????????????????
				/*	            
				$whitelist = array('127.0.0.1', "::1");
				if(in_array($_SERVER['REMOTE_ADDR'], $whitelist)){
				    ?>
					<script>
						window.location = "verify_reset_pass?e=<?php echo $email_hex; ?>&vcode=<?php echo $vcode; ?>&go=0";
					</script>
					<?php
				} else {
					*/
					$to = $r_email;
			        $subject = "URSA Reset Password";

			        $message = "
			        <html>
			        <head>
			        <title>Password Recovery</title>
			        </head>
			        <body>
			        <p>Hello ".$fname.", </p>
			    	<p>Please visit this link to verify your action <a href='http://biglo.co/verify_reset_pass?e=".$email_hex."&vcode=".$vcode."&go=1' target='_blank'>Verify Reset Password</a>. </p>
			        <br/>
			        <br/>
			        <p><strong>Thanks,</strong></p>
			        <p>URSA</p>
			        </body>
			        </html>
			        ";

			        // Always set content-type when sending HTML email
			        $headers = "MIME-Version: 1.0" . "\r\n";
			        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

			        // More headers
			        $headers .= 'From: <URSA@lumikha.co>' . "\r\n";

			        if(mail($to,$subject,$message,$headers)) {
			        	$reset_pass_success = 1;
			        }
				//}
			} 
			$i++;
		}

		if($reset_email_found == false) {
			$reset_pass_error = 1;
		}

	    $email = "";
	}

	if(isset($_SESSION['user_now_id'])) {
		if(isset($_SESSION['user_now_db_customer_id'])) {
			$i=0;
			while(isset($result_db_customers['Items'][$i])) {
				if($result_db_customers['Items'][$i]['customer_id']['S'] == $_SESSION['user_now_db_customer_id']) {
					$email = $result_db_customers['Items'][$i]['customer_email']['S'];
					$fname = $result_db_customers['Items'][$i]['customer_first_name']['S'];
					$lname = $result_db_customers['Items'][$i]['customer_last_name']['S'];
					$chargifyID = $result_db_customers['Items'][$i]['chargify_id']['S'];
                	$salutation = $result_db_customers['Items'][$i]['customer_salutation']['S'];
                	$title = $result_db_customers['Items'][$i]['customer_title']['S'];
					$sales_date = $result_db_customers['Items'][$i]['sale_date']['S'];
					$sales_agent = $result_db_customers['Items'][$i]['sale_agent']['S'];
					$sales_center = $result_db_customers['Items'][$i]['sale_center']['S'];
					$product_id = $result_db_customers['Items'][$i]['product_id']['S'];
	                $product_handle = $result_db_customers['Items'][$i]['product_handle']['S'];
	                $product_name = $result_db_customers['Items'][$i]['product_name']['S'];
	                $product_component_id = $result_db_customers['Items'][$i]['product_component_id']['S'];
	                $product_component_name = $result_db_customers['Items'][$i]['product_component_name']['S'];
	                $product_component_quantity = $result_db_customers['Items'][$i]['product_component_quantity']['S'];
	                $product_coupon_code = $result_db_customers['Items'][$i]['product_coupon_code']['S'];
	                $product_coupon_name = $result_db_customers['Items'][$i]['product_coupon_name']['S'];
				}
				$i++;
			}
			
			$test = true;
			$subscription = new ChargifySubscription(NULL, $test);

			try {
				$result_customer_id = $subscription->getByCustomerID($chargifyID);
			} catch (ChargifyValidationException $cve) {
				  echo $cve->getMessage();
			}

			if($result_customer_id[0]->state == "trialing") {
				?><style>
				.cust_id {
					color: #b300b3;
				}
				</style><?php
			} elseif($result_customer_id[0]->state == "active") {
				?><style>
				.cust_id {
					color: #28B22C;
				}
				</style><?php
			} elseif($result_customer_id[0]->state == "past_due") {
				?><style>
				.cust_id {
					color: #e6e600;
				}
				</style><?php
			} elseif($result_customer_id[0]->state == "unpaid") {
				?><style>
				.cust_id {
					color: #ff0000;
				}
				</style><?php
			} elseif($result_customer_id[0]->state == "canceled") {
				?><style>
				.cust_id {
					color: #000000;
				}
				</style><?php
			} else {
				?><style>
				.cust_id {
					color: #cccccc;
				}
				</style><?php
			}

			$billing_sum = "$".number_format(($result_customer_id[0]->total_revenue_in_cents /100), 2, '.', ' ');
			$fin = explode('T',$result_customer_id[0]->updated_at,-1);
			$fin2 = explode('-',$fin[0]);
			$char_upd_at = $fin2[1].".".$fin2[2].".".$fin2[0];
			$business_name = $result_customer_id[0]->customer->organization;


			//for agent search customerID
			if($result_customer_id[0]->state == "trialing") {
				$cust_search_state = "Trial Ended: ".$result_customer_id[0]->trial_ended_at;
			} elseif($result_customer_id[0]->state == "active") {
				$cust_search_state = "Next Billing: ".$result_customer_id[0]->next_billing_at;
			} else {
				$cust_search_state = "Cancelled At: ".$result_customer_id[0]->canceled_at;
			}

		} else {
			$fname = $_SESSION['user_now_fname'];
			$lname = $_SESSION['user_now_lname'];
		}
	}
	function GUID()
	  {
	      	date_default_timezone_set("Asia/Manila");
			$t = microtime(true);
			$micro = sprintf("%06d",($t - floor($t)) * 1000000);
			$d = new DateTime( date('Y-m-d H:i:s.'.$micro, $t) );

			return $d->format("YmdHisu");
	  }
?>
	