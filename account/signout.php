<?php
	ini_set('display_errors','On');
	require_once (__DIR__."/../controller/board.php");
	signout();
	header("Location:/board");
?>