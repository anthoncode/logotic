<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

require_once('../system/config-user.php');
require_once('../system/gateways.php');

$gateway = $_SESSION['payment']['gateway'];
$amount = $_SESSION['payment']['amount'];
$detail = $_SESSION['payment']['detail'];
$crf = $_SESSION['payment']['crf'];
$currency = $setting['currency'];
$crf_gen = md5("DSP" . date('YMD'));
if ($crf_gen != $crf || !$gateway || !$amount) {
?>

	<head>
		<title>Error</title>
	</head>
	<center>
		<h2>Unknown Transaction.</h2>
	</center>
	<?php
	exit;
}

switch ($_GET['action']) {
	case 'process':
		if ($_SESSION['payment']['action'] == "deposit") {
			$transaction_id = $transaction->add($_SESSION['uid'], $amount, '0', '2', '2');
		} elseif ($_SESSION['payment']['action'] == "purchase") {
			$transaction_id = $transaction->add($_SESSION['uid'], $amount, $_SESSION['payment']['product_id'], '1', '2');
		}

		if (!$transaction_id) {
	?>

			<head>
				<title>Error</title>
			</head>
			<center>
				<h2>Unable to process transaction.</h2>
			</center>
		<?php
			exit;
		}
		$_SESSION['payment']['id'] = $transaction_id;
		$detail .= " (" . $transaction_id . ")";
		$_SESSION['payment']['hash'] = md5($crf . $transaction_id . $amount);
		header("location:../system/gateways/$gateway?transaction_id=$transaction_id&detail=$detail&amount=$amount&currency=$currency&crf=$crf");
		break;
	case 'callback':
		if (!$_SESSION['payment']['id']) {
		?>

			<head>
				<title>Error</title>
			</head>
			<center>
				<h2>Unknown Transaction.</h2>
			</center>
		<?php
		}
		$transaction_id = $_SESSION['payment']['id'];
		$status = $_POST['auth'];
		$txn = $_POST['txn'];
		$amt = $_POST['amt'];
		$gcrf = $_POST['crf'];
		$hsh = $_SESSION['payment']['hash'];
		$hash = md5($gcrf . $txn_id . $amt);
		$crf = md5($gateway, $transaction_id, $amount, $crf);
		if ($transaction_id != $txn || $amount != $amt || $crf != $gcrf || $hash != $hsh) {
		?>

			<head>
				<title>Error</title>
			</head>
			<center>
				<h2>Transaction rejected.</h2>
			</center>
		<?php
		}
		$action = $transaction->update($transaction_id, 'callback', $status);
		unset($_SESSION['payment']);
		$type = ($status = '1' ? 'alert-success' : 'alert-error');
		$msg = ($status = '1' ? 'Transaction completed' : 'Transaction failed');
		?>
		<html>

		<head>
			<title>Processing Payment...</title>
		</head>

		<body onLoad="document.forms['payment_form'].submit();">
			<center>
				<h2>Please wait, your order is being processed and you will be redirected back to our website.</h2>
			</center>
			<center><img src="../system/assets/uploads/img/91.gif"></center>
			<form method="post" name="payment_form" action="transactions.php">
				<input type="hidden" name="msg" value="<?php echo $msg; ?>">
				<input type="hidden" name="type" value="<?php echo $type; ?>">
			</form>
		</body>

		</html>
	<?php
		break;
	default:
	?>

		<head>
			<title>Error</title>
		</head>
		<center>
			<h2>Unknown Transaction.</h2>
		</center>
<?php
}
?>