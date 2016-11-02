<?php
require '../../../DynamoDB/dbCredentials.php';
require '../vendor/autoload.php';
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

$params2 = [
    'TableName' => 'ursa-customers',
    'ProjectionExpression' => 'customer_first_name,customer_last_name,chargify_id,
        business_email,business_name,keywords
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

print_r(json_encode($result_db_customers['Items']));
?>