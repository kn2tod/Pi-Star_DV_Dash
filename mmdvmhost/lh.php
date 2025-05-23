<?php @session_start(); ?>
<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/config/config.php';          // MMDVMDash Config
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/tools.php';        // MMDVMDash Tools
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/functions.php';    // MMDVMDash Functions
include_once $_SERVER['DOCUMENT_ROOT'].'/config/language.php';	      // Translation Code

if (! isset($_SESSION['LHSW'])) { $_SESSION['LHSW'] = 0; }

if ( ! isset($_SESSION['CS_URL'])) {
// Check if the config file exists
if (file_exists('/etc/pistar-css.ini')) {
    // Use the values from the file
    $piStarCssFile = '/etc/pistar-css.ini';
    if (fopen($piStarCssFile,'r')) { $piStarCss = parse_ini_file($piStarCssFile, true); }

    // Set the Values from the config file
    if (isset($piStarCss['Lookup']['Service'])) { $callsignLookupSvc = $piStarCss['Lookup']['Service']; }		// Lookup Service "QRZ" or "RadioID"
    else { $callsignLookupSvc = "RadioID"; }										// Set the default if its missing
} else {
    // Default values
    $callsignLookupSvc = "RadioID";
}

// Safety net
if (($callsignLookupSvc != "RadioID") && ($callsignLookupSvc != "QRZ") && ($callsignLookupSvc != "Callook")) { $callsignLookupSvc = "RadioID"; }

// Setup the URL(s)
$idLookupUrl = "https://database.radioid.net/database/view?id=";
if ($callsignLookupSvc == "RadioID") { $callsignLookupUrl = "https://database.radioid.net/database/view?callsign="; }
if ($callsignLookupSvc == "QRZ") { $callsignLookupUrl = "https://www.qrz.com/db/"; }
if ($callsignLookupSvc == "Callook") { $callsignLookupUrl = "https://callook.info/"; }
$_SESSION['CS_URL'] = $callsignLookupUrl;
}
$idLookupUrl = "https://database.radioid.net/database/view?id=";
$callsignLookupUrl = $_SESSION['CS_URL'];

if ( ! isset($_SESSION['LH_limits'])) {
  $lcount = exec ('sed -n "s%Depth=\([0-9]*\)%\1%p" /etc/pistar-css.ini');
  if ( $lcount < 20 )  { $lcount = 20; }
  if ( $lcount > 200 ) { $lcount = 200; }
  $_SESSION['LH_limits'] = $lcount;
}
$lcount = $_SESSION['LH_limits'];

?>
<b><?php echo $lang['last_heard_list'];?></b>
<?php
  $lhsw = $_SESSION['LHSW'];
  $lhsw = ( file_exists("/tmp/lhsw") ? 1 : $lhsw );
  $lhlh = ( $lhsw ? "Live Log" : "Last Heard" );
  $lhcount = count($lastHeard);
  $fsmode = exec ('sed -n "s/\/dev\/.* \/ ext4 \(r[ow]\).*/\1/p" /proc/mounts');
  $pubprv = exec ('sed -n "/\[DMR\]/,/^$/ {s%SelfOnly=\([0-1]\).*%\1%p}" /etc/mmdvmhost');
  $ovfl = exec ('grep -a -m 1 -ih -E "overflow in the (DMR|M17)" /var/log/pi-star/MMDVM*.log | sed "s/.* overflow .*/ov/g"');
  $sessions = exec ('df /var/lib/php/sessions | sed -n "s/.*\( [0-9]*% \).*/\1/p"');
