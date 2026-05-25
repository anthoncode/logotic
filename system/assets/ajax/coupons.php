<?php
require_once('../../config-user.php');

$jsonReturn = array();
if(isset($_REQUEST['discountcoupon'])){
if(!empty($_REQUEST['discountcoupon'])){
$c = $_REQUEST['discountcoupon'];
$c = strtoupper($c);
	if($coupon->getDetails($c)){
	if($purchases->is_coupon_fresh($crypt->decrypt($_SESSION['uid'],'USER'),$c)){
	$_SESSION['discountcoupon'] = $c;
	}else{
	unset($_SESSION['discountcoupon']);
	$jsonReturn['error'] ='Coupon already used';
	}
	}else{
	unset($_SESSION['discountcoupon']);
	$jsonReturn['error'] ='Coupon not available';
	}
	}else{
	unset($_SESSION['discountcoupon']);
	$jsonReturn['error'] ='Coupon removed';
	}
}else{
	$jsonReturn['error'] ='Invalid request';
	}
$jsonReturn = json_encode($jsonReturn);
echo $jsonReturn;
?>