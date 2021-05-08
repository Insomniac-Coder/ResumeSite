<!DOCTYPE HTML>
<html> 
    <head>
        <link rel="stylesheet" href="background.css">
        <title>Ishtdeep's Website</title>
    </head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <body>
        <script type="text/javascript">
            function ShowHide(num, count)
            {
                var x = document.getElementById("div".concat(num.toString()));
                x.style.display = "block";

                for (var i = 1; i <= count; i++){
                    if ("div".concat(i.toString()) != "div".concat(num.toString())) {
                        x = document.getElementById("div".concat(i.toString()));
                        x.style.display = "none";
                    }
                }
            }
        </script>
        <?php

            function read_json($file, &$arr) {
                $jsonData = json_decode(file_get_contents($file), true);

                foreach ($jsonData as $key => $value) {
                    $arr[$key] = $value;
                }
            }

            $info = array(
                            'Name' => "",
                            'PicturePath' => "", 
                            'CurrentPosition' => "",
                            'CurrentCompany' => "",
                            'LinkedInLnk' => "#",
                            'GithubLnk' => "#",
                        );

            $tabInfo = array();
            
            read_json("./config.json", $info);
            read_json("./tab-config.json", $tabInfo);
            
            $marginPercentage = 1;

            echo '<div class="placard"><div id="holder" style="position: fixed; width: 55%; background-color: black;">';
                echo "<div style=\"margin-left: " . $marginPercentage . "%; margin-right: " . $marginPercentage . "%;\">";
                $i = 1;
                foreach ($tabInfo as $key => $value) {
                    echo "<button class=\"button\" onclick=\"ShowHide(" . $i . ", " . count($tabInfo) . ")\" style=\"width:" . (100 - (2 * $marginPercentage)) / count($tabInfo) . "%\">" . $key . "</button>";
                    $i++;
                }
                
                echo '</div></div>';
                echo '<div><br>';
                    $i = 1;
                    foreach($tabInfo as $key => $value) {
                        if( $tabInfo[$key]['ActiveState'] == true ) {
                            echo "<div id=\"div" . $i . "\">";
                                echo "<h1 style=\"text-align: center; font-size: 48px\">" . $tabInfo[$key]['Heading'] . "</h1>";
                                echo "<h3 style=\"margin-left: 20px\"><p>" . str_replace("\n", "<br>", $tabInfo[$key]['Content']) . "</p></h3>";
                            echo '</div>';
                        } else {
                            echo "<div id=\"div" . $i . "\" style=\"display:none;>";
                                echo "<h1 style=\"text-align: center; font-size: 48px\">" . $tabInfo[$key]['Heading'] . "</h1>";
                                echo "<h3 style=\"margin-left: 20px\"><p>" . str_replace("\n", "<br>", $tabInfo[$key]['Content']) . "</p></h3>";
                            echo '</div>';
                        }
                        $i++;
                    }
                echo '</div>';
            echo '</div>';

            echo '<div class="red-box"></div>';
            echo '<div class="info-box">';
                echo "<img src=\"" . $info['PicturePath'] . "\" alt=\"My Picture\" class=\"myimage\">";
                echo '<div class="profdetails">';
                    echo "<p>" . $info['Name'] . "</p>";
                    echo '<hr class="divide">';
                    echo "<p>" . $info['CurrentPosition'] . "</p>";
                    echo "<p>" . $info['CurrentCompany'] . "</p>";
                    echo '</div>';
                echo '<div class="social-box">';
                    echo "<a href=\"" . $info['LinkedInLnk'] . "\" target=\"_blank\" class=\"fa fa-linkedin\" style=\"position: absolute; left: 28%; line-height:inherit; top: -2%;\"></a>";
                    echo "<a href=\"" . $info['GithubLnk'] . "\" target=\"_blank\" class=\"fa fa-github-square\" style=\"position: absolute; left: 50%; line-height:inherit; top: -2%;\"></a>";
                echo '</div>';
            echo '</div>';

            echo "<button class=\"button\" style=\"width: auto; left: 85%; top: 92%; position: absolute;\" onclick=\"document.location='view.php'\">View Resume PDF</button>";
            echo "<button class=\"button\" style=\"width: auto; left: 85%; top: 2%; position: absolute;\" onclick=\"document.location='sendmessage.php'\">Send a message</button>";
        ?>
    </body>
</html>
