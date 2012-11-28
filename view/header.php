<div id="header">
	<?php if(array_key_exists("email",$_SESSION)) : ?>
			<span class='nickname'><?php getNickname($_SESSION["email"]); ?></span>
			<a href="./account/signout.php">로그아웃</a>
	<?php else : ?>
			<a href="./account/login.php">로그인</a>, <a href="./account/signup.php">회원가입</a>
	<?php endif; ?>		
</div>