<?php

$pageTitle = 'Send Newsletter';
require_once('../system/config-admin.php');
$customer = new Customer($DB_con);
require_once('includes/header1.php');

?>
<script src="//cdnjs.cloudflare.com/ajax/libs/tinymce/4.6.5/tinymce.min.js"></script>
<div class="content">

  <?php
  if (isset($_GET['success']) === TRUE && empty($_GET['success']) === TRUE) {
  ?>
    <p>Email has been sent</p>
  <?php
  } else {
    if (empty($_POST) === FALSE) {
      if (empty($_POST['subject']) === TRUE) {
        echo '<div class="ui attached error message">Subject is required</div>';
      }
      if (empty($_POST['body']) === TRUE) {
        echo '<div class="ui attached error message">Body is required</div>';
      } else {
        $customer->mail_users($_POST['subject'], $_POST['body']);
        echo '<script>window.location = "' . $setting['website_url'] . '/admin/send-mail.php?success"</script>';
        // exit();
      }
    }

  ?>

    <div class="my-3 p-3 bg-white rounded box-shadow">
      <form action="" method="post" class="form-horizontal">

        <div class="form-group">
          <label>Subject:</label>
          <input type="text" class="form-control" name="subject" value="<?php if (isset($_POST['subject'])) echo $_POST['subject']; ?>" id="coupon-code">
        </div>

        <div class="form-group">
          <label>Message:</label>

          <textarea name="body" class="form-control" id="exampleFormControlTextarea1" rows="5"><?php if (isset($_POST['body'])) echo $_POST['body']; ?></textarea>

        </div>



        <button type="submit" class="btn btn-primary w-100">Send Message</button>
      </form>
    </div>
</div>
<?php
  }
?>
<script type="text/javascript">
  tinymce.init({
    selector: "textarea",
    themes: "modern",
    branding: false,
    plugins: [
      'advlist autolink lists link image charmap preview',
      'visualblocks code',
      'insertdatetime media contextmenu paste code'
    ],
    toolbar: 'bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image code'
  });
</script>
<?php
require_once('includes/footer.php');

?>