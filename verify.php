<?php

require './vendor/AWS/aws-autoloader.php';

//var_dump($_POST);
use Aws\DynamoDb\DynamoDbClient;

$client = new DynamoDbClient([
    'version' => 'latest',
    'region' => 'ap-south-1',
]);

$result = $client->scan([
    'TableName' => 'ChatHistory',
]);

$userHashList = array();

foreach ($result['Items'] as $key => $value) {
    $userHashList[$value['userHash']['S']]= $value['password']['S'];
}

function read_json($file, &$arr) {
    $jsonData = json_decode(file_get_contents($file), true);

    foreach ($jsonData as $key => $value) {
        $arr[$key] = $value;
    }
}

while ($result['LastEvaluatedKey'] != NULL) {
    $result = $client->scan([
        'ExpressionAttributeNames' => [
            '#u' => 'userHash',
        ],
        'TableName' => 'ChatHistory',
        'ExclusiveStartKey' => $result['LastEvaluatedKey'],
    ]);

    foreach ($result['Items'] as $key => $value) {
        $userHashList[$value['userHash']['S']]= $value['password']['S'];
    }
}

if($userHashList[$_POST['unamefinal1']] == $_POST['pswfinal1']) {
    $userdetails = array();
    
    read_json("./rootuser.json", $userdetails);

    echo "Login success!";
    session_start();
    if($_POST['unamefinal1'] != $userdetails['userid']) {
        $_SESSION['id'] = $_POST['unamefinal1'];
        header("location: messenger.php");
    } else {
        $_SESSION['id'] = $_POST['unamefinal1'];
        header("location: adminmessenger.php");
    }
} else {
    echo "Login failed!";
    header("location: sendmessage.php?MSG=false");
}

?>