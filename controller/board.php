<?php
	
	session_start();
	
	//카우치 디비에 들어갈 데이터 베이스 이름.
	define("DB_NAME", "board");
	define("DB_ID", "jun");
	define("DB_PASS", "manson");
	define("DB_ADDR", "192.168.0.84:5984");

	header('Access-Control-Allow-Origin: *');  
	
	require_once (__DIR__."/../lib/couch.php");
	require_once (__DIR__."/../lib/couchClient.php");
	require_once (__DIR__."/../lib/couchDocument.php");

	function insertContent($title,$content){
		$client = new couchClient ('http://'.DB_ID.':'.DB_PASS.'@'.DB_ADDR,DB_NAME);
		$id = getCount("content_total");
		$id = $id+1;

		try {
			$doc = new couchDocument($client);
			$doc->set( array(
				'_id'=>(string)$id,
				'type'=>'content',
				'email'=>$_SESSION["email"],
				'title'=>$title,
				'content'=>$content,
				'creation'=>getCreation()
				) );
			$doc = $client->getDoc($id);
			return "OK";
		} catch (Exception $e) {
			return "false";
		}
	}

	function signup($email,$password,$nickname){
		
		if(!filter_var( $email, FILTER_VALIDATE_EMAIL)){
			return "이메일 주소가 올바르지 않습니다.";
		}
		if (strlen($password) < 4) {
			return "암호는 4자 이상 가능합니다.";
		}
		if (strlen($nickname) < 1) {
			return "닉네임을 작성해 주세요.";
		}

		$client = new couchClient ('http://'.DB_ID.':'.DB_PASS.'@'.DB_ADDR,DB_NAME);
		try{
			$doc = $client->getDoc($email);	
			return "이미 가입하셨습니다.";
		} catch (Exception $e) {
			try {
				$doc = new couchDocument($client);
				$doc->set( array(
					'_id'=>$email,'type'=>'account','creation'=>getCreation(),
					'nickname'=>$nickname,'password'=>$password) );
				$doc = $client->getDoc($email);
				$_SESSION["email"]=$email;
				return "OK";
			} catch (Exception $e) {
				return "회원가입 실패";
			}	
		}
	}

	function signout(){
		session_destroy();
	}

	function login($email,$password){
		if(!filter_var( $email, FILTER_VALIDATE_EMAIL)){
			return "이메일 주소가 올바르지 않습니다.";
		}
		$client = new couchClient ('http://'.DB_ID.':'.DB_PASS.'@'.DB_ADDR,DB_NAME);
		try{
			$doc = $client->getDoc($email);	
			if($doc->password === $password){
				$_SESSION["email"]=$email;
				return "OK";
			}else{
				return "암호가 잘 못 되었습니다.";
			}
		} catch (Exception $e) {
			return "이메일 주소가 잘 못 되었습니다.";
		}	
	}
	
	
	function selectContentList($skip,$limit,$descending){
		$client = new couchClient ('http://'.DB_ID.':'.DB_PASS.'@'.DB_ADDR,DB_NAME);
		try {
		   $view = $client->skip($skip)->limit($limit)->descending($descending)->getView('view','content_list');
		   return $view->rows;
		} catch (Exception $e) {
		   return "something weird happened: ".$e->getMessage()."<BR>\n";
		}
	}

	function selectContent($id){
		$client = new couchClient ('http://'.DB_ID.':'.DB_PASS.'@'.DB_ADDR,DB_NAME);
		try{
			$doc = $client->getDoc($id);	
			return $doc;
		} catch (Exception $e) {
			return "";
		}
	}

	function createDB(){
		$client = new couchClient ('http://'.DB_ID.':'.DB_PASS.'@'.DB_ADDR,DB_NAME);
		try { 
			$client->createDatabase(DB_NAME); 
		} catch ( Exception $e ) { 
			echo $e; 
		}
	}

	function deleteDB(){
		$client = new couchClient ('http://'.DB_ID.':'.DB_PASS.'@'.DB_ADDR,DB_NAME);
		try { 
			$client->deleteDatabase(DB_NAME);
		} catch ( Exception $e ) { 
			echo $e; 
		}
	}

	function makeView() {
		$client = new couchClient ('http://'.DB_ID.':'.DB_PASS.'@'.DB_ADDR,DB_NAME);
		$doc = new couchDocument($client);
		$doc->_id = "_design/view";
		$views = array();
		$map = "function (doc) {
			if ( doc.type && doc.type == 'content' ) {
				emit(doc.type,1);
			}
		}";
		$reduce = "function (keys, values) {
			return sum(values);
		}";
		$map2 = "function (doc) {
			if ( doc.type && doc.type == 'content' ) {
				emit(doc.creation,{'id':doc._id,'title':doc.title,'email':doc.email,'creation':doc.creation});
			}
		}";
		$doc->views = array (
			"content_total" => array (
				"map"=>$map,
				"reduce"=>$reduce
			),
			"content_list" => array (
				"map"=>$map2
			)

		);
	}

	function getNickname ($email) {
		if(!filter_var( $email, FILTER_VALIDATE_EMAIL)){
			return "false";
		}
		$client = new couchClient ('http://'.DB_ID.':'.DB_PASS.'@'.DB_ADDR,DB_NAME);
		try{
			$doc = $client->getDoc($email);	
			return $doc->nickname;
		} catch (Exception $e) {
			return "false";
		}
	}

	function getCount ($id) {
		$client = new couchClient ('http://'.DB_ID.':'.DB_PASS.'@'.DB_ADDR,DB_NAME);
		$test = $client->reduce(true)->getView("view",$id);
		if(count($test->rows) > 0){
			return (int)$test->rows[0]->value;
		}else{
			return 0;
		}
	}

	function getCreation(){
		$dateTime = new DateTime(); 
		$creation = $dateTime->format('U');
		return (string)$creation;
	}

	function getPageNumber($page,$total_record,$page_set){

		$block_set = 10;

		$totalpages = ceil($total_record / $page_set); 

		if(!$page) {$page=1;} 

		$bottom_page_start = (floor($page /$block_set)) * $block_set + 1 ; 
		$bottom_page_end = min($totalpages,$bottom_page_start + $block_set-1); 
		$bottom_pre_page = max(0,$bottom_page_start - $block_set) ;
		$bottom_next_page = $bottom_page_start + $block_set ; 
		if ($bottom_next_page>$totalpages) $bottom_next_page =0 ; 
		$moveurl = 'view.php'; 
		$leftstr = '<';  //이미지를 넣을수 있겟네요 <img src=pre.jpg> 
		$rightstr = '>';  ////이미지를 넣을수 있겟네요 <img src=next.jpg> 
		
		$return = "";
		if (($bottom_pre_page) >0) {

			$return .= '<a href=' .$moveurl.'?page='.$bottom_pre_page.'>'.$leftstr.'</a>'; 	
		}
		
		for($i=$bottom_page_start;$i<=$bottom_page_end;$i++){ 
			$return .= '<a href=' .$moveurl.'?page='.$i.'>['.$i.']</a>' ;  
		} 

		if (($bottom_next_page) >0){
			$return .= '<a href=' .$moveurl.'?page='.$bottom_next_page.'>'.$rightstr.'</a>' ; 	
		} 
		return $return;
	
	}

?>