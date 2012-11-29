<div id="list">
	<?php 

		$page_set = 10;

		if($page===1){
			$skip = 0;	
		}else{
			$skip = ($page*$page_set)-$page_set;
		}
		
		$total_record = getCount($id."_total");
		
		$list = selectContentList($skip,$page_set,true,$id); 

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
		
			
			
			$_id = $list[$i]->value->id;
			$link = $_id."&page=".$page;
			$title = "<a href='".$path."?id=".$id."&no=".$link."'>".$list[$i]->value->title."</a>";
			$creation = $list[$i]->value->creation; 
			$date = date("Y.m.d",$creation);
			$nickname = getNickname($list[$i]->value->email);
			
			echo "<tr>
					<td align='center'>$_id</td>
					<td>$title</td>
					<td align='center'>$nickname</td>
					<td align='center'>$date</td>
				</tr>";
		}
		echo "</table>";
		echo '<div id="write"><a href="write.php?id='.$id.'&path='.urlencode($_SERVER['REQUEST_URI']).'">글쓰기</a></div>';
		echo "<div id='page'>";
		
		$block_set = 10;
		
		$totalpages = ceil($total_record / $page_set); 
	
		if(!$page) {$page=1;} 
	
		$bottom_page_start = (floor($page /$block_set)) * $block_set + 1 ; 
		$bottom_page_end = min($totalpages,$bottom_page_start + $block_set-1); 
		$bottom_pre_page = max(0,$bottom_page_start - $block_set) ;
		$bottom_next_page = $bottom_page_start + $block_set ; 
		if ($bottom_next_page>$totalpages) $bottom_next_page =0 ; 
		$moveurl = 'view.php'; 
		$leftstr = '◀';  //이미지를 넣을수 있겟네요 <img src=pre.jpg> 
		$rightstr = '▶';  ////이미지를 넣을수 있겟네요 <img src=next.jpg> 
		
		if (($bottom_pre_page) >0) {
	
			echo '<a href="' .$path.'?page='.$bottom_pre_page.'&id='.$id.'">'.$leftstr.'</a>'; 	
		}
		
		for($i=$bottom_page_start;$i<=$bottom_page_end;$i++){ 
			echo '<a href="' .$path.'?page='.$i.'&id='.$id.'">['.$i.']</a>' ;  
		} 
	
		if (($bottom_next_page) >0){
			echo '<a href="' .$path.'?page='.$bottom_next_page.'&id='.$id.'">'.$rightstr.'</a>' ; 	
		} 	
			
			
		echo "</div>";


	?>
</div>