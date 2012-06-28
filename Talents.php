<?php
/* MONK 
Sieze The Initiative 	Armor += Dexterity
One With Everything		Find highest resistence on gear, All resistence is that one
Lightning Flash			16% dodge
Blazing Wrath			15% dmg
Time of Need			20% more resistence
Keen Eye				Increases armor by 50%
Foresight				Increases Dmg by 18%
Hard Target				+15% dodge, 20%armor
Wind Through the Reeds	5% Movement speed
Earth Ally				Life by 10%
Heavenly Body			Vitality by 10%
*/
/* Wizard
Magic Weapon -  			+10% dmg
Force Weapon				+15% dmg

Sparkflint					+12% dmg
		
Energy Armor				+65% armor
Pinpoint Barrier			+65% armor +5% crit
Prismatic Armor				+65% armor +40% resist all

Glass Cannon				+15% dmg -10% to resists and armor
Blur 						-20% dmg from melee

--- not relevant for character sheet ---
Arcane Torrent - Disruption		+15% dmg from arcane
Cold Blooded				+20% cold dmg to chilled/frozen targets
Conflagration				+10% dmg debuff from fire dm
--- not relevant for character sheet ---
*/

/* Demon Hunter
Steady Aim							+20% dmg if no enemies within 10yrds
Archery (varies with wep type)		bow: +15% dmg, 
									xbow: +50% crit dmg, 
									1h xbow: +10% crit chance
Sharpshooter						+100% crit chance (3% every second)
Vengeance							+25 max hatred
Hot Pursuit							+15% movement speed (at full hatred only)

--- not relevant for character sheet ---
Cull the Weak						+15% dmg to slowed targets
Ballistics							+50% dmg from rocket attacks
*/


$Talents = array(
'Monk' => array('Seize The Initiative','One With Everything',
'Lightning Flash','Blazing Wrath','Time of Need','Keen Eye','Foresight','Hard Target',
'Wind Through the Reeds','Earth Ally','Heavenly Body','Exalted Soul'),
'Wizard' =>array('Magic Weapon','Force Weapon','Sparkflint','Energy Armor',
'Pinpoint Barrier','Prismatic Armor','Glass Cannon','Blur'),
'DemonHunter' => array('Steady Aim','Archery : Bow','Archery : Crossbow','Archery : 1h Crossbow','Sharpshooter','Vengeance','Hot Pursuit'),
'Barbarian' => array('Nerves of Steel','Tough As Nails')
);
?>