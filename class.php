<?php
//Live Now 28JUN12
session_start();
include "config.php";//Mysql information.
include "Mysql.class.php";//Mysql Class
include "ArmorStats.php";//Gear information

//Setting up the ENUM for classes.						
(String)$Class = null;
if(	$_GET['c'] == 'Barbarian' ||
	$_GET['c'] == 'DemonHunter' ||
	$_GET['c'] == 'Monk' ||
	$_GET['c'] == 'WitchDoctor' ||
	$_GET['c'] == 'Wizard'){
	
	$Class = $_GET['c'];
}

//Mana Stuff
if($Class == 'Monk'){
	$ManaName = 'Spirit';
	$Mana = 150;
}
if($Class == 'Wizard'){
	$ManaName = 'Arcane Power';
	$Mana = 100;
}
if($Class == 'Barbarian'){
//TODO check this
	$ManaName = 'Fury';
	$Mana = 100;
}
if($Class == 'WitchDoctor'){
	$ManaName = 'Mana';
	$Mana = 140 + (10 * $_SESSION[$Class]['level']);
}
if($Class == 'DemonHunter'){
	$ManaName  = 'Hatred | Discipline';
	$Mana = 125 . ' | '. 30;
}
//List of all equipment and SLOT ID
$equipment = array("Helm", "Shoulder", "Amulet", "Chest",
						"Gloves", "Bracer", "LRing", "Belt",
						"RRing", "Pants", "MHand", "OHand", "Boots");
						
//Saving gear
if($_POST['Mysql'] == 'Save'){
	if(isset($_SESSION[$Class]['level']) && isset($_SESSION[$Class]['Spec'])){
		$savemysql = new Mysql($MADDRESS,$MUSER,$MPASSWORD,$MDATABASE);
		$level  = $savemysql->c($_SESSION[$Class]['level']);
		$ownerid = $savemysql->c($_SESSION['User']['id']);
		$spec = '';
		foreach($_SESSION[$Class]['Spec'] as $v){
			$spec .= $v;
		}
		
		$savemysql->query("SELECT charid FROM `char` WHERE ownerid = '".$ownerid."' && class='".$Class."';");
		$data = mysql_fetch_row($savemysql->getResult());
		$charid = $savemysql->c($data[0]);
		$savemysql->query("DELETE FROM  `char` WHERE  `charid` ='".$charid."'");
		
		if($savemysql->query("INSERT INTO  `char` (`level` ,`class` ,`ownerid`,`spec`)VALUES ('".$level."',  '".$Class."', '".$ownerid."', '".$spec."');")){
			$info = "Gear has been saved";
			$savemysql->query("SELECT charid FROM `char` WHERE ownerid = '".$ownerid."' && class='".$Class."';");
			$data = mysql_fetch_row($savemysql->getResult());
			$charid = $savemysql->c($data[0]);
			$savemysql->query("DELETE FROM  `stats` WHERE  `charid` ='".$charid."'");
			foreach($_SESSION[$Class] as $k => $v){
				if($k != 'level' && $k != 'Spec'){
					$slot = array_search($k, $equipment);
					foreach($v as $key => $value){
						////"INSERT INTO table (id,box) VALUES (1,'box1'),(2,'box2'),(3,'box3')"
						
						$sql = "INSERT INTO `stats` (`charid`, `slot`, `key`, `value`,`ownerid`) VALUES ('".$charid."', '".$slot."', '".$key."', '".$value."','".$ownerid."');";
						$savemysql->query($sql);
					}
				}
			}
		}else{
			$info = "Problem with Mysql";
		}
		
	}else{
		$info = "Please fill out level and spec before saving";
	}
}			
		
