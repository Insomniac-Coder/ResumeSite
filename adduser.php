<?php
require './vendor/AWS/aws-autoloader.php';

//var_dump($_POST);
use Aws\DynamoDb\DynamoDbClient;

$client = new DynamoDbClient([
    'version' => 'latest',
    'region' => 'ap-south-1',
]);

$client->putItem([
    'Item' => [
        'userHash' => [
            'S' => $_POST["unamefinal2"],
        ],
        'password' => [
            'S' => $_POST["pswfinal2"],
           ]
        ],
    'ReturnConsumedCapacity' => 'TOTAL',
    'TableName' => 'ChatHistory',
]);

echo "success!";
header("location: sendmessage.php?MSG=true");

?>