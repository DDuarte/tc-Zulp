<?php
include_once("config.php");
include_once ("geshi.php");

//types enum
$item     = 3;
$creature = 9; // supported
$player   = 25;
$go       = 33; // supported
$spell    = 65;

if (isset($_POST['formdata']) && isset($dbh) && !isset($e)) {
    $formData = $_REQUEST['formdata'];
    $data = explode("Block Value 0:", $formData["blockdata"]);
    $d = preg_match_all('/Block Value 0:/',$formData["blockdata"],$s); // returns the number of "Block Value 0:" found in input data
    while ($i <= $d) {
        $data2 = explode("\n", $data[$i]);
        $RegEx = '/(Block Value ){1}([0-9]{1,3})(: {1}){1}([0-9]{1,12})/';
        foreach ($data2 as $key => $Value) {
            preg_match($RegEx, $Value, $temp);
            if ($temp[2] != "") $block[$temp[2]] = $temp[4];
            // Note: commented out updatefields are not sent in sniffs
            $type           = $block[2]; // OBJECT_FIELD_TYPE
            $entry          = $block[3]; // OBJECT_FIELD_ENTRY
            // $size        = $block[4]; // OBJECT_FIELD_SCALE_X
            
            // Updatefields: creature only
            $bytes0         = $block[23]; // UNIT_FIELD_BYTES_0
            $maxhp          = $block[32]; // UNIT_FIELD_MAXHEALTH
            $level          = $block[54]; // UNIT_FIELD_LEVEL
            $faction        = $block[55]; // UNIT_FIELD_FACTIONTEMPLATE
            $equip1         = $block[56]; // UNIT_VIRTUAL_ITEM_SLOT_ID
            $equip2         = $block[57]; // UNIT_VIRTUAL_ITEM_SLOT_ID
            $equip3         = $block[58]; // UNIT_VIRTUAL_ITEM_SLOT_ID
            $unitFlags      = $block[59]; // UNIT_FIELD_FLAGS
            // $aura        = $block[61]; // UNIT_FIELD_AURASTATE
            $meleeTime      = $block[62]; // UNIT_FIELD_BASEATTACKTIME
            // $meleetime2  = $block[63]; // UNIT_FIELD_BASEATTACKTIME dupe
            // $rangedtime  = $block[64]; // UNIT_FIELD_RANGEDATTACKTIME
            $model          = $block[67]; // UNIT_FIELD_DISPLAYID
            $model2         = $block[68]; // UNIT_FIELD_NATIVEDISPLAYID dupe
            $mount          = $block[69]; // UNIT_FIELD_MOUNTDISPLAYID
            // $mindmg      = $block[70]; // UNIT_FIELD_MINDAMAGE
            // $maxdmg      = $block[71]; // UNIT_FIELD_MAXDAMAGE
            $bytes1         = $block[74]; // UNIT_FIELD_BYTES_1
            $dynamicFlags   = $block[79]; // UNIT_DYNAMIC_FLAGS
            $npcFlags       = $block[82]; // UNIT_NPC_FLAGS
            $emote          = $block[83]; // UNIT_NPC_EMOTESTATE
            // $resistance1 = $block[99]; // UNIT_FIELD_RESISTANCES
            // $resistance2 = $block[100]; // UNIT_FIELD_RESISTANCES
            // $resistance3 = $block[101]; // UNIT_FIELD_RESISTANCES
            // $resistance4 = $block[102]; // UNIT_FIELD_RESISTANCES
            // $resistance5 = $block[103]; // UNIT_FIELD_RESISTANCES
            // $resistance6 = $block[104]; // UNIT_FIELD_RESISTANCES
            // $resistance7 = $block[105]; // UNIT_FIELD_RESISTANCES
            // $manamod     = $block[120]; // UNIT_FIELD_BASE_MANA
            // $healthmod   = $block[121]; // UNIT_FIELD_BASE_HEALTH
            $bytes2         = $block[122]; // UNIT_FIELD_BYTES_2
            // $meleeap     = $block[123]; // UNIT_FIELD_ATTACK_POWER
            // $dmgmultiplier = $block[125]; // UNIT_FIELD_ATTACK_POWER_MULTIPLIER
            // $rangedap    = $block[126]; // UNIT_FIELD_RANGED_ATTACK_POWER
            // $rangedmindmg = $block[129]; // UNIT_FIELD_MINRANGEDDAMAGE
            // $rangedmaxdmg = $block[130]; // UNIT_FIELD_MAXRANGEDDAMAGE
            if (isset($bytes0)) {
                $powerType= ($bytes0 & 2147483647) >> 24;
                $gender   = ($bytes0 & 16711680) >> 16;
                $class    = ($bytes0 & 65280) >> 8;
                $race     = ($bytes0 & 255);
            }
            $RegEx65 = '/Block Value 65: [0-9]{1,12}\/([0-9](.[0-9]{1,12}|))/'; // bounding radius
            $RegEx66 = '/Block Value 66: [0-9]{1,12}\/([0-9](.[0-9]{1,12}|))/'; // combat reach
            preg_match($RegEx65, $data[$i], $tmp);
            $boundingRadius = $tmp[1];
            preg_match($RegEx66, $data[$i], $tmp);
            $combatReach = $tmp[1];
            $regexwalk = '/Walk Speed: ([0-9]*(.|)[0-9]*)/'; // walk speed
            preg_match($regexwalk, $_REQUEST['formdata'][blockdata], $walker);
            if(isset($walker)) $walkspeed = $walker[1]/$walkBase;
            $regexrun = '/Run Speed: ([0-9]*(.|)[0-9]*)/'; // run speed
            preg_match($regexrun, $_REQUEST['formdata'][blockdata], $runner);
            if(isset($runner)) $runspeed = $runner[1]/$runBase;
            $regexveh = '/Vehicle ID: ([0-9]*)/'; // vehicle id
            preg_match($regexveh, $_REQUEST['formdata'][blockdata], $vehicler);
            if(isset($vehicler)) $vehicle = $vehicler[1];
            
            // Update fields: gameobject only
            $gModel  = $block[8]; // GAMEOBJECT_DISPLAYID
            $gFlags  = $block[9]; // GAMEOBJECT_FLAGS
            // $gRot1   = $block[10]; // GAMEOBJECT_PARENTROTATION
            // $gRot2   = $block[11]; // GAMEOBJECT_PARENTROTATION
            // $gRot3   = $block[12]; // GAMEOBJECT_PARENTROTATION
            $gFaction = $block[15]; // GAMEOBJECT_FACTION
            // &gLevel = $block[16]; // GAMEOBJECT_LEVEL (not implemented on DB)
            // $gBytes1 = $block[17]; // GAMEOBJECT_BYTES_1 (unknown use)
        }
        if (isset($block)) {
            switch ($type) {
                case $creature:
                    $sql = "SELECT * FROM creature_template WHERE entry =$entry";
                    $stmt = $dbh->query($sql);
                    $npc = $stmt->fetch(PDO::FETCH_OBJ);
                    // Get name
                    if (!empty($npc->name)) {
                        $name = $npc->name;
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
                            $cache = dirname(__FILE__) . "\cache\c$entry.txt";
                            if (!file_exists($cache) || filemtime($cache) < (time() - $cache_check_time)) {
                                $query = "http://www.wowhead.com/npc=$entry";
                                $wh = file_get_contents($query, true);
                                $cachefile = fopen($cache, 'wb');
                                fwrite($cachefile, $wh);
                                fclose($cachefile);
                            }
                            else $wh = file_get_contents($cache);
                            preg_match('/<li>Level: ([0-9]*) - ([0-9]*)<\/li>/', $wh, $tmp);
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
                        if ($npcFlags != $npc->npcflags) $up .= " `npcflag`=`npcflag`|$npcFlags "; // need bitwise math here
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
                    if (isset($equip1) || isset($equip2) || isset($equip3)) {
                        if (empty($equip1)) $equip1 = 0;
                        if (empty($equip2)) $equip2 = 0;
                        if (empty($equip3)) $equip3 = 0;
                        if ($npc->equipment_id != 0) {
                            $equip = $npc->equipment_id;
                            $sql = "SELECT * FROM creature_equip_template WHERE entry =$equip";
                            $stmt = $dbh->query($sql);
                            $eqs = $stmt->fetch(PDO::FETCH_OBJ);
                            if ($eqs->equipentry1 == $equip1 && $eqs->equipentry2 == $equip2 && $eqs->equipentry3 == $equip3)
                                $log .= "&#8226; Equip template of $entry ($name) does not need an update.<br />";
                            else {
                                $eqheader .= "SET @EquiEntry = XXX; -- (creature_equip_template.entry - need 1)\n";
                                $up .= " `equipment_id`=@EquiEntry ";
                                $ins .= "-- Equipment of $entry ($name)\n";
                                $ins .= "DELETE FROM `creature_equip_template` WHERE `entry`=@EquiEntry;\n";
                                $ins .= "INSERT INTO `creature_equip_template` (`entry`,`equipentry1`,`equipentry2`,`equipentry3`) VALUES \n";
                                $ins .= "(@EquiEntry,$equip1,$equip2,$equip3);\n";
                            }
                        } else {
                            $sql = "SELECT * FROM creature_equip_template WHERE equipentry1=$equip1 AND equipentry2=$equip2 AND equipentry3=$equip3";
                            $stmt = $dbh->query($sql);
                            $eq = $stmt->fetch(PDO::FETCH_OBJ);
                            if ($eq == FALSE) {
                                $eqheader .= "SET @EquiEntry = XXX; -- (creature_equip_template.entry - need 1)\n";
                                $up .= " `equipment_id`=@EquiEntry ";
                                $ins .= "-- Equipment of $entry ($name)\n";
                                $ins .= "DELETE FROM `creature_equip_template` WHERE `entry`=@EquiEntry;\n";
                                $ins .= "INSERT INTO `creature_equip_template` (`entry`,`equipentry1`,`equipentry2`,`equipentry3`) VALUES\n";
                                $ins .= "(@Entry,$equip1,$equip2,$equip3);\n";
                            }
                            else {
                                $equipe = $eq->entry;
                                $up .= " `equipment_id`=$equipe ";
                            }
                        }
                    }
                    // Unit_Class
                    if (isset($class)) {
                        if ($class != $npc->unit_class) $up .= " `unit_class`=$class ";
                        else $log .= "&#8226; Unit_class of $entry ($name) does not need an update.<br />";
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
                        if ($runspeed == 1.1428571428571) $runspeed = 1.14286; // need round function here
                        if ($walkspeed != $npc->speed_walk) $up .= " `speed_walk`=$walkspeed ";
                        else $log .= "&#8226; Walk speed of $entry ($name) does not need an update.<br />";
                        if($runspeed != $npc->speed_run) $up .= " `speed_run`=$runspeed ";
                        else $log .= "&#8226; Run speed of $entry ($name) does not need an update.<br />";
                    }
                    // Vehicle
                    if (isset($vehicle)) {
                        if($vehicle != $npc->vehicleid) $up .= " `vehicleid`=$vehicle ";
                        else $log .= "&#8226; Vehicleid of $entry ($name) does not need an update.<br />";
                    }
                    
                    // sql output
                    $up2 = preg_replace('/  /', ',', $up); // replace double spaces by commas
                    $up3 = preg_replace('/ /', '', $up2); // remove single spaces
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
                    if (!empty($go->name)) {
                        $name = $go->name;
                        $log .= "&#8984; $entry ($name) is a gameobject.<br />";
                    }
                    else {
                        $log .= "&#8984; <b>Gameobject_template.entry $entry ($name) not found!</b><br />";
                        break;
                    }
                    // Flags
                    if (isset($gFlags)) {
                        $cf = $go->flags;
                        if ($gFlags != $go->flags) {
                            $up .= " `flags`=`flags`|$gFlags ";
                            $log .= "&#8226; Flags of $entry ($name) is $cf (vs $gFlags) in DB.<br />";
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
                    $up2 = preg_replace('/  /', ',', $up); // replace double spaces by commas
                    $up3 = preg_replace('/ /', '', $up2); // remove single spaces
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
        unset($walkspeed,$runspeed,$upheader,$upfoot,$eqheader,$addon,$basehp,$basehpi,$block,$boundingRadius,$bytes0,$bytes1,$bytes2,$cache,$cachefile,$class,$combatReach,$dded,$dynamicFlags,$emote,$entry,$eq,$eqs,$equip,$equip1,$equip2,$equip3,$equipe,$exp,$faction,$gFlags,$gLevel,$gModel,$gRot1,$gRot2,$gRot3,$gender,$header,$health_mod,$hpstat,$ins,$level,$maxLevel,$maxhp,$meleeTime,$minLevel,$model,$model2,$mount,$name,$ndec,$npc,$npcFlags,$powerType,$query,$race,$sql,$stmt,$type,$udder,$unitFlags,$up,$up2,$up3,$wh,$tmp,$temp);
        $i++;
        $sqlres = new GeSHi($result, sql);
    }
}
?>