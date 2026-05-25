<?php
require_once('../../config-user.php');
$jsonReturn = array();
if(isset($_REQUEST['product_id'])){
	if(!empty($_REQUEST['product_id'])){
		$id = $_REQUEST['product_id'];
		if(isset($_SESSION['product'][$id])){
		$details = $_SESSION['product'][$id];
		}else{
		$details = $product->details($id);
		$_SESSION['product'][$id] = $details;
		}
		$jsonReturn = array();	
		if($details){
		switch($_REQUEST['type']){
		case 'all':
		$jsonReturn['name'] = $details['name'];
		$jsonReturn['price'] = $details['price'];
		$jsonReturn['image'] = $details['image'];
		$jsonReturn['desc'] = nl2br($details['desc']);
		case 'btn':
		$jsonReturn['name'] = $details['name'];
		$jsonReturn['price'] = $details['price'];
		break;
		
		case 'img':
		$jsonReturn['image'] = $details['image'];
		break;
		case 'desc':
		$jsonReturn['desc'] = nl2br($details['desc']);
		break;
		default:
		$jsonReturn['error'] ='Invalid request';
	}
	}else{
	$jsonReturn['error'] ='Invalid product';
	}
	}else{
	$jsonReturn['error'] ='Invalid request';
	}
	}else{
	$jsonReturn['error'] ='Invalid request';
	}
$jsonReturn['currency'] =$setting['currency_sym'];
$jsonReturn = json_encode($jsonReturn);
echo $jsonReturn;
?>