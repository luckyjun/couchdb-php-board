<?php
	ini_set('display_errors','On');
	require_once (__DIR__."/../controller/board.php");
	$return = "";
	if (array_key_exists("email",$_SESSION)) {
		header("Location:view.php");
	}else{
		if(array_key_exists("email",$_POST) && array_key_exists("ps",$_POST)){
			$return = login($_POST["email"],$_POST["ps"]);
			if($return === "OK"){
				header("Location:index.php");	
			}	
		}
	}	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>회원가입</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<link rel="stylesheet" href="../css/login.css" type="text/css">
</head>
<body>
	<div class="Warp">
		<?php echo $return; ?>
		<form method="POST" action="./login.php">
			<div class="loginWarp">
				<input type="text" name="email" class="email" />
				<input type="password" name="ps" class="ps" />
			</div>
				<input type="submit" value="로그인" class="submit"/>
		</form>
	</div>
</body>
</html>