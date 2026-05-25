<?php

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
$pageTitle = 'Admin Account';
$pg = '6';
require_once('../system/config-admin.php');

//rewrite in next update
if (isset($_REQUEST['account'])) {
  if ($_REQUEST['account'] == 'cpassword') {

    $pwd = md5($_POST['edit_pwd']);
    $npwd = $_POST['edit_new_pwd'];
    $npwd2 = $_POST['edit_new_pwd2'];
    if (!empty($npwd)) {
      $details = $auth->details($_SESSION['uid']);
      if (md5($pwd) == $details['password']) {
        if ($npwd == $npwd2) {
          $npwd = md5($npwd);
          $result = $auth->update($_SESSION['uid'], 'password', $npwd);
          $auth->msg = "Password changed successfully";
        } else {
          $auth->error  = 'New passwords do not match.';
        }
      } else {

        $auth->error  = 'Please enter a correct current password.';
      }
    } else {
      $auth->error  = 'All fields are required!';
    }
  } elseif ($_REQUEST['account'] == 'profile') {

    $fname = $_POST['fname'];
    $email = $_POST['email'];
    if (!empty($fname) || empty($email)) {
      $details = $auth->details($_SESSION['uid']);
      $result = $auth->update($_SESSION['uid'], 'email', $email);
      $result = $auth->update($_SESSION['uid'], 'fname', $fname);
      $auth->msg = "Your profile was successfully updated!";
    } else {
      $auth->error  = 'All fields are required!';
    }
  }
}



require_once('includes/header1.php');
?>

<?php
if ($auth->msg) {
  echo  "<div class=\"alert alert-success mb-0 mt-3\" style=\"display:block;\">" . $auth->msg . "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button></div>";
}
if ($auth->error) {
  echo "<div class=\"alert alert-danger mb-0 mt-3\" style=\"display:block;\">" . $auth->error . "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button></div>";
}
?>

<div class="row">

  <div class="col-8 pl-0">

    <style>
      .accordion .card-header:after {
        font-family: 'FontAwesome';
        content: "\f068";
        float: right;
      }

      .accordion .card-header.collapsed:after {
        /* symbol for "collapsed" panels */
        content: "\f067";
      }

      .accordion .card-header {
        font-weight: 700;
      }
    </style>
    <div id="accordion" class="accordion">

      <div class="my-3 p-3 bg-white rounded box-shadow">

        <div class="card m-b-0">
          <div class="card-header mpointer collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseOne">
            <a class="card-title">
              My Account
            </a>
          </div>
          <div id="collapseOne" class="card-block collapse">
            <div class="card-body">

              <form action="account.php" method="post" id="edit-admin">

                <input class="form-control" type="hidden" name="account" value="profile">
                <div class="input-group mb-3">
                  <div class="input-group-prepend">
                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-envelope" aria-hidden="true"></i></span>
                  </div>
                  <input type="text" class="form-control" name="email" value="<?php echo $userDetails['email']; ?>" placeholder="Enter Email" title="Email Address" data-toggle="tooltip" aria-label="Enter Email" aria-describedby="basic-addon1">
                </div>

                <div class="input-group mb-3">
                  <div class="input-group-prepend">
                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-user-circle-o" aria-hidden="true"></i></span>
                  </div>
                  <input type="text" class="form-control" name="fname" value="<?php echo $userDetails['fname']; ?>" placeholder="Enter Name" title="First Name" data-toggle="tooltip" aria-label="Enter Name" aria-describedby="basic-addon1">
                </div>

                <hr>



                <div class="clearfix">
                  <button type="submit" name="submit" class="btn btn-primary float-right" title="Update Account">Update Account</button>
                </div>
              </form>

            </div>
          </div>
        </div>

      </div>

      <div class="my-3 p-3 bg-white rounded box-shadow">

        <div class="card m-b-0">
          <div class="card-header mpointer collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo">
            <a class="card-title">
              Change Password
            </a>
          </div>
          <div id="collapseTwo" class="card-block collapse">
            <div class="card-body">

              <form action="account.php" method="post" id="edit-admin">
                <input class="form-control" type="hidden" name="account" value="cpassword">
                <div class="input-group mb-3">
                  <div class="input-group-prepend">
                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-key" aria-hidden="true"></i></span>
                  </div>
                  <input type="password" class="form-control" name="edit_pwd" placeholder="Current Password" aria-label="Current Password" aria-describedby="basic-addon1">
                </div>
                <div class="input-group mb-3">
                  <div class="input-group-prepend">
                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-key" aria-hidden="true"></i></span>
                  </div>
                  <input type="password" class="form-control" name="edit_new_pwd" placeholder="New Password" aria-label="New Password" aria-describedby="basic-addon1">
                </div>
                <div class="input-group mb-3">
                  <div class="input-group-prepend">
                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-key" aria-hidden="true"></i></span>
                  </div>
                  <input type="password" class="form-control" name="edit_new_pwd2" placeholder="Confirm New Password" aria-label="Confirm New Password" aria-describedby="basic-addon1">
                </div>
                <div class="clearfix">
                  <button type="submit" name="submit" class="btn btn-primary float-right" title="Change Password">Change Password</button>
                </div>
              </form>

            </div </div>
          </div>
        </div>
      </div>

    </div>

    <?php
    require_once('includes/footer.php');
    ?>