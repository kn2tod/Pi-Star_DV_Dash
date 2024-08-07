<?php
// Load the language support
require_once('../config/version.php');
require_once('../config/language.php');
require_once('../config/ircddblocal.php');
$progname = basename($_SERVER['SCRIPT_FILENAME'],".php");
$rev=$version;
$MYCALL=strtoupper($callsign);
$MYHOST=php_uname('n');
$Debian=exec('sed -n "s/VERSION_CODENAME=\(.*\)/\u\1/p" /etc/os-release');
$Linux=php_uname('s')." ".php_uname('r')." ".php_uname('v')." ".php_uname('m');
$Hardware=exec('sed -n "s|^Model.*: Raspberry \(.*\)|\1|p" /proc/cpuinfo');

//Load the Pi-Star Release file
$pistarReleaseConfig = '/etc/pistar-release';
$configPistarRelease = array();
$configPistarRelease = parse_ini_file($pistarReleaseConfig, true);
$ver=$configPistarRelease['Pi-Star']['Version'];
//Load the Version Info
?>
  <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
  <html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" lang="en">
  <head>
    <meta name="robots" content="index" />
    <meta name="robots" content="follow" />
    <meta name="language" content="English" />
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <?php echo "<meta name=\"generator\" content=\"$progname $rev\" />\n"; ?>
    <?php echo "<meta name=\"system\" content=\"$Debian $Linux\" />\n"; ?>
    <?php echo "<meta name=\"platform\" content=\"$Hardware\" />\n"; ?>
    <?php echo "<meta name=\"version\" content=\"Pi-Star: $ver - $rev\" />\n"; ?>
    <meta name="Author" content="Andy Taylor (MW0MWZ), Mark Prichard (KN2TOD)" />
    <meta name="Description" content="Pi-Star Expert Editor" />
    <meta name="KeyWords" content="Pi-Star" />
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="pragma" content="no-cache" />
    <link rel="shortcut icon" href="../images/favicon.ico" type="image/x-icon" />
    <meta http-equiv="Expires" content="0" />
    <title><?php echo "$MYCALL ($MYHOST) - ".$lang['digital_voice']." ".$lang['dashboard']?> - Expert Editor</title>
    <link rel="stylesheet" type="text/css" href="../css/pistar-css.php" />
  </head>
  <body>
  <div class="container">
  <?php include './header-menu.inc'; ?>
  <div class="contentwide">

  <table width="100%">
    <tr><th>Expert Editors</th></tr>
    <tr><td align="center">
      <h2>**WARNING**</h2>
      Pi-Star Expert editors have been created to make editing some of the extra settings in the<br />
      config files more simple, allowing you to update some areas of the config files without the<br />
      need to login to your Pi over SSH.<br />
      <br />
      Please keep in mind when making your edits here, that these config files can be updated by<br />
      the dashboard, and that your edits can be over-written. It is assumed that you already know<br />
      what you are doing editing the files by hand, and that you understand what parts of the files<br />
      are maintained by the dashboard.<br />
      <br />
      With that warning in mind, you are free to make any changes you like, for help come to the Facebook<br />
      group (link at the bottom of the page) and ask for help if / when you need it.<br />
      73 and enjoy your Pi-Star experiance.<br />
      Pi-Star UK Team.<br />
      <br />
    </td></tr>
  </table>
  </div>

<div class="footer">
Pi-Star / Pi-Star Dashboard, &copy; Andy Taylor (MW0MWZ) 2014-<?php echo date("Y"); ?>.<br />
Further enhancements by Mark Prichard (KN2TOD), <br />
Need help? Click <a style="color: #ffffff;" href="https://www.facebook.com/groups/pistarusergroup/" target="_new">here for the Support Group</a><br />
Get your copy of Pi-Star from <a style="color: #ffffff;" href="http://www.pistar.uk/downloads/" target="_new">here</a>.<br />
</div>

</div>
</body>
</html>
