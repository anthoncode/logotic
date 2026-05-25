<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

require_once '../system/config-admin.php';
//$productDetails = $product->details($_REQUEST['id']);
$output = array('error' => false);


//collect the passed id
//$id = $_GET['cat_id'];

//run a query 
//$stmt = $DB_con->query('SELECT * FROM ' . PFX . 'fonts WHERE fontId = '.$fontId.' ORDER BY fontFile');

$stmt = $DB_con->query('SELECT * FROM ' . PFX . 'products ORDER BY id DESC LIMIT 1'); //esto esta seleccionando el ultimo producto
//$stmt = $DB_con->lastInsertId();
//loop through all returned rows
while($row = $stmt->fetch(PDO::FETCH_OBJ)) {
  //  echo "<option value='$row->id'>$row->fontFile</option>";
  $id = $row->id;
  $name = $row->name;
  $desc = $row->description;
  $logo = $row->icon_img;
  $tags = $row->tags;





  echo '<form id="upload3" method="post">';
echo '<div class="col-lg-12">';


echo '<div class="form-group">';
echo      '<input class="form-control" type="hidden" placeholder="id" name="id" id="id" type="text" value="'. $id .'">';
echo  '</div>';

echo '<img itemprop="image" class="pull-left thumb-logo m-r-md rounded mr-3 mb-3" src="'.$setting['website_url'].'/system/assets/uploads/vector-files/'. $logo .'" width="80" height="80">';

echo '<div class="form-group mx-sm-3 mb-2">';
  echo '<div class="form-group">';
  echo      '<input class="form-control" placeholder="Name" name="name_gr" id="name" type="text" value="'. $name .'">';
  echo  '</div>';

  echo '<div class="form-group">';
  echo      '<input class="form-control" placeholder="Description" name="desc_gr" id="description" type="text" value="'. $desc .'">';
  echo  '</div>';

  echo '<div class="form-group">';
  echo      '<input class="form-control" placeholder="Tags" name="tags_gr" id="skills" data-role="tagsinput" type="text" value="'. $tags .'">';
  echo  '</div>';


    echo '<input type="button" id="submitFormData'. $id .'" onclick="SubmitFormData'. $id .'();" value="Edit" class="btn btn-success btn-block text-white ml-1 mt-0"/><i class="fas fa-pen"></i>';


echo "</div>";

echo "</div>";

echo '</form>';

//return $logo;
//exit();

  

}





?>



  <!-- el usuario puede editar los nombres de los archivos enviados -->
<script type="text/javascript">
  function SubmitFormData() {
      //tinyMCE.triggerSave();

      //$('input').attr('name', 'new_name')

      var id = $("#id").val();
      var name = $("input[name='name_gr']").val();
      //var description = $("#description").val();
      var description = $("input[name='desc_gr']").val();
      var tags = $("input[name='tags_gr']").val();
      $.post("<?php echo $setting['website_url']; ?>/admin/ajax-update-list.php", { id: id, name: name, description: description, tags: tags},
      function(data) {
     $('#results').html(data);
     //$('#upload3')[0].reset();
      });
  }
</script>