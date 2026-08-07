<?php
class Search{
	
		 	private $db;
	
	function __construct($DB_con)
	{
		$this->db = $DB_con;
	}
	
	//SELECT * FROM  `logotic_products` WHERE (`name` LIKE ? OR `description` LIKE ?) AND status = 'approved'
  public function search($keyword) {
	$exc = $this->db->prepare("SELECT * FROM  " . PFX . "products WHERE (`name` LIKE ? OR `description` LIKE ?) AND status = 'approved'");
	$exc->execute( ['%' . $keyword . '%', '%' . $keyword . '%']);         // Executes an array |>  <> Require PHP >= 5.4.0
        return $exc->fetchAll();
    }
  
  public function get($form) {
   	return (isset($_GET["{$form}"]) ? true : false);                                                
   }

 	public function searchLogos($page_num, $keyword) {
     $Per_Page = 30;
     if (isset($page_num)) {
         $page_num = $page_num;
     } else {
         $page_num = 1;
     }
     $Page_Start = ($page_num - 1) * $Per_Page;

     $exc = $this->db->prepare("SELECT * FROM  " . PFX . "products WHERE (`name` LIKE ? OR `description` LIKE ?) AND status = 'approved' ORDER BY `name` DESC LIMIT $Page_Start,$Per_Page");

 		$exc->execute( ['%' . $keyword . '%', '%' . $keyword . '%']);         // Executes an array |>  <> Require PHP >= 5.4.0
        return $exc->fetchAll();
 	}


}