?>
  <table>
    <tr>
      <form action="" method="post">
      <td align="left">
         <input type="submit" style="font-size: 10px; border-width:thin" value="LH / LL" name="LastHeardSW";/> <?php echo $lhlh; ?>
      </td>
      <td align="right" width="35"><?php echo $sessions;  echo "&nbsp ";?></td>
      <td align="right" width="25"><?php echo $ovfl;    echo "&nbsp ";?></td>
      <td align="right" width="25"><?php echo $fsmode;  echo "&nbsp ";?></td>
      <td align="right" width="25"><?php echo $pubprv;  echo "&nbsp ";?></td>
      <td align="right" width="35"><?php echo $lhcount; echo "&nbsp ";?></td>
      <td align="right" width="25"><?php echo $lcount;  echo "&nbsp ";?></td>
      <td align="right" width="25">
         <input type="submit" style="font-size: 10px; border-width:thin" value="CL" name="CutlineSW";/>
      </td>
      </form>
    </tr>
  </table>
  <table>
    <tr>
      <th><a class="tooltip" href="#"><?php echo $lang['time'];?> (<?php echo date('T')?>)<span><b>Time in <?php echo date('T')?> time zone</b></span></a></th>
      <th><a class="tooltip" href="#"><?php echo $lang['mode'];?><span><b>Transmitted Mode</b></span></a></th>
      <th style="min-width:22ch"><a class="tooltip" href="#"><?php echo $lang['callsign'];?><span><b>Callsign</b></span></a></th>
      <th><a class="tooltip" href="#"><?php echo $lang['target'];?><span><b>Target, D-Star Reflector, DMR Talk Group etc</b></span></a></th>
      <th><a class="tooltip" href="#"><?php echo $lang['src'];?><span><b>Received from source</b></span></a></th>
      <th><a class="tooltip" href="#"><?php echo $lang['dur'];?>(s)<span><b>Duration in Seconds</b></span></a></th>
      <th><a class="tooltip" href="#"><?php echo $lang['loss'];?><span><b>Packet Loss</b></span></a></th>
      <th><a class="tooltip" href="#"><?php echo $lang['ber'];?><span><b>Bit Error Rate</b></span></a></th>
    </tr>
