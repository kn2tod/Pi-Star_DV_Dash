<?php
// Load the language support
require_once('../config/language.php');
// Load the Pi-Star Release file
$pistarReleaseConfig = '/etc/pistar-release';
$configPistarRelease = array();
$configPistarRelease = parse_ini_file($pistarReleaseConfig, true);
// Load the Version Info
require_once('../config/version.php');

// Force the Locale to the stock locale just while we run the update
setlocale(LC_ALL, "LC_CTYPE=en_GB.UTF-8;LC_NUMERIC=C;LC_TIME=C;LC_COLLATE=C;LC_MONETARY=C;LC_MESSAGES=C;LC_PAPER=C;LC_NAME=C;LC_ADDRESS=C;LC_TELEPHONE=C;LC_MEASUREMENT=C;LC_IDENTIFICATION=C");

// Sanity Check that this file has been opened correctly
if ($_SERVER["PHP_SELF"] == "/admin/expert/nextion_fw_update.php") {

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (isset($_POST['nextion'])) {
	    $selectedOption = $_POST['nextion'];
	}
    }

    if (!isset($_GET['ajax'])) {
	system('sudo touch /var/log/pi-star/pi-star_nextion_update.log > /dev/null 2>&1 &');
	system('sudo echo "" > /var/log/pi-star/pi-star_nextion_update.log > /dev/null 2>&1 &');
	if (isset($selectedOption)) { system('sudo NP=1 /usr/local/sbin/pistar-nextionupdate ' . escapeshellarg($selectedOption) . ' > /dev/null 2>&1 &'); }
    }

    // passed sanity chk.
    header('Cache-Control: no-cache');
    session_start();

    if (!isset($_GET['ajax'])) {
	if (file_exists('/var/log/pi-star/pi-star_nextion_update.log')) {
	    $_SESSION['update_offset'] = filesize('/var/log/pi-star/pi-star_nextion_update.log');
	} else {
	    $_SESSION['update_offset'] = 0;
        }
    }

    if (isset($_GET['ajax'])) {
        if (!file_exists('/var/log/pi-star/pi-star_nextion_update.log')) {
            exit();
        }
        $handle = fopen('/var/log/pi-star/pi-star_nextion_update.log', 'rb');
        if (isset($_SESSION['update_offset'])) {
            fseek($handle, 0, SEEK_END);
            if ($_SESSION['update_offset'] > ftell($handle)) //log rotated/truncated
                $_SESSION['update_offset'] = 0; //continue at beginning of the new log
                $data = stream_get_contents($handle, -1, $_SESSION['update_offset']);
                $_SESSION['update_offset'] += strlen($data);
                echo nl2br($data);
            }
        else {
            fseek($handle, 0, SEEK_END);
            $_SESSION['update_offset'] = ftell($handle);
        } 
        exit();
    }
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
  <html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" lang="en">
  <head>
    <meta name="robots" content="index" />
    <meta name="robots" content="follow" />
    <meta name="language" content="English" />
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <meta name="Author" content="Andrew Taylor (MW0MWZ)" />
    <meta name="Description" content="Pi-Star Update" />
    <meta name="KeyWords" content="Pi-Star" />
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="pragma" content="no-cache" />
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon" />
    <meta http-equiv="Expires" content="0" />
    <title>Pi-Star - <?php echo $lang['digital_voice']." ".$lang['dashboard']." - Nextion FW ".$lang['update'];?></title>
    <link rel="stylesheet" type="text/css" href="../css/pistar-css.php" />
    <script type="text/javascript" src="/jquery.min.js"></script>
    <script type="text/javascript" src="/jquery-timing.min.js"></script>
    <script type="text/javascript">

    function disableSubmitButtons() {
            var inputs = document.getElementsByTagName('input');
            for (var i = 0; i < inputs.length; i++) {
                    if (inputs[i].type === 'button') {
                            inputs[i].disabled = true;
		            inputs[i].value = 'Please Wait...';
                    }
            }
    }

    function submitform() {
	disableSubmitButtons();
	document.getElementById("up_fw").submit();
    }

    $(function() {
      $.repeat(1000, function() {
        $.get('/admin/expert/nextion_fw_update.php?ajax', function(data) {
          if (data.length < 1) return;
          var objDiv = document.getElementById("tail");
          var isScrolledToBottom = objDiv.scrollHeight - objDiv.clientHeight <= objDiv.scrollTop + 1;
          $('#tail').append(data);
          if (isScrolledToBottom)
            objDiv.scrollTop = objDiv.scrollHeight;
        });
      });
    });
    </script>
  </head>

  <body>
  <div class="container">
  <?php include './header-menu.inc'; ?>
  <div class="contentwide">
  <table width="100%">
  <?php if (empty($_POST['nextion'])) { ?>
  <tr><th>Nextion Firmware Update Utility</th></tr>
  <tr><td>
	<br />
	<h2>Nextion Firmware Update Utility</h2>
	<p>This tool will attempt to update your Nextion screen from a selected list of firmware versions.<br />
	<p>When ready, select your Nextion screen choice from the list below and click, "Update Screen".<br />
	<p>Do not interrupt the process or navigate away from the page while the process is running.</p>
	<p><strong>Please understand what you are doing, as well as the risks associated with flashing your Nextion device.</strong></p>
	<p><em>(IMPORTANT: Please note, we are not firmware developers, and we offer no support for firmware.<br />
	We provide utilities to update the firmware. For firmware support, you will need to utilise other<br />
	support resources from the firmware developers/maintainers or the web.)</em></p>
  </td></tr>
  <tr><td>
<?php

    $friendlyNames = [
      'hs_hat' => 'MMDVM_HS_Hat (14.7456MHz TCXO) GPIO',
      //'mmdvm_pi-f7' => 'MMDVM Pi F7 Board 460800 baud (12.000MHz TCXO) GPIO',
      //'mmdvm_pi-f4' => 'MMDVM Pi F4 Board 460800 baud (12.000MHz TCXO) GPIO',
    ];

    $output = shell_exec('sudo /usr/local/sbin/pistar-nextionupdate');

    if ($output !== null) {
        // Split the output into an array of options
        $options = explode("\n", trim($output));

        // Create the select element
        echo '<p><form method="post" id="up_fw">';
        echo '<label for="nextion">Select Screen:</label>';
        echo '<select id="nextion" name="nextion">';
	echo '<option value="" disabled selected>Please choose screen file...</option>';
	// Output each option with user-friendly names
	foreach ($options as $option) {
	    $friendlyName = isset($friendlyNames[$option]) ? $friendlyNames[$option] : $option;
	    echo '<option value="' . htmlspecialchars($option) . '">' . htmlspecialchars($friendlyName) . '</option>';
 	}
        echo '</select>';
        echo '<input type="button" value="Update Screen" onclick="submitform()">';
        echo '</form></p>';
    } else {
        echo '<p>Error executing the command.</p>';
    }

?>
  </form>
  </td></tr>
  </table>
  </div>
  <div class="footer">
  Pi-Star / Pi-Star Dashboard, &copy; Andy Taylor (MW0MWZ) 2014-<?php echo date("Y"); ?>.<br />
  Nextion Firmware Update Tool &copy; Mark Prichard (KN2TOD) 2024-<?php echo date("Y");?><br />
  Need help? Click <a style="color: #ffffff;" href="https://www.facebook.com/groups/pistarusergroup/" target="_new">here for the Support Group</a><br />
  Get your copy of Pi-Star from <a style="color: #ffffff;" href="http://www.pistar.uk/downloads/" target="_blank">here</a>.<br />
  </div>
  </div>
  </body>
  </html>
  <?php } else { ?>

  <tr><th>Nextion Firmware Update Output</th></tr>
  <tr><td align="left"><div id="tail">Starting FW Update, please wait...<br /></div></td></tr>
  </table>
  </div>
  <div class="footer">
  Pi-Star / Pi-Star Dashboard, &copy; Andy Taylor (MW0MWZ) 2014-<?php echo date("Y"); ?>.<br />
  Need help? Click <a style="color: #ffffff;" href="https://www.facebook.com/groups/pistarusergroup/" target="_new">here for the Support Group</a><br />
  Get your copy of Pi-Star from <a style="color: #ffffff;" href="http://www.pistar.uk/downloads/" target="_blank">here</a>.<br />
  </div>
  </div>
  </body>
  </html>
<?php }
}
?>

