<?php
//***** user **********
//*****
require_once('../system/config-user.php');

//collect the passed id
$id = $_GET['cat_id'];

//run a query 
$stmt = $DB_con->query('SELECT id,name FROM ' . PFX . 'subcat WHERE active = 1 AND cat_id = '.$DB_con->quote($id).' ORDER BY name');

//loop through all returned rows
while($row = $stmt->fetch(PDO::FETCH_OBJ)) {
    echo "<option value='$row->id'>$row->name</option>";
    //".($id == $row->id ? "selected" : )."
}

?>