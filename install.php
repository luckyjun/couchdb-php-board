<?php
	/*
		1. controller.php 에서 DB_NAME, DB_ID, DB_PASS를 수정합니다.
		2. popup/quick_photo/QuickPhotoPopup.js 파일을 아래와 같이 서버 풀 URL로 수정합니다.
		   var serverAddress = "http://dimeet.iptime.org:8080/board/popup/";
		3. 아래의 소스 주석을 제고 하고 한번만 실행합니다.
	*/
	
	
	
	ini_set('display_errors','On');
	require_once (__DIR__."/controller/board.php");
	createDB();
	makeView();
	makeBaseDocument();
	
?>