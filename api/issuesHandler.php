<?php
    set_time_limit(0);
    /*
        ===========================================================
        1-Grid Technical Assessment by William Madede
        ===========================================================
        Author: William Madede
        Project: 1-Grid Technical Assessment

        You may not copy, alter or distribute this code without
        the express permission from the author.
        ===========================================================
    */

    /*
     *  This script will handle most server side operations such as Getting Issues, Creating New Issues and additional functions.
     * */

    if($_POST['type'] == "getIssues"){ //Get Issues
        $set_type = "1";
        echo getAPIData($set_type);
    }elseif($_POST['type'] == "getForm"){ //Get From -- choosing not to store these options in database because it's just 3 options per select..
        $client_options = "";
        $priority_options = "";
        $type_options = "";
        for($x=1; $x <= 3; $x++){
            //Client, Priority & Type Options
            if($x == 1){
                $client_options .= "<option value='C: Client ABC'>C: Client ABC</option>";
                $priority_options .= "<option value='P: Low'>P: Low</option>";
                $type_options .= "<option value='T: Bug'>T: Bug</option>";
            }elseif($x == 2){
                $client_options .= "<option value='C: Client MNO'>C: Client MNO</option>";
                $priority_options .= "<option value='P: Medium'>P: Medium</option>";
                $type_options .= "<option value='T: Support'>T: Support</option>";
            }else{
                $client_options .= "<option value='C: Client XYZ'>C: Client XYZ</option>";
                $priority_options .= "<option value='P: High'>P: High</option>";
                $type_options .= "<option value='T: Enhancement'>T: Enhancement</option>";
            };
        };

        //Build form for
        $form = "<table align='center' class='table table-hover table-center'>
                    <tr>
                        <td>Title</td>
                        <td><input type='text' id='issue_title' class='form-control'/></td>
                    </tr>
                    <tr>
                        <td>Description</td>
                        <td><textarea id='issue_description' cols='8' rows='5' class='form-control'></textarea></td>
                    </tr>
                    <tr>
                        <td>Choose Client</td>
                        <td><select id='client_name' class='form-control'>
                                <option value=''>Select</option>".$client_options."
                            </select>
                        </td>
                    <tr>
                    <tr>
                        <td>Choose Priority</td>
                        <td><select id='issue_priority' class='form-control'>
                                <option value=''>Select</option>".$priority_options."
                            </select>
                        </td>
                    <tr>
                    <tr>
                        <td>Choose Type</td>
                        <td><select id='issue_type' class='form-control'>
                                <option value=''>Select</option>".$type_options."
                            </select>
                        </td>
                    <tr>
                        <td colspan='2'><input type='button' style='width:100%; background-color: #fd7e14' class='btn btn-warning submit_issue' value='Create'/></td>
                    </tr>
                 </table>";
        echo $form;
    }elseif($_POST['type'] == "createIssue"){
        $title = stringCleaner($_POST['title']);
        $description = stringCleaner($_POST['description']);
        $client_name = stringCleaner($_POST['client_name']);
        $priority = stringCleaner($_POST['priority']);
        $issue_type = stringCleaner($_POST['issue_type']);

        if($title != '' AND $description != '' AND $client_name != '' AND $priority != '' AND $issue_type != ''){
        }else{
            die("1|Some of your fields are missing. Please recheck.");
        };

        $file_name = 'GitIntegration_Issues.csv'; //Will be writing new issues to file
        $fp = fopen("".$file_name, 'a'); //Using append instead of write to add instead of overwriting
        if($fp AND file_exists($file_name)) {
            $status = "open";
            $assigned_to = "";
            $date = date('Y-m-d H:i:s');
            $values = array($date,$title,$description,$client_name,$priority,$issue_type);
            $values = str_replace('"','', $values); //Remove the qoutes around the column values
            fputs($fp,implode(',',$values)."\n");

            fclose($fp); //Close stream
            echo getAPIData($type);
            die; //Killing script here because my createIssue method is not working properly. Currently writing issues to csv file.
            $set_type = "2";
            echo createIssue($set_type, $title, $description, $client_name, $priority, $issue_type);
        }else{
            die("1|Error on file stream");
        };
    }elseif($_GET['t'] == "downloadSC"){
        //Download source code
        $filename = '1-grid.assess.willato.co.za.zip';
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private', false); // required for certain browsers
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="'. basename($filename) . '";');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . filesize($filename));
        readfile($filename);
        die;
    }

    //Function to call GitHub API using curl
    function getAPIData($type){
        //URL API - Hardcoded here
        $agent = "1-grid";
        $repo = "GitIntegration";
        //GitHub API URL
        $url = "https://api.github.com/repos/".$agent."/".$repo."/issues";

        //Initialise curl
        $curl_init = curl_init();
        curl_setopt($curl_init, CURLOPT_URL,$url);
        //Get response back as json
        curl_setopt($curl_init, CURLOPT_HTTPHEADER, array( 'Accept: application/json'));
        //Set the User Agent as username
        curl_setopt($curl_init, CURLOPT_USERAGENT, "William");
        //Set curl to return response
        curl_setopt($curl_init, CURLOPT_RETURNTRANSFER, true);
        //Execute curl request
        $curl_response=curl_exec($curl_init);

        if ($curl_response === false) { //If response is false, get response info, but return 1 for this task
            $info = curl_getinfo($curl_init); //For this task I will not be using this variable.
            curl_close($curl_init);
            return 1;
        }else { //If response is not false, get data and build table rows
            curl_close($curl_init);

            $table = '';
            $x = 0;
            $result = json_decode($curl_response);
            foreach($result as $data){
                $x++;
                $title = $data->title;
                $description = $data->body;
                $assigned_to = $data->assignee->login;
                $status = $data->state;
                $labels = $data->labels;

                $table .= '<tr>';
                $table .= '<td>'.$x.'</td>';
                $table .= '<td>'.$title.'</td>';
                $table .= '<td>'.$description.'</td>';
                $table .= '<td>'.getLabels($labels, "C:").'</td>';
                $table .= '<td>'.getLabels($labels, "P:").'</td>';
                $table .= '<td>'.getLabels($labels, "T:").'</td>';
                $table .= '<td>'.$assigned_to.'</td>';
                $table .= '<td>'.strtoupper($status).'</td>';
                $table .= '</tr>';
            };
            return $x."|".$table; //Return $x as count for
        };
    };

    //Function to create issue - NOT WORKING
    function createIssue($type,$title, $description, $client, $priority, $issue_type){
        //URL API - Hardcoded here
        $agent = "1-grid";
        $repo = "GitIntegration";
        $client_id = "bf56b82110ed92bfc649";
        $client_secret = "7c10b630520202c13ee98a8551b19b3fb34adb54";
        $token = "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";
        //GitHub API URL
        $url = "https://api.github.com/repos/".$agent."/".$repo."/issues";

        echo $url."<br>";
        $labels = array("name" => $priority, "name" => $client, "name" => $issue_type);
        //Values to be posted
        $data = '{"title":"'.$title.'","body": "'.$description.'", "labels":"'.$labels.'"}';

        //file_get_contents headers
        $context = stream_context_create(array(
            'http' => array(
                'header' => "Authorization: Basic " . base64_encode("$client_id:$client_secret"),
            ),
        ));

        //Request
        $content = file_get_contents($url, false, $context);

        //Decode response
        $response_array = json_decode($content, true);

        //Get Issue number
        $number = $response_array['number'];
        if(is_numeric($number) AND $number > 0){
            return "0|".$number;
        }else{
            return "1|Error: ".var_export($content);
        };
    };

    //Function to get Labels from labels array and map to table.
    function getLabels($labels, $type){
        if(!empty($labels)){
            foreach($labels as $label){
                $label_name = $label->name;
                if (strpos($label_name, ':') !== false){
                    $label_prefix = substr($label_name, 0,2); //Get Prefix (first 2 chars of string)
                    $label_value = substr($label_name, 3); //Get priority name (from 4th char of initial label)
                    if($type == $label_prefix){ //If current label is for same column name
                        return $label_value;
                    };
                };
            };
        };
        return "NONE";
    };

    //Use this function to eliminate illegal characters from user input
    function stringCleaner($string){
        $a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'Ð', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'Œ', 'œ', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'Š', 'š', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Ÿ', 'Z', 'z', 'Z', 'z', 'Ž', 'ž', 'ƒ', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u','/\n','/\r');
        $b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i','J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u','','');
        $string = str_replace($a, $b, $string);
        return $string;
    };
exit;
?>