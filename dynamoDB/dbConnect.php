<?php
require '../DynamoDB/dbCredentials.php';
require 'vendor/autoload.php';
use Aws\S3\S3Client;
use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\DynamoDb\Marshaler;

$sharedConfig = [
    'region'  => 'us-west-2',
    'version' => 'latest',
    'credentials' => [
        'key'    => $key,
        'secret' => $secret,
    ],
    'http'    => [
        'verify' => false
    ]
];

$sdk = new Aws\Sdk($sharedConfig);
$dynamodb = $sdk->createDynamoDb();
$marshaler = new Marshaler();

$params = [
    'TableName' => 'ursa-users',
    'ProjectionExpression' => 'user_id,email,password,userType,user_first_name,user_last_name,#st,chargify_id,customer_id',
    'ExpressionAttributeNames'=> [ '#st' => 'status' ]
];

	try {
    while (true) {
        $result_db_users = $dynamodb->scan($params);

        
        foreach ($result_db_users['Items'] as $i) {
            $movie = $marshaler->unmarshalItem($i);
        }

        if (isset($result_db_users['LastEvaluatedKey'])) {
            $params['ExclusiveStartKey'] = $result_db_users['LastEvaluatedKey'];
            $result_db_users = $dynamodb->scan($params);
        } else {
            break;
        }
    }
} catch (DynamoDbException $e) {
    echo "Unable to scan USERS:\n";
    echo $e->getMessage() . "\n";
}

$params2 = [
    'TableName' => 'ursa-customers',
    'ProjectionExpression' => 'customer_id,customer_first_name,customer_last_name,
        customer_email,chargify_id,customer_salutation,customer_title,sale_date,
        sale_agent,sale_center,product_id,product_handle,product_name,product_component_id,
        product_component_name,product_component_quantity,product_coupon_code,product_coupon_name,
        business_email,business_name,business_phone_no,business_category,business_website,business_address,
        business_suite_no,business_city,business_state,business_zip,business_hours,
        business_post_address,payment_method,business_country,business_alternate_phone_no,customer_phone_no,
        customer_card_last_four,customer_card_expire_month,customer_card_expire_year,customer_billing_address,
        customer_suite_no,customer_billing_city,customer_billing_state,customer_billing_zip,prov_gmail,prov_keywords,
        prov_special_request,prov_existing_social1,prov_existing_social2,prov_biglo_website,prov_analytical_address,
        prov_google_plus,prov_google_maps,prov_facebook,prov_foursquare,prov_twitter,prov_linkedin,customer_alternate_email,
        customer_alternate_phone_no,customer_mobile_no
        '
];
try {
    while (true) {
        $result_db_customers = $dynamodb->scan($params2);

        
        foreach ($result_db_customers['Items'] as $i) {
            $movie = $marshaler->unmarshalItem($i);
        }

        if (isset($result_db_customers['LastEvaluatedKey'])) {
            $params2['ExclusiveStartKey'] = $result_db_customers['LastEvaluatedKey'];
            $result_db_customers = $dynamodb->scan($params2);
        } else {
            break;
        }
    }
} catch (DynamoDbException $e) {
    echo "Unable to scan CUSTOMERS:\n";
    echo $e->getMessage() . "\n";
}

//$days_ago = date('Y/m/d', mktime(0, 0, 0, date("m") , date("d") - 2, date("Y")))." 00:00:00";

$params3 = [
    'TableName' => 'ursa-logs',
    'ProjectionExpression' => 'user_id,customer_id,event,#data,#date',
    'ExpressionAttributeNames'=> [ '#data' => 'data','#date' => 'date' ]
    //'FilterExpression' => 'date >= '.$days_ago.''
];

    try {
    while (true) {
        $result_db_logs = $dynamodb->scan($params3);

        
        foreach ($result_db_logs['Items'] as $i) {
            $movie = $marshaler->unmarshalItem($i);
        }

        if (isset($result_db_logs['LastEvaluatedKey'])) {
            $params3['ExclusiveStartKey'] = $result_db_logs['LastEvaluatedKey'];
            $result_db_logs = $dynamodb->scan($params3);
        } else {
            break;
        }
    }
} catch (DynamoDbException $e) {
    echo "Unable to scan USERS:\n";
    echo $e->getMessage() . "\n";
}
?>