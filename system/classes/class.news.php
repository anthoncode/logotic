<?php 
class News{

private $db;
	
	function __construct($DB_con)
	{
		$this->db = $DB_con;
	}

	public function get_all_news(){

		$query = $this->db->prepare("SELECT * FROM " . PFX . "news WHERE active = '1' ORDER BY date DESC");
		$query->execute();

		return $query->fetchAll(PDO::FETCH_ASSOC);
	}
		public function get_important_news(){

		$query = $this->db->prepare('SELECT * FROM " . PFX . "news WHERE active = "1" AND important = "1" order by date desc');
		$query->execute();

		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

public function is_news($id){
		
		$result = $this->db->prepare("SELECT active FROM  " . PFX . "news WHERE id = ? AND  active = 1");
		$result->execute(array($id));
		if ($result){
    	return true;
		}
		$this->error = "No such news exists";
		return false;
		
}

	public function details($id){
		if($this->is_news($id)){
			
			$result = $this->db->prepare("SELECT * FROM  " . PFX . "news WHERE id = ?");
		$result->execute(array($id));
			
			while($result=$result->fetch(PDO::FETCH_ASSOC)){
			return $result;
			}
			}
		return false;
	}

public function add($title,$content){
    
		$title = trim($title);
		$content = trim($content);
		$date = date("Y-m-d H:i:s");
	    if(empty($title) || empty($content)){
		$this->error = 'Please input all details';
		return false;
		}
		$add = $this->db->prepare("INSERT INTO " . PFX . "news (`title`, `content`, `date`, `active`, `author`) VALUES (:title, :content, '$date', '1', 'Admin')");
        $add->bindParam(':title', $title);
        $add->bindParam(':content', $content);
		$add->execute();
	    	if($add){
		$this->msg = "News/Annoucement added successfully";
		return true;
		}	
		$this->error = 'Error saving news';
		return false;	
		
}

public function remove($id){
    
		if($this->is_news($id)){
		$update = $this->db->prepare("UPDATE " . PFX . "news SET `active` = '0' WHERE id = ?");
		$update->execute(array($id));
								
	    if($update){
		$this->msg = "News removed successfully";
		return true;
	    }
	    $this->error = "Error removing News";
	    return false;
	    }
	    $this->error = "Error removing news";
	    return false;
	    
}	

public function restore($id){
		
		$update = $this->db->prepare("UPDATE " . PFX . "news SET `active` = '1' WHERE id = ?");
		$update->execute(array($id));
								
	    if($update){
		$this->msg = "news restored successfully";
		return true;
	    }
	    $this->error = "Error restoring news";
	    return false;
	    
}

public function updatenews($id,$title,$content){
    
		$title = trim($title);
		$content = trim($content);
		if($this->is_news($id)){
		if(empty($title) || empty($content)){
		$this->error = 'Please input all details';
		return false;
		}
		$update = $this->db->prepare("UPDATE " . PFX . "news  SET title=:title,content=:content WHERE id=:id");
		$update->bindParam(':title', $title);
        $update->bindParam(':content', $content);
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

public function getDeletedNews(){
	    
	    $query = $this->db->prepare("SELECT * FROM  " . PFX . "news WHERE active = 0 ORDER BY `id` DESC");
		$query->execute();

		return $query->fetchAll(PDO::FETCH_ASSOC);
	    
}

public function countAllDeleted(){
    
    	$result = $this->db->prepare("SELECT count(*) FROM  " . PFX . "news WHERE active = 0");
		$result->execute();
		$news = $result->fetchColumn();
	    return $news;
	
}
public function countAll(){
    
    	$result = $this->db->prepare("SELECT count(*) FROM  " . PFX . "news WHERE active = 1");
		$result->execute();
		$news = $result->fetchColumn();
	    return $news;
	
}

}
