<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Gender definer 0.1</title>
    </head>
    <body>

<?php

createDB('data/prenoms-femmes.txt', 'f');
//createDB('data/prenoms-hommes.txt', 'm');

function connect($server = '127.0.0.1', $user = 'root', $password = '')
{
    // connect 
    $mysqli = new mysqli($server, $user, $password);
    if (mysqli_connect_errno()) {
        echo 'Connect failed: '. mysqli_connect_error();
        return false;
    }
    
    return $mysqli;
}



function createDB($file, $gender){
 
    $lines = file($file); 
    $count = count($lines);
    
    $count = min(100000, $count);
    
    // connect 
    $mysqli = connect();
    $mysqli->query("SET NAMES 'utf8'");
    
    
    // create db
    $sql = 'CREATE DATABASE IF NOT EXISTS `gender` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci';
    if ($mysqli->query($sql) === TRUE) {
        echo 'Database successfully created<br/>';
    }else {
        echo 'Error: '.$mysqli->error.'<br/>';
        die;
    }
    
    $mysqli->select_db('firstname');
    
    // create table
    $sql = "CREATE TABLE IF NOT EXISTS `firstname_list` (
     `id` INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
     `firstname` VARCHAR(25) NOT NULL,
     `gender` VARCHAR(25) NOT NULL
     ) CHARACTER SET utf8 COLLATE utf8_general_ci"; 
    if ($mysqli->query($sql) === TRUE) {
        echo 'Table successfully created<br/>';
    }else {
        echo 'Error: '.$mysqli->error.'<br/>';
        die;
    }
    
    for($i=0; $i<$count; $i++){
        set_time_limit(0);
        
        $name = trim($lines[$i]);
        
        $sql = sprintf('SELECT gender FROM `firstname_list` WHERE firstname="%s"', $name);
        $res = $mysqli->query($sql);
        if ($res === FALSE) {
            echo $sql;
            echo 'Error: '. $mysqli->error.'<br/>';
            die;
        }else{
            if($res->num_rows == 0){
                $sql = sprintf('INSERT INTO `firstname_list` (firstname, gender) VALUES ("%s","%s")', $name, $gender);
                if (!$mysqli->query($sql) === TRUE) {
                    echo $sql;
                    echo 'Error: '. $mysqli->error.'<br/>';
                    die;
                }
            }else if($res->num_rows > 1){
                echo 'Error: '. $name.' already in base '.$res->num_rows.' times <br/>';
            }else{
                $row = $res->fetch_object();
                
                if($row->gender != $gender){
                    echo 'Insert aborted: '. $name.' already in base with gender '.$row->gender.' instead of '.$gender.' <br/>';
                }else{
                    echo 'Insert aborted: '. $name.' already in base <br/>';
                    echo $sql.'<br/>';
                }
            }
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