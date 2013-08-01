<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html dir="ltr" xmlns="http://www.w3.org/1999/xhtml" lang="en"><head>


	<title>WordPress.com Blog › Login</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="wp-login_files/login.css" type="text/css" media="all">
<link rel="stylesheet" href="wp-login_files/colors-fresh.css" type="text/css" media="all">
	<script type="text/javascript">
		function focusit() {
			document.getElementById('user_login').focus();
		}
		window.onload = focusit;
	</script>
</head><body class="login">

<div id="login"><h1><a href="http://wordpress.com/" title="Powered by WordPress">WordPress.com Blog</a></h1>

<form name="loginform" id="loginform" action="wp-login.php" method="post">
	<p>
		<label>Username<br>
		<input name="log" id="user_login" class="input" value="" size="20" tabindex="10" type="text"></label>
	</p>
	<p>
		<label>Password<br>
		<input name="pwd" id="user_pass" class="input" value="" size="20" tabindex="20" type="password"></label>
	</p>
	<p class="forgetmenot"><label><input name="rememberme" id="rememberme" value="forever" tabindex="90" type="checkbox"> Remember Me</label></p>
	<p class="submit">
		<input name="wp-submit" id="wp-submit" value="Log In" tabindex="100" type="submit">
		<input name="redirect_to" value="wp-admin/" type="hidden">
		<input name="testcookie" value="1" type="hidden">
	</p>
</form>

<p id="nav">
<a href="http://wordpress.com/signup/?ref=wplogin">Get a free WordPress account</a> |
<a href="http://wordpress.com/wp-login.php?action=lostpassword" title="Password Lost and Found">Lost your password?</a>
</p>

</div>

<p id="backtoblog"><a href="http://wordpress.com/" title="Are you lost?">« Back to WordPress.com Blog</a></p>

</body></html>