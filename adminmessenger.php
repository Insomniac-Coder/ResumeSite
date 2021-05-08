<?php
    session_start();
    if(!isset($_SESSION['id'])){
        header("location: sendmessage.php");
    }
?>
<!DOCTYPE HTML>
<html> 
    <head>
        <link rel="stylesheet" href="background.css">
        <title>Admin Messenger</title>
    </head>
    <style>

    .button2 {
        background-color: #c70000;
        border: none;
        color: white;
        padding: 15px 10px;
        text-align: center;
        font-family: space-font;
        font-size: 0.8vw;
        margin: 4px 2px;
        cursor: pointer;
        width: 89%;
        height: 10%;
        left: 10px;
    }

    .button2:hover {
        opacity: 0.7;
    }

    .grid-container {
      display: none;
      grid-template-columns:  auto auto auto auto auto auto auto auto;
      background-color: #383838;
      border: 2px solid red;
      padding: 10px;
      box-shadow: 6px 6px 20px black;
      width: 400px;
      height: 400px;
      overflow: auto;
    }
    .grid-item {
      background-color: #383838;
      text-align: center;
    }

    .msg-container
    {
        position:relative;
        width: auto;
        height: auto;
        margin: 10px auto;
    }

    p {
        margin-left: 12px;
        margin-right: 12px;
        font-weight: bold;
    }

    .sent {
        background-color: #c70000;
        transform: translate(68%, 0%);
        width: 600px;
        height: auto;
        min-height: 50px;
        margin: 5px auto;
        border: 2px solid black;
        color: black;
        text-align: left;
        word-break: break-word;
    }

    .receive {
        background-color: white;
        transform: translate(-68%, 0%);
        width: 600px;
        height: auto;
        min-height: 50px;
        margin: 5px auto;
        border: 2px solid black;
        color: black;
        text-align: left;
        word-break: break-word;
    }
    .emote {
        background-color: #383838;
        border: none;
        color: white;
        text-align: center;
        font-family: space-font;
        display: inline-block;

        width: auto;
        height: auto;
    }
    .emote:hover{
        background-color: #c70000;
    }
    </style>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <script type="text/javascript">
        var activeusr = "";

        function emoteShowHide() {
            var x = document.getElementById("emotebox");

            if(x.style.display === "grid") {
                x.style.display = "none";
            } else {
                x.style.display = "grid";
            }
        }

        function ifVisible() {
            var x = document.getElementById("emotebox");

            if(x.style.display === "grid") {
                emoteShowHide();
            }
        }

        function addEmote(emote) {
            var x = document.getElementById("msg");

            x.value = x.value.concat(emote);
        }

        function updateScroll(){
            var element = document.getElementById("chat");
            element.scrollTop = element.scrollHeight;
            setValue();
            console.log(activeusr);
        }

        function messageadd() {
            var x = document.getElementById("msg");

            var arr = document.querySelectorAll('[id^="msg"]');

            var required;

            for (var i=0; i < arr.length; i++) {
                var l = arr[i].id.charAt(arr[i].id.length-1);
                console.log(arr[i], l, arr[i].style.display);
                if(!isNaN(l) && arr[i].style.display == "block") {
                    required = arr[i];
                    break;
                }
            }

            var nomsg = document.getElementById(activeusr);

            if(nomsg) {
                nomsg.style.display = "none";
            }

            if(x.value != "") {
                var new_row = document.createElement('div');
                new_row.className = "sent";

                var new_content = document.createElement('p');
                new_content.appendChild(document.createTextNode(x.value));
                
                new_row.appendChild(new_content);

                required.appendChild(new_row);
                x.value = "";
            }
        }

        function ShowHide(num, count)
        {
            var x = document.getElementById("msg".concat(num.toString()));
            x.style.display = "block";

            var button = document.getElementById("btn".concat(num.toString()));

            activeusr = button.innerHTML.split(" ").slice(0, button.innerHTML.split(" ").length - 1).join(" ");
            console.log(activeusr);

            for (var i = 1; i <= count; i++){
                if ("msg".concat(i.toString()) != "msg".concat(num.toString())) {
                    x = document.getElementById("msg".concat(i.toString()));
                    x.style.display = "none";
                }
            }
        }

        $(document).ready(function(){
            $('#sendbutton').click(function(){
                var x = document.getElementById("msg");
                console.log(activeusr);
                if(x.value != "") {
                    $.post("addmessageadmin.php",
                            {
                              MSG: document.getElementById("msg").value,
                              USR: activeusr,
                            }
                    );
                }
                messageadd();
                updateScroll();
                ifVisible();
            });
        });
    </script>
    <body onload="updateScroll()">  
            <?php
            echo "<h3 style=\"text-align: center; position: absolute; left: 50%; transform: translate(-50%, 0%);\">Hello " . $_SESSION['id'] . "!</h3>";
            ?>
            <div class="red-box"></div>
            <div class="placard" style="width: 92%; left: 4%; border: none;">
                <div class="placard" style="width: 15%; height: 90%; top: 5%; left: 2%; position: relative; background-color: #383838; border: 2px solid #c70000; overflow: hidden;">
                    <?php
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
                            if(isset($value['chat'])) {
                                $userHashList[$value['userHash']['S']] = count(json_decode($value['chat']['S'], true)['Messages']);
                            } else {
                                $userHashList[$value['userHash']['S']] = 0;
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
                                if(isset($value['chat'])) {
                                    $userHashList[$value['userHash']['S']] = count(json_decode($value['chat']['S'], true)['Messages']);
                                } else {
                                    $userHashList[$value['userHash']['S']] = 0;
                                }
                            }
                        }
                        $i = 1;
                        foreach ($userHashList as $key => $value) {
                            if ($key != "Ishtdeep96") {
                                echo "<button class=\"button2\" id=\"btn" . $i .  "\" onclick=\"ShowHide(" . $i . ", " . (count($userHashList) - 1)  . ")\">" . $key . " (" . $value . ")" . "</button><br>";
                                $i++;
                            }
                        }
                    ?>
                </div>
                <div class="placard" style="width: 81.69%; height: 90%; top: 5%; left: 15.81%; position: absolute; background-color: #383838; border: 2px solid #c70000; overflow: hidden;">
                    <div class="msg-container" id="chat" style="height: 570px; overflow: auto;">
                        <?php
                            error_reporting(-1);
                            ini_set('display_errors', 'On');

                            $client = new DynamoDbClient([
                                'version' => 'latest',
                                'region' => 'ap-south-1',
                            ]);
                            
                            $result = $client->scan([
                                'TableName' => 'ChatHistory',
                            ]);

                            $messagesarray = array();

                            foreach($result['Items'] as $key => $value) {
                                if(isset($value['chat'])) {
                                    $messagesarray[$value['userHash']['S']] = $value['chat']['S'];
                                } else {
                                    $messagesarray[$value['userHash']['S']] = NULL;
                                }
                            }
                            

                            while ($result['LastEvaluatedKey'] != NULL) {
                                $result = $client->scan([
                                    'TableName' => 'ChatHistory',
                                    'ExclusiveStartKey' => $result['LastEvaluatedKey'],
                                ]);

                                foreach($result['Items'] as $key => $value) {
                                    if(isset($value['chat'])) {
                                        $messagesarray[$value['userHash']['S']] = $value['chat']['S'];
                                    } else {
                                        $messagesarray[$value['userHash']['S']] = NULL;
                                    }
                                }

                            }
 

                            //var_dump($msgjson);

                            $i = 1;

                            foreach ($messagesarray as $key => $messages) {

                                if($key != "Ishtdeep96") {
                                    if($i == 1) {
                                        echo "<div id= \"msg" . $i . "\" style=\"display: block;\">";
                                        echo "<script> function setValue() { activeusr=\"" . $key . "\"; } </script>";
                                    } else {
                                        echo "<div id= \"msg" . $i . "\" style=\"display: none;\">";
                                    }

                                    if($messages != NULL) {
                                        $msgjson = json_decode($messages, true);

                                        foreach($msgjson['Messages'] as $key => $value) {
                                            if($value['Type'] == "sent") {
                                                echo "<div class=\"receive\"><p>" . $value['Msg'] . "</p></div>";
                                            }
                                            if ($value['Type'] == "received") {
                                                echo "<div class=\"sent\"><p>" . $value['Msg'] . "</p></div>";
                                            }
                                        }
                                    } else {
                                        echo "<p style=\"text-align: center;\" id= \"" . $key . "\">No Chat History!</p>";
                                    }
                                    $i++;
                                    echo '</div>';
                                }
                            }
                            
                        ?>
                    </div>
                    <div style= "position: fixed; top: 80%; width: 72.2%;">    
                        <input type="text" placeholder="Type message..." name="msg" id="msg" style= "width: 92%; position: absolute; left: 0.5%; border: 2px solid black;" required>
                        <button class="button" id="sendbutton" style="width: 100px; height: 50px; position: absolute; left: 96.2%" >Send</button>
                        <button class="button" style="width: 3.5%; height: 50px; position: absolute; left: 92.5%; padding-top: 2px; padding-left: 4px;" onclick="emoteShowHide()"><span style='font-size:1.5vw;'>&#128512;</span></button>
                        <div class="grid-container" id= "emotebox" style= "transform: translate(225%, -100%);">
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128512;')"><p style="font-size: 20px">&#128512;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128513;')"><p style="font-size: 20px">&#128513;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128514;')"><p style="font-size: 20px">&#128514;</p></button></div>  
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128515;')"><p style="font-size: 20px">&#128515;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128516;')"><p style="font-size: 20px">&#128516;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128517;')"><p style="font-size: 20px">&#128517;</p></button></div>  
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128518;')"><p style="font-size: 20px">&#128518;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128519;')"><p style="font-size: 20px">&#128519;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128520;')"><p style="font-size: 20px">&#128520;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128521;')"><p style="font-size: 20px">&#128521;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128522;')"><p style="font-size: 20px">&#128522;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128523;')"><p style="font-size: 20px">&#128523;</p></button></div>  
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128524;')"><p style="font-size: 20px">&#128524;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128525;')"><p style="font-size: 20px">&#128525;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128526;')"><p style="font-size: 20px">&#128526;</p></button></div>  
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128527;')"><p style="font-size: 20px">&#128527;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128528;')"><p style="font-size: 20px">&#128528;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128529;')"><p style="font-size: 20px">&#128529;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128530;')"><p style="font-size: 20px">&#128530;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128531;')"><p style="font-size: 20px">&#128531;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128532;')"><p style="font-size: 20px">&#128532;</p></button></div>  
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128533;')"><p style="font-size: 20px">&#128533;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128534;')"><p style="font-size: 20px">&#128534;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128535;')"><p style="font-size: 20px">&#128535;</p></button></div>  
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128536;')"><p style="font-size: 20px">&#128536;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128537;')"><p style="font-size: 20px">&#128537;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128538;')"><p style="font-size: 20px">&#128538;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128539;')"><p style="font-size: 20px">&#128539;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128540;')"><p style="font-size: 20px">&#128540;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128541;')"><p style="font-size: 20px">&#128541;</p></button></div>  
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128542;')"><p style="font-size: 20px">&#128542;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128543;')"><p style="font-size: 20px">&#128543;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128544;')"><p style="font-size: 20px">&#128544;</p></button></div>  
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128545;')"><p style="font-size: 20px">&#128545;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128546;')"><p style="font-size: 20px">&#128546;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128547;')"><p style="font-size: 20px">&#128547;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128548;')"><p style="font-size: 20px">&#128548;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128549;')"><p style="font-size: 20px">&#128549;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128550;')"><p style="font-size: 20px">&#128550;</p></button></div>  
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128551;')"><p style="font-size: 20px">&#128551;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128552;')"><p style="font-size: 20px">&#128552;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128553;')"><p style="font-size: 20px">&#128553;</p></button></div>  
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128554;')"><p style="font-size: 20px">&#128554;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128555;')"><p style="font-size: 20px">&#128555;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128556;')"><p style="font-size: 20px">&#128556;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128557;')"><p style="font-size: 20px">&#128557;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128558;')"><p style="font-size: 20px">&#128558;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128559;')"><p style="font-size: 20px">&#128559;</p></button></div>  
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128560;')"><p style="font-size: 20px">&#128560;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128561;')"><p style="font-size: 20px">&#128561;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128562;')"><p style="font-size: 20px">&#128562;</p></button></div>  
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128563;')"><p style="font-size: 20px">&#128563;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128564;')"><p style="font-size: 20px">&#128564;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128565;')"><p style="font-size: 20px">&#128565;</p></button></div>  
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128566;')"><p style="font-size: 20px">&#128566;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128567;')"><p style="font-size: 20px">&#128567;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128577;')"><p style="font-size: 20px">&#128577;</p></button></div>  
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128578;')"><p style="font-size: 20px">&#128578;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128579;')"><p style="font-size: 20px">&#128579;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#128580;')"><p style="font-size: 20px">&#128580;</p></button></div>  
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#129296;')"><p style="font-size: 20px">&#129296;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#129297;')"><p style="font-size: 20px">&#129297;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#129298;')"><p style="font-size: 20px">&#129298;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#129299;')"><p style="font-size: 20px">&#129299;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#129300;')"><p style="font-size: 20px">&#129300;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#129301;')"><p style="font-size: 20px">&#129301;</p></button></div>  
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#129312;')"><p style="font-size: 20px">&#129312;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#129313;')"><p style="font-size: 20px">&#129313;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#129314;')"><p style="font-size: 20px">&#129314;</p></button></div>  
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#129315;')"><p style="font-size: 20px">&#129315;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#129316;')"><p style="font-size: 20px">&#129316;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#129317;')"><p style="font-size: 20px">&#129317;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#129319;')"><p style="font-size: 20px">&#129319;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#129320;')"><p style="font-size: 20px">&#129320;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#129321;')"><p style="font-size: 20px">&#129321;</p></button></div>  
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#129322;')"><p style="font-size: 20px">&#129322;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#129323;')"><p style="font-size: 20px">&#129323;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#129324;')"><p style="font-size: 20px">&#129324;</p></button></div>  
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#129325;')"><p style="font-size: 20px">&#129325;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#129326;')"><p style="font-size: 20px">&#129326;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#129327;')"><p style="font-size: 20px">&#129327;</p></button></div>
                          <div class="grid-item"><button class="emote" onclick="addEmote('&#129488;')"><p style="font-size: 20px">&#129488;</p></button></div>
                        </div>
                    </div>
                </div> 
              </div>
            <button class="button" style="width: auto; left: 85%; top: 2%; position: absolute;" onclick="document.location='logout.php'">Logout</button>
    </body>
</html>