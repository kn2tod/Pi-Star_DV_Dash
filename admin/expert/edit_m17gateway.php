<?php
// Load the language support
require_once('../config/language.php');
//Load the Pi-Star Release file
$pistarReleaseConfig = '/etc/pistar-release';
$configPistarRelease = array();
$configPistarRelease = parse_ini_file($pistarReleaseConfig, true);
//Load the Version Info
require_once('../config/version.php');
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
    <meta name="Description" content="Pi-Star Expert Editor" />
    <meta name="KeyWords" content="Pi-Star" />
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="pragma" content="no-cache" />
    <link rel="shortcut icon" href="../images/favicon.ico" type="image/x-icon" />
    <meta http-equiv="Expires" content="0" />
    <title>Pi-Star - Digital Voice Dashboard - Expert Editor</title>
    <link rel="stylesheet" type="text/css" href="../css/pistar-css.php" />
  </head>
  <body>
  <div class="container">
  <?php include './header-menu.inc'; ?>
  <div class="contentwide">

<?php
//Do some file wrangling...
exec('sudo cp /etc/m17gateway /tmp/0zHu4WjJyeT4lJy.tmp');
exec('sudo chown www-data:www-data /tmp/0zHu4WjJyeT4lJy.tmp');
exec('sudo chmod 664 /tmp/0zHu4WjJyeT4lJy.tmp');

//ini file to open
$filepath = '/tmp/0zHu4WjJyeT4lJy.tmp';

//after the form submit
if($_POST) {
	$data = $_POST;
	//update ini file, call function
	update_ini_file($data, $filepath);
	}

//this is the function going to update your ini file
	function update_ini_file($data, $filepath) {
		$content = "";

		//parse the ini file to get the sections
		//parse the ini file using default parse_ini_file() PHP function
		$parsed_ini = parse_ini_file($filepath, true);

		foreach($data as $section=>$values) {
			// UnBreak special cases
			$section = str_replace("_", " ", $section);
			$content .= "[".$section."]\n";
			//append the values
			foreach($values as $key=>$value) {
				if ($section == "DMR Network" && $key == "Password" && $value) {
					$value = str_replace('"', "", $value);
					$content .= $key."=\"".$value."\"\n";
				}
				elseif ($section == "DMR Network" && $key == "Options" && $value) {
					$value = str_replace('"', "", $value);
					$content .= $key."=\"".$value."\"\n";
				}
				elseif ($section == "DMR Network" && $key == "Options" && !$value) {
					$content .= $key."= \n";
				}
				elseif ($value == '') { 
                                        $content .= $key."=none\n";
                                        }
				else {
					$content .= $key."=".$value."\n";
				}
			}
			$content .= "\n";
		}

		//write it into file
		if (!$handle = fopen($filepath, 'w')) {
			return false;
		}

		$success = fwrite($handle, $content);
		fclose($handle);

		// Updates complete - copy the working file back to the proper location
		exec('sudo mount -o remount,rw /');				// Make rootfs writable
		exec('sudo cp /tmp/0zHu4WjJyeT4lJy.tmp /etc/m17gateway');	// Move the file back
		exec('sudo chmod 644 /etc/m17gateway');				// Set the correct runtime permissions
		exec('sudo chown root:root /etc/m17gateway');			// Set the owner
		exec('sudo mount -o remount,ro /');				// Make rootfs read-only

		// Reload the affected daemon
		exec('sudo systemctl restart m17gateway.service');		// Reload the daemon
		return $success;
	}

//parse the ini file using default parse_ini_file() PHP function
$parsed_ini = parse_ini_file($filepath, true);

echo '<form action="" method="post">'."\n";
	foreach($parsed_ini as $section=>$values) {
		// keep the section as hidden text so we can update once the form submitted
		echo "<input type=\"hidden\" value=\"$section\" name=\"$section\" />\n";
		echo "<table>\n";
		echo "<tr><th colspan=\"2\">$section</th></tr>\n";
		// print all other values as input fields, so can edit. 
		// note the name='' attribute it has both section and key
		foreach($values as $key=>$value) {
			if (($key == "Options") || ($value)) {
				echo "<tr><td align=\"right\" width=\"30%\">$key</td><td align=\"left\"><input type=\"text\" size=\"30\" name=\"{$section}[$key]\" value=\"$value\" /></td></tr>\n";
			}
			elseif (($key == "Display") && ($value == '')) {
				echo "<tr><td align=\"right\" width=\"30%\">$key</td><td align=\"left\"><input type=\"text\" size=\"30\" name=\"{$section}[$key]\" value=\"None\" /></td></tr>\n";
			}
			else {
				echo "<tr><td align=\"right\" width=\"30%\">$key</td><td align=\"left\"><input type=\"text\" size=\"30\" name=\"{$section}[$key]\" value=\"0\" /></td></tr>\n";			
			}
		}
		echo "</table>\n";
		echo '<input type="submit" value="'.$lang['apply'].'" />'."\n";
		echo "<br />\n";
	}
echo "</form>";
?>
</div>

<div class="footer">
Pi-Star / Pi-Star Dashboard, &copy; Andy Taylor (MW0MWZ) 2014-<?php echo date("Y"); ?>.<br />
Need help? Click <a style="color: #ffffff;" href="https://www.facebook.com/groups/pistarusergroup/" target="_new">here for the Support Group</a><br />
Get your copy of Pi-Star from <a style="color: #ffffff;" href="http://www.pistar.uk/downloads/" target="_new">here</a>.<br />
</div>

</div>
</body>
</html>
