<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>게시판</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<link rel="stylesheet" href="css/list.css" type="text/css">
</head>
<body>
	
	<?php ini_set('display_errors','On');?>
	<?php require_once (__DIR__."/controller/board.php"); ?>
	<?php require_once (__DIR__."/view/header.php"); ?>
	<?php require_once (__DIR__."/view/content.php"); ?>
	
	<div id="list">
		<?php 

			if(array_key_exists("page",$_GET)){
				$page = (int)$_GET["page"];
			}else{
				$page = 1;
			}

			$page_set = 10;

			if($page===1){
				$skip = 0;	
			}else{
				$skip = ($page*$page_set)-$page_set;
			}
			
			$total_record = getCount("content_total");

			$list = selectContentList($skip,$page_set,true); 

			echo "<table cellspacing='0' cellpadding='0' border='0' width=100% class='list'>
					<colgroup><col width='70'><col width='*'><col width='122'><col width='70'></colgroup>
					<tr><td colspan='5' class='board-line'></td></tr>
					<tr class='header'>
						<td align='center' class='number'>번호</td>
						<td align='center'>제목</td>
						<td align='center'>작성자</td>
						<td align='center'>작성일</td>
					</tr>";

			for($i =0; $i < count($list); $i++){
				$id = $list[$i]->value->id;
				$link = $id."&page=".$page;
				$title = "<a href='index.php?no=".$link."'>".$list[$i]->value->title."</a>";
				$creation = $list[$i]->value->creation; 
				$date = date("Y.m.d",$creation);
				$nickname = getNickname($list[$i]->value->email);
				
				echo "<tr>
						<td align='center'>$id</td>
						<td>$title</td>
						<td align='center'>$nickname</td>
						<td align='center'>$date</td>
					</tr>";
			}
			echo "</table>";
			echo '<div id="write"><a href="write.php">글쓰기</a></div>';
			echo "<div id='page'>".getPageNumber($page,$total_record,$page_set)."</div>";

		?>
	</div>
</body>

</html>