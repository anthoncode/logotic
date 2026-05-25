<?php

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
$pageTitle = 'My Account';
$pg = '4';
require_once('../system/config-user.php');

//rewrite in next update
if (isset($_REQUEST['account'])) {
  if ($_REQUEST['account'] == 'cpassword') {

    $pwd = md5($_POST['edit_pwd']);
    $npwd = $_POST['edit_new_pwd'];
    $npwd2 = $_POST['edit_new_pwd2'];
    if (!empty($npwd)) {
      $details = $user->details($_SESSION['uid']);
      if (md5($pwd) == $details['password']) {
        if ($npwd == $npwd2) {
          $npwd = md5($npwd);
          $result = $user->update($_SESSION['uid'], 'password', $npwd);
          $user->msg = "Password changed successfully";
        } else {
          $user->error  = 'New passwords do not match.';
        }
      } else {

        $user->error  = 'Please enter a correct current password.';
      }
    } else {
      $user->error  = 'All fields are required!';
    }
  } elseif ($_REQUEST['account'] == 'profile') {

    $fname = $_POST['fname'];
    $email = $_POST['email'];
    $newsletter = (isset($_POST['allow_email'])) ? 1 : 0;
    if (!empty($fname) || empty($email)) {
      $details = $user->details($_SESSION['uid']);
      $result = $user->update($_SESSION['uid'], 'email', $email);
      $result = $user->update($_SESSION['uid'], 'fname', $fname);
      $result = $user->update($_SESSION['uid'], 'allow_email', $newsletter);
      $user->msg = "Your profile was successfully updated!";
    } else {
      $user->error  = 'All fields are required!';
    }
  }
}



require_once('includes/header.php');
?>

<?php
if ($user->msg) {
  echo  "<div class=\"alert alert-success mb-0 mt-3\" style=\"display:block;\">" . $user->msg . "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button></div>";
}
if ($user->error) {
  echo "<div class=\"alert alert-danger mb-0 mt-3\" style=\"display:block;\">" . $user->error . "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button></div>";
}
?>