//Loading Gear
if($_POST['Mysql'] == 'Load'){
	$loadmysql = new Mysql($MADDRESS,$MUSER,$MPASSWORD,$MDATABASE);
	$ownerid = $loadmysql ->c($_SESSION['User']['id']);
	$loadmysql->query("SELECT `level`,`spec`,`charid` FROM `char` WHERE ownerid = '".$ownerid."' && class='".$Class."';");
	$data = mysql_fetch_row($loadmysql->getResult());
	$_SESSION[$Class]['level'] = $data[0];
	for($i = 0;$i<=strlen($data[1]);$i++){
		$_SESSION[$Class]['Spec'][$i] = $data[1][$i];
	}
	$charid = $data[2];
	$loadmysql->query("SELECT `slot`,`key`,`value` FROM `stats` WHERE charid = '".$charid."';");
	while ($row = mysql_fetch_assoc($loadmysql->getResult())) {
		$_SESSION[$Class][$equipment[$row['slot']]][$row['key']] = $row['value'];
	}
	$info = "Gear has been loaded";
}
//Checking to see if the users level was updated.
if(isset($_POST['charlevel'])){

	$_SESSION[$Class]['level'] = $_POST['lvlText'];
}
//checking to see if the users spec was updated.
if(isset($_POST['spec']))
{
	for ($i = 0; $i <= $_POST['spec']; $i++) {
		if($_POST[$i] == 1){
			$_SESSION[$Class]['Spec'][$i] = 1;
		}else{
			$_SESSION[$Class]['Spec'][$i] = 0;
		}
	}

}
//Making the dropdown menu 
if(isset($_GET['i'])){
	if(!isset($_GET['e'])){
		$DropDown='';
		$DropDown .='<select name="'.$_GET['i'].'[]">';
		foreach($ArmorStats as $k=>$v){
			$DropDown .= '<option value="'.$k.'">'.$v.'</option>';
		}
		$DropDown .='</select>';
	}else{
	//Here is the code to edit instead of enter
		$DropDown='';
		$DropDown .='<select name="'.$_GET['i'].'[]">';
		foreach($_SESSION[$Class][$_GET['i']] as $k=>$v){
			$DropDown .= '<option value="'.$k.'">'.$v.'</option><input type="text" value="'.$v.'"name="vipInfo['.$k.']" />';
		}
		$DropDown .='</select>';	
	}
}else{
	$DropDown = "empty";
}
//Building the display for items / character itself
$Display ='';
if($Class == null){
	$Display = "Please select a class <a href=\"/\">here</a>";
}else if(isset($_GET['d'])){
	$Input = true;
	if(in_array($_GET['d'],$equipment)){
		unset($_SESSION[$Class][$_GET['d']]);
	}else if($_GET['d'] == 'level'){
		unset($_SESSION[$Class]['level']);
	}
	echo '<script language="Javascript">';
	echo 'window.location="'.$_SERVER['PHP_SELF']."?c=".$Class.'"';
	echo '</script>';
}else if(!isset($_GET['i'])){
//Building the actual display of items here.
	$item=array();
	foreach ($equipment as $e){
		$displayitem = '';
		if(!isset($_SESSION[$Class][$e])){
			$displayitem =  'onclick="location.href=\''.$_SERVER['PHP_SELF'].'?'.$_SERVER['argv'][0].'&i='.$e.'\';" style="cursor:pointer;">';
		}else{
			$displayitem .= $e.'<a href="'.$_SERVER['PHP_SELF'].'?'.htmlspecialchars($_SERVER['argv'][0]).'&d='.$e.'">D</a> <a href="'.$_SERVER['PHP_SELF'].'?'.htmlspecialchars($_SERVER['argv'][0]).'&e='.$e.'">E</a> <br />';

			foreach($_SESSION[$Class][$e] as $k=>$v){
				if($k == '1337'){
					if($v > 0){
						$displayitem = $v ." Armor<br />".$displayitem;
					}
				}else if($k == '1400'){
					if($v > 0){
						$displayitem ="Attack Speed ".$v."<br />".$displayitem;
					}
				}else if($k == '1338'){
					if($v > 0){
						$displayitem =$v." - ".$_SESSION[$Class][$e][1339]." Damage<br />".$displayitem;	
					}
				}else if($k == '1401'){
					if($v > 0){
						$BlockChance = $v;
						$displayitem ="Block Chance ".$v."<br />".$displayitem;
					}
				}else if($k == '1402'){
					if($v > 0){
						$displayitem ="Block Amout ".$v." - ".$_SESSION[$Class][$e][1403]."<br />".$displayitem;
					}
				}else if($k == '24'){
					if($v > 0){
						$displayitem =$v." - ".$_SESSION[$Class][$e][25]."<br />".$displayitem;
					}
				}else if($k == '25'){

				}else if($k == '1339'){

				}else if($k == '1403'){

				}else {
					$displayitem .= '&nbsp;&nbsp;&nbsp;'.$ArmorStats[$k]. ': '.$v.'<br />';
					//$displayitem .= '   '.$ArmorStats[$k]. ': '.$v.'<br />';
				}
				
			}
			$displayitem  = ' style="background-image: url(images/'.$e.'.png);">'.$displayitem;				
		}
		$item[$e] = $displayitem;
	}
	
}else if(isset($_GET['i'])){

//Building the input for items. This gets tricky :)
	$Input = true;
	if(!isset($_POST['hidden'])){
		$Display = '<form method="post" action="'.$_SERVER['PHP_SELF']."?".htmlspecialchars($_SERVER['argv'][0]).'">';
		if($_GET['i'] == 'MHand' || $_GET['i'] == 'OHand')
		{
			if($_GET['i'] == 'OHand' && ($Class == 'Wizard' || $Class == 'WitchDoctor')){
			
			}else{
				$Display .= 'WeaponMinDmg <input type = "text" name = "Min" value = "0"><br />';
				$Display .= 'WeaponMaxDmg <input type = "text" name = "Max" value = "0"><br />';
				$Display .= 'Attacks Per Second <input type = "text" name = "As" value = "0"><br />';
			}
			if( $_GET['i'] == 'OHand'){
				$Display .= 'Armor <input type = "text" name = "Armor" value = "0"><br />';
				$Display .= 'Block Chance <input type = "text" name = "Block" value = "0"><br />';
				$Display .= 'Block Amount  <input type = "text" name = "BlockMin" value = "0">-<input type = "text" name = "BlockMax" value = "0"><br />';
			}
			
		}else if($_GET['i'] != 'RRing' && $_GET['i'] != 'LRing' && $_GET['i'] != 'Amulet'){
			$Display .= 'Armor <input type = "text" name = "Armor" value = "0"><br />';
			if($_GET['i'] == 'OHand'){
				$Display .= 'Block Chance <input type = "text" name = "Block" value = "0"><br />';
				$Display .= 'Block Amount  <input type = "text" name = "BlockMin" value = "0">-<input type = "text" name = "BlockMax" value = "0"><br />';
			}
		}
		$Display .= '
		<div class="field">
		'.$DropDown.'
		&nbsp;&nbsp;&nbsp;<input type="text" name="vipInfo[]" />
		</div>';

		$Display .= '
		<input type="button" id="newFieldBtn" value="New Field">
		<input 	type="hidden" name="hidden" value="submitted"/>
		<button type="submit">Save</button>
		</form>';
		
		$Tips = "<br /><br />When dealing with gear with gems you need to add up all of the gems and input them with the original stat<br />
				For instance, a Chest with 10 Int and 3 gems each with 5 int has a total of 25 int.<br />
				When dealing with Weapons, Weaponmindmg and weaponmaxdmg are the white values before all the blue, Same with attackspeed<br />";
				
		$Display .= $Tips;
	}else{
	
		$stats = array();
		foreach($_POST[$i] as $k => $v){
			foreach($_POST['vipInfo'] as $key => $val){
				if($k == $key){
					$stats[$v] = $val;
				}
			}
		}
		if($_GET['i'] != 'RRing' && $_GET['i'] != 'LRing' && $_GET['i'] != 'Amulet' && $_GET['i'] != 'MHand'){
			$stats[1337] = $_POST['Armor'];
		}
		if($_GET['i'] == 'MHand' || $_GET['i'] == 'OHand'){
			$stats[1338] = $_POST['Min'];
			$stats[1339] = $_POST['Max'];
			$stats[1400] = $_POST['As'];
			$stats[1401] = $_POST['Block'];
			$stats[1402] = $_POST['BlockMin'];
			$stats[1403] = $_POST['BlockMax'];
		}
		$_SESSION[$Class][$_GET['i']] = $stats;
		echo '<script language="Javascript">';
		echo 'window.location="'.$_SERVER['PHP_SELF']."?c=".$Class.'"';
		echo '</script>';
	}
}else{
	echo "How Did you get here!?<br />";
}
/* Calculate Things*/
$Armor = 0;
if(isset($_SESSION[$Class])){
	if(isset($_SESSION[$Class]['level'])){
		if($Class == 'Barbarian'){
			$Strength = 10 + (($_SESSION[$Class]['level'] -1)*3);
			$Intelligence = 8 + (($_SESSION[$Class]['level'] -1));
			$Dexterity = 8 + (($_SESSION[$Class]['level'] -1));
			$Vitality = 9 + (($_SESSION[$Class]['level'] -1)*2);
			
		}
		if($Class == 'DemonHunter'){
			$Strength = 8 + (($_SESSION[$Class]['level'] -1));
			$Intelligence = 8 + (($_SESSION[$Class]['level'] -1));
			$Dexterity = 10 + (($_SESSION[$Class]['level'] -1)*3);
			$Vitality = 9 + (($_SESSION[$Class]['level'] -1)*2);

		}
		if($Class == 'Monk'){
			$Strength = 8 + (($_SESSION[$Class]['level'] -1));
			$Intelligence = 8 + (($_SESSION[$Class]['level'] -1));
			$Dexterity = 10 + (($_SESSION[$Class]['level'] -1)*3);
			$Vitality = 9 + (($_SESSION[$Class]['level'] -1)*2);

		}
		if($Class == 'WitchDoctor'){
			$Strength = 8 + (($_SESSION[$Class]['level'] -1));
			$Intelligence = 10 + (($_SESSION[$Class]['level'] -1)*3);
			$Dexterity = 8 + (($_SESSION[$Class]['level'] -1));
			$Vitality = 9 + (($_SESSION[$Class]['level'] -1)*2);

		}
		if($Class == 'Wizard'){
			$Strength = 8 + (($_SESSION[$Class]['level'] -1));
			$Intelligence = 10 + (($_SESSION[$Class]['level'] -1)*3);
			$Dexterity = 8 + (($_SESSION[$Class]['level'] -1));
			$Vitality = 9 + (($_SESSION[$Class]['level'] -1)*2);

		}
		
	}else{
	
		$Intelligence = 0;
		$Strength = 0;
		$Dexterity = 0;
		$Vitality = 0;
	}
	$CCrit = 5;
	$CCritDmg = 50;
	//Get Stats from Items here NOWHERE ELSE Seriously! Don't fuck around
	$AllRes = 0;
	foreach($_SESSION[$Class] as $k => $v){
		if($k != 'level' && $k != 'Spec'){
			$Armor += $v[1337];
			if($k == 'RRing' || $k == 'LRing' || $v == 'Amulet'){
				$Armor += $v[3];
			}
			$Dexterity += $v[31];
			$Strength += $v[69];
			$Intelligence += $v[39];
			$Vitality += $v[70];
			$chardmg += $v[24] +$v[25];
			
			if($k != 'MHand'){
				$CAs += $v[4];
			}
			$CCrit += $v[21];
			$CCritDmg += $v[22];
			$BlockChance += $v[11];
			$AllRes += $v[0];
			$ColdRes += $v[20];
			$FireRes += $v[34];
			$ArcaRes += $v[2];
			$PhysRes += $v[61];
			$LighRes += $v[46];
			$PoisRes += $v[63];
			$LifePercent += $v[40];
			$Thorns+= $v[60];
			$CrowdControlReduction += $v[23];
			$MovementSpeed +=$v[59];
		}
	}
	$AllRes += round($Intelligence/10,0);
	$AllRes = round($AllRes,0);
	$Armor += $Strength;
	if($_SESSION[$Class]['OHand'][1402] > 0)
	{
		$BlockMin = $_SESSION[$Class]['OHand'][1402];
		$BlockMax = $_SESSION[$Class]['OHand'][1403];
	}
	switch ($Class) {
    case 'Barbarian':
        $primestat = $Strength;
        break;
    case 'Monk':
        $primestat = $Dexterity;
        break;
	case 'DemonHunter':
        $primestat = $Dexterity;
        break;
	case 'WitchDoctor':
        $primestat = $Intelligence;
        break;
	case 'Wizard':
        $primestat = $Intelligence;
        break;
	
	}
	//All important calculations for dmg
	$wdmg = $_SESSION[$Class]['MHand'][1338] + $_SESSION[$Class]['MHand'][1339];
	$odmg = $_SESSION[$Class]['OHand'][1338] + $_SESSION[$Class]['OHand'][1339];
	$damage = ($wdmg + $chardmg)/2;
	$damageW = ($wdmg + $chardmg)/2;
	$damageO = ($odmg + $chardmg)/2;
	$attackspeedW = ($_SESSION[$Class]['MHand'][1400] * (1 + ($CAs/100))); // modified attack speed main hand
	$attackspeedO = ($_SESSION[$Class]['OHand'][1400] * (1 + ($CAs/100))); // modified attack speed off hand
	$AttackSpeed = $_SESSION[$Class]['MHand'][1400] * (1+$CAs/100);

}
//Build display for talents and then display them.
$talents ="";
$talentcount =0;
include "Talents.php";
foreach($Talents as $k => $v){
	if($Class == $k){
		foreach($v as $key => $value){
			if($_SESSION[$Class]['Spec'][$talentcount] == 1){
				$checked = ' checked="" ';
			}else{
				$checked = '';
			}
			$talents .='<label>
						<input type="checkbox" name="'.$talentcount.'" value="1"'.$checked.'id="CheckboxGroup1_'.$talentcount.'" />'.$value.'
						</label><br />';
			$talentcount++;
		}
	}
	
}
$talents .= '<input type="hidden" name="spec" value="'.$talentcount.'"/>';
//Finding Dodge
$tmpDex = $Dexterity;
$Dodge = 0;
if($tmpDex >=1001){
	$Dodge = 10 + 10 + 10 + (($tmpDex - 500-400-100)*.01);
}else if($tmpDex >=501){
	$Dodge = 10 + 10  + (($tmpDex -400-100)*.02);
}else if($tmpDex >=101){
	$Dodge = 10 + (($tmpDex -100)*.025);
}else{
	$Dodge = $tmpDex * .1;
}
//Finding Health
$Health = 0;
if($_SESSION[$Class]['level'] < 35){
	$Health = 36 + (4*$_SESSION[$Class]['level']) + (10 *$Vitality);
}else {
	//36+(4+60)+(60-25)*555 checks good
	$Health = 36 + (4 * $_SESSION[$Class]['level']) + ($_SESSION[$Class]['level'] - 25) * $Vitality;
}

