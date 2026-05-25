<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

$pageTitle = 'News Announcements';
$pg = '7';
require_once('../system/config-user.php');
require_once('includes/header.php');
$news = $newsl->get_all_news();
//$news1 = $newsl->get_important_news();
?>
<div class="row">
    <div class="col-4">
        <div class="my-3 p-3 bg-white rounded box-shadow">
            <?php include 'includes/sidenav.php'; ?>
        </div>
    </div>

    <div class="col-8 pl-0">
        <div class="my-3 p-3 bg-white rounded box-shadow">
            <div id="news" class="collapse show">
                <?php foreach ($news as $newsl) { ?>
                    <div class="d-flex align-items-center newsitem p-3 mb-3 text-white-50 <?php echo ($newsl['important'] == '1' ? 'bg-purple' : 'bg-primary'); ?> rounded box-shadow">
                        <!-- <img class="mr-3" src="<?php echo $setting['website_url']; ?>/system/assets/uploads/img/newspaper.png" alt="" width="48" height="48"> -->
                        <span class="fa-solid fa-newspaper fa-2xl mr-3"></span>
                        <div class="lh-100 full-width">
                            <h6 class="mb-0 text-white lh-100 font-weight-bold"><?php echo $newsl['title'] ?></h6>
                            <small>Posted <?php $timeago = get_timeago(strtotime($newsl['date']));
                                            echo $timeago; ?> | By <?php echo $newsl['author']; ?></small>
                        </div>
                        <div class="clearfix float-right"> <button class="btn <?php echo ($newsl['important'] == '1' ? 'btn-light' : 'btn-light'); ?> float-right openPopup" data-href="includes/getnews.php?id=<?php echo $newsl['id']; ?>">Read More</button></div>
                    </div>

                <?php } ?>
            </div>
        </div>

    </div>

    <div class="modal fade" id="myModal" role="dialog">
        <div class="modal-dialog modal-lg">
            <!-- Modal content-->
            <div class="modal-body">
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $('.openPopup').on('click', function() {
                var dataURL = $(this).attr('data-href');
                $('.modal-body').load(dataURL, function() {
                    $('#myModal').modal({
                        show: true
                    });
                });
            });
        });
    </script>
    <?php
    require_once('includes/footer.php');
    ?>