<div class="row">
  <div class="col-4">

    <div class="my-3 p-3 bg-white rounded box-shadow">

      <?php include 'includes/sidenav.php'; ?>

    </div>

  </div>
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
              <?php echo $l['my_account'] ?>
            </a>
          </div>
          <div id="collapseOne" class="card-block collapse">
            <div class="card-body">

              <form action="account.php" method="post" id="edit-admin">

                <input class="form-control" type="hidden" name="account" value="profile">
                <div class="input-group mb-3">
                  <div class="input-group-prepend">
                    <span class="input-group-text" id="basic-addon1"><i class="fa-solid fa-envelope" aria-hidden="true"></i></span>
                  </div>
                  <input type="text" class="form-control" name="email" value="<?php echo $userDetails['email']; ?>" placeholder="Enter Email" title="Email Address" data-toggle="tooltip" aria-label="Enter Email" aria-describedby="basic-addon1">
                </div>

                <div class="input-group mb-3">
                  <div class="input-group-prepend">
                    <span class="input-group-text" id="basic-addon1"><i class="fa-solid fa-user-circle" aria-hidden="true"></i></span>
                  </div>
                  <input type="text" class="form-control" name="fname" value="<?php echo $userDetails['fname']; ?>" placeholder="Enter Name" title="First Name" data-toggle="tooltip" aria-label="Enter Name" aria-describedby="basic-addon1">
                </div>

                <div class="input-group mb-3">
                  <div class="input-group-prepend">
                    <span class="input-group-text" id="basic-addon1"><i class="fa-solid fa-user-circle" aria-hidden="true"></i></span>
                  </div>
                  <input type="text" class="form-control" value="<?php echo $userDetails['username']; ?>" placeholder="Username" aria-label="Username" title="Username" data-toggle="tooltip" disabled aria-describedby="basic-addon1">
                </div>

                <hr>

                <div class="form-check">
                  <input type="checkbox" name="allow_email" <?php if ($userDetails['allow_email'] == 1) {
                                                              echo 'checked="checked"';
                                                            } ?> id="exampleCheck1">
                  <label class="form-check-label" for="exampleCheck1"><?php echo $l['wouldnewsletter'] ?></label>
                </div>



                <div class="clearfix">
                  <button type="submit" name="submit" class="btn btn-primary float-right" title="Update Account"><?php echo $l['update_account'] ?></button>
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
              <?php echo $l['changepwd'] ?>
            </a>
          </div>
          <div id="collapseTwo" class="card-block collapse">
            <div class="card-body">

              <form action="account.php" method="post" id="edit-admin">
                <input class="form-control" type="hidden" name="account" value="cpassword">
                <div class="input-group mb-3">
                  <div class="input-group-prepend">
                    <span class="input-group-text" id="basic-addon1"><i class="fa-solid fa-key" aria-hidden="true"></i></span>
                  </div>
                  <input type="password" class="form-control" name="edit_pwd" placeholder="Current Password" aria-label="Current Password" aria-describedby="basic-addon1">
                </div>
                <div class="input-group mb-3">
                  <div class="input-group-prepend">
                    <span class="input-group-text" id="basic-addon1"><i class="fa-solid fa-key" aria-hidden="true"></i></span>
                  </div>
                  <input type="password" class="form-control" name="edit_new_pwd" placeholder="New Password" aria-label="New Password" aria-describedby="basic-addon1">
                </div>
                <div class="input-group mb-3">
                  <div class="input-group-prepend">
                    <span class="input-group-text" id="basic-addon1"><i class="fa-solid fa-key" aria-hidden="true"></i></span>
                  </div>
                  <input type="password" class="form-control" name="edit_new_pwd2" placeholder="Confirm New Password" aria-label="Confirm New Password" aria-describedby="basic-addon1">
                </div>
                <div class="clearfix">
                  <button type="submit" name="submit" class="btn btn-primary float-right" title="<?php echo $l['changepwd'] ?>"><?php echo $l['changepwd'] ?></button>
                </div>
              </form>

            </div>
          </div>
        </div>
      </div>

      <div class="my-3 p-3 bg-white rounded box-shadow">
        <div class="card m-b-0">
          <div class="card-header mpointer collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseThree">
            <a class="card-title">
              <?php echo $l['profilepic'] ?>
            </a>
          </div>
          <div id="collapseThree" class="panel-collapse collapse">

            <div class="card-body">
              <?php
              if (isset($_FILES['profile']) === TRUE) {
                if (empty($_FILES['profile']['name']) === TRUE) {
                  echo '<div class="ui error message">Please choose a file</div>';
                } else {
                  $allowed = array('jpg', 'jpeg', 'gif', 'png');

                  $file_name = $_FILES['profile']['name'];
                  $file_extn = strtolower(end(explode('.', $file_name)));
                  $file_temp = $_FILES['profile']['tmp_name'];

                  $size = $_FILES['profile']['size'];
                  $max_size = 2097152;
                  // $max_size = 10000;


                  if (in_array($file_extn, $allowed) === TRUE) {
                    if ($size >= $max_size) {
                      echo '<div class="ui error message">File must be 2MB or less.</div>';
                    } else {
                      //uploading the file 
                      if ($user->change_profile_image($_SESSION['uid'], $file_temp, $file_extn) == TRUE) {
                        header('Location: ' . $current_file);
                        exit();
                      } else {
                        echo '<div class="alert alert-danger">We could not upload your profile picture. Please try again later or contact Administrator</div>';
                      }
                    }
                  } else {
                    echo '<div class="alert alert-danger">Incorrect file type.</div>';
                  }
                }
              }
              if (empty($userDetails['profile']) === FALSE) { ?>

              <?php } ?>
              <img width="80" height="80" src="<?php echo $userDetails['profile'] ?>">

              <form action="" method="post" enctype="multipart/form-data">
                <input type="file" name="profile">
                <input type="submit" class="btn btn-primary" value="<?php echo $l['changepp'] ?>">
              </form>
            </div>

          </div>
        </div>
      </div>
    </div>

    <?php
    require_once('includes/footer.php');
    ?>