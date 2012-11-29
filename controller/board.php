<?php
		
	session_start();
	ini_set('display_errors','On');
	
	//카우치 디비에 들어갈 데이터 베이스 이름.
	define("DB_NAME", "board");
	define("DB_ID", "jun");
	define("DB_PASS", "manson");
	define("DB_ADDR", "192.168.0.84:5984");
	
	header('Access-Control-Allow-Origin: *');  
	
	require_once (__DIR__."/../lib/couch.php");
	require_once (__DIR__."/../lib/couchClient.php");
	require_once (__DIR__."/../lib/couchDocument.php");
	
	
	/*
		Document Type 정보
		content 게시물
		commant 댓글
		info 게시판정보
		account 회원정보
	*/
	
	
	##################################
	#####   install function
	##################################
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
		$doc->views = array (
			"content_total" => array (
				"map"=>makeTotalView("type","content"),
				"reduce"=>makeTotalReduce()
			),
			"default_list" => array (
				"map"=>makeBoardView("default")
			),
			"default_total" => array (
				"map"=>makeTotalView("id","default"),
				"reduce"=>makeTotalReduce()
			)
		);
	}
	function makeBaseDocument(){
		$client = new couchClient ('http://'.DB_ID.':'.DB_PASS.'@'.DB_ADDR,DB_NAME);
		try {
			$doc = new couchDocument($client);
			$doc->set( array(
				'_id'=>"BoardList",
				'type'=>'info',
				'list'=>array(array("id"=>"default","name"=>"기본 게시판","creation"=>getCreation()))
				) );
			return "OK";
		} catch (Exception $e) {
			return "false";
		}
	}
	
	
	##################################
	#####   board function
	##################################
	function makeBoard($id,$name){
		$client = new couchClient ('http://'.DB_ID.':'.DB_PASS.'@'.DB_ADDR,DB_NAME);
		
		//board doc update
		try {
			$doc = $client->getDoc("BoardList");
			array_push($doc->list, array("id"=>$id,"name"=>$name,"creation"=>getCreation()));
			print_r($doc->list);			
			$client->storeDoc($doc);
		} catch (Exception $e) {
		   	echo "something weird happened: ".$e->getMessage()."<BR>\n";
		}
		
		//board view
		try {
			$doc = $client->getDoc("_design/view");
			
			$list = array (
						"map"=>makeBoardView($id)
					);
		   
			$total = array (
						"map"=>makeTotalView("id",$id),
						"reduce"=>makeTotalReduce()
					);
			$list_id = $id."_list";
			$total_id = $id."_total";
			$doc->views->$list_id = $list;
			$doc->views->$total_id = $total;
			$client->storeDoc($doc);
		} catch (Exception $e) {
		   	echo "something weird happened: ".$e->getMessage()."<BR>\n";
		}
	}
	
	
	##################################
	#####   content function
	##################################
	function insertContent($title,$content,$id){
		$client = new couchClient ('http://'.DB_ID.':'.DB_PASS.'@'.DB_ADDR,DB_NAME);
		$_id = getCount("content_total");
		$_id = $_id+1;
	
		try {
			$doc = new couchDocument($client);
			$doc->set( array(
				'_id'=>(string)$_id,
				'type'=>'content',
				'email'=>$_SESSION["email"],
				'title'=>$title,
				'content'=>$content,
				'id'=>$id,
				'creation'=>getCreation()
				) );
			$doc = $client->getDoc($_id);
			return "OK";
		} catch (Exception $e) {
			return "false".$e;
		}
	}
	function selectContentList($skip,$limit,$descending,$id){
		$client = new couchClient ('http://'.DB_ID.':'.DB_PASS.'@'.DB_ADDR,DB_NAME);
		try {
		   $view = $client->skip($skip)->limit($limit)->descending($descending)->getView('view', $id.'_list');
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

	
	
	##################################
	#####   comment function
	##################################
	function insertComment($content){
		$client = new couchClient ('http://'.DB_ID.':'.DB_PASS.'@'.DB_ADDR,DB_NAME);
		$id = getCount("content_total");
		$id = $id+1;
	
		try {
			$doc = new couchDocument($client);
			$doc->set( array(
				'_id'=>(string)$id,
				'type'=>'comment',
				'email'=>$_SESSION["email"],
				'content'=>$content,
				'creation'=>getCreation()
				) );
			$doc = $client->getDoc($id);
			return "OK";
		} catch (Exception $e) {
			return "false";
		}
	}
	
	
	##################################
	#####   account function
	##################################
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
					'_id'=>$email,
					'type'=>'account',
					'creation'=>getCreation(),
					'nickname'=>$nickname,
					'password'=>$password) );
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
	
	
	##################################
	#####   utils function
	##################################
	
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
	
	function makeTotalView($key,$id){
		$map = "
		function (doc) 
		{
			if ( doc.".$key." && doc.".$key." == '".$id."' ) 
			{
				emit(doc.".$key.", 1);
			}
		}";
		return $map;
	}
	
		
	function makeTotalReduce(){
		$reduce = "
		function (keys, values) 
		{
			return sum(values);
		}";
		return $reduce;
	}
	function makeBoardView($id){
		$map = "
		function (doc) 
		{
			if ( doc.type && doc.type == 'content' && doc.id == '".$id."' ) 
			{
				emit(doc.creation,{'id':doc._id,'title':doc.title,'email':doc.email,'creation':doc.creation});
			}
		}";
		return $map;
	}
	
	function get_path_info()
	{
	    if( ! array_key_exists('PATH_INFO', $_SERVER) )
	    {
	        $pos = strpos($_SERVER['REQUEST_URI'], $_SERVER['QUERY_STRING']);
	    
	        $asd = substr($_SERVER['REQUEST_URI'], 0, $pos - 2);
	        $asd = substr($asd, strlen($_SERVER['SCRIPT_NAME']) + 1);
	        
	        return $asd;    
	    }
	    else
	    {
	        return trim($_SERVER['PATH_INFO'], '/');
	    }
	}


?>