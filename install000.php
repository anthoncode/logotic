<!DOCTYPE html>
<html>
<head>
<title>Digital Sell Pro - Installation</title>
 <link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.2.11/semantic.min.css">
<script src="//ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.2.11/semantic.min.js"></script>
</head>
<?php
$step = (isset($_GET['step']) && $_GET['step'] != '') ? $_GET['step'] : '';
switch($step){
  case '1':
  step_1();
  break;
  case '2':
  step_2();
  break;
  case '3':
  step_3();
  break;
  case '4':
  step_4();
  break;
  case '5':
  step_5();
  break;
  case '6':
  step_6();
  break;
  default:
  step_1();
}
?>
<body>
<?php
function step_1(){ 
?>
<div class="ui text container">

   <div class="ui middle aligned center aligned grid">
  <div class="column">
   <div class="ui segments">
      <div class="ui inverted green segment">
          
<div class="ui small breadcrumb">
  <div class="section active">Begin</div>
  <i class="right chevron icon divider"></i>
  <div class="section">License</div>
    <i class="right chevron icon divider"></i>
  <div class="section">Requirements</div>
  <i class="right chevron icon divider"></i>
  <div class="section">Purchase Code</div>
    <i class="right chevron icon divider"></i>
  <div class="section">Database Details</div>
    <i class="right chevron icon divider"></i>
  <div class="section">Finish</div>
</div>
<div class="ui divider hidden">
</div>
<h1>Digital Sell Pro Install</h2>
<p>Thanks for purchasing Digital Sell Pro</p>
<p><a href="install.php?step=2" class="ui right labeled icon button">
  <i class="right arrow icon"></i>
  Begin Installation
</a></p>
<div class="ui divider hidden">
</div>
      </div>
      <div class="ui stacked secondary segment">
    <p>&copy ChewiScripts 2018</p>
  </div>
</div>

  </div>
</div>

</div>
<?php 
}
function step_2(){ 
 if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agree'])){
  $ag1 = $ag+1;
  echo'<script>window.location = "install.php?step=3"</script>';
  exit;
 }
 if($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['agree'])){
  echo '<div class="ui error attached message">You must agree to the license in order to continue!</div>';
 }
?>
<div class="ui text container">

   <div class="ui middle aligned center aligned grid">
  <div class="column">
   <div class="ui segments">
      <div class="ui inverted green segment">
          
<div class="ui small breadcrumb">
  <div class="section active">Begin</div>
  <i class="right chevron icon divider"></i>
  <div class="section active">License</div>
    <i class="right chevron icon divider"></i>
  <div class="section">Requirements</div>
  <i class="right chevron icon divider"></i>
  <div class="section">Purchase Code</div>
    <i class="right chevron icon divider"></i>
  <div class="section">Database Details</div>
    <i class="right chevron icon divider"></i>
  <div class="section">Finish</div>
</div>
<div class="ui divider hidden">
</div>
<h1>Digital Sell Pro License</h2>
<p>Please agree to the license you purchased below to continue!</p>
<p> <form action="install.php?step=2" class="ui form" method="post">
    <div style="height:200px;width:100%;border:2px solid #ccc; border-radius: 2px;padding: 20px;overflow:auto; text-align: left;background: #fff;">
        <h3 class="ui header">
 Regular License
  <div class="sub header">A regular license allows an item to be used in one project for either personal or commercial use by you or on behalf of a client. The item cannot be offered for resale either on its own or as part of a project. Distribution of source files is not permitted.</div>
</h3>
<div class="ui message">
  <div class="header">
    Can I share or resell this script?
  </div>
  <p>No. You must not resell or in any way redistribute Digital Sell Pro!</p>
</div>
<div class="ui message">
  <div class="header">
   How many domains can I use this script on?
  </div>
  <p>As many as you like as long as you own the domain!</p>
</div>
<div class="ui divider hidden"></div>
        <h3 class="ui header">
 Extended License
  <div class="sub header">An extended license allows an item to be used in unlimited projects for either personal or commercial use. The item cannot be offered for resale "as-is". It is allowed to distribute/sublicense the source files as part of a larger project.</div>