/* Monk Talents */ 
$ArmorMult = 0;
$DodgeMult = 0;
$LifeMult = 0;
$HealthMult = 0;
if($Class == 'Monk')
{
	//STI
	if($_SESSION[$Class]['Spec'][0] == 1){
		$Armor += $Dexterity;
	}

	//OWE
	if($_SESSION[$Class]['Spec'][1] == 1){
		$res = array($PhysRes,$ColdRes,$FireRes,$LighRes,$PoisRes,$ArcaRes);
		$highest = 0;
		foreach($res as $v){
			if($v > $highest){
				$highest = $v;
			}
		}
		$PhysRes = $highest;
		$ColdRes = $highest;
		$FireRes = $highest;
		$LighRes = $highest;
		$PoisRes = $highest;
		$ArcaRes = $highest;
	}
	//Lightning Flash
	if($_SESSION[$Class]['Spec'][2] == 1){
		$DodgeMult += 16;
	}
	//Bl Wr
	if($_SESSION[$Class]['Spec'][3] == 1){
		$CSkill += 15;
	}
	//Time of Need
	if($_SESSION[$Class]['Spec'][4] == 1){
		$PhysRes = 1.2*$PhysRes;
		$ColdRes = 1.2*$ColdRes;
		$FireRes = 1.2*$FireRes;
		$LighRes = 1.2*$LighRes;
		$PoisRes = 1.2*$PoisRes;
		$ArcaRes = 1.2*$ArcaRes;
		$AllRes =  1.2*$AllRes;
	}
	//Keen Eye
	if($_SESSION[$Class]['Spec'][5] == 1){
		$ArmorMult += 50;
	}
	//Foresight
	if($_SESSION[$Class]['Spec'][6] == 1){
		$CSkill += 18;
	}
	//Hard Target
	if($_SESSION[$Class]['Spec'][7] == 1){
		$DodgeMult += 15;
		$ArmorMult += 20;
	}
	//Walk through the Reeds
	if($_SESSION[$Class]['Spec'][8] == 1){
		$MovementSpeed += 5;
	}
	//Earth Ally
	if($_SESSION[$Class]['Spec'][9] == 1){
		$HealthMult += 10; 
	}
	//Heavenly Body
	if($_SESSION[$Class]['Spec'][10] == 1){
		$Vitality = 1.1 * $Vitality;
	}
	//Exalted Soul
	if($_SESSION[$Class]['Spec'][11] == 1){
		$Mana += 100;
	}
	
}
//Demon Hunter Talents
if($Class == 'DemonHunter')
{	
	//Steady Aim
	if($_SESSION[$Class]['Spec'][0] == 1){
		$CSkill += 20;
	}
	//Archery Bow
	if($_SESSION[$Class]['Spec'][1] == 1){
		$CSkill += 15;
	}
	//Archery XBow
	if($_SESSION[$Class]['Spec'][2] == 1){
		$CCritDmg += 50;
	}
	//Archery 1h XBow
	if($_SESSION[$Class]['Spec'][3] == 1){
		$CCrit += 10;
	}
	//SharpShooter
	$Sharpshooter = false;
	if($_SESSION[$Class]['Spec'][4] == 1){
		$Sharpshooter = true;
	}
	//Vengeance
	if($_SESSION[$Class]['Spec'][5] == 1){
		$Mana = 150 . ' | '. 30;
	}
	//Hot Pursuit
	if($_SESSION[$Class]['Spec'][6] == 1){
		$MovementSpeed += 15;
	}
	
}
//Barb Talents
if($Class == 'Barbarian')
{
	//Nerves of Steel
	if($_SESSION[$Class]['Spec'][0] == 1){
		$Armor += $Vitality;
	}
	
	//Tough as Nails
	if($_SESSION[$Class]['Spec'][1] == 1){
		$ArmorMult +=25;
	}
}
//Wizard Talents
if($Class == 'Wizard')
{
	//Magic Weapon
	if($_SESSION[$Class]['Spec'][0] == 1){
		$CSkill += 10;
	}
	//Force Weapon
	if($_SESSION[$Class]['Spec'][1] == 1){
		$CSkill += 15;
	}
	//Sparkflint
	if($_SESSION[$Class]['Spec'][2] == 1){
		$CSkill += 12;
	}
	//Energy Armor
	if($_SESSION[$Class]['Spec'][3] == 1){
		$ArmorMult += 65;
	}
	//PP Armor
	if($_SESSION[$Class]['Spec'][4] == 1){
		$ArmorMult += 65;
		$CCrit += 5;
	}
	//Pris Armor
	if($_SESSION[$Class]['Spec'][5] == 1){
		$ArmorMult += 65;
		$PhysRes = 1.4*$PhysRes;
		$ColdRes = 1.4*$ColdRes;
		$FireRes = 1.4*$FireRes;
		$LighRes = 1.4*$LighRes;
		$PoisRes = 1.4*$PoisRes;
		$ArcaRes = 1.4*$ArcaRes;
		$AllRes =  1.4*$AllRes;
	}
	//GC
	//Double check your rounds after this. This is going to break display.
	if($_SESSION[$Class]['Spec'][6] == 1){
		$CSkill += 15;
		$ArmorMult += -10;
		$PhysRes = $PhysRes/1.1;
		$ColdRes = $ColdRes/1.1;
		$FireRes = $FireRes/1.1;
		$LighRes = $LighRes/1.1;
		$PoisRes = $PoisRes/1.1;
		$ArcaRes = $ArcaRes/1.1;
		$AllRes =  $AllRes/1.1;
	}
	//Blur
	if($_SESSION[$Class]['Spec'][7] == 1){
		$DamageReductionAddition = 20;
	}
	
}
	$crit = ($CCrit/100) * ($CCritDmg/100);	
