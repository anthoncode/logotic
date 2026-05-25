<?php

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

$pageTitle = 'Buy Credits';
$pg = '3';
require_once('../system/config-user.php');
require_once('../system/gateways.php');
$msg = false;
$error = false;
$crf = md5("DSP" . date('YMD'));
if (isset($_POST) && isset($_POST['crf'])) {
  $error = ($_POST['crf'] != $crf ? 'Invalid CRF' : false);
  $error = (empty($_POST['amount']) ? 'Please select amount' : $error);
  $error = (empty($_POST['gateway']) ? 'Please select payment method' : $error);

  if (!$error) {

    $_SESSION['payment']['crf'] = $crf;
    $_SESSION['payment']['amount'] = $_POST['amount'];
    $_SESSION['payment']['gateway'] = $_POST['gateway'];
    $_SESSION['payment']['detail'] = "Funds Deposit";
    $_SESSION['payment']['redir'] = "transactions.php";
    $_SESSION['payment']['action'] = "deposit";


    if (!isset($_REQUEST['ajax'])) {
      header('location:process.php?action=process');
      exit;
    }
  }
  if (isset($_REQUEST['ajax'])) {
    echo ($error ? $error : 'success');
    exit;
  }
}
require_once('includes/header.php');
?>

<div class="row">
  <div class="col-4">

    <div class="my-3 p-3 bg-white rounded box-shadow">

      <?php include 'includes/sidenav.php'; ?>

    </div>

  </div>
  <div class="col-8 pl-0">

    <div class="my-3 p-3 bg-white rounded box-shadow">
      <form class="form-horizontal amount" action="credits.php" method="post" id="deposit-funds">

        <div class="alert alert-primary">Remember prepaid credit is <b>NOT</b> refundable!</div>

        <?php
        if ($msg) {
          echo  "<div class=\"alert alert-success\" style=\"display:block;\">" . $user->msg . "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button></div>";
        }
        if ($error) {
          echo "<div class=\"alert alert-danger\" style=\"display:block;\">" . $user->error . "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button></div>";
        }
        ?>


        <div class="card-deck text-center">

          <div class="card mb-4">
            <div class="card-header">
              <h4 class="my-0 font-weight-normal">Small</h4>
            </div>
            <div class="card-body">
              <h1 class="card-title pricing-card-title"><?php echo $setting['currency_sym']; ?>30</h1>
              <ul class="list-unstyled mt-3 mb-4">
                <li>No Transaction Fees</li>
                <li>Super Easy!</li>
              </ul>
              <input name="amount" type="radio" value="30" data-toggle="collapse" data-target=".collapseExample" aria-expanded="false" aria-controls="collapseExample" class="btn btn-lg btn-block btn-outline-primary">Select</input>
            </div>
          </div>
          <div class="card mb-4">
            <div class="card-header">
              <h4 class="my-0 font-weight-normal">Medium</h4>
            </div>
            <div class="card-body">
              <h1 class="card-title pricing-card-title"><?php echo $setting['currency_sym']; ?>50</h1>
              <ul class="list-unstyled mt-3 mb-4">
                <li>No Transaction Fees</li>
                <li>Super Easy!</li>
              </ul>
              <input name="amount" type="radio" value="50" data-toggle="collapse" data-target=".collapseExample" aria-expanded="false" aria-controls="collapseExample" class="btn btn-lg btn-block btn-outline-primary">Select</input>
            </div>
          </div>
          <div class="card mb-4">
            <div class="card-header">
              <h4 class="my-0 font-weight-normal">Large</h4>
            </div>
            <div class="card-body">
              <h1 class="card-title pricing-card-title"><?php echo $setting['currency_sym']; ?>100</h1>
              <ul class="list-unstyled mt-3 mb-4">
                <li>No Transaction Fees</li>
                <li>Super Easy!</li>
              </ul>
              <input name="amount" type="radio" value="100" data-toggle="collapse" data-target=".collapseExample" aria-expanded="false" aria-controls="collapseExample" class="btn btn-lg btn-block btn-outline-primary">Select</input>
            </div>
          </div>
        </div>

        <div class="collapse collapseExample" id="collapseExample">
          <div class="card card-body">
            <table class="table gateways">

              <?php
              $g_count = 1;
              foreach ($gateways as $gateway) {
                if (!is_int($g_count / 2)) {
                  echo "<tr>";
                }

              ?>

                <td style="width:50%;"><input name="gateway" type="radio" name="gateway" value="<?php echo $gateway['file']; ?>" id="<?php echo $gateway['file']; ?>"><label class="text-center" for="<?php echo $gateway['file']; ?>">
                    <h4><img src="../system/gateways/<?php echo $gateway['logo'];  ?>" /></h4>
                  </label></td>
              <?php
                if (is_int($g_count / 2) && $g_count != 1) {
                  echo "</tr>";
                }
                $g_count = $g_count + 1;
              }
              ?>
            </table>
          </div>
        </div>

        <div class="clearfix"> <button type="submit" name="submit" class="btn btn-primary pull-right w-100">Buy Credits <i class="icon-arrow-right icon-white"></i></button></div>
        <input type="hidden" name="crf" value="<?php echo $crf; ?>">
      </form>
    </div>

  </div>
  <script type="text/javascript">
    $(document).ready(function() {
      $("input[name=gateway]").change(function() {
        $(".gateways td").removeClass("selected text-success");
        $(this).parent().addClass("selected text-success");
      });

      $("input[name=amount]").change(function() {
        $(".amount label").removeClass("selected text-success");
        $(this).parent().children('label').addClass("selected text-success");
      });

      $("#deposit-funds").unbind().bind('submit', function() {
        $(".alert").remove();
        $("#deposit-funds").prepend("<div class=\"alert\" style=\"display:none;\"><span></span><button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button></div>");
        if (!$("input[name=amount]:checked").val()) {
          $(".alert span").text("Please select amount");
          $(".alert").addClass("alert-danger");
          $(".alert").show();
          return false;
        }
        if (!$("input[name=gateway]:checked").val()) {
          $(".alert span").text("Please select payment method");
          $(".alert").addClass("alert-danger");
          $(".alert").show();
          return false;
        }
        $(".alert span").text("Please Wait...");
        $(".alert").addClass("alert-success");
        $(".alert").show();

        var actionUrl = $(this).attr('action');
        var serialized = $(this).serialize();
        $.ajax({
          type: "POST",
          url: actionUrl,
          data: serialized + "&ajax=true",
          success: function(html) {
            if (html == "success") {
              top.location.href = 'process.php?action=process';
            } else {
              $(".alert").remove();
              $("#deposit-funds").prepend("<div class=\"alert alert-error\"><span>" + html + "</span><button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button></div>");
            }
          }
        });

        return false;
      });
    });
  </script>
  <?php
  require_once('includes/footer.php');
  ?>