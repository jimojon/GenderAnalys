<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Gender definer 0.1</title>
    </head>
    <body>

<?php

//updateDB('data/mixtes.txt', '');
//updateDB('data/mixtes_h2.txt', 'm');
updateDB('data/mixtes_f.txt', 'f');
//createDB('data/prenoms-hommes.txt', 'm');

function connect($server = '127.0.0.1', $user = 'root', $password = '')
{
    // connect 
    $mysqli = new mysqli($server, $user, $password, 'firstname');
    if (mysqli_connect_errno()) {
        echo 'Connect failed: '. mysqli_connect_error();
        return false;
    }
    
    return $mysqli;
}

function updateDB($file, $gender){
 
    $lines = file($file); 
    $count = count($lines);
    $count = min(10000, $count);
    
    // connect 
    $mysqli = connect();
    $mysqli->query("SET NAMES 'utf8'");
  
    for($i=0; $i<$count; $i++){
        set_time_limit(0);
        $sql = sprintf('UPDATE `firstname_list` SET gender = "%s" WHERE firstname="%s"', $gender, trim($lines[$i]));
        $req = $mysqli->query($sql);
        if ($req === FALSE) {
            echo $sql.'<br/>';
            echo 'Error: '. $mysqli->error.'<br/>';
            die;
        }else{
            echo 'Updated '.$mysqli->affected_rows.'<br/>';
        }
    }
    
    $mysqli->close();
}

function print_a($a){
    echo '<pre>'.print_r($a, true).'</pre>';
}

?>
</body>
</html>