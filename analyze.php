<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>GenderAnalys 0.1</title>
    </head>
    <body>

<?php

/*
 * @Author Jonas
 * 
 * DO NOT USE IN PRODUCTION : LOCALHOST STRONGLY RECOMMENDED
 * 
 */

//////////////////////////////////////////////
// CONFIG
//////////////////////////////////////////////

$mysql_server = '127.0.0.1';
$mysql_user = 'root';
$mysql_pass = '';
$mysql_db = 'firstname';

// Database to define
$mysql_target_table = 'firstname_test'; // The table to analyze
$mysql_target_firstname_field = 'firstname'; // The firstname field
$mysql_target_gender_field = 'gender'; // The result field

// Database of firstnames
$mysql_list_table = 'firstname_list'; // The list of firstname
$mysql_list_firstname_field = 'firstname'; // The result field
$mysql_list_gender_field = 'gender'; // The firstname field


$callInterval = '3'; // 3 secs between each call
$callMaxCheck = 100;
$callNum;


//////////////////////////////////////////////
//////////////////////////////////////////////
//////////////////////////////////////////////

session_start();

// connect
$mysqli = new mysqli($mysql_server, $mysql_user, $mysql_pass, $mysql_db);
if (mysqli_connect_errno()) {
    echo 'Connect failed: '. mysqli_connect_error();
    die;
}

// utf8
$mysqli->query("SET NAMES 'utf8'");

// Define total lines to analyze
$sql = sprintf('SELECT COUNT(*) AS total FROM %s', $mysql_target_table);
$res = $mysqli->query($sql);
if ($res === FALSE) {
    echo 'Error: '. $mysqli->error.'<br/>';
    echo $sql;
    die;
}else{
    $obj = $res->fetch_object();
    $total = $obj->total;
}

// Define distinct first names
$sql = sprintf('SELECT COUNT(DISTINCT %s) AS total FROM %s', $mysql_target_firstname_field, $mysql_target_table);
$res = $mysqli->query($sql);
if ($res === FALSE) {
    echo 'Error: '. $mysqli->error.'<br/>';
    echo $sql;
    die;
}else{
    $obj = $res->fetch_object();
    $distinct = $obj->total;
}

// Define index
if(isset($_GET['i'])){
    $index = $_GET['i']; 
}else{
    $index = 0;
    $_SESSION['found'] = 0;
    $_SESSION['affected'] = 0;
    $_SESSION['undefined'] = 0;
}

$callNum = ceil($total / $callMaxCheck);
$percent = round(($index+1)/$callNum*100, 1);
echo $total.' rows with '.$distinct.' distinct firstnames<br/>-----------------------------------------------------<br/>';
echo '<strong>Call '.($index+1).'/'.$callNum.' ('.$percent.'%)</strong><br/>-----------------------------------------------------';

$affected = 0;
$affected_rows = 0;
$undefined = 0;

$from = $index*$callMaxCheck;

// Get lines
$sql = sprintf('SELECT DISTINCT(%s) FROM %s LIMIT %s,%s', $mysql_target_firstname_field, $mysql_target_table, $from, $callMaxCheck);
$res = $mysqli->query($sql);
if ($res === FALSE) {
    echo 'Error: '. $mysqli->error.'<br/>';
    echo $sql;
    die;
}else{
    
    while ($obj = $res->fetch_object()) 
    {
        $search = trim($obj->$mysql_target_firstname_field);
        $search = str_replace(' ', '-', $search);
        $search = $mysqli->real_escape_string($search);
        
        $sql2 = sprintf("SELECT %s FROM %s WHERE %s='%s'", $mysql_list_gender_field, $mysql_list_table, $mysql_list_firstname_field, $search);
        $res2 = $mysqli->query($sql2);
        if ($res2 === FALSE) {
            echo 'Error: '. $mysqli->error.'<br/>';
            echo $sql2;
            die;
        }
        else
        {
            if($res2->num_rows == 1){
                $obj2 = $res2->fetch_object();
                
                $sql3 = sprintf('UPDATE %s SET %s = "%s" WHERE %s="%s"', $mysql_target_table, $mysql_target_gender_field, $obj2->$mysql_list_gender_field, $mysql_target_firstname_field, $search);
                if (!$mysqli->query($sql3) === TRUE) {
                    echo 'Error: '. $mysqli->error.'<br/>';
                    echo $sql3;
                    die;
                }else{
                   if($obj2->$mysql_list_gender_field == ''){
                       echo '<br/>UNDEFINED: '.$obj->firstname;
                       $undefined += 1;
                   }
                   $affected += 1;
                   $affected_rows = $affected_rows+$mysqli->affected_rows;
                }
                //echo 'FOUND: '.$obj->firstname.'<br/>';
            }else{
                echo '<br/>NOT FOUND: '.$obj->firstname;
            }
        }
    }
   
}

$_SESSION['found'] += $affected;
$_SESSION['undefined'] += $undefined;
$_SESSION['affected'] += $affected_rows;


echo '<br/>-----------------------------------------------------<br/>';
echo 'Found = '.$affected.'/'.$callMaxCheck.' ('.$undefined.' undefined)<br/>';
echo 'Affected = '.$affected_rows.'<br/>';
echo '<br/>-----------------------------------------------------<br/>';
echo '<strong>TOTAL</strong>';
echo '<br/>-----------------------------------------------------<br/>';
echo 'Found = '.$_SESSION['found'].'/'.$distinct.'<br/>';
echo 'Affected = '.$_SESSION['affected'].'/'.$total.'<br/>';

if($index == $callNum-1){
    echo 'Traitement complet <br/>';
}else{
    $page = $_SERVER['PHP_SELF'].'?i='.($index+1);
    header("Refresh: $callInterval; url=$page");
}

?>
</body>
</html>