</h3>
<div class="ui message">
  <div class="header">
    Can I share or resell this script?
  </div>
  <p>No. You must not resell or in any way redistribute Digital Sell Pro! "as-is"</p>
</div>
<div class="ui message">
  <div class="header">
    Can I distribute/sublicense source files to use in a larger project?
  </div>
  <p>Yes, but let us know also! We'd love to hear how you used our script.</p>
</div>
<div class="ui message">
  <div class="header">
   How many domains can I use this script on?
  </div>
  <p>As many as you like!</p>
</div>
<div class="ui divider hidden"></div>
</div>
<br>
  <div class="ui checkbox">
  <input name="agree" type="checkbox">
  <label style="color: #fff;">I agree to the license that I purchased!</label>
</div>
<br><br>
<a href="install.php?step=1" class="ui left labeled icon button">
  <i class="left arrow icon"></i>
  Previous
</a>
  <button type="submit" class="ui right labeled icon button" value="Continue"><i class="right arrow icon"></i>Continue</button>
 </form></p>
<div class="ui divider hidden">
</div>
      </div>
      <div class="ui stacked secondary segment">
    <p>&copy ChewiScripts 2018</p>
  </div>
</div>

  </div>
</div>

</div>
<?php 
}
function step_3(){
  if($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['pre_error'] ==''){
   echo'<script>window.location = "install.php?step=4"</script>';
   exit;
  }
  if($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['pre_error'] != '')
   echo $_POST['pre_error'];
      
  if (phpversion() < '5.6') {
   $pre_error = '<div class="ui error attached message">You need to use PHP 5.6 or above in order to use Digital Sell Pro!</div>';
  }
  if (!extension_loaded('pdo')) {
   $pre_error .= '<div class="ui error attached message">You need to have the PDO extention activated in order to use Digital Sell Pro!</div>';
  }
  if (!is_writable('system/db.php')) {
   $pre_error .= '<div class="ui error attached message">db.php needs to be writable for our site to be installed!</div>';
  }
  ?>
  <div class="ui text container">
      
   <div class="ui middle aligned center aligned grid">
  <div class="column">
   <div class="ui segments">
      <div class="ui inverted green segment">
<div class="ui small breadcrumb">
  <div class="section active">Begin</div>
  <i class="right chevron icon divider"></i>
  <div class="section active">License</div>
    <i class="right chevron icon divider"></i>
  <div class="section active">Requirements</div>
  <i class="right chevron icon divider"></i>
  <div class="section">Purchase Code</div>
    <i class="right chevron icon divider"></i>
  <div class="section">Database Details</div>
    <i class="right chevron icon divider"></i>
  <div class="section">Finish</div>
</div>
<div class="ui divider hidden">
</div>
<h1>Digital Sell Pro Requirements</h2>
<p>You must have to following in order to continue!</p>
<p>
     <table class="ui blue definition table">
           <thead>
    <tr><th></th>
    <th>Current</th>
    <th>Required</th>
    <th>Status</th>
  </tr></thead>
  <tr>
   <td>PHP Version:</td>
   <td><div class="ui blue horizontal label"><?php echo phpversion(); ?></div></td>
   <td><div class="ui horizontal label">PHP 5.6+</div></td>
   <td><?php echo (phpversion() >= '5.6') ? '<div class="ui green horizontal label">Ok</div>' : '<div class="ui red horizontal label">Fail</div>'; ?></td>
  </tr>
  <tr>
   <td>PDO Extention:</td>
   <td><?php echo extension_loaded('pdo') ? '<div class="ui blue horizontal label">On</div>' : '<div class="ui red horizontal label">Off</div>'; ?></td>
   <td><div class="ui horizontal label">On</div></td>
   <td><?php echo extension_loaded('pdo') ? '<div class="ui green horizontal label">Ok</div>' : '<div class="ui red horizontal label">Fail</div>'; ?></td>
  </tr>
  <tr>
   <td>db.php</td>
   <td><?php echo is_writable('system/db.php') ? '<div class="ui blue horizontal label">Writable</div>' : '<div class="ui red horizontal label">Unwritable</div>'; ?></td>
   <td><div class="ui horizontal label">Writable</div></td>
   <td><?php echo is_writable('system/db.php') ? '<div class="ui green horizontal label">Ok</div>' : '<div class="ui red horizontal label">Fail</div>'; ?></td>
  </tr>
  </table>
  <div class="ui divider hidden">
</div>
  <form action="install.php?step=3" method="post">
   <input type="hidden" name="pre_error" id="pre_error" value="<?php echo $pre_error;?>" />
   
   <p><a href="install.php?step=2" class="ui left labeled icon button">
  <i class="left arrow icon"></i>
  Previous
</a><button type="submit" name="continue" class="ui right labeled icon button">
  <i class="right arrow icon"></i>
  Next</button></p>
  </form>
    
</p>
<div class="ui divider hidden">
</div>
      </div>
      <div class="ui stacked secondary segment">
    <p>&copy ChewiScripts 2018</p>
  </div>
</div>

  </div>
</div>

</div>
<?php 
}
function step_4(){ 
 if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['key'])){
  echo'<script>window.location = "install.php?step=5"</script>';
  exit;
 }
 if($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['key'])){
  echo '<div class="ui error attached message">You must enter a license key in order to continue!</div>';
 }
