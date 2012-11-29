<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>게시판</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<link rel="stylesheet" href="css/list.css" type="text/css">
	<link rel="stylesheet" href="css/content.css" type="text/css">
</head>
<body>
	
	<?php ini_set('display_errors','On');?>
	<?php require_once (__DIR__."/controller/board.php"); ?>
	<?php require_once (__DIR__."/view/header.php"); ?>
	
	<?php
		$path = parse_url($_SERVER['REQUEST_URI']);
		$path = $path["path"];
		if(array_key_exists("id",$_GET)){
			
			//게시판 아이디
			$id = $_GET["id"];
			
			if(array_key_exists("no",$_GET)){
				//게시물 가져오기
				$no = $_GET["no"];
				require_once (__DIR__."/view/content.php"); 
			}
			
			//게시판 페이지 번호
			if(array_key_exists("page",$_GET)){
				$page = (int)$_GET["page"];
			}else{
				$page = 1;
			}
			
			//게시판 리스트 불러오기
			require_once (__DIR__."/view/list.php");	
		}
		
		
	?>

</body>

</html>