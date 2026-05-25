<?php 
class Pages{

private $db;
	
	function __construct($DB_con)
	{
		$this->db = $DB_con;
	}

	public function get_all_pages(){

		$query = $this->db->prepare("SELECT * FROM " . PFX . "custompages WHERE active = 1 OR active = 2 order by indate desc");
		$query->execute();

		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

public function is_page($id){
		
		$result = $this->db->prepare("SELECT active FROM  " . PFX . "custompages WHERE id = ? AND  active = 1");
		$result->execute(array($id));
		if ($result){
    	return true;
		}
		$this->error = "No such page exists";
		return false;
		
}

	public function details($id){
		if($this->is_page($id)){
			
			$result = $this->db->prepare("SELECT * FROM  " . PFX . "custompages WHERE id = ?");
		$result->execute(array($id));
			
			while($result=$result->fetch(PDO::FETCH_ASSOC)){
			return $result;
			}
			}
		return false;
	}

public function add($title,$content,$level){
    
		$title = trim($title);
		$slug = Product::formatName($title);
		$content = trim($content);
		$date = date("Y-m-d H:i:s");
	    if(empty($title) || empty($content)){
		$this->error = 'Please input all details';
		return false;
		}
		$add = $this->db->prepare("INSERT INTO " . PFX . "custompages (`title`, `slug_page`, `content`, `indate`, `level`, `active`) VALUES (:title, :slug_page, :content, '$date', :level, '1')");
		$add->bindParam(':slug_page', $slug);
        $add->bindParam(':title', $title);
        $add->bindParam(':content', $content);
        $add->bindParam(':level', $level);
		$add->execute();
	    	if($add){
		$this->msg = "Custom Page added successfully";
		return true;
		}	
		$this->error = 'Error saving custom page';
		return false;	
		
}

public function remove($id){
    
		if($this->is_page($id)){
		$update = $this->db->prepare("UPDATE " . PFX . "custompages SET `active` = '0' WHERE id = ?");
		$update->execute(array($id));
								
	    if($update){
		$this->msg = "Custom Page removed successfully";
		return true;
	    }
	    $this->error = "Error removing Custom Page";
	    return false;
	    }
	    $this->error = "Error removing Custom Page";
	    return false;
	    
}	

public function restore($id){
		
		$update = $this->db->prepare("UPDATE " . PFX . "custompages SET `active` = '1' WHERE id = ?");
		$update->execute(array($id));
								
	    if($update){
		$this->msg = "Custom Page restored successfully";
		return true;
	    }
	    $this->error = "Error restoring Custom Page";
	    return false;
	    
}

public function updatepages($id,$title,$slug,$content,$level,$active){

		$title = trim($title);
		$slug = Product::formatName($title);
		$content = trim($content);
		if($this->is_page($id)){
		if(empty($title) || empty($content)){
		$this->error = 'Please input all details';
		return false;
		}
		$update = $this->db->prepare("UPDATE " . PFX . "custompages  SET slug_page=:slug,title=:title,content=:content,level=:level,active=:active WHERE id=:id");
		$update->bindParam(':slug', $slug);
		$update->bindParam(':title', $title);
        $update->bindParam(':content', $content);
        $update->bindParam(':level', $level);
        $update->bindParam(':active', $active);
        $update->bindParam(':id', $id);
		$update->execute();
		if($update){
		$this->msg = "News updated successfully";
		return true;
	}
	    	$this->error = "Error saving News";
	    	return false;
	    	}
    		$this->error = "Error saving News";
	    	return false;

}

public function getDeletedPages(){
	    
	    $query = $this->db->prepare("SELECT * FROM  " . PFX . "custompages WHERE active = 0 ORDER BY `id` DESC");
		$query->execute();

		return $query->fetchAll(PDO::FETCH_ASSOC);
	    
}

public function countAllDeleted(){
    
    	$result = $this->db->prepare("SELECT count(*) FROM  " . PFX . "custompages WHERE active = 0");
		$result->execute();
		$cpage = $result->fetchColumn();
	    return $cpage;
	
}
public function countAll(){
    
    	$result = $this->db->prepare("SELECT count(*) FROM  " . PFX . "custompages WHERE active = 1");
		$result->execute();
		$cpage = $result->fetchColumn();
	    return $cpage;
	
}

}