?>
        <div class="ui text container">
           <div class="ui middle aligned center aligned grid">
  <div class="column">
   <div class="ui segments">
      <div class="ui inverted green segment">
          
<div class="ui small breadcrumb">
  <div class="section active">Begin</div>
  <i class="right chevron icon divider"></i>
  <div class="section active">License</div>
    <i class="right chevron icon divider"></i>
  <div class="section active">Requirements</div>
  <i class="right chevron icon divider"></i>
  <div class="section active">Purchase Code</div>
    <i class="right chevron icon divider"></i>
  <div class="section">Database Details</div>
    <i class="right chevron icon divider"></i>
  <div class="section">Finish</div>
</div>
<div class="ui divider hidden">
</div>
<h1>Activation Purchase Key</h2>
<div class="ui divider hidden">
</div>
<table class="ui celled table red">
  <thead>
    <tr><th colspan="2">
    </th>
  </tr></thead>
</table>
<div class="ui divider hidden">
</div>
<p><a href="install.php?step=3" class="ui left labeled icon button">
  <i class="left arrow icon"></i>
  Previous
</a><a href="install.php?step=5" class="ui right labeled icon button">
  <i class="right arrow icon"></i>
  Next
</a></p>
<div class="ui divider hidden">
</div>
      </div>
      <div class="ui stacked secondary segment">
    <p>&copy ChewiScripts <?php echo date('Y');?></p>
  </div>
</div>

  </div>
</div>
</div>
<?php
}
function step_5(){
  if (isset($_POST['submit']) && $_POST['submit']=="Install") {
   $database_host=isset($_POST['database_host'])?$_POST['database_host']:"";
   $database_name=isset($_POST['database_name'])?$_POST['database_name']:"";
   $database_username=isset($_POST['database_username'])?$_POST['database_username']:"";
   $database_password=isset($_POST['database_password'])?$_POST['database_password']:"";
   $web_url=isset($_POST['web_url'])?$_POST['web_url']:"";
  
  if (empty($web_url) || empty($database_host) || empty($database_username) || empty($database_name)) {
   echo '<div class="ui error message attached">All fields are required. Try again!</div>';
  } else {
  $connection = mysqli_connect($database_host, $database_username, $database_password);
   mysqli_select_db($connection,$database_name);
  
   $file ='install.sql';
   if ($sql = file($file)) {
   $query = '';
   foreach($sql as $line) {
    $tsl = trim($line);
   if (($sql != '') && (substr($tsl, 0, 2) != "--") && (substr($tsl, 0, 1) != '#')) {
   $query .= $line;
  
   if (preg_match('/;\s*$/', $line)) {
  
    mysqli_query($connection,$query);
    $err = mysqli_error();
    if (!empty($err))
      break;
   $query = '';
   }
   }
   }
   @mysqli_query($connection,"INSERT INTO `dsptesty_settings` (`setting`, `value`) VALUES ('website_url', '".$web_url."')");
   mysqli_close($connection);
   }
   $f=fopen("system/db.php","w");
   $database_inf="<?php
   
    DEFINE('PFX','dsptesty_'); 
    DEFINE('DB_host','".$database_host."');
    DEFINE('DB_name','".$database_name."');
    DEFINE('DB_user','".$database_username."');
    DEFINE('DB_pass','".$database_password."');

     ?>";
  if (fwrite($f,$database_inf)>0){
   fclose($f);
  }
 echo'<script>window.location = "install.php?step=6"</script>';
  }
  }
?>
   <div class="ui text container">
   <div class="ui middle aligned center aligned grid">
  <div class="column">
   <div class="ui segments">
      <div class="ui inverted green segment">
          
<div class="ui small breadcrumb">
  <div class="section active">Begin</div>
  <i class="right chevron icon divider"></i>
  <div class="section active">Requirements</div>
  <i class="right chevron icon divider"></i>
  <div class="section active">Purchase Code</div>
    <i class="right chevron icon divider"></i>
  <div class="section active">Database Details</div>
    <i class="right chevron icon divider"></i>
  <div class="section">Finish</div>
</div>
<div class="ui divider hidden">
</div>
<h1>Enter your database details</h2>
<div class="ui divider hidden">
</div>

 <form method="post" action="install.php?step=5" class="ui form">
  <div class="field">
   <input type="text" name="database_host" placeholder="Database Host" value='localhost' size="30">
 </div>
 <div class="field">
   <input type="text" name="database_name" placeholder="Database Name" size="30" value="<?php echo $database_name; ?>">
 </div>
 <div class="field">
   <input type="text" name="database_username" placeholder="Database Username" size="30" value="<?php echo $database_username; ?>">
 </div>
 <div class="field">
   <input type="text" name="database_password" placeholder="Database Password" size="30" value="<?php echo $database_password; ?>">
  </div>
   <div class="ui horizontal inverted divider">
    Admin Options
  </div>
              <div class="field">
    <input type="text" name="web_url" value="<?php echo 'http://'.$_SERVER['HTTP_HOST']. dirname($_SERVER['PHP_SELF']); ?>" placeholder="Script Directory URL">
  </div>
 <p>
   <input type="submit" name="submit" class="ui button" value="Install">
  </p>
  </form>
  

      </div>
      <div class="ui stacked secondary segment">
    <p>&copy ChewiScripts <?php echo date('Y');?></p>
  </div>
</div>

  </div>
</div>
</div>
<?php
}
function step_6(){
?>
 <div class="ui text container">
     <div class="ui middle aligned center aligned grid">
  <div class="column">
   <div class="ui segments">
      <div class="ui inverted green segment">
          
<div class="ui small breadcrumb">
  <div class="section active">Begin</div>
  <i class="right chevron icon divider"></i>
  <div class="section active">Requirements</div>
  <i class="right chevron icon divider"></i>
  <div class="section active">Purchase Code</div>
    <i class="right chevron icon divider"></i>
  <div class="section active">Database Details</div>
    <i class="right chevron icon divider"></i>
  <div class="section">Finish</div>
</div>
<div class="ui divider hidden">
</div>
<h1>Installation Complete!</h1>
<p>You have successfully installed this script!</p>
<p>
    <div class="ui left aligned message">
  <div class="header">
   Login
  </div>
  <p>For yor first login click the button below and login using the credientials:  <div class="ui blue horizontal label">admin@admin.com</div><div class="ui blue horizontal label">12345678</div></p>
  <p>P.S. Don't forget to delete this file and you will be forced to change your password once logged in!</p>
</div>

  <br>
<a href="admin/" class="ui right labeled icon button">
  <i class="right arrow icon"></i>
  Go to admin
</a></p>
<div class="ui divider hidden">
</div>
      </div>
      <div class="ui stacked secondary segment">
    <p>&copy ChewiScripts <?php echo date('Y');?></p>
  </div>
</div>

  </div>
</div>
 </div>
<?php 
}
?>
</body>
</html>