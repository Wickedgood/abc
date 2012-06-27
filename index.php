<?php 
session_start();
include "config.php";
include "Mysql.class.php";
$Form = '';
if($_GET['l'] == 2){
	if(!isset($_POST['password'])){
		$Form ='<form method="post" action="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['argv'][0].'">
		Username: <input type="text" name="user" /><br />
		Password: <input type="password" name="password" /><br />
		Verify Password: <input type="password" name="vpassword" /><br />
		(Optional) E-Mail <input type="text" name="email" /><br />
		<input type="submit" value="Register" />
		</form>';
		$Form .= "<br /><br /> Please DO NOT USE your Battle.net login information <br />";
	} else {
		if(filter_has_var(INPUT_POST, "user") && filter_has_var(INPUT_POST, "password") && filter_has_var(INPUT_POST, "vpassword") ){
			$rms = new Mysql($MADDRESS,$MUSER,$MPASSWORD,$MDATABASE);
			$name = "";
			if($_POST['user'] == ""){
				$name = ".";
			}
			$name .= $rms->c($_POST['user']);
			$password = $rms->c(md5($SALT . md5($_POST['password'])));
			$email = $rms->c($_POST['email']);	
			if($rms->query("INSERT INTO  `user` (`name` ,`password` ,`email`)VALUES ('".$name."',  '".$password."', '".$email."');")){
				$Form = 'Thank you for you registering <br />Please click <a href="'.$_SERVER['PHP_SELF'].'">here.</a><br />';
				$_SESSION['User']['name'] = $name;
				$_SESSION['User']['id'] = mysql_insert_id();
				// The message
				$message = "Username : ".$name."\nPassword : ".$_POST['password']."\nThanks for Registering!";

				// In case any of our lines are larger than 70 characters, we should use wordwrap()
				$message = wordwrap($message, 70);

				// Send

				$to      = $_POST['email'];
				$subject = 'Unofficial Diablo III Armory User Information';

				$headers = 'From: Admin@ud3a.com' . "\r\n" .
					'Reply-To: Admin@ud3a.com' . "\r\n" .
					'X-Mailer: PHP/' . phpversion();

				mail($to, $subject, $message, $headers);
				
			}else{
				if(substr($rms ->error,0,15) == 'Duplicate Entry'){
					$Form = 'That name is taken, Sorry!<br />Please click <a href="'.$_SERVER['PHP_SELF'].'">here.</a><br />';
				}
			}
			
		} else {
			unset($_POST);
			$Form =  "Please fill in required fields";
		}
	}
}else if($_GET['l'] == 1){
	if(!isset($_POST['password'])){
		$Form ='<form method="post" action="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['argv'][0].'">
		Username: <input type="text" name="user" /><br />
		Password: <input type="password" name="password" /><br />
		<input type="submit" value="Login" />
		</form>';

	} else {
	
		if(filter_has_var(INPUT_POST, "user") && filter_has_var(INPUT_POST, "password")){
			$loginms = new Mysql($MADDRESS,$MUSER,$MPASSWORD,$MDATABASE);
			$user = $loginms->c($_POST['user']);
			$password = $loginms->c(md5($SALT . md5($_POST['password'])));
			$loginms->query("SELECT id FROM user WHERE name = '".$user."' && password='".$password."';");
			$data = mysql_fetch_row($loginms->getResult());
			$id = $data[0];
			if($id >= 1){
				$_SESSION['User']['name'] = $user;
				$_SESSION['User']['id'] = $id;
				$Form = 'Thank you for you Logging in '.$_SESSION['User']['name'].'<br />Please click <a href="'.$_SERVER['PHP_SELF'].'">here.</a>';
			}else {
				$Form = '<br />Bad User / Pass combo <br />';
			}
		} else {
			unset($_POST);
			$Form =  "Please fill in required fields";
		}
	}
}else{
	$Form = '';
}

if(isset($_SESSION['User'])){
	
	$LoginLink .= '<br /><a href="/unset.php">Logout</a><br />';
	$LoginLink .= $_SESSION['User']['name']."<br />";
} else {
	$LoginLink = 	'<a style="font-size:10px;color: white;" href="'.$_SERVER['PHP_SELF'].'?l=1">Login</a>
					 <a style="font-size:10px;color: white;" href="'.$_SERVER['PHP_SELF'].'?l=2">Register</a>';
}
$Form .= '<br /><br /><br /><a href="/comments.php">Feedback to the Developer</a><br />';
?>
<html>
<head>
<title>UD3A.COM</title>
</head>
<body style="background-color:black;color:white">

<table width="100%" height="100%" cellpadding="0" cellspacing="0">
<tr>
<td height="30" colspan="5" align="right" style="font-size:10px"></td>
</tr>
<tr>
<td  colspan="5" height="25%"></td>
</tr>

<tr>
<td>
	<table cellpadding="1" cellspacing="0" align="center" valign="bottom">
	<tr>
	<td align="center">Barbarian</td>
	<td align="center">Demon Hunter</td>
	<td align="center">Monk</td>
	<td align="center">Witch Doctor</td>
	<td align="center">Wizard</td>
	</tr>
	<tr>
	<td height="150">
		<a alt="Barbarian" href="class.php?c=Barbarian"><img alt="Barbarian" border="0" src="images/Barbarian.png"></a>
	</td>
	<td height="150">
		<a href="class.php?c=DemonHunter"><img border="0"  alt="Demon Hunter" src="images/DemonHunter.png"></a>
	</td>
	<td height="150">
		<a href="class.php?c=Monk"><img border="0"  alt="Monk" src="images/Monk.png"></a>
	</td>
	<td height="150">
		<a href="class.php?c=WitchDoctor"><img alt="Witch Docter" border="0" src="images/WitchDoctor.png"></a>
	</td>
	<td height="150">
		<a href="class.php?c=Wizard"><img alt="Wizard" border="0" src="images/Wizard.png"></a>
	</td>
	</td>
	</tr>
	</table>
</td>
</tr>
<tr>
<td height="100%" colspan="5" align="center"><?php echo $LoginLink; echo $Form; ?></td>
</tr>
<tr>
<td height="30" colspan="5" align="center" style="font-size:10px">Art: Sabotage#1114&nbsp;&nbsp;&nbsp;FrontEnd : Dan#1482<br />JS : Cites#1362&nbsp;&nbsp;&nbsp;Misc : Adam#1323</td>
</tr>
<tr>
<td height="8" colspan="5" align="center" style="font-size:7px">Unofficial Diablo III Armory</td>
</tr>
</table>
</body>
</html>