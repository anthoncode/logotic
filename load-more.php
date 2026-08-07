<?php
//add database configuration file
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

error_reporting(E_ALL);
require_once 'system/config-global.php';
require_once 'system/config-user.php';


//collect the passed id
$id = $_GET['last_id'];

//run a query 
$stmt = $DB_con->query('SELECT * FROM ' . PFX . 'products WHERE status = "approved" AND last_id < '.$DB_con->quote($id));

//loop through all returned rows
while($row = $stmt->fetch(PDO::FETCH_OBJ)) {
    echo "<option value='$row->id'>$row->name</option>";
}