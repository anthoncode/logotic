<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

if (!empty($_GET['id']))
	{
	$id = $_GET['id'];
	include '../../system/config-user.php';

	$details = $newsl->details($id);

?>
        
        <div class="card my-4">
            <h5 class="card-header bg-dark text-white"><?php
		echo $details['title']; ?></h5>
            <div class="card-body">
              <?php
		echo $details['content']; ?>
            </div>
          </div>
        
        <?php
		}
?>