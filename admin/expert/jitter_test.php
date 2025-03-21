<?php
// Load the language support
require_once('../config/language.php');
// Load the Pi-Star Release file
$pistarReleaseConfig = '/etc/pistar-release';
$configPistarRelease = array();
$configPistarRelease = parse_ini_file($pistarReleaseConfig, true);
// Load the Version Info
require_once('../config/version.php');

// Sanity Check that this file has been opened correctly
if ($_SERVER["PHP_SELF"] == "/admin/expert/jitter_test.php") {

  if (! empty($_POST['hostgroup'] )) {
    $target = $_POST['hostgroup'];
    unset ($_POST['hostgroup']);
  } else {
  if (isset($_GET['group'])) {
    $target = strtoupper($_GET['group']);
    if ($target == "BRANDMEISTER") { $target = "BM"; }
    if ($target == "DMRPLUS")      { $target = "DMR+"; }
    if ($target == "HBLINK")       { $target = "HB"; }
    if ($target == "FREEDMR")      { $target = "FreeDMR"; }
    if ($target == "FREESTAR")     { $target = "FreeSTAR"; }
  } else {
    $target = "BM";
  }
  }

  // Sanity Check Passed.
  header('Cache-Control: no-cache');
  session_start();

  if (!isset($_GET['ajax'])) {
    system('sudo touch /tmp/jittertest.log > /dev/null 2>&1 &');
    system('sudo truncate -s 0 /tmp/jittertest.log > /dev/null 2>&1 &');
    system('sudo /usr/local/sbin/pistar-jittertest '.$target.' > /dev/null 2>&1 &');
    $_SESSION['update_offset'] = @filesize('/tmp/jittertest.log');
    }

  else {
    if (file_exists('/tmp/jittertest.log')) {

      $handle = fopen('/tmp/jittertest.log', 'rb');
      if (isset($_SESSION['update_offset'])) {
        fseek($handle, 0, SEEK_END);
        if ($_SESSION['update_offset'] > ftell($handle))    //log rotated/truncated
          $_SESSION['update_offset'] = 0;                   //continue at beginning of the new log
          $data = stream_get_contents($handle, -1, $_SESSION['update_offset']);
          $_SESSION['update_offset'] += strlen($data);
          echo "<pre>$data</pre>";
        }
      else {
        fseek($handle, 0, SEEK_END);
        $_SESSION['update_offset'] = ftell($handle);
        }
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
    <link rel="shortcut icon" href="../images/favicon.ico" type="image/x-icon" />
    <meta http-equiv="Expires" content="0" />
    <title>Pi-Star - <?php echo $lang['digital_voice']." ".$lang['dashboard']." - ".$lang['update'];?></title>
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
        $.get('/admin/expert/jitter_test.php?ajax', function(data) {
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
  <tr><th>Jitter Tests</th></tr>
  <tr><td>
  <?php
    $output = shell_exec('sed -n "s/\(^[A-Za-z+]*\)_.*/\1/p" /usr/local/etc/DMR_Hosts.txt | sort | uniq');
    $output = $output."M17\nNXDN\nP25\nYSF";   // + some alternate lists/links for jitter tests

    if ($output !== null) {
        // Split the output into an array of options
        $options = explode("\n", trim($output));

        // Create the select element
        echo '<p><form method="post" id="up_fw">';
        echo '<label for="hostgroup">Select host group: </label>';
        echo '<select id="hostgroup" name="hostgroup">';
        echo '<option value="" disabled selected>select group...</option>';
        // Output each option with user-friendly names
        foreach ($options as $option) {
            $select = ($option == $target ? "selected=selected" : "");
            echo '<option value="' .$option.'"' .$select. '>' .$option. '</option>';
        }
        echo '</select>';
        echo '    ';
        echo '<input type="button" value="test" onclick="submitform()">';
        echo '</form></p>';
    } else {
        echo '<p>Error executing the command.</p>';
    }
  ?>
  </form>
  </td></tr>

  <div class="contentwide">
  <table width="100%" style="font-size:88%">
  <tr><th>Results</th></tr>
  <tr><td align="left"><div id="tail" style="width:auto">Starting jitter tests, please wait...<br /></div></td></tr>
  </table>
  </div>

  <div class="footer">
  Pi-Star web config, &copy; Andy Taylor (MW0MWZ) 2014-<?php echo date("Y"); ?>.<br />
  Further enhancements by Mark Prichard (KN2TOD), <br />
  Need help? Click <a style="color: #ffffff;" href="https://www.facebook.com/groups/pistarusergroup/" target="_new">here for the Support Group</a><br />
  Get your copy of Pi-Star from <a style="color: #ffffff;" href="http://www.pistar.uk/downloads/" target="_blank">here</a>.<br />
  <br />
  </div>
  </div>
  </body>
  </html>

<?php
}
?>