if($_SESSION[$Class]['OHand'][1400] > 0){
	if(!$Sharpshooter){
		$crit = ($CCrit/100) * ($CCritDmg/100);	
		$first = (1.15 * (($damageW + $damageO) * (1+$crit) * (1+(($primestat-1)/100)) * 1+($CSkill/100)) )/((1/$attackspeedW) + (1/$attackspeedO));
		$second = (1.15 * (($damageW + $damageO) * (1+$crit) * (1+(($primestat)/100)) * 1+($CSkill/100)) )/((1/$attackspeedW) + (1/$attackspeedO));
		$Damage = round(($first + $second)/2,2);
	}else{
		$crit = ($CCritDmg/100);
		$first = (1.15 * (($damageW + $damageO) * (1+$crit) * (1+(($primestat-1)/100)) * 1+($CSkill/100)) )/((1/$attackspeedW) + (1/$attackspeedO));
		$second = (1.15 * (($damageW + $damageO) * (1+$crit) * (1+(($primestat)/100)) * 1+($CSkill/100)) )/((1/$attackspeedW) + (1/$attackspeedO));
		$Damage = round(($first + $second)/2,2);		
	}
}else{
	if(!$Sharpshooter){
		$crit = ($CCrit/100) * ($CCritDmg/100);	
		$Damage = round((1+($primestat/100))*$damage*$AttackSpeed*(1+$crit)*(1+($CSkill/100)),2);
	}else{
		$crit = ($CCritDmg/100);
		$Damage = round((1+($primestat/100))*$damage*$AttackSpeed*(1+$crit)*(1+($CSkill/100)),2);
	}
}
/*
These go last
*/
$Armor = round($Armor * (1+($ArmorMult/100)),0);
$Dodge = round(1-((1-($DodgeMult / 100)) * (1-($Dodge / 100))),2);

