<?php
include_once 'db.php';
include_once 'config.php';
include_once 'geshi.php';
include_once 'utility.php';

//types enum
$item     = 3;
$creature = 9; // supported
$player   = 25;
$go       = 33; // supported
$spell    = 65;

$log = '';
$formData = '';
$formData["blockdata"] = '';
$i = 0;
$result = '';
$up = '';

$regexwalk = '/Walk Speed: ([0-9]*(.|)[0-9]*)/'; // walk speed
$regexrun = '/Run Speed: ([0-9]*(.|)[0-9]*)/'; // run speed
$regexveh = '/Vehicle ID: ([0-9]*)/'; // vehicle id

if (isset($_POST['formdata']) && isset($dbh) && !isset($e)) {
    $formData = $_REQUEST['formdata'];
    $data = explode("OBJECT_FIELD_GUID:", $formData["blockdata"]);
    $d = preg_match_all('/OBJECT_FIELD_GUID:/',$formData["blockdata"],$s); // returns the number of "Block Value 0:" found in input data
    while ($i <= $d) {
        $data2 = explode("\n", $data[$i]);
        $RegEx = '/()([A-Z0-9]+_[A-Z0-9]+_*[A-Z0-9_]*)(: {1}){1}([0-9]{1,12})/';
        foreach ($data2 as $key => $Value) {
            preg_match($RegEx, $Value, $temp);
            if ($temp[2] != "") $block[$temp[2]] = $temp[4];

            $type           = $block["OBJECT_FIELD_TYPE"];
            $entry          = $block["OBJECT_FIELD_ENTRY"];
            $size           = $block["OBJECT_FIELD_SCALE_X"];
            $bytes0         = $block["UNIT_FIELD_BYTES_0"];
            $maxhp          = $block["UNIT_FIELD_MAXHEALTH"];
            $level          = $block["UNIT_FIELD_LEVEL"];
            $faction        = $block["UNIT_FIELD_FACTIONTEMPLATE"];
            $equip15         = $block["UNIT_VIRTUAL_ITEM_SLOT_ID"];
            $equip1         = $block["UNIT_VIRTUAL_ITEM_SLOT_ID1"];
            $equip2         = $block["UNIT_VIRTUAL_ITEM_SLOT_ID2"];
            $equip3         = $block["UNIT_VIRTUAL_ITEM_SLOT_ID3"];
            $unitFlags      = $block["UNIT_FIELD_FLAGS"];
            // $aura        = $block["UNIT_FIELD_AURASTATE"];
            $meleeTime      = $block["UNIT_FIELD_BASEATTACKTIME"];
            $meleetime2     = $block["UNIT_FIELD_BASEATTACKTIME2"];
            $rangedtime     = $block["UNIT_FIELD_RANGEDATTACKTIME"];
            $model          = $block["UNIT_FIELD_DISPLAYID"];
            $model2         = $block["UNIT_FIELD_NATIVEDISPLAYID"];
            $mount          = $block["UNIT_FIELD_MOUNTDISPLAYID"];
            $mindmg         = $block["UNIT_FIELD_MINDAMAGE"];
            $maxdmg         = $block["UNIT_FIELD_MAXDAMAGE"];
            $bytes1         = $block["UNIT_FIELD_BYTES_1"];
            $dynamicFlags   = $block["UNIT_DYNAMIC_FLAGS"];
            $npcFlags       = $block["UNIT_NPC_FLAGS"];
            $emote          = $block["UNIT_NPC_EMOTESTATE"];
            $resistance1    = $block["UNIT_FIELD_RESISTANCES1"];
            $resistance2    = $block["UNIT_FIELD_RESISTANCES2"];
            $resistance3    = $block["UNIT_FIELD_RESISTANCES3"];
            $resistance4    = $block["UNIT_FIELD_RESISTANCES4"];
            $resistance5    = $block["UNIT_FIELD_RESISTANCES5"];
            $resistance6    = $block["UNIT_FIELD_RESISTANCES6"];
            $resistance7    = $block["UNIT_FIELD_RESISTANCES7"];
            $manamod        = $block["UNIT_FIELD_BASE_MANA"];
            $healthmod      = $block["UNIT_FIELD_BASE_HEALTH"];
            $bytes2         = $block["UNIT_FIELD_BYTES_2"];
            $meleeap        = $block["UNIT_FIELD_ATTACK_POWER"];
            $dmgmultiplier  = $block["UNIT_FIELD_ATTACK_POWER_MULTIPLIER"];
            $rangedap       = $block["UNIT_FIELD_RANGED_ATTACK_POWER"];
            $rangedmindmg   = $block["UNIT_FIELD_MINRANGEDDAMAGE"];
            $rangedmaxdmg   = $block["UNIT_FIELD_MAXRANGEDDAMAGE"];
            $RegEx65 = '/UNIT_FIELD_BOUNDINGRADIUS: [0-9]{1,12}\/([0-9]+(.[0-9]{1,12}|))/'; // bounding radius
            $RegEx66 = '/UNIT_FIELD_COMBATREACH: [0-9]{1,12}\/([0-9]+(.[0-9]{1,12}|))/'; // combat reach
            $RegExhh = '/UNIT_FIELD_HOVERHEIGHT: [0-9]{1,12}\/([0-9]+(.[0-9]{1,12}|))/'; // hover height

            if (isset($bytes0)) {
                $powerType= ($bytes0 & 2147483647) >> 24;
                $gender   = ($bytes0 & 16711680) >> 16;
                $class    = ($bytes0 & 65280) >> 8;
                $race     = ($bytes0 & 255);
            }

            preg_match($RegEx65, $data[$i], $tmp);
            $boundingRadius = $tmp[1];
            preg_match($RegEx66, $data[$i], $tmp);
            $combatReach = $tmp[1];
            preg_match($RegExhh, $data[$i], $tmp);
            $hoverHeight = $tmp[1];
            preg_match($regexwalk, $_REQUEST['formdata']['blockdata'], $walker);
            if(isset($walker)) $walkspeed = $walker[1]/$walkBase;
            preg_match($regexrun, $_REQUEST['formdata']['blockdata'], $runner);
            if(isset($runner)) $runspeed = $runner[1]/$runBase;
            preg_match($regexveh, $_REQUEST['formdata']['blockdata'], $vehicler);
            if(isset($vehicler)) $vehicle = $vehicler[1];

            // Update fields: gameobject only
            $gModel   = $block["GAMEOBJECT_DISPLAYID"];
            $gFlags   = $block["GAMEOBJECT_FLAGS"];
            $gRot1    = $block["GAMEOBJECT_PARENTROTATION1"];
            $gRot2    = $block["GAMEOBJECT_PARENTROTATION2"];
            $gRot3    = $block["GAMEOBJECT_PARENTROTATION3"];
            $gFaction = $block["GAMEOBJECT_FACTION"]; //
            $gLevel   = $block["GAMEOBJECT_LEVEL"]; // (not implemented on DB)
            $gBytes1  = $block["GAMEOBJECT_BYTES_1"]; // (unknown use)
        }
        if (isset($block)) {
            switch ($type) {
                case $creature:
                    $sql = "SELECT * FROM creature_template WHERE entry =$entry";
                    $stmt = $dbh->query($sql);
                    $npc = $stmt->fetch(PDO::FETCH_OBJ);
                    // Get name
                    if (!empty($npc->entry)) {
                        $name = $npc->name;
						if (empty($name)) $name = "NONAME";
                        $log .= "&#8984; $entry ($name) is a creature.<br />";
                    }
                    else {
                        $log .= "&#8984; <b>Creature_template.entry $entry ($name) not found!</b><br />";
                        break;
                    }
                    // Factions
                    if (isset($faction)) {
                        if ($faction == 1 || $faction == 2 || $faction == 3 || $faction == 4 || $faction == 5 || $faction == 6 || $faction == 115 || $faction == 116 || $faction == 1610 || $faction == 1629) $faction = 35; // player factions
                        if ($faction != $npc->faction_A) $up .= " `faction_A`=$faction  `faction_H`=$faction ";
                        else $log .= "&#8226; Faction of $entry ($name) does not need an update.<br />";
                    }
                    // Levels & Exp
                    if (isset($level)) {
                        // Exp
                        $health_mod = $npc->Health_mod;
                        $basehp = $maxhp / $health_mod;
                        $basehp = round($basehp, 0);
                        $sql = "SELECT * FROM creature_classlevelstats WHERE level=$level AND class=$class";
                        $stmt = $dbh->query($sql);
                        $hpstat = $stmt->fetch(PDO::FETCH_OBJ);
                        if (!isset($hpstat) || ($basehp == !$hpstat->basehp0) || ($basehp == !$hpstat->basehp1) || ($basehp == !$hpstat->basehp2))
                            $log .= "&#8226; Exp (bhp$basehp, l$level, c$class) not found for creature $entry ($name).<br />";
                        else {
                            $basehpi = $maxhp / $health_mod;
                            $basehpi = round($basehpi, 0);
                            if ($basehpi == $hpstat->basehp0) $exp = 0;
                            if ($basehpi == $hpstat->basehp1) $exp = 1;
                            if ($basehpi == $hpstat->basehp2) $exp = 2;
                            if ($exp == $npc->exp) $log .= "&#8226; Exp ($exp) of $entry ($name) does not need an update.<br />";
                            elseif (isset($exp)) $up .= " `exp`=$exp ";
                            else $log .= "&#8226; Exp (bhp$basehp, l$level, c$class) not found for creature $entry ($name).<br />";
                        }
                        // Levels
                        if (($npc->minlevel != $npc->maxlevel)) { // shoulda query wowhead instead and check if minlevel != maxlevel
                            $cache = dirname(__FILE__) . "/cache/c$entry.txt";
                            if (!file_exists($cache) || filemtime($cache) < (time() - $cache_check_time)) {
                                $query = "http://www.wowhead.com/npc=$entry";
                                $wh = file_get_contents($query, true);
                                $cachefile = fopen($cache, 'wb');
                                fwrite($cachefile, $wh);
                                fclose($cachefile);
                            }
                            else $wh = file_get_contents($cache);
                            preg_match('/\[li\]Level: ([0-9]*) - ([0-9]*)\[\/li\]/', $wh, $tmp);
                            $minLevel = $tmp[1];
                            $maxLevel = $tmp[2];
                            if (!isset($minLevel) && !isset($maxLevel))
                                $log .= "&#8226; Minlevel=!Maxlevel (creature $entry) but Wowhead does not have any data about that.<br />";
                            elseif ($npc->minlevel == $minLevel && $npc->maxlevel == $maxLevel) $log .= "&#8226; Level of $entry ($name) does not need an update.<br />";
                            else {
                                $up .= " `minlevel`=$minLevel  `maxlevel`=$maxLevel ";
                                $log .= "&#8226; Minlevel($minLevel) and maxlevel($maxLevel) info for creature $entry ($name) from Wowhead.<br />";
                            }
                        }
                        elseif (($level != $npc->minlevel) || ($level != $npc->maxlevel)) {
                            $up .= " `minlevel`=$level  `maxlevel`=$level ";
                        }
                        else $log .= "&#8226; Level of $entry ($name) does not need an update.<br />";
                    }
                    // Baseattack time
                    if (isset($meleeTime)) {
                        if ($meleeTime != $npc->baseattacktime) $up .= " `baseattacktime`=$meleeTime ";
                        else $log .= "&#8226; Baseattacktime of $entry ($name) does not need an update.<br />";
                    }
                    // Npc flags
                    if (isset($npcFlags)) {
                        if ($npcFlags != $npc->npcflag) $up .= " `npcflag`=`npcflag`|$npcFlags "; // need bitwise math here
                        else $log .= "&#8226; Npcflags of $entry ($name) does not need an update.<br />";
                    }
                    // Unit flags
                    if (isset($unitFlags)) {
                        if ($unitFlags != $npc->unit_flags) $up .= " `unit_flags`=`unit_flags`|$unitFlags "; // need bitwise math here
                        else $log .= "&#8226; Unit_flags of $entry ($name) does not need an update.<br />";
                    }
                    // Dynamic flags
                    if (isset($dynamicFlags)) {
                        if ($dynamicFlags != $npc->dynamicflags) $up .= " `dynamicflags`=`dynamicflags`|$dynamicFlags "; // need bitwise math here
                        else $log .= "&#8226; Dynamicflags of $entry ($name)does not need an update.<br />";
                    }
                    // Equipment template
                    if (isset($equip1) || isset($equip2) || isset($equip3) || isset($equip15)) {
                        if (empty($equip15)) $equip15 = 0;
                        else $equip1 = $equip15;
                        if (empty($equip1)) $equip1 = 0;
                        if (empty($equip2)) $equip2 = 0;
                        if (empty($equip3)) $equip3 = 0;

                        $sql = "SELECT * FROM creature_equip_template WHERE entry =$entry LIMIT 1";
                        $stmt = $dbh->query($sql);
                        $eqs = $stmt->fetch(PDO::FETCH_OBJ);
                        $idequip = $eqs->id;

                        if (empty($eqs->entry) {
                            $ins .= "-- Equipment of $entry ($name)\n";
                            $ins .= "DELETE FROM `creature_equip_template` WHERE `entry`=$entry;\n";
                            $ins .= "INSERT INTO `creature_equip_template` (`entry`,`id`,`itemEntry1`,`itemEntry2`,`itemEntry3`) VALUES \n";
                            $ins .= "($entry,1,$equip1,$equip2,$equip3);\n";
                        }
                        else {
                            if ($eqs->itemEntry1 == $equip1 && $eqs->itemEntry2 == $equip2 && $eqs->itemEntry3 == $equip3)
                                $log .= "&#8226; Equip template of $entry ($name) does not need an update.<br />";
                            else
                                $ins .= "UPDATE `creature_equip_template` SET `itemEntry1`=$equip1, `itemEntry2`=$equip2, `itemEntry3`=$equip3 WHERE `entry`=$entry AND `id`=$idequip;\n";
                        }
                    }
                    // Unit Class
                    if (isset($class)) {
                        if ($class != $npc->unit_class) $up .= " `unit_class`=$class ";
                        else $log .= "&#8226; Unit_class of $entry ($name) does not need an update.<br />";
                    }
                    // Hover Height
                    if (isset($hoverHeight)) {
                        if ($hoverHeight != $npc->HoverHeight) $up .= " `HoverHeight`=$hoverHeight ";
                        else $log .= "&#8226; HoverHeight of $entry ($name) does not need an update.<br />";
                    }
                    // Model
                    if (isset($model2)) {
                        /*if ($model2 != $npc->modelid1 || $model2 != $npc->modelid2 || $model2 != $npc->modelid3 || $model2 != $npc->modelid4)
                            $up .= " `modelidX`=$model2 "; // WDB data, shouldn't need update
                        else $log .= "&#8226; Model of $entry does not need an update.<br />";*/
                        // Gender, combat reach, bounding radius
                        if (empty($boundingRadius)) $boundingRadius = 0;
                        if (empty($combatReach)) $combatReach = 0;
                        if (empty($gender)) $gender = 0;
                        $sql = "SELECT * FROM creature_model_info WHERE modelid=$model2";
                        $stmt = $dbh->query($sql);
                        $model = $stmt->fetch(PDO::FETCH_OBJ);
                        if ($model == FALSE) {
                            $ins .= "-- Model data $model2 (creature $entry ($name))\n";
                            $ins .= "DELETE FROM `creature_model_info` WHERE `modelid`=$model2;\n";
                            $ins .= "INSERT INTO `creature_model_info` (`modelid`,`bounding_radius`,`combat_reach`,`gender`) VALUES\n";
                            $ins .= "($model2,$boundingRadius,$combatReach,$gender); -- $name\n";
                        }
                        elseif ($model->bounding_radius == $boundingRadius && $model->combat_reach == $combatReach && $model->gender == $gender)
                            $log .= "&#8226; Model_info (bouding_radius,combat_reach and gender) of model $model2 (creature $entry ($name)) does not need an update.<br />";
                        else {
                            $ins .= "-- Model data $model2 (creature $entry ($name))\n";
                            $ins .= "UPDATE `creature_model_info` SET `bounding_radius`=$boundingRadius,`combat_reach`=$combatReach,`gender`=$gender WHERE `modelid`=$model2; -- $name\n";
                        }
                    }
                    // Creature_template_addon
                    if (isset($mount) || isset($bytes1) || isset($bytes2) || isset($emote)) {
                        if (empty($mount)) $mount = 0;
                        if (empty($bytes1)) $bytes1 = 0;
                        if (empty($bytes2)) $bytes2 = 0;
                        if (empty($emote)) $emote = 0;
                        $auras = 'NULL';
                        $sql = "SELECT * FROM creature_template_addon WHERE entry=$entry";
                        $stmt = $dbh->query($sql);
                        $addon = $stmt->fetch(PDO::FETCH_OBJ);
                        if ($addon == FALSE) {
                            $ins .= "-- Addon data for creature $entry ($name)\n";
                            $ins .= "DELETE FROM `creature_template_addon` WHERE `entry`=$entry;\n";
                            $ins .= "INSERT INTO `creature_template_addon` (`entry`,`mount`,`bytes1`,`bytes2`,`emote`,`auras`) VALUES\n";
                            $ins .= "($entry,$mount,$bytes1,$bytes2,$emote, $auras); -- $name\n";
                        }
                        elseif ($addon->mount == $mount && $addon->bytes1 == $bytes1 && $addon->bytes2 == $bytes2 && $addon->emote == $emote)
                            $log .= "&#8226; Addon (bytes1,bytes2,mount and emote) of $entry ($name) does not need an update.<br />";
                        else {
                            $ins .= "-- Addon data for creature $entry ($name)\n";
                            $ins .= "UPDATE `creature_template_addon` SET `bytes1`=$bytes1,`bytes2`=$bytes2,`mount`=$mount,`emote`=$emote,`auras`=$auras WHERE `entry`=$entry; -- $name\n\n";
                        }
                    }
                    // Walk & Run speed
                    if (isset($walkspeed) && isset($runspeed)) {
                        $runspeed = round($runspeed ,5);
                        if ($walkspeed != $npc->speed_walk) $up .= " `speed_walk`=$walkspeed ";
                        else $log .= "&#8226; Walk speed of $entry ($name) does not need an update.<br />";
                        if($runspeed != $npc->speed_run) $up .= " `speed_run`=$runspeed ";
                        else $log .= "&#8226; Run speed of $entry ($name) does not need an update.<br />";
                    }
                    // Vehicle
                    if (isset($vehicle)) {
                        if($vehicle != $npc->VehicleId) $up .= " `VehicleId`=$vehicle ";
                        else $log .= "&#8226; Vehicleid of $entry ($name) does not need an update.<br />";
                    }

                    // sql output
                    $up2 = commatize($up);
                    $up3 = spaceLess($up2);
                    $header = "-- Template updates for creature $entry ($name)\n";
                    $upheader = "UPDATE `creature_template` SET ";
                    $upfoot = " WHERE `entry`=$entry;";
                    $nn = " -- $name\n";
                    if (isset($eqheader)) $result .= $eqheader;
                    if (isset($up) || isset($ins)) $result .= $header;
                    if (isset($up)) $result .= $upheader . $up3 . $upfoot . $nn;
                    if (isset($ins)) $result .= $ins;
                    break;
                case $go:
                    $sql = "SELECT * FROM gameobject_template WHERE entry =$entry";
                    $stmt = $dbh->query($sql);
                    $go = $stmt->fetch(PDO::FETCH_OBJ);
                    // Get name
                    if (!empty($go->entry)) {
						$name = $go->name;
                        if (empty($name)) $name = "NONAME";
                        $log .= "&#8984; $entry ($name) is a gameobject.<br />";
                    }
                    else {
                        $log .= "&#8984; <b>Gameobject_template.entry $entry ($name) not found!</b><br />";
                        break;
                    }
                    // Flags
                    if (isset($gFlags)) {
                        if ($gFlags != $go->flags) {
                            $up .= " `flags`=`flags`|$gFlags ";
                            $log .= "&#8226; Flags of $entry ($name) is $go->flags (vs $gFlags) in DB.<br />";
                        }
                        else $log .= "&#8226; Flags of $entry ($name) does not need an update.<br />";
                    }
                    // Faction
                    if (isset($gFaction)) {
                        if ($gFaction == 1 || $gFaction == 2 || $gFaction == 3 || $gFaction == 4 || $gFaction == 5 || $gFaction == 6 || $gFaction == 115 || $gFaction == 116 || $gFaction == 1610 || $gFaction == 1629) $gFaction = 0; // player factions
                        if ($gFaction != $go->faction) $up .= " `faction`=$gFaction ";
                        else $log .= "&#8226; Faction of $entry ($name) does not need an update.<br />";
                    }
                    // sql output
                    $up2 = commatize($up);
                    $up3 = spaceLess($up2);
                    $header = "-- Template updates for gameobject $entry ($name)\n";
                    $upheader = "UPDATE `gameobject_template` SET ";
                    $upfoot = " WHERE `entry`=$entry;";
                    $nn = " -- $name\n";
                    if (isset($eqheader)) $result .= $eqheader;
                    if (isset($up) || isset($ins)) $result .= $header;
                    if (isset($up)) $result .= $upheader . $up3 . $upfoot . $nn;
                    if (isset($ins)) $result .= $ins;

                    break;
                case $item:
                    $log .= "&#8226; $entry is an item - not supported<br />";
                    break;
                case $player:
                    $log .= "&#8226; $entry is a player - not supported<br />";
                    break;
                case $spell:
                    $log .= "&#8226; $entry is an area spell effect - not supported<br />";
                    break;
                default:
                    $log .= "&#8226; ERROR: Type ($type) not found.<br />";
            }
        }
        unset($vehicle,$vehicler,$walkspeed,$runspeed,$upheader,$upfoot,$eqheader,$addon,$basehp,$basehpi,$block,$boundingRadius,$bytes0,$bytes1,$bytes2,$cache,$cachefile,$class,$combatReach,$hoverHeight,$dded,$dynamicFlags,$emote,$entry,$eq,$eqs,$equip,$equip1,$equip2,$equip3,$equipe,$exp,$faction,$gFlags,$gLevel,$gModel,$gRot1,$gRot2,$gRot3,$gender,$header,$health_mod,$hpstat,$ins,$level,$maxLevel,$maxhp,$meleeTime,$minLevel,$model,$model2,$mount,$name,$ndec,$npc,$npcFlags,$powerType,$query,$race,$sql,$stmt,$type,$udder,$unitFlags,$up,$up2,$up3,$wh,$tmp,$temp);
        $i++;
        $sqlres = new GeSHi($result, 'sql');
    }
}
?>