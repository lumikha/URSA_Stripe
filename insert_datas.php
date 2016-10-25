<?php
//====Variables Here ====//






//=======End=========//
  //require 'couchDB/dbConnect.php';
  require_once 'couchDB/PHP-on-Couch-master/lib/couch.php';
  require_once 'couchDB/PHP-on-Couch-master/lib/couchClient.php';
  require_once 'couchDB/PHP-on-Couch-master/lib/couchDocument.php';
  require 'Chargify-PHP-Client/lib/Chargify.php';

  
  $done = 0;
  $error_message = "error";

    $client_customer = new couchClient ('http://127.0.0.1:5984','ursa-customers');
    $client_user = new couchClient ('http://127.0.0.1:5984','ursa-users');
    $client_user_id = new couchClient ('http://127.0.0.1:5984','ursa-customers');
    $doc = new stdClass();


    for($i=1;$i<=50;$i++){

    $doc->business_name = ${'biz_name' . $i}; //
    $doc->business_address = ${'biz_street' . $i}; //
    $doc->business_suite_no = ${'biz_suite' . $i}; //
    $doc->business_city = ${'biz_city' . $i}; //
    $doc->business_state = ${'biz_state' . $i}; //
    $doc->business_zip = ${'biz_zip' . $i}; //
    $doc->business_country = "US"; //
    $doc->business_phone_no = ${'biz_phone' . $i}; //
    $doc->business_email = ${'biz_email' . $i};//
    $doc->business_website = "";// none
    $doc->business_hours = ""; // none
    
    $doc->business_alternate_phone_no = ""; // none
    $doc->business_post_address= ""; // none
/*
    $count_paymet = 0;
    $paymethod = "";
    foreach($_POST["payment-method"] as $method) {
      if($count_paymet == 0) {
        $paymethod = $method;
      } else {
        $paymethod .= " $method";
      }
      $count_paymet++;
    }
*/
    $doc->payment_method= ${'method' . $i}; //
  
    try {
      $response4 = $client_customer->storeDoc($doc);
    } catch ( Exception $e ) {
      die("Unable to store the document : ".$e->getMessage());
    }

  
  $created_doc_id = $response4->id;

  echo $created_doc_id;
  //--------------Register 2 --------------//
  $err_msg = "error";
  
    
//[[[[]]]]
    //$client_customer = new couchClient ('http://127.0.0.1:5984','bigloco-customers');


  $doc4 = $client_customer->getDoc($created_doc_id); 
    $test = true;

    $new_customer = new ChargifyCustomer(NULL, $test);
    $new_customer->first_name = ${'bfname' . $i};
    $new_customer->last_name = ${'blname' . $i};
    $new_customer->email = ${'biz_email' . $i};
    $new_customer->organization = stripslashes(${'biz_name' . $i});
    $new_customer->phone = ${'biz_phone' . $i};
    $saved_customer = $new_customer->create();
    
    $new_payment_profile = new ChargifyCreditCard(NULL, $test);
    $new_payment_profile->first_name = ${'bfname' . $i};
    $new_payment_profile->last_name = ${'blname' . $i};
    $new_payment_profile->full_number = "4111111111111111"; //
    $new_payment_profile->expiration_month = ${'cc_exp_month' . $i}; //
    $new_payment_profile->expiration_year = ${'cc_exp_year' . $i}; //
    $new_payment_profile->cvv = "8888"; //
    $new_payment_profile->billing_address = ${'biz_street' . $i}; //
    $new_payment_profile->billing_address_2 = ${'biz_suite' . $i}; //
    $new_payment_profile->billing_city = ${'biz_city' . $i}; //
    $new_payment_profile->billing_state = ${'biz_state' . $i}; //
    $new_payment_profile->billing_zip = ${'biz_zip' . $i}; //
    $new_payment_profile->billing_country = "US"; //
    
    $new_subscription = new ChargifySubscription(NULL, $test);
    $new_subscription->product_handle = "prod_001"; //-----------?????????
    $new_subscription->customer_id = $saved_customer->id;
    $new_subscription->credit_card_attributes = $new_payment_profile;
    //$new_subscription->coupon_code = $_POST["coupon-code"];
    
    
      $saved_subscription = $new_subscription->create();
      $doc4->chargify_id = @$saved_customer->id;
      $doc4->business_name = ${'biz_name' . $i};
      $doc4->customer_salutation = "";
      $doc4->customer_title = "";
      $doc4->customer_first_name = ${'bfname' . $i};
      $doc4->customer_last_name = ${'blname' . $i};
      $doc4->customer_email = ${'biz_email' . $i};
      $doc4->customer_phone_no = ${'biz_phone' . $i};
      $doc4->customer_billing_address = ${'biz_street' . $i};
      $doc4->customer_suite_no = ${'biz_suite' . $i};
      $doc4->customer_billing_city = ${'biz_city' . $i};
      $doc4->customer_billing_state = ${'biz_state' . $i};
      $doc4->customer_billing_country = "US";
      $doc4->customer_billing_zip = ${'biz_zip' . $i};
      $doc4->customer_card_last_four = substr("4111111111111111", -4);
      $doc4->customer_card_cvc = "8888";
      $doc4->customer_card_expire_month = ${'cc_exp_month' . $i};
      $doc4->customer_card_expire_year = ${'cc_exp_year' . $i};
      if(empty($saved_subscription->credit_card->customer_vault_token)) {
        $doc4->payment_processor_id = "N.A. - Bogus";
      } else {
        $doc4->payment_processor_id = $saved_subscription->credit_card->customer_vault_token;
      }
      $doc4->product_id = "3881312";
      $doc4->product_handle = "prod_001";
      $doc4->product_name = "Basic Plan";
      $doc4->product_component_id = "";
      $doc4->product_component_name = "";
      $doc4->product_coupon_id = "";
      $doc4->product_coupon_name = "";
      $doc4->sale_date = date("m/d/Y");
      $doc4->sale_center = "BIGLO_SALES_CENTER"; //=========== 
      $doc4->sale_agent = "Jasper"; //============
      $doc4->business_category = "";
      $doc4->prov_gmail = "";
      $doc4->prov_keywords = "";
      $doc4->prov_special_request = "";
      $doc4->prov_existing_social1 = "";
      $doc4->prov_existing_social2 = "";
      $doc4->prov_biglo_website = "";
      $doc4->prov_analytical_address = "";
      $doc4->prov_google_plus = "";
      $doc4->prov_google_maps = "";
      $doc4->prov_facebook = "";
      $doc4->prov_foursquare = "";
      $doc4->prov_twitter = "";
      $doc4->prov_linkedin = "";

      // update the document on CouchDB server
   
        $response = $client_customer->storeDoc($doc4);
        print_r($response);

      

        
        $doc2 = new stdClass();

        $user_pass_a = mt_rand(0 , 100000);
        $user_pass_b = mt_rand(0 , 100000);
        $user_pass_final = $user_pass_a.$user_pass_b;

        $doc2->customer_id = $created_doc_id;
        $doc2->email = ${'biz_email' . $i};
        $doc2->password = @$user_pass_final;
        $doc2->userType = "Customer";

        
          $response2 = $client_user->storeDoc($doc2);
      
            $doc3 = $client_user_id->getDoc($created_doc_id);
          

          $doc3->user_id = $response2->id;

          
            $response3 = $client_user_id->storeDoc($doc3);
        

}
  ?>