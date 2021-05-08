<?php
    session_start();
    $msg = $_POST['MSG'];
    $usr = $_POST['USR'];

    require './vendor/AWS/aws-autoloader.php';

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
        array_push($userHashList, $value['userHash']['S']);
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
            array_push($userHashList, $value['userHash']['S']);
        }
    }

    $result = $client->scan([
        'ExpressionAttributeNames' => [
            '#CH' => 'chat',
        ],
        'ExpressionAttributeValues' => [
            ':u' => [
                'S' => $usr,
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
                        'S' => $usr,
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
        array_push($msgjson['Messages'], array("Type" => "received", "Msg" => $msg));
    }
    else {
        $msgjson['Messages'] = array(array("Type" => "received", "Msg" => $msg));
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
                'S' => $usr,
            ],
        ],
        'ReturnValues' => 'ALL_NEW',
        'TableName' => 'ChatHistory',
        'UpdateExpression' => 'SET #M = :m',
    ]);
?>