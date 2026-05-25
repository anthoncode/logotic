<?php

class Product
{
	var $error = false;
	var $msg = false;

	private $db;

	function __construct($DB_con)
	{
		$this->db = $DB_con;
	}

	public function all()
	{
		$result = $this->db->prepare("SELECT * FROM  " . PFX . "products WHERE active = 1 ORDER BY `id` DESC");
		$result->execute();
		$products = array();
		while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
			$products[] = $row;
		}
		return $products;
	}

	static function formatName($str, $options = array())
	{   //formatea y quita carácteres como acentos y otros signos
		// Make sure string is in UTF-8 and strip invalid UTF-8 characters
  $str = mb_convert_encoding((string)$str, 'UTF-8', mb_list_encodings());
  
  $defaults = array(
    'delimiter' => '-',
    'limit' => null,
    'lowercase' => true,
    'replacements' => array(),
    'transliterate' => false,
  );
  
  // Merge options
  $options = array_merge($defaults, $options);
  
  $char_map = array(
    // Latin
    'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE', 'Ç' => 'C', 
    'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 
    'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ő' => 'O', 
    'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ű' => 'U', 'Ý' => 'Y', 'Þ' => 'TH', 
    'ß' => 'ss', 
    'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae', 'ç' => 'c', 
    'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 
    'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ő' => 'o', 
    'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ű' => 'u', 'ý' => 'y', 'þ' => 'th', 
    'ÿ' => 'y',

    // Latin symbols
    '©' => '(c)',

    // Greek
    'Α' => 'A', 'Β' => 'B', 'Γ' => 'G', 'Δ' => 'D', 'Ε' => 'E', 'Ζ' => 'Z', 'Η' => 'H', 'Θ' => '8',
    'Ι' => 'I', 'Κ' => 'K', 'Λ' => 'L', 'Μ' => 'M', 'Ν' => 'N', 'Ξ' => '3', 'Ο' => 'O', 'Π' => 'P',
    'Ρ' => 'R', 'Σ' => 'S', 'Τ' => 'T', 'Υ' => 'Y', 'Φ' => 'F', 'Χ' => 'X', 'Ψ' => 'PS', 'Ω' => 'W',
    'Ά' => 'A', 'Έ' => 'E', 'Ί' => 'I', 'Ό' => 'O', 'Ύ' => 'Y', 'Ή' => 'H', 'Ώ' => 'W', 'Ϊ' => 'I',
    'Ϋ' => 'Y',
    'α' => 'a', 'β' => 'b', 'γ' => 'g', 'δ' => 'd', 'ε' => 'e', 'ζ' => 'z', 'η' => 'h', 'θ' => '8',
    'ι' => 'i', 'κ' => 'k', 'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ξ' => '3', 'ο' => 'o', 'π' => 'p',
    'ρ' => 'r', 'σ' => 's', 'τ' => 't', 'υ' => 'y', 'φ' => 'f', 'χ' => 'x', 'ψ' => 'ps', 'ω' => 'w',
    'ά' => 'a', 'έ' => 'e', 'ί' => 'i', 'ό' => 'o', 'ύ' => 'y', 'ή' => 'h', 'ώ' => 'w', 'ς' => 's',
    'ϊ' => 'i', 'ΰ' => 'y', 'ϋ' => 'y', 'ΐ' => 'i',

    // Turkish
    'Ş' => 'S', 'İ' => 'I', 'Ç' => 'C', 'Ü' => 'U', 'Ö' => 'O', 'Ğ' => 'G',
    'ş' => 's', 'ı' => 'i', 'ç' => 'c', 'ü' => 'u', 'ö' => 'o', 'ğ' => 'g', 

    // Russian
    'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh',
    'З' => 'Z', 'И' => 'I', 'Й' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O',
    'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
    'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sh', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu',
    'Я' => 'Ya',
    'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh',
    'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
    'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c',
    'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sh', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu',
    'я' => 'ya',

    // Ukrainian
    'Є' => 'Ye', 'І' => 'I', 'Ї' => 'Yi', 'Ґ' => 'G',
    'є' => 'ye', 'і' => 'i', 'ї' => 'yi', 'ґ' => 'g',

    // Czech
    'Č' => 'C', 'Ď' => 'D', 'Ě' => 'E', 'Ň' => 'N', 'Ř' => 'R', 'Š' => 'S', 'Ť' => 'T', 'Ů' => 'U', 
    'Ž' => 'Z', 
    'č' => 'c', 'ď' => 'd', 'ě' => 'e', 'ň' => 'n', 'ř' => 'r', 'š' => 's', 'ť' => 't', 'ů' => 'u',
    'ž' => 'z', 

    // Polish
    'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'e', 'Ł' => 'L', 'Ń' => 'N', 'Ó' => 'o', 'Ś' => 'S', 'Ź' => 'Z', 
    'Ż' => 'Z', 
    'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n', 'ó' => 'o', 'ś' => 's', 'ź' => 'z',
    'ż' => 'z',

    // Latvian
    'Ā' => 'A', 'Č' => 'C', 'Ē' => 'E', 'Ģ' => 'G', 'Ī' => 'i', 'Ķ' => 'k', 'Ļ' => 'L', 'Ņ' => 'N', 
    'Š' => 'S', 'Ū' => 'u', 'Ž' => 'Z',
    'ā' => 'a', 'č' => 'c', 'ē' => 'e', 'ģ' => 'g', 'ī' => 'i', 'ķ' => 'k', 'ļ' => 'l', 'ņ' => 'n',
    'š' => 's', 'ū' => 'u', 'ž' => 'z'
  );
  
  // Make custom replacements
  $str = preg_replace(array_keys($options['replacements']), $options['replacements'], $str);
  
  // Transliterate characters to ASCII
  if ($options['transliterate']) {
    $str = str_replace(array_keys($char_map), $char_map, $str);
  }
  
  // Replace non-alphanumeric characters with our delimiter
  $str = preg_replace('/[^\p{L}\p{Nd}]+/u', $options['delimiter'], $str);
  
  // Remove duplicate delimiters
  $str = preg_replace('/(' . preg_quote($options['delimiter'], '/') . '){2,}/', '$1', $str);
  
  // Truncate slug to max. characters
  $str = mb_substr($str, 0, ($options['limit'] ? $options['limit'] : mb_strlen($str, 'UTF-8')), 'UTF-8');
  
  // Remove delimiter from ends
  $str = trim($str, $options['delimiter']);

  $str = strtolower(trim(preg_replace('~[^0-9a-z]+~i', '-', html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($str, ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8')), '-'));
  
  return $options['lowercase'] ? mb_strtolower($str, 'UTF-8') : $str;

		//return $str;
	}

	static function getFileExtension($name)
	{
		return explode(".", strtolower($name));
	}


	public function getProducts($start, $total)
	{
		$result = $this->db->prepare("SELECT * FROM  " . PFX . "products WHERE active = 1 OR active = 2 ORDER BY `id` DESC LIMIT $start , $total");
		$result->execute();
		$products = array();
		while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
			$products[] = $row;
		}
		return $products;
	}

	public function getLogoList($start, $total, $search)
	{
		$result = $this->db->prepare("SELECT * FROM  " . PFX . "products WHERE active = 1 AND name LIKE '%$search%' ORDER BY `id` DESC LIMIT $start , $total" );
		$result->execute();
		$products = array();
		while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
			$products[] = $row;
		}
		return $products;
	}
	
	public function getAllDuplicateLogos($start, $total, $search)
	{
		$result = $this->db->prepare("SELECT * FROM " . PFX . "products u WHERE name IN (
        SELECT name FROM " . PFX . "products 
            GROUP BY name HAVING COUNT(*)>1)  
		ORDER BY `u`.`name`  DESC LIMIT $start , $total");

		$result->execute();
		$products = array();
		while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
			$products[] = $row;
		}
		return $products;
	}

	public function getLogoListPending($start, $total, $search)
	{
		$result = $this->db->prepare("SELECT * FROM  " . PFX . "products WHERE active = 0 AND name LIKE '%$search%' ORDER BY `id` DESC LIMIT $start , $total" );
		$result->execute();
		$products = array();
		while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
			$products[] = $row;
		}
		return $products;
	}

	public function getPending($start, $total)
	{
		$result = $this->db->prepare("SELECT * FROM  " . PFX . "products WHERE active = 0 ORDER BY `id` DESC LIMIT $start , $total");
		$result->execute();
		$products = array();
		while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
			$products[] = $row;
		}
		return $products;
	}

	public function getCProducts($id = null, $cat_id = null)
	{
		$result = $this->db->prepare("SELECT * FROM  " . PFX . "products WHERE active = 1 AND cat_id = '$cat_id' ORDER BY `id` DESC");
		$result->execute();
		$products = array();
		while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
			$products[] = $row;
		}
		return $products;
	}

	public function getCSProducts($id = null, $sub_id = null)
	{
		$result = $this->db->prepare("SELECT * FROM  " . PFX . "products WHERE active = 1 AND subc_id = '$sub_id' ORDER BY `id` DESC");
		$result->execute();
		$products = array();
		while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
			$products[] = $row;
		}
		return $products;
	}

	public function getFeaturedProducts()
	{
		$result = $this->db->prepare("SELECT * FROM  " . PFX . "products WHERE active = 1 AND featured = 1 ORDER BY `id` LIMIT 18");
		$result->execute();
		$products = array();
		while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
			$products[] = $row;
		}
		return $products;
	}

	/*most downloaded - mas descargados*/
	/*most downloaded - mas descargados*/
	public function getPopularProducts($limit = 18, $offset = 0)
	{
    $query = "
        SELECT p.*, COUNT(d.products_id) AS totalDescargas
        FROM " . PFX . "downloads d
        INNER JOIN " . PFX . "products p ON p.id = d.products_id
        WHERE p.active = 1
        GROUP BY d.products_id
        ORDER BY COUNT(d.products_id) DESC
        LIMIT :limit OFFSET :offset
    ";

    $result = $this->db->prepare($query);
    $result->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $result->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $result->execute();

    $products = $result->fetchAll(PDO::FETCH_ASSOC);
    $result->closeCursor(); // Cierra conexión abierta

    return $products;
	}

	/*public function getPopularProducts()
	{
    $query = "
        SELECT p.*, COUNT(d.products_id) AS totalDescargas
        FROM " . PFX . "downloads d
        INNER JOIN " . PFX . "products p ON p.id = d.products_id
        WHERE p.active = 1
        GROUP BY d.products_id
        ORDER BY COUNT(d.products_id) DESC
        LIMIT 27
    ";

    $result = $this->db->prepare($query);
    $result->execute();

    $products = $result->fetchAll(PDO::FETCH_ASSOC);
    $result->closeCursor(); // Cierra conexión abierta

    return $products;
	}*/

	public function getNewProducts()
	{
		$result = $this->db->prepare("SELECT * FROM  " . PFX . "products WHERE active = 1 ORDER BY `id` DESC LIMIT 4");
		$result->execute();
		$products = array();
		while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
			$products[] = $row;
		}
		return $products;
	}


	/*funcion para inifinite scroll en inicio*/
	public function posts($page_num) {
    $Per_Page = 18;
    if (isset($page_num)) {
        $page_num = $page_num;
    } else {
        $page_num = 1;
    }

    $Page_Start = ($page_num - 1) * $Per_Page;

    $result = $this->db->prepare("SELECT * FROM  " . PFX . "products WHERE active = 1 ORDER BY `id` DESC LIMIT $Page_Start,$Per_Page");

		$result->execute();
		$products = array();
		while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
			$products[] = $row;
		}
		return $products;
	}

	/*infinite scroll para subcategorías*/
	public function subcateLogos($page_num, $id = null, $sub_id = null) {

    $Per_Page = 48; //número de columnas para mostrar logotipos
    if (isset($page_num)) {
        $page_num = $page_num;
    } else {
        $page_num = 1;
    }
    $Page_Start = ($page_num - 1) * $Per_Page;
    //WHERE active = 1 AND subc_id = '$sub_id' ORDER BY `id` DESC
    $result = $this->db->prepare("SELECT * FROM  " . PFX . "products WHERE active = 1 AND subc_id = '$sub_id' ORDER BY `id` DESC LIMIT $Page_Start,$Per_Page");

		$result->execute();
		$products = array();
		while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
			$products[] = $row;
		}
		return $products;
	}

	/*infinite scroll para categorías*/
	public function cateLogos($page_num, $id = null, $cat_id = null) {
    $Per_Page = 48;
    if (isset($page_num)) {
        $page_num = $page_num;
    } else {
        $page_num = 1;
    }
    $Page_Start = ($page_num - 1) * $Per_Page;

    $result = $this->db->prepare("SELECT * FROM  " . PFX . "products WHERE active = 1 AND cat_id = '$cat_id' ORDER BY `id` DESC LIMIT $Page_Start,$Per_Page");

		$result->execute();
		$products = array();
		while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
			$products[] = $row;
		}
		return $products;
	}

		/*infinite scroll para tags*/
		public function tagLogos($page_num, $tags = null) {
	    $Per_Page = 48;
	    if (isset($page_num)) {
	        $page_num = $page_num;
	    } else {
	        $page_num = 1;
	    }
	    $Page_Start = ($page_num - 1) * $Per_Page;

	    $result = $this->db->prepare("SELECT * FROM  " . PFX . "products WHERE active = 1 AND tags LIKE '%$tags%' ORDER BY `id` DESC LIMIT $Page_Start,$Per_Page");

			$result->execute();
			$products = array();
			while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
				$products[] = $row;
			}
			return $products;
		}


	public function getAllProducts()
	{
		$result = $this->db->prepare("SELECT * FROM  " . PFX . "products WHERE active = 1 ORDER BY `id` DESC");
		$result->execute();
		$products = array();
		while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
			$products[] = $row;
		}
		return $products;
	}

	/*get similar tag*/
	public function getSimilarProducts($tags = null)
	{	
		//$result = $this->db->prepare("SELECT * FROM  " . PFX . "products WHERE active = 1 AND tags LIKE '%$tags%' LIMIT 9");
		$result = $this->db->prepare("SELECT * FROM  " . PFX . "products WHERE active = 1 AND tags REGEXP '$tags' LIMIT 32");
		$result->execute();
		$products = array();
		while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
			$products[] = $row;
		}
		return $products;
	}

	/*public function getSimilarProducts($sub_cat = null)
	{	
		$result = $this->db->prepare("SELECT * FROM  " . PFX . "products WHERE active = 1 AND subc_id = $sub_cat LIMIT 3");
		$result->execute();
		$products = array();
		while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
			$products[] = $row;
		}
		return $products;
	}*/

	/*public function getFreeProducts()
	{
		$result = $this->db->prepare("SELECT * FROM  " . PFX . "products WHERE active = 1 AND free = 1 ORDER BY `id` DESC LIMIT 6");
		$result->execute();
		$products = array();
		while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
			$products[] = $row;
		}
		return $products;
	}*/

	public function countDownload()
	{
		$result = $this->db->prepare("SELECT count(*) as  products_id FROM  " . PFX . "downloads");
		$result->execute();
		$products = $result->fetchColumn();
		return $products;
	}

	public function countAll($search = null) //envia un valor nulo si no existe
	{

		$result = $this->db->prepare("SELECT count(*) FROM  " . PFX . "products WHERE name LIKE '%$search%' AND active = 1 ORDER BY `id` DESC");
		$result->execute();
		$products = $result->fetchColumn();
		return $products;
	}

	public function countAllPending($search = null) //envia un valor nulo si no existe
	{

		$result = $this->db->prepare("SELECT count(*) FROM  " . PFX . "products WHERE name LIKE '%$search%' AND active = 0 ORDER BY `id` DESC");
		$result->execute();
		$products = $result->fetchColumn();
		return $products;
	}

	public function countPending() //envia un valor nulo si no existe
	{
		$result = $this->db->prepare("SELECT count(*) FROM  " . PFX . "products WHERE active = 0 ORDER BY `id` DESC");
		$result->execute();
		$products = $result->fetchColumn();
		return $products;
	}

	public function countUpload($id)
	{

		$result = $this->db->prepare("SELECT count(*) FROM  " . PFX . "products WHERE active = 1 AND submit_user_id = '$id' ORDER BY `id` DESC");
		$result->execute();
		$products = $result->fetchColumn();
		return $products;
	}


	public function top()
	{

		$result = $this->db->prepare("SELECT *, count(products_id) AS totalDescargas FROM " . PFX . "downloads INNER JOIN " . PFX . "products
ON " . PFX . "products.id = " . PFX . "downloads.products_id WHERE active = 1 GROUP BY products_id ORDER BY SUM(products_id) DESC LIMIT 10");
		$result->execute();
		$products = array();
		while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
			$products[] = $row;
		}
		return $products;
	}

	public function is_product($id)
	{

		$result = $this->db->prepare("SELECT active FROM  " . PFX . "products WHERE id = '$id' AND  active = 1");
		$result->execute();

		if ($result) {
			return true;
		}
		$this->error = "No such product exists";
		return false;
	}

	public function details($id)
	{
		if ($this->is_product($id)) {

			$result = $this->db->prepare("SELECT * FROM  " . PFX . "products WHERE id = :id");
			$result->bindParam(':id', $id);
			$result->execute();

			while ($result = $result->fetch(PDO::FETCH_ASSOC)) {
				return $result;
			}
		}
		return false;
	}


	public function catdetails($id)
	{

		$result = $this->db->prepare("SELECT * FROM  " . PFX . "categories WHERE id = :id");
		$result->bindParam(':id', $id);
		$result->execute();

		while ($result = $result->fetch(PDO::FETCH_ASSOC)) {
			return $result;
		}
	}

	public function tagdetailsCate()
	{
		//products WHERE tags LIKE '%$tags%' AND active = 1
		$result = $this->db->prepare("SELECT * FROM  " . PFX . "categories");
		$result->bindParam(':id', $id);
		$result->execute();

		while ($result = $result->fetch(PDO::FETCH_ASSOC)) {
			return $result;
		}
	}

	public function scatdetails($id)
	{

		$result = $this->db->prepare("SELECT * FROM  " . PFX . "subcat WHERE id = :id");
		$result->bindParam(':id', $id);
		$result->execute();

		while ($result = $result->fetch(PDO::FETCH_ASSOC)) {
			return $result;
		}
	}

	/*limon*/
	/*public function tagdetails($tags){

				//$parameter = ["%$tags%"];
				
			$result = $this->db->prepare("SELECT * FROM " . PFX . "products WHERE tags LIKE '%:tags%'");
	      $result->bindParam(':tags', $tags);
			$result->execute();
			//$result = count($tags);
				
				while($result=$result->fetch(PDO::FETCH_ASSOC)){
				return $result;
				}
		}
*/

	public function getTProducts($id = null, $tags = null)
	{
		$result = $this->db->prepare("SELECT * FROM  " . PFX . "products WHERE tags LIKE '%$tags%' AND active = 1");
		$result->execute();
		$products = array();
		while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
			$products[] = $row;
		}
		return $products;
	}

	/*author*/
	public function getAuthor($id)
	{
		$result = $this->db->prepare("SELECT * FROM " . PFX . "users INNER JOIN " . PFX . "products ON " . PFX . "products.submit_user_id=" . PFX . "users.id WHERE " . PFX . "products.id=:id");
		$result->bindParam(':id', $id);
		$result->execute();

		while ($result = $result->fetch(PDO::FETCH_ASSOC)) {
			return $result;
		}
	}

	public function addDownload($pro_id, $ip, $date)
	{
		/*$message1 = trim($message1);
		if (empty($message1)) {
			$this->error = 'Please input all details';
			return false;
		}*/
		$add = $this->db->prepare("INSERT INTO " . PFX . "downloads ( `products_id`, `ip_address`, `date_created`) VALUES ( '$pro_id', '$ip', '$date')");
		$add->execute();
		/*if ($add) {
			$this->msg = "Review added successfully";
			return true;
		}
		$this->error = 'Review hmmm';*/
		return true;
	}

	/*Download Count*/ /*The result is stored in doCount*/
	public function downloadCount($id)
	{
		$result = $this->db->prepare("SELECT COUNT(DISTINCT " . PFX . "downloads.id) as doCount FROM " . PFX . "downloads INNER JOIN " . PFX . "products ON " . PFX . "products.id=" . PFX . "downloads.products_id WHERE " . PFX . "products.id=:id");

		$result->bindParam(':id', $id);
		$result->execute();

		while ($result = $result->fetch(PDO::FETCH_ASSOC)) {
			return $result;
		}
	}
	
	function formatCount($number) {
    if ($number >= 1000000) {
        return round($number / 1000000, 1) . 'M';
    } elseif ($number >= 1000) {
        return round($number / 1000, 1) . 'K';
    }
    return $number;
}


	public function ToProducts($id = null, $name = null)
	{
		$result = $this->db->prepare("SELECT * FROM  " . PFX . "products WHERE name LIKE '%$name%'");
		$result->execute();
		$products = array();
		while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
			$products[] = $row;
		}
		return $products;
	}

	public function ToDownloads($id = null, $user = null)
	{
		$result = $this->db->prepare("SELECT * FROM  " . PFX . "downloads WHERE user_id LIKE '%$user%'");
		$result->execute();
		$products = array();
		while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
			$products[] = $row;
		}
		return $products;
	}

	public function show_filesize($filename, $decimalplaces = 0)
	{

		$size = filesize($filename);
		$sizes = array('B', 'kB', 'MB', 'GB', 'TB');

		for ($i = 0; $size > 1024 && $i < count($sizes) - 1; $i++) {
			$size /= 1024;
		}
		return round($size, $decimalplaces) . ' ' . $sizes[$i];
	}




	public function add_sale($id)
	{
		if ($this->is_product($id)) {
			$sales = $this->details($id);
			$sales = $sales['sales'] + 1;
			$update = $this->db->prepare("UPDATE " . PFX . "products  SET `sales` = '$sales' WHERE id ='$id'");
			$update->execute();
			if ($update) {
				return true;
			}
			return false;
		}
	}

	public function remove($id)
	{
		$detail = $this->details($id);
		if ($this->is_product($id)) {
			$update = $this->db->prepare("DELETE from " . PFX . "products WHERE id ='$id'");
			$update->execute();
			if ($update) {
				$this->msg = "Product removed successfully";
				return true;
				
			}
			$this->error = "Error removing product";
			return false;
		}
		$this->error = "Error removing product";
		return false;
	}
	//Categories start here	
	public function countAllCat()
	{

		$result = $this->db->prepare("SELECT count(*) FROM  " . PFX . "categories ORDER BY `id` DESC");
		$result->execute();
		$category = $result->fetchColumn();
		return $category;
	}

	public function countAllSCat()
	{

		$result = $this->db->prepare("SELECT count(*) FROM  " . PFX . "subcat ORDER BY `id` DESC");
		$result->execute();
		$category = $result->fetchColumn();
		return $category;
	}

	public function countAllCatDeleted()
	{

		$result = $this->db->prepare("SELECT count(*) FROM  " . PFX . "categories WHERE active = 0");
		$result->execute();
		$categories = $result->fetchColumn();
		return $categories;
	}

	public function countAllCatSDeleted()
	{

		$result = $this->db->prepare("SELECT count(*) FROM  " . PFX . "subcat WHERE active = 0");
		$result->execute();
		$categories = $result->fetchColumn();
		return $categories;
	}

	public function get_categories($id = null)
	{

		$query = $this->db->prepare("SELECT * FROM " . PFX . "categories WHERE active = 1");
		$query->execute();
		$categories = array();
		while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
			$categories[] = $row;
		}

		return $categories;
	}

	public function get_scategories($id = null)
	{

		$query = $this->db->prepare("SELECT * FROM " . PFX . "subcat WHERE active = 1");
		$query->execute();
		$categories = array();
		while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
			$categories[] = $row;
		}

		return $categories;
	}

	public function dispsubcategories($parent_id)
	{

		$query = $this->db->prepare("SELECT * FROM " . PFX . "subcat WHERE active = 1 AND cat_id = :parent_id ORDER BY name ASC");
		$query->bindParam(':parent_id', $parent_id);
		$query->execute();
		$scategories = array();
		while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
			$scategories[] = $row;
		}
		return $scategories;
	}

	public function getReviews($id)
	{

		$query = $this->db->prepare("SELECT * FROM " . PFX . "reviews WHERE product_id = $id AND status = 1");
		$query->execute();
		$reviews = array();
		while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
			$reviews[] = $row;
		}

		return $reviews;
	}

	public function is_alreadyadd($userID, $productID)
	{

		$result = $this->db->prepare("SELECT * FROM  " . PFX . "reviews WHERE user_id = '$userID' AND product_id = '$productID' AND status = '1'");
		$result->execute();
		if ($result->fetchColumn() >= 1) {
			return true;
		}
		$this->error = "No such review exists";
		return false;
	}

	public function countReview($pid)
	{

		$result = $this->db->prepare("SELECT count(id) FROM  " . PFX . "reviews WHERE product_id = ? AND status = 1");
		$result->execute(array($pid));
		$reviews = $result->fetchColumn();
		return $reviews;
	}

	public function addreview($pid, $uid, $message1, $rating)
	{

		$message1 = trim($message1);
		if (empty($message1)) {
			$this->error = 'Please input all details';
			return false;
		}
		$add = $this->db->prepare("INSERT INTO " . PFX . "reviews (`product_id`, `user_id`, `review`, `rating`, `status`) VALUES ('$pid', '$uid', '$message1', '$rating', '1')");
		$add->execute();
		if ($add) {
			$this->msg = "Review added successfully";
			return true;
		}
		$this->error = 'Review hmmm';
		return false;
	}

	public function addcat($name)
	{

		$name = trim($name);
		if (empty($name)) {
			$this->error = 'Please input all details';
			return false;
		}
		$add = $this->db->prepare("INSERT INTO " . PFX . "categories (`id`, `name`, `active`) VALUES (NULL, :name, '1')");
		$add->bindParam(':name', $name);
		$add->execute();
		if ($add) {
			$this->msg = "Category added successfully";
			return true;
		}
		$this->error = 'Category saved';
		return false;
	}

	public function addscat($name, $cid)
	{

		$name = trim($name);
		if (empty($name)) {
			$this->error = 'Please input all details';
			return false;
		}
		$add = $this->db->prepare("INSERT INTO " . PFX . "subcat (`name`, `cat_id`, `active`) VALUES (:name, :cid, '1')");
		$add->bindParam(':name', $name);
		$add->bindParam(':cid', $cid);
		$add->execute();
		if ($add) {
			$this->msg = "SubCategory added successfully";
			return true;
		}
		$this->error = 'SubCategory saved';
		return false;
	}

	public function updatecat($id, $name)
	{

		$name = trim($name);
		if ($this->is_cat($id)) {
			if (empty($name)) {
				$this->error = 'Please input all details';
				return false;
			}
			$update = $this->db->prepare("UPDATE " . PFX . "categories  SET `name` = :name WHERE id = :id");
			$update->bindParam(':name', $name);
			$update->bindParam(':id', $id);
			$update->execute();
			if ($update) {
				$this->msg = "Category updated successfully";
				return true;
			}
			$this->error = "Error saving Category";
			return false;
		}
		$this->error = "Error saving Category";
		return false;
	}

	public function is_cat($id)
	{

		$result = $this->db->prepare("SELECT active FROM  " . PFX . "categories WHERE id = '$id' AND  active = 1");
		$result->execute();

		if ($result) {
			return true;
		}
		$this->error = "No such category exists";
		return false;
	}

	public function updatescat($id, $name, $cid)
	{

		$name = trim($name);
		if ($this->is_scat($id)) {
			if (empty($name)) {
				$this->error = 'Please input all details';
				return false;
			}
			$update = $this->db->prepare("UPDATE " . PFX . "subcat  SET `name` = :name, `cat_id` = :cid WHERE id = :id");
			$update->bindParam(':name', $name);
			$update->bindParam(':cid', $cid);
			$update->bindParam(':id', $id);
			$update->execute();
			if ($update) {
				$this->msg = "SubCategory updated successfully";
				return true;
			}
			$this->error = "Error saving SubCategory";
			return false;
		}
		$this->error = "Error saving SubCategory";
		return false;
	}

	public function is_scat($id)
	{

		$result = $this->db->prepare("SELECT active FROM  " . PFX . "subcat WHERE id = '$id' AND  active = 1");
		$result->execute();

		if ($result) {
			return true;
		}
		$this->error = "No such subcategory exists";
		return false;
	}

	public function removecat($id)
	{

		if ($this->is_cat($id)) {
			$update = $this->db->prepare("UPDATE " . PFX . "categories  SET `active` = '0' WHERE id ='$id'");
			$update->execute();

			if ($update) {
				$this->msg = "Category Removed Successfully";
				return true;
			}
			$this->error = "Error removing Category";
			return false;
		}
		$this->error = "Error removing Category";
		return false;
	}
	public function removescat($id)
	{

		if ($this->is_cat($id)) {
			$update = $this->db->prepare("UPDATE " . PFX . "subcat  SET `active` = '0' WHERE id ='$id'");
			$update->execute();

			if ($update) {
				$this->msg = "SubCategory Removed Successfully";
				return true;
			}
			$this->error = "Error removing SubCategory";
			return false;
		}
		$this->error = "Error removing SubCategory";
		return false;
	}

	public function getDeletedCategories($start, $total)
	{

		$result = $this->db->prepare("SELECT * FROM  " . PFX . "categories WHERE active = 0 ORDER BY `id` DESC LIMIT $start , $total");
		$result->execute();
		$categories = array();
		while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
			$categories[] = $row;
		}
		return $categories;
	}

	public function getDeletedSubCategories()
	{

		$result = $this->db->prepare("SELECT * FROM  " . PFX . "subcat WHERE active = 0 ORDER BY `id`");
		$result->execute();
		$categories = array();
		while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
			$categories[] = $row;
		}
		return $categories;
	}

	public function restorecat($id)
	{

		$update = $this->db->prepare("UPDATE " . PFX . "categories  SET `active` = '1' WHERE id ='$id'");
		$update->execute();

		if ($update) {
			$this->msg = "Category restored successfully";
			return true;
		}
		$this->error = "Error restoring category";
		return false;
	}

	public function restorescat($id)
	{

		$update = $this->db->prepare("UPDATE " . PFX . "subcat  SET `active` = '1' WHERE id ='$id'");
		$update->execute();

		if ($update) {
			$this->msg = "SubCategory restored successfully";
			return true;
		}
		$this->error = "Error restoring subcategory";
		return false;
	}

	public function details_downloads($id)
	{
		if ($this->is_product($id)) {

			$result = $this->db->prepare("SELECT * FROM  " . PFX . "products WHERE id = :id");
			$result->bindParam(':id', $id);
			$result->execute();

			while ($result = $result->fetch(PDO::FETCH_ASSOC)) {
				return $result;
			}
		}
		return false;
	}


/*	public function add_download($userID,$productID){
	global $crypt;
	global $user;
	global $product;

	    $userID = $crypt->decrypt($userID,'USER');
	    $add = $this->db->prepare("INSERT INTO " . PFX . "sales (`pro_id`, `user_id`) VALUES (:pid, :uid)");
	    $add->bindparam(":pid",$productID);
	$add->bindparam(":uid",$userID);
	$add->execute();
	  if($add){
    	return true;
		}
		$this->error = "Failed to add to your wishlist";
		return false;
		
	}*/
}