<?php
$i = 0;
$prevElem = array();

  if (!empty($_POST) && isset($_POST["CutlineSW"])) {
     unset($_POST);
     system ('echo "M: $(date -u "+%Y-%m-%d %H:%M:%S.%N" | cut -c-23) DMR Slot 1, received network end of voice transmission from ******** to TG 00000, 0.0 seconds, 0% packet loss, BER: 0.0%" | sudo tee -a $(ls -tr /var/log/pi-star/MMDV* | tail -n 1) >/dev/null');
     echo '<script type="text/javascript">';
     echo '  setTimeout(function() { window.location=window.location;},500);';
     echo '</script>';
  }

  if (!empty($_POST) && isset($_POST["LastHeardSW"])) {
     $_SESSION['LHSW'] = 1 - $_SESSION['LHSW'];
     unset($_POST);
     echo '<script type="text/javascript">';
     echo '  setTimeout(function() { window.location=window.location;},500);';
     echo '</script>';
  }
  else {

for ($i = 0;  ($i < $lcount); $i++) { //Last 20 calls
	if (isset($lastHeard[$i])) {
		$listElem = $lastHeard[$i];
//		listElem[0] time, [1] mode, [2] callsign, [3] target, [4] source, [5] duration, [6] loss, [7] BER
		$currElem = array($listElem[1],$listElem[2],$listElem[3],$listElem[4],$listElem[5],$listElem[6]);
		if ( $listElem[2] && $currElem !== $prevElem ) {
			$prevElem = $currElem;
			$utc_time = $listElem[0];
                        $utc_tz =  new DateTimeZone('UTC');
                        $local_tz = new DateTimeZone(date_default_timezone_get ());
                        $dt = new DateTime($utc_time, $utc_tz);
                        $dt->setTimeZone($local_tz);
                        $local_time = $dt->format('H:i:s M jS');

		if ($listElem[2] == "********" ) {
			echo "<tr style=\"background: #f93;\">";
			echo "<td align=\"left\">$local_time</td>";
			echo "<td align=\"left\">-------</td><td align=\"left\">-------</td>";
			echo "<td align=\"left\">--------</td>";
			echo "<td>---</td><td>---</td><td>---</td><td>---</td>";
		} else {

		echo "<tr>";
		echo "<td align=\"left\">$local_time</td>";
		echo "<td align=\"left\">".str_replace('Slot ', 'TS', $listElem[1])."</td>";
		if (is_numeric($listElem[2]))   {
			$DMRinfo = getDMRinfo($listElem[2],$listElem[4]);
			if ($listElem[2] > 9999) { echo "<td align=\"left\"><a href=\"".$idLookupUrl.$listElem[2]."\" title=\"$DMRinfo\" target=\"_blank\">$listElem[2]</a></td>"; }
			else { echo "<td align=\"left\">".$listElem[2]."</td>"; }
		} elseif (!preg_match('/[A-Za-z].*[0-9]|[0-9].*[A-Za-z]/', $listElem[2])) {
                        echo "<td align=\"left\">$listElem[2]</td>";
		} else {
			if (strpos($listElem[2],"-") > 0) { $listElem[2] = substr($listElem[2], 0, strpos($listElem[2],"-"));}
			$DMRinfo = getDMRinfo($listElem[2],$listElem[4]);
			$fname = explode(' ',$DMRinfo);
			$fname[2] = isset($fname[2]) ? strtolower($fname[2]) : "";
			if ( $listElem[3] && $listElem[3] != '    ' ) {
				echo "<td align=\"left\"><div style=\"float:left;\"><a href=\"".$callsignLookupUrl.$listElem[2]."\" title=\"$DMRinfo\" target=\"_blank\">$listElem[2]</a>/$listElem[3]<a> [$fname[2]]</a></div><div style=\"text-align:right;\">&#40;<a href=\"https://aprs.fi/#!call=".$listElem[2]."\" target=\"_blank\">GPS</a>&#41;</div></td>";
			} else {
				echo "<td align=\"left\"><div style=\"float:left;\"><a href=\"".$callsignLookupUrl.$listElem[2]."\" title=\"$DMRinfo\" target=\"_blank\">$listElem[2] [$fname[2]]</a></div> <div style=\"text-align:right;\">&#40;<a href=\"https://aprs.fi/#!call=".$listElem[2]."\" target=\"_blank\">GPS</a>&#41;</div></td>";
			}
		}

		if (strlen($listElem[4]) == 1) { $listElem[4] = str_pad($listElem[4], 8, " ", STR_PAD_LEFT); }
		if ( substr($listElem[4], 0, 6) === 'CQCQCQ' ) {
			echo "<td align=\"left\">$listElem[4]</td>";
		} else {
			$tgName = getTGdesc($listElem[4]);
			echo "<td title=\"$tgName\" align=\"left\">".str_replace(" ","&nbsp;", $listElem[4])."</td>";
		}


		if ($listElem[5] == "RF"){
			echo "<td style=\"background:#1d1;\">RF</td>";
		}else{
			echo "<td>$listElem[5]</td>";
		}
		if ($listElem[6] == null) {
			// Live duration
			$utc_time = $listElem[0];
			$utc_tz =  new DateTimeZone('UTC');
			$now = new DateTime("now", $utc_tz);
			$dt = new DateTime($utc_time, $utc_tz);
			$duration = $now->getTimestamp() - $dt->getTimestamp();
			$duration_string = $duration<999 ? round($duration) . "+" : "&infin;";
			echo "<td colspan =\"3\" style=\"background:#f33;\">TX " . $duration_string . " sec</td>";
		} else if ($listElem[6] == "DMR Data") {
			echo "<td colspan =\"3\" style=\"background:#1d1;\">DMR Data</td>";
		} else if ($listElem[6] == "POCSAG Data") {
			echo "<td colspan =\"3\" style=\"background:#1d1;\">POCSAG Data</td>";
		} else {
			echo "<td>$listElem[6]</td>";

			// Colour the Loss Field
			if (floatval($listElem[7]) < 1) { echo "<td>$listElem[7]</td>"; }
			elseif (floatval($listElem[7]) == 1) { echo "<td style=\"background:#1d1;\">$listElem[7]</td>"; }
			elseif (floatval($listElem[7]) > 1 && floatval($listElem[7]) <= 3) { echo "<td style=\"background:#fa0;\">$listElem[7]</td>"; }
			else { echo "<td style=\"background:#f33;\">$listElem[7]</td>"; }

			// Colour the BER Field
			if (floatval($listElem[8]) == 0) { echo "<td>$listElem[8]</td>"; }
			elseif (floatval($listElem[8]) >= 0.0 && floatval($listElem[8]) <= 1.9) { echo "<td style=\"background:#1d1;\">$listElem[8]</td>"; }
			elseif (floatval($listElem[8]) >= 2.0 && floatval($listElem[8]) <= 4.9) { echo "<td style=\"background:#fa0;\">$listElem[8]</td>"; }
			else { echo "<td style=\"background:#f33;\">$listElem[8]</td>"; }
		}
		}
		echo "</tr>\n";
		}
	}
}

  }
?>
  </table>