$Health = round($Health * (1 + (($LifePercent + $HeathMult) / 100)),0);
$Vitality = round($Vitality,0);
$DamageReduction = round(($Armor/(50*60 + $Armor))*100,2);
$DamageReduction += $DamageReductionAddition;
?>
<html>
	<head>
		<title>UD3A - <?php echo $Class;?> - BETA</title>
		<script type="text/javascript" src="./jq.js"></script>
		<script type="text/javascript">
			$(document).ready(function() {
				$("#newFieldBtn").click(function(){
					var c = $(".field:first").clone();
					$("#newFieldBtn").before(c);
					c.children("input:text").val('');
					var deleteBtn = document.createElement('input');
					deleteBtn.type = "button";
					deleteBtn.value = "Remove";
					deleteBtn.id = "delBtn";
					deleteBtn.style.marginLeft = "5px";
					$(".field:last").append(deleteBtn);
					$("input[value='Remove']:button").click(function(){
						$(this).parent().remove();
					});
				});
			});
		</script>
<link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" bgcolor="black" color="white">
<?php
//Check to see if user is logged in, if yes, allow save
if(isset($_SESSION['User'])){
	$MysqlAllow = '<form method="post" action = "'.$_SERVER['PHP_SELF']."?".htmlspecialchars($_SERVER['argv'][0]).'">
				  (<input type="submit" name="Mysql" id="saveBtn" value="Save" /> / 
				<input type="submit" name="Mysql" id="loadBtn" value="Load" />) Your Character. <br /> In order for your character to save, you need to click Save
				</form>';
} else {
	$MysqlAllow = 	'Please Login to Save your Character\'s Gear';
}
if($Input == true || $Class == null){
//This is the input fields for the items. Don't fuck with it.
	echo $Display;
}else{
// This is prototype for the stash.. It looks like shit, don't enable it.
echo '
<!--
<div id="stashBack">
  <div class="stashBox" id="s00"></div>
  <div class="stashBox" id="s01"></div>
  <div class="stashBox" id="s02"></div>
  <div class="stashBox" id="s03"></div>
  <div class="stashBox" id="s04"></div>
  <div class="stashBox" id="s05"></div>
  <div class="stashBox" id="s06"></div>
  <div class="stashBox" id="s07"></div>
  <div class="stashBox" id="s08"></div>
  <div class="stashBox" id="s09"></div>
  <div class="stashBox" id="s10"></div>
  <div class="stashBox" id="s11"></div>
  <div class="stashBox" id="s12"></div>
  <div class="stashBox" id="s13"></div>
  <div class="stashBox" id="s14"></div>
  <div class="stashBox" id="s15"></div>
  <div class="stashBox" id="s16"></div>
  <div class="stashBox" id="s17"></div>
  <div class="stashBox" id="s18"></div>
  <div class="stashBox" id="s19"></div>
  <div class="stashBox" id="s20"></div>
  <div class="stashBox" id="s21"></div>
  <div class="stashBox" id="s22"></div>
  <div class="stashBox" id="s23"></div>
  <div class="stashBox" id="s24"></div>
  <div class="stashBox" id="s25"></div>
  <div class="stashBox" id="s26"></div>
  <div class="stashBox" id="s27"></div>
  <div class="stashBox" id="s28"></div>
  <div class="stashBox" id="s29"></div>
  <div class="stashBox" id="s30"></div>
  <div class="stashBox" id="s31"></div>
  <div class="stashBox" id="s32"></div>
  <div class="stashBox" id="s33"></div>
  <div class="stashBox" id="s34"></div>
  <div class="stashBox" id="s35"></div>
  <div class="stashBox" id="s36"></div>
  <div class="stashBox" id="s37"></div>
  <div class="stashBox" id="s38"></div>
  <div class="stashBox" id="s39"></div>
  <div class="stashBox" id="s40"></div>
  <div class="stashBox" id="s41"></div>
  <div class="stashBox" id="s42"></div>
  <div class="stashBox" id="s43"></div>
  <div class="stashBox" id="s44"></div>
  <div class="stashBox" id="s45"></div>
  <div class="stashBox" id="s46"></div>
  <div class="stashBox" id="s47"></div>
  <div class="stashBox" id="s48"></div>
  <div class="stashBox" id="s49"></div>
  <div class="stashBox" id="s50"></div>
  <div class="stashBox" id="s51"></div>
  <div class="stashBox" id="s52"></div>
  <div class="stashBox" id="s53"></div>
  <div class="stashBox" id="s54"></div>
  <div class="stashBox" id="s55"></div>
  <div class="stashBox" id="s56"></div>
  <div class="stashBox" id="s57"></div>
  <div class="stashBox" id="s58"></div>
  <div class="stashBox" id="s59"></div>
</div>
-->
<div id="footer"><a style="font-size:10px;color: white;" href="/">Home</a> '.$MysqlAllow.'
</div>
<div id="container">
  <div id="atrBox">
    <div id="ab1"></div>
    <div id="str">
      <form id="strInput" name="strInput" method="post" action="">
        <div align="right">
          <input name="strText" type="text" id="strText" value="'.$Strength.'" maxlength="4" />
        </div>
      </form>
    </div>
    <div id="ab3"></div>
    <div id="dex">';
	//Looks better uneditable, but for now keep it enabled for uniformity.
     echo '<form id="form1" name="form1" method="post" action=""><input name="dexText" type="text" id="dexText" value="'.$Dexterity;
	echo '" maxlength="4" /></form>';	  
	echo '</div>
    <div id="ab5"></div>
    <div id="int">
      <form id="form2" name="form2" method="post" action="">
        <input name="intText" type="text" id="intText" value="'.$Intelligence.'" maxlength="4" />
      </form>
    </div>
    <div id="ab7"></div>
    <div id="vit">
      <form id="form3" name="form3" method="post" action="">
        <input name="vitText" type="text" id="vitText" value="'.$Vitality.'" maxlength="4" />
      </form>
    </div>
    <div id="ab9"></div>
    <div id="arm">
      <form id="form4" name="form4" method="post" action="">
        <input name="armText" type="text" id="armText" value="'.$Armor;
		echo '" maxlength="9" />
      </form>
    </div>
    <div id="ab11"></div>
    <div id="dmg">
      <form id="form5" name="form5" method="post" action="">
        <input name="dmgText" type="text" id="dmgText" value="'.$Damage.'" maxlength="9" />
      </form>
    </div>
    <div id="detailsContain">      	  <div id="details"></div>    	</div>	    <div id="goldContain">
      <div id="gold"><br />&nbsp;&nbsp;&nbsp;'.$info.'</div>
    </div>
  </div>
  <div id="leftContain">
    <div id="shoulderC">
      <div id="shoulder"'.$item['Shoulder'].'</div>
    </div>
    <div id="gloveC">
      <div id="glove"'.$item['Gloves'].'</div>
    </div>
    <div id="ring1C">
      <div id="ring1"'.$item['LRing'].'</div>
    </div>
    <div id="mainC">
      <div id="main"'.$item['MHand'].'</div>
    </div>
  </div>
  <div id="midContain">
    <div id="helmC">
      <div id="helm" '.$item['Helm'].'</div>
    </div>
    <div id="chestC">
      <div id="chest"'.$item['Chest'].'</div>
    </div>
    <div id="beltC">
      <div id="belt"'.$item['Belt'].'</div>
    </div>
    <div id="pantsC">
      <div id="pants"'.$item['Pants'].'</div>
    </div>
    <div id="bootsC">
      <div id="boots"'.$item['Boots'].'</div>
    </div>
  </div>
  <div id="rightContain">
    <div id="levelContain">
      <div id="level">';
	  //Display the form if they need to submit level otherwise don't.
	if(!isset($_SESSION[$_GET['c']]['level'])){
		echo '<form id="form6" name="form6" method="post" action="'.$_SERVER['PHP_SELF']."?".htmlspecialchars($_SERVER['argv'][0]).'">
		  <input 	type="hidden" name="charlevel" value="submitted"/>
          <input name="lvlText" type="text" id="lvlText" maxlength="2" />
		  <input type="submit" value="Submit" />
        </form>';
	}else{
		echo '<br /><a href="'.$_SERVER['PHP_SELF'].'?'.htmlspecialchars($_SERVER['argv'][0]).'&d=level" style="font-size:18px;color: white;" >'.$_SESSION[$Class]['level'].'</a>';
	}
    echo'  </div>
    </div>
    <div id="amuC">
      <div id="amulet"'.$item['Amulet'].'</div>
    </div>
    <div id="bracerC">
      <div id="bracer"'.$item['Bracer'].'</div>
    </div>
    <div id="ring2C">
      <div id="ring2"'.$item['RRing'].'</div>
    </div>
    <div id="offC">
      <div id="offhand"'.$item['OHand'].'</div>
    </div>
  </div>
</div>
<div id="colC"></div>
<div id="col1">
  <div id="debug3">
    <form id="form7" name="form7" method="post" action="'.$_SERVER['PHP_SELF']."?".htmlspecialchars($_SERVER['argv'][0]).'">
      <p>';
		echo $talents;
		//Below is displays for all of offense, above is display for talents and the form.
		if($Sharpshooter){
			$CCrit = 100;
		}
	  echo '
	  </p>
	<input type="submit" value="Submit" />
    </form>
    <p>&nbsp;</p>
  </div>
</div>
  <div id="debug2">
    <div id="debug2Var">
      <p>'.$primestat.'%<br />
        '.$CSkill.'%<br />
        '.$AttackSpeed.'<br />
        '.$CCrit.'<br />
        '.$CCritDmg.'<br />
        <br />
        <br />
      </p>
      <p>&nbsp;</p>
    </div>
    <p>Dmg+ by Main Stat<br />
      Dmg+ by Skills<br />
      Attacks per Second<br />
      Critical Hit Chance<br />
      Critical Hit Damage
      <br />
      <br />
    </p>
  </div>
  <div id="debug1">
    <div id="debug1Var">
		<p>';
		
		if($BlockMin > 0){
			echo $BlockMin.' - '.$BlockMax.'<br />
			'.$BlockChance.'<br />';
		}
		//Displays for all of defense here.
        echo round($Dodge*100,0).'%<br />
        '.$DamageReduction.'<br />
        '.round($AllRes + $PhysRes,0).'<br />
        '.round($AllRes + $ColdRes,0).'<br />
        '.round($AllRes + $FireRes,0).'<br />
        '.round($AllRes + $LighRes,0).'<br />
        '.round($AllRes + $PoisRes,0).'<br />
        '.round($AllRes + $ArcaRes,0).'<br />
		'.$CrowdControlReduction.'<br />
		'.$Thorns.'<br />
		'.$MovementSpeed.'<br />
		<br />
		  '.$Health.'<br />
		  '.$Mana.'<br />
		  </p>
</div>
    <p>';
	
	
	if($BlockMin > 0){
		echo 'Block Amount<br />
		Block Chance<br />';
	}
      echo 'Dodge Chance<br />
      Damage Reduction<br />
      Physical Resis.<br />
      Cold Resis.<br />
      Fire Resis.<br />
      Lightning Resis.<br />
      Poison Resis.<br />
      Arcane/Holy Resis.<br />
      Crowd Control Rd.<br />
      Thorns<br />
	  Movement Speed<br />
	  <br />
	  Health<br />
	  '.$ManaName.'<br />
      
    </p>
  </div>';
}
//Obviously disable this shit later.
//So much data can be mined here.
echo "<!-- ";
print_r($_SESSION);
echo " -->";
?>
</body>
</html>