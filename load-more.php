<?php
//add database configuration file
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

error_reporting(E_ALL);
require_once 'system/config-global.php';
require_once 'system/config-user.php';

//fetch data from database
/*$sql = "SELECT * FROM blog WHERE id < '".$_GET['last_id']."' ORDER BY id DESC LIMIT 8";
$result = mysqli_query($connection, $sql) or die("Error " . mysqli_error($connection));
include('blog-data.php'); */



//collect the passed id
$id = $_GET['last_id'];

//run a query 
$stmt = $DB_con->query('SELECT * FROM ' . PFX . 'products WHERE active = 1 AND last_id < '.$DB_con->quote($id));

//loop through all returned rows
while($row = $stmt->fetch(PDO::FETCH_OBJ)) {
    echo "<option value='$row->id'>$row->name</option>";
}
