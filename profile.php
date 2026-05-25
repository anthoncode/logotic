<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
require_once('system/config-global.php');

if (isset($_GET['user'])) {
  $id = $_GET['user'];
  $details = $user->userdetails($id);
  //$myproducts = $user->myproducts($id);

  if ($details) {
  } else {
    display_post_not_found($id);
    exit();
  }

  $pageTitle = $details['username'] . "'s Profile";

  require_once('system/assets/header.php');
?>
  <main role="main">

    <header class="masthead text-white text-center mb-0 mt-0 rounded-0 box-shadow">
      <div class="overlay rounded-0 box-shadow"></div>
      <div class="container">
        <div class="row">
          <div class="col-xl-9 mx-auto">
            <h1 class="mb-1 font-weight-bold"><?php echo $details['username'] ?>'s Profile</h1>
          </div>
        </div>
      </div>
    </header>

    <div class="container">
      <div class="row">
        <div class="col-12">
          <div class="card mb-3">

          </div>
        </div>
        <div class="col-4">
          <div class="card mb-3">
            <div class="card-body"><button disabled data-toggle="" data-target="#exampleModal" type="button" class="btn btn-primary">Send Message</button></div>
          </div>
          <div class="card mb-3">
            <div class="card-body">
              <h5><i class="fa fa-info mr-3"></i>Info<br></h5>
              <hr>
              <div class="row">
                <div class="col-6">
                  <h5>Created:</h5>
                </div>
                <div class="col-6">
                  <h5><?php echo $details['created'] ?></h5>
                </div>
                <div class="col-6">
                  <h5>Status:</h5>
                </div>
                <div class="col-6">
                  <h5><?php echo ($details['active'] == '1' ? 'Active' : 'Banned'); ?></h5>
                </div>
              </div>
            </div>
          </div>
          <div class="card">
            <div class="card-body">
              <h5><i class="fa fa-trophy mr-3"></i>Achievements<br></h5>
              <hr>
              <ul class="list-inline">
                <?php if ($details['active'] == 1) { ?> <li class="list-inline-item">Pioneer</li><?php } ?>
                <?php if ($details['verified'] == 1) { ?> <li class="list-inline-item"><i class="fas fa-user-md"></i> Became an Author</li><?php } ?>
                <?php if ($details['allow_email'] == 1) { ?> <li class="list-inline-item"><i class="far fa-newspaper"></i> Newsletter Squad</li><?php } ?>
                <?php if ($details['moderator'] == 1) { ?> <li class="list-inline-item"><i class="fas fa-user-md"></i> Staff Moderator</li><?php } ?>
                <?php if ($details['purchases'] >= 1 && $details['purchases'] <= 10) { ?> <li class="list-inline-item"><i class="fas fa-cart-plus"></i> Buyer Level 1</li><?php } ?>
                <?php if ($details['purchases'] >= 11 && $details['purchases'] <= 30) { ?> <li class="list-inline-item"><i class="fas fa-cart-plus"></i> Buyer Level 2</li><?php } ?>
                <?php if ($details['purchases'] >= 31 && $details['purchases'] <= 100) { ?> <li class="list-inline-item"><i class="fas fa-cart-plus"></i> Buyer Level 3 (max level)</li><?php } ?>
              </ul>
            </div>
          </div>
        </div>
        <div class="col order-lg-8">
          <div class="row">

            <div class="col-12">
              <div class="alert alert-primary" role="alert">
                Nothing to show here!
              </div>
            </div>

            <div class="col-md-6 col-lg-6 mb-4">

              <!--cards go here-->

            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel"><?php echo $l['support']; ?> </h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <?php if ($user->is_loggedin()) { ?> <form id="support">
                <div class="form-group">
                  <input type="hidden" class="form-control" name="name" placeholder="Enter Name" value="<?php echo $userDetails['fname']; ?>"="">
                </div>
                <div class="form-group">
                  <input type="hidden" name="email" class="form-control" value="<?php echo $userDetails['email']; ?>"="">
                </div>
                <div class="form-group">
                  <input type="hidden" name="subject" class="form-control" value="<?php echo $details['name']; ?>"="">
                </div>
                <div class="form-group">
                  <label for="exampleInputPassword1"><?php echo $l['message']; ?></label>
                  <textarea name="message" class="form-control" id="exampleInputPassword1"></textarea>
                </div>
                <button type="submit" class="btn btn-primary submit"><?php echo $l['submit']; ?></button>
                <script type="text/javascript">
                  $(document).ready(function(e) {
                    $("#support").on('submit', (function(e) {
                      e.preventDefault();
                      $.ajax({
                        url: "<?php echo $setting['website_url']; ?>/system/assets/ajax/form-process.php",
                        type: "POST",
                        data: new FormData(this),
                        contentType: false,
                        cache: false,
                        processData: false,
                        beforeSend: function() {
                          $("#result").html('Verifying...');
                        },
                        success: function(response) {
                          $("#result").html(response);
                        }
                      });
                    }));
                  });
                </script>
                <div id="result"></div>
              </form><?php } else { ?>
              <div class="alert alert-primary" role="alert">
                You need to be logged in to send a message!
              </div>
            <?php } ?>
          </div>
        </div>
      </div>
    </div>

  </main>
  <?php
  require_once('system/assets/footer.php');
  ?>
<?php
} else {
  header('Location:index.php');
}

function display_post_not_found($id)
{
  echo "Profile with username:$id does not exist!";
}
