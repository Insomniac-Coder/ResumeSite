<!DOCTYPE HTML>
<html>
    <head>
        <link rel="stylesheet" href="background.css">
        <title>Send Message</title>
    </head>
    <script type="text/javascript">
            function ShowHide(show, hide)
            {
                var x = document.getElementById(show);
                x.style.display = "block";

                x = document.getElementById(hide);
                x.style.display = "none";

            }

    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.9-1/core.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.9-1/sha256.js"></script>

    <?php
            error_reporting(-1);
            ini_set('display_errors', 'On');
            require './vendor/AWS/aws-autoloader.php';

            if (!empty($_GET['MSG'])) {
                if($_GET['MSG'] == "true") {
                    echo "<body onload=\"status('User has been created, please login!')\">";
                }  
                if($_GET['MSG'] == "false") {
                    echo "<body onload=\"status('Incorrect login credentials provided, please try again!')\">";
                }     
            } else {
                echo "<body>";
            }

            echo '<script type="text/javascript">
                function status(msg) {
                    x = document.getElementById("warn");
                    x.innerHTML = msg;
                    x.style.display = "block";
                }
            </script>';
    
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
                array_push($userHashList, "\"" . $value['userHash']['S'] . "\"");
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
                    array_push($userHashList, "\"" . $value['userHash']['S'] . "\"");
                }
            }

            //var_dump($userHashList);

            echo '<script type="text/javascript">
                        function CheckAndSubmit()
                        {
                            var username = document.getElementById("uname2").value;
                            var pass1 = document.getElementById("psw2").value;
                            var pass2 = document.getElementById("psw3").value;
                            var error = false;
                            console.log(username);';
            echo            "var userlist = [" . join(", ", $userHashList) . "];";
            echo '          x = document.getElementById("warn");
                            if( userlist.includes(username) )
                            {
                                x.innerHTML = "UserName is Already taken."
                                x.style.display = "block";
                                error = true;
                            }
                            if(username == ""){
                                x.innerHTML = "Please enter a user name."
                                x.style.display = "block";
                                error = true;
                            }
                            if(pass1 == "" && error == false){
                                x.innerHTML = "Please enter a password."
                                x.style.display = "block";
                                error = true;
                            }
                            if(pass1.length < 8 && error == false){
                                x.innerHTML = "Enter a password with atleast 12 characters"
                                x.style.display = "block";
                                error = true;
                            }
                            if(pass2 == "" && error == false){
                                x.innerHTML = "Please re-enter the password."
                                x.style.display = "block";
                                error = true;
                            }
                            if(pass2 != pass1 && error == false){
                                x.innerHTML = "Passwords don\'t match."
                                x.style.display = "block";
                                error = true;
                            }

                            if(error == false) {
                                console.log("no error found, proceeding!")
                                x.style.display = "none";
                                document.getElementById("unamefinal2").value = username;
                                document.getElementById("pswfinal2").value = CryptoJS.SHA256(pass1).toString();
                                document.getElementById("finalForm2").submit();
                            }
                        }
                 </script>';
            
                 echo '<script type="text/javascript">
                             function LoginVerify()
                             {
                                 var username = document.getElementById("uname1").value;
                                 var pass1 = document.getElementById("psw1").value;

                                 var error = false;
                                 console.log(username);';
                 echo            "var userlist = [" . join(", ", $userHashList) . "];";
                 echo '          x = document.getElementById("warn");
                                 if( !userlist.includes(username) )
                                 {
                                     x.innerHTML = "Username is not registered."
                                     x.style.display = "block";
                                     error = true;
                                 }
                                 if(username == ""){
                                     x.innerHTML = "Please enter a user name."
                                     x.style.display = "block";
                                     error = true;
                                 }
                                 if(pass1 == "" && error == false){
                                     x.innerHTML = "Please enter a password."
                                     x.style.display = "block";
                                     error = true;
                                 }
                                 
                                 if(error == false) {
                                     console.log("no error found, proceeding!")
                                     x.style.display = "none";
                                     document.getElementById("unamefinal1").value = username;
                                     document.getElementById("pswfinal1").value = CryptoJS.SHA256(pass1).toString();
                                     document.getElementById("finalForm1").submit();
                                 }
                             }
                      </script>';
        ?>

    <div class="red-box"></div>';
    <div class="message-box" >
        <div class="instruction-box">
            <p style="margin-left: 20px; margin-right: 20px">
                <strong>Note: To send a message just create an account by picking a username and a password, I've added a small messaging system to make thing convenient, if you already have an account just login to access the conversation!
                </strong>
            </p>
        </div>
        <div class="details-box">
            <div id= "div1" style="display: block">
                <button class="button" style="width: 200px; left: 50%; top: 40%; position: absolute; transform: translate(-50%, -50%);" onclick="ShowHide('login', 'div1')">Login</button>
                <button class="button" style="width: 200px; left: 50%; top: 55%; position: absolute; transform: translate(-50%, -50%);" onclick="ShowHide('signup', 'div1')">Sign Up</button>
            </div>
            <div id= "login" style="display: none">
                <div style= "top: 40%; left: 50%; position: absolute; transform: translate(-50%, -50%);">
                    <form id="finalForm1" action="/verify.php" method="POST" style="display: none">
                        <input type="text" name="unamefinal1" id="unamefinal1" required>
                        <input type="text" name="pswfinal1" id="pswfinal1" required>
                    </form>
                    <label for="uname"><b>Username</b></label>
                    <input type="text" placeholder="Enter Username" name="uname1" id="uname1" required>
                    <br>
                    <label for="psw"><b>Password</b></label>
                    <input type="password" placeholder="Enter Password" name="psw1" id="psw1" required>
                </div>
                <div>
                    <button class="button" style="width: auto; left: 38%; top: 80%; position: absolute; transform: translate(-50%, -50%);" onclick="LoginVerify()">Login</button>
                    <button class="button" style="width: auto; left: 60%; top: 80%; position: absolute; transform: translate(-50%, -50%);" onclick="ShowHide('div1', 'login')">Go Back</button>
                </div>
            </div>
            <div id= "signup" style="display: none">
                <div style= "top: 40%; left: 50%; position: absolute; transform: translate(-50%, -50%);">
                    <form id="finalForm2" action="/adduser.php" method="POST" style="display: none">
                        <input type="text" name="unamefinal2" id="unamefinal2" required>
                        <input type="text" name="pswfinal2" id="pswfinal2" required>
                    </form>
                    <label for="uname"><b>Username</b></label>
                    <input type="text" placeholder="Enter Username" name="uname2" id="uname2" required>
                    <br>
                    <label for="psw"><b>Enter password</b></label>
                    <input type="password" placeholder="Enter Password" name="psw2" id="psw2" required>
                    <br>
                    <label for="psw"><b>Re-enter password</b></label>
                    <input type="password" placeholder="Enter Password" name="psw3" id="psw3" required>
                </div>
                <div>
                    <button class="button" style="width: auto; left: 38%; top: 85%; position: absolute; transform: translate(-50%, -50%);" onclick="CheckAndSubmit()">Sign Up</button>
                    <button class="button" style="width: auto; left: 60%; top: 85%; position: absolute; transform: translate(-50%, -50%);" onclick="ShowHide('div1', 'signup')">Go Back</button>
                </div>
            </div>
            <p id= "warn" style="color: red; display: none;">message</p>
        </div>
      </div>
    echo "<button class="button" style="width: auto; left: 85%; top: 92%; position: absolute;" onclick="document.location='index.php'">Go Back</button>";
    </body>
</html>