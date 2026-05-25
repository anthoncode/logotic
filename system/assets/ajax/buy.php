<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
require_once('../../config-user.php');
require_once('../../gateways.php');
if(isset($_REQUEST['product_id'])){
	if(!empty($_REQUEST['product_id'])){
	$id = $_REQUEST['product_id'];
		if(isset($_SESSION['product'][$id])){
			$details = $_SESSION['product'][$id];
		}else{
			$details = $product->details($id);
			$_SESSION['product'][$id] = $details;
		}
	if($details){
	$base = $setting['website_url'];
	$name = $details['name'];
	$price = $details['price'];
		$success = false;
		$error = false;
	if($user->is_loggedin()){
	if($purchases->is_purchased($_SESSION['uid'],$id)){
	$error = "You have already downloaded this item!";
	}
	}

	
	if(isset($_SESSION['discountcoupon'])){
$discountCoupon = $coupon->getDetails($_SESSION['discountcoupon']);
 if($price >= $discountCoupon['order_min']){
	$discount = $discountCoupon['off'];
 	if($discountCoupon['off_type'] == '1'){
	$discount = $price  * $discount / 100;
	}
	$price = $price - $discount;
 }
}

if(isset($_REQUEST['buy'])){
$error =false;
$error = ($userDetails['balance'] >= $price?false:"You don't have enough credit to buy this item!");
if(!$error){
$query_age = (isset($_SESSION['discountcoupon']) ? $_SESSION['discountcoupon'] : null);
$_SESSION['payment']['use_prepaid'] = true;
$trans_id = $transaction->add($_SESSION['uid'],$price,$id,'1','1');
$error = ($trans_id?false:$transaction->error);
$stockminus = $purchases->updatestock($id);
$buy = $purchases->add($_SESSION['uid'],$id,$trans_id,'1',$query_age);
$error = ($buy?false:$purchases->error);
unset($_SESSION['payment']);
unset($_SESSION['discountcoupon']);
}
if(isset($_REQUEST['ajax'])){
echo ($error ? $error:'success');
exit;
}
}
//////////
$crf = md5("DSP" . date('YMD'));
if(isset($_POST) && isset($_POST['pay_now'])){
$error = ($_POST['crf'] != $crf ? 'Invalid CRF':false);
$error = (empty($_POST['gateway'])? 'Please select payment method':$error);

if(!$error){
$price = ($setting['txn'] > 0?$price + $setting['txn']:$price);
$_SESSION['payment']['product_id'] = $id;
$_SESSION['payment']['crf'] = $crf;
$_SESSION['payment']['amount'] = $price;
$_SESSION['payment']['gateway'] = $_POST['gateway'];
$_SESSION['payment']['detail'] = "Item Purchase";
$_SESSION['payment']['redir'] = "downloads.php";
$_SESSION['payment']['action'] = "purchase";


if(!isset($_REQUEST['ajax'])){
header('location:'.$setting['website_url'].'/user/process.php?action=process');
exit;
}
}
if(isset($_REQUEST['ajax'])){
echo ($error ? $error:'success');
exit;
}
}
///////////
	
	if($success){
	echo "<div class='alert alert-success  text-center'>$success</div>";
	} 
	if($error){
	echo "<div class='alert alert-danger text-center'>$error</div>";
	} ?>
	<div id="product_id_div" class="hide"><?php echo $id;?></div>
	<div class="row" id="dsp-buy-info">
	<div class="col-12">
	    
	    <ul class="list-group">
  <li class="list-group-item d-flex justify-content-between align-items-center">
    <h4><?php echo $name; ?> <b>(<?php echo $setting['currency_sym'] . $price; ?>)</b></h4>
    <span><a href="#" class="btn btn-primary" id="dsp-buy-btn"><span class="glyphicon glyphicon-ok-sign"></span> Buy Now</a></span>
  </li>
</ul>
	</div>
	</div> <!-- end .row !-->
	<?php if(!$user->is_loggedin()){?>
	<div class="row" id="dsp-buy-now" style="display: none;">
	<div class="col-12">
	<h3 class="no-topr">Sign in</h3>
	<form class="form-horizontal" method="post" id="dsp-buy-login-form">
	<div class="form-group">
    <label class="col-md-4 control-label" for="inputEmail">Email</label>
    <div class="col-md-8">
      <input class="form-control"type="text" id="inputEmail" placeholder="Email" name="email">
    </div>
  </div>
  <div class="form-group">
    <label class="col-md-4 control-label" for="inputPassword" >Password</label>
    <div class="col-md-8">
      <input class="form-control"type="password" id="inputPassword" name="pwd" placeholder="Password">
    </div>
  </div>
  <div class="form-group">
        <div class="col-md-offset-4 col-md-8">
      <input class="form-control"type="hidden" name="login">
      <button type="submit" class="btn btn-primary">Sign in</button>
    </div>
  </div>  
  <div class="form-group">
    <div class="col-md-offset-4 col-md-8">
         <a class="btn btn-link" href="<?php echo $setting['website_url'];?>/user/register.php">Sign up</a> <a class="btn btn-link dsp-show-forgot-pwd" href="#">Forgot Password</a>
    </div>
  </div>
</form> </div> <!-- end .col-md-12 !-->
		</div> <!-- end .row #dsp-buy-login!-->
	
	<div class="row" id="dsp-forgot-pwd" style="display: none;">
		<div class="col-md-12">
	<h3 class="no-top text-center">Reset password</h3>
	<form method="post" class="form-horizontal" action="<?php echo $setting['website_url'];?>/user/login.php">
		 	 <div class="form-group">
    <label class="col-md-4 control-label" for="email">Email</label>
    <div class="col-md-8">
	<input class="form-control"type="text" name="email" value="" id="email"><input class="form-control"type="hidden" name="action" value="recover">
	</div></div>
	<div class="form-group">
    <div class="col-md-offset-4 col-md-8">
	<button type="submit" name="submit" class="btn btn-primary" ><i class="icon-white icon-envelope"></i> Reset Password</button>
	</div>
	</div>
	</form><hr>
	<div class="text-center">Remember password? <a href="#" class="btn btn-primary dsp-show-buy-login">Sign in here</a></div>
	</div> <!-- end .col-md-12 !-->
		</div> <!-- end .row #dsp-forgot-pwd !-->
	
		<div class="row" id="dsp-new-account" style="display: none;">
		<div class="col-md-12">
		
	<h3 class="no-top text-center">Sign up</h3>
<form class="form-horizontal"  method="post" id="dsp-new-user">

				 <div class="form-group">
    <label class="col-md-4 control-label" for="new-email">Email</label>
    <div class="col-md-8">

<input class="form-control"type="text" name="new_email" value="" id="new-email">
</div></div>


 <div class="form-group">
    <label class="col-md-4 control-label" for="new-pwd">Password</label>
    <div class="col-md-8">
<input class="form-control"type="password" name="new_pwd" value="" id="new-pwd"></div></div>

 <div class="form-group">
    <label class="col-md-4 control-label" for="new-pwd2">Confirm password</label>
    <div class="col-md-8">
<input class="form-control"type="password" name="new_pwd2" value="" id="new-pwd2"></div></div> 
<div class="form-group">
  
    <div class="col-md-offset-4 col-md-8">
Click Sign up if you accept terms and condition.
</div>
</div>
 <div class="form-group">
      <div class="col-md-offset-4 col-md-8">
<button type="submit" name="submit" class="btn btn-primary" >Sign up</button></div></div>
</form>  <hr>
<div class="text-center">Already have account? <a href="#" class="btn btn-primary dsp-show-buy-login">Sign in here</a></div>
</div> <!-- end .col-md-12 !-->
		</div> <!-- end .row #dsp-new-account !-->
<?php
}else{
?>
<div class="row" id="dsp-buy-now" style="display: none;"><div class="col-md-12">
<h3 class="no-top text-center"><?php echo $name; ?> <b>(<?php echo $setting['currency_sym'] . $price; ?>)</b></h4>
<hr>
<div class="row text-center"><div class="col-md-6">
<button class="btn btn-primary mb-2 <?php echo ($price > $userDetails['balance']?"disabled":"");?>" id="dsp-use-prepaid">Use prepaid credit</button><br>
<p>You have <?php echo $setting['currency_sym'] . $userDetails['balance'];?> in your credit balance.
You can buy more <a href="<?php echo $setting['website_url'] . "/user/credits.php";?>">here</a></p>
</div><div class="col-md-6">
<button class="btn btn-primary mb-2" id="dsp-pay-now">Pay Directly</button><br>
<p><?php echo ($setting['txn'] > 0?$setting['currency_sym'].$setting['txn']." will be added to the total as a transaction fee!":"") ?></p>
</div><form  style="display: none;" class="form-horizontal" method="post" action="#" id="dsp-pay-now-form"><input class="form-control" type="hidden" value="<?php echo $id;?>" name="product_id"><input class="form-control"type="hidden" value="<?php echo $crf;?>" name="crf">
<table class="table table-bordered gateways">
		
	<?php 
	$g_count= 1;
	foreach($gateways as $gateway){
	if(!is_int($g_count/2)){
		echo "<tr>";
		}
	
	?>
	
	<td style="width:50%;"><input class="form-control" name="gateway" type="radio" name="gateway" value="<?php echo $gateway['file']; ?>" id="<?php echo $gateway['file']; ?>"><label class="text-center"for="<?php echo $gateway['file']; ?>"><img src="<?php echo $base;?>/system/gateways/<?php echo $gateway['logo'];	?>" /></label></td>
	<?php 
	if(is_int($g_count/2) && $g_count != 1 ){
		echo "</tr>";
		}
		$g_count = $g_count + 1;
	}
	?>
</table>
<button class="btn btn-primary" >Pay Now</button></form>
</div><hr>
<div class="row text-center"><div class="col-md-12">
	<form class="form-inline" method="post" id="dsp-coupons-form">Coupon Code: <input class="form-control" type="text" name="coupon" id="dsp-coupon-code" value="<?php if(isset($_SESSION['discountcoupon'])){ echo $_SESSION['discountcoupon'];} ?>"> <button  type="submit" class='btn btn-primary'><i class='glyphicon glyphicon-ok'></i> Apply Coupon</button><?php if(isset($_SESSION['discountcoupon'])){ ?> <button   type='button' name='remove' id="dsp-coupon-remove" class="btn btn-danger"><i class='glyphicon glyphicon-remove-sign'></i> Remove Coupon</button><?php } ?>
	</div>
	<?php if(isset($_SESSION['discountcoupon'])){ 
	$sym = ($discountCoupon['off_type'] == 1)? "%":$setting['currency_sym'];
	echo "<br><br><p>Current Coupon: ".$discountCoupon['off'] . $sym." off on purchase of ". $setting['currency_sym']. $discountCoupon['order_min']." and above.</p>";
	} ?></form>

</div>
</div>
</div></div>
<?php
}
	exit;
	} // if_$details
	}
}
echo "<div class='row'><div class='alert alert-danger col-md-12 text-center'>Some error occurred, Please try again</div>";
?>