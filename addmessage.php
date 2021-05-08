<?php
    session_start();
    $user = $_SESSION['id']; 
    $msg = $_POST['MSG'];

    require './vendor/AWS/aws-autoloader.php';

    use Aws\DynamoDb\DynamoDbClient;

    $client = new DynamoDbClient([
        'version' => 'latest',
        'region' => 'ap-south-1',
    ]);

    $result = $client->scan([
        'ExpressionAttributeNames' => [
            '#CH' => 'chat',
        ],
        'ExpressionAttributeValues' => [
            ':u' => [
                'S' => $user,
            ],
        ],
        'FilterExpression' => 'userHash = :u',
        'ProjectionExpression' => '#CH',
        'TableName' => 'ChatHistory',
    ]);

    $messages = "";
    
    if (count($result['Items']) == 0) {
        while(count($result['Items']) == 0) {
            $result = $client->scan([
                'ExpressionAttributeNames' => [
                    '#CH' => 'chat',
                ],
                'ExpressionAttributeValues' => [
                    ':u' => [
                        'S' => $_SESSION['id'],
                    ],
                ],
                'FilterExpression' => 'userHash = :u',
                'ProjectionExpression' => '#CH',
                'TableName' => 'ChatHistory',
                'ExclusiveStartKey' => $result['LastEvaluatedKey'],
            ]);

            if(count($result['Items']) != 0) {
                if(isset($result['Items'][0]['chat'])) {
                    $messages = $result['Items'][0]['chat']['S'];
                }
            }
        }
    } else {
        if(isset($result['Items'][0]['chat'])) {
            $messages = $result['Items'][0]['chat']['S'];
        }
    }


    $msgjson = json_decode($messages, true);
    

    if(isset($msgjson['Messages'])) {
        array_push($msgjson['Messages'], array("Type" => "sent", "Msg" => $msg));
    }
    else {
        $msgjson['Messages'] = array(array("Type" => "sent", "Msg" => $msg));
    }

    $messages = json_encode($msgjson);

    $result = $client->updateItem([
        'ExpressionAttributeNames' => [
            '#M' => 'chat',
        ],
        'ExpressionAttributeValues' => [
            ':m' => [
                'S' => $messages,
            ],
        ],
        'Key' => [
            'userHash' => [
                'S' => $user,
            ],
        ],
        'ReturnValues' => 'ALL_NEW',
        'TableName' => 'ChatHistory',
        'UpdateExpression' => 'SET #M = :m',
    ]);
?>