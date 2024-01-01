<?php @session_start();
if (! isset($_SESSION['Platform'])) { $_SESSION['Platform'] = exec('/usr/local/bin/platformDetect.sh'); }
$platform = $_SESSION['Platform'];
if (! isset($_SESSION['Debian'])) { $_SESSION['Debian'] = exec('echo $(cat /etc/os-release | sed -n "s/VERSION_CODENAME=\(.*\)/(\u\1)/p")'); }
$debian = $_SESSION['Debian'];
// Load the language support
require_once('config/language.php');
require_once('config/ircddblocal.php');
$MYCALL=strtoupper($callsign);
$MYHOST=php_uname('n');
// Load the Pi-Star Release file
$pistarReleaseConfig = '/etc/pistar-release';
$configPistarRelease = array();
$configPistarRelease = parse_ini_file($pistarReleaseConfig, true);
// Load the Version Info
require_once('config/version.php');

// Retrieve server information
$system = system_information();

function system_information() {
    @list($system, $host, $kernel) = preg_split('/[\s,]+/', php_uname('a'), 5);
    $meminfo = false;
    if (@is_readable('/proc/meminfo')) {
        $data = explode("\n", file_get_contents("/proc/meminfo"));
        $meminfo = array();
        foreach ($data as $line) {
            if (strpos($line, ':') !== false) {
                list($key, $val) = explode(":", $line);
                $meminfo[$key] = 1024 * floatval( trim( str_replace( ' kB', '', $val ) ) );
            }
        }
    }
    return array('date' => date('Y-m-d H:i:s T'),
                 'mem_info' => $meminfo,
                 'partitions' => disk_list()
                 );
}

function disk_list() {
    $partitions = array();
    // Fetch partition information from df command
    // I would have used disk_free_space() and disk_total_space() here but
    // there appears to be no way to get a list of partitions in PHP?
    $output = array();
    @exec('df --block-size=1', $output);
    foreach($output as $line) {
        $columns = array();
        foreach(explode(' ', $line) as $column) {
            $column = trim($column);
            if($column != '') $columns[] = $column;
        }

        // Only process 6 column rows
        // (This has the bonus of ignoring the first row which is 7)
        if(count($columns) == 6) {
            $partition = $columns[5];
            $partitions[$partition]['Temporary']['bool'] = in_array($columns[0], array('tmpfs', 'devtmpfs'));
            $partitions[$partition]['Partition']['text'] = $partition;
            $partitions[$partition]['FileSystem']['text'] = $columns[0];
            if(is_numeric($columns[1]) && is_numeric($columns[2]) && is_numeric($columns[3])) {
                $partitions[$partition]['Size']['value'] = $columns[1];
                $partitions[$partition]['Free']['value'] = $columns[3];
                $partitions[$partition]['Used']['value'] = $columns[2];
            }
            else {
                // Fallback if we don't get numerical values
                $partitions[$partition]['Size']['text'] = $columns[1];
                $partitions[$partition]['Used']['text'] = $columns[2];
                $partitions[$partition]['Free']['text'] = $columns[3];
            }
        }
    }
    return $partitions;
}

function formatSize( $bytes ) {
    $types = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB' );
    for( $i = 0; $bytes >= 1024 && $i < ( count( $types ) -1 ); $bytes /= 1024, $i++ );
    return( round( $bytes, 2 ) . " " . $types[$i] );
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
    <meta name="Description" content="Pi-Star SysInfo" />
    <meta name="KeyWords" content="Pi-Star" />
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="pragma" content="no-cache" />
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon" />
    <meta http-equiv="Expires" content="0" />
    <title><?php echo "$MYCALL ($MYHOST) - ".$lang['digital_voice']." ".$lang['dashboard'];?></title>
    <link rel="stylesheet" type="text/css" href="css/pistar-css.php" />
    <script type="text/javascript" src="/jquery.min.js"></script>
    <script type="text/javascript" src="/jquery-timing.min.js"></script>
    <style>
    .progress .bar + .bar {
      -webkit-box-shadow: inset 1px 0 0 rgba(0, 0, 0, 0.15), inset 0 -1px 0 rgba(0, 0, 0, 0.15);
      -moz-box-shadow: inset 1px 0 0 rgba(0, 0, 0, 0.15), inset 0 -1px 0 rgba(0, 0, 0, 0.15);
      box-shadow: inset 1px 0 0 rgba(0, 0, 0, 0.15), inset 0 -1px 0 rgba(0, 0, 0, 0.15);
    }
    .progress-info .bar, .progress .bar-info {
      background-color: #4bb1cf;
      background-image: -moz-linear-gradient(top, #5bc0de, #339bb9);
      background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#5bc0de), to(#339bb9));
      background-image: -webkit-linear-gradient(top, #5bc0de, #339bb9);
      background-image: -o-linear-gradient(top, #5bc0de, #339bb9);
      background-image: linear-gradient(to bottom, #5bc0de, #339bb9);
      background-repeat: repeat-x;
      filter: progid: DXImageTransform.Microsoft.gradient(startColorstr='#ff5bc0de', endColorstr='#ff339bb9', GradientType=0);
    }
    </style>
  </head>
  <body>
  <div class="container">
  <div class="header">
  <div style="font-size: 8px; text-align: left; padding-left: 8px; float: left;">Hostname: <?php echo php_uname('n'); ?></div><div  style="font-size: 8px; text-align: right; padding-right: 8px;">Pi-Star:<?php echo $configPistarRelease['Pi-Star']['Version']?> / Dashboard:<?php echo $version; ?></div>
  <h1>Pi-Star <?php echo $lang['digital_voice']." ".$lang['dashboard']." - SysInfo";?></h1>
  <p style="padding-right: 5px; text-align: right; color: #ffffff;">
    <a href="/" style="color: #ffffff;"><?php echo $lang['dashboard'];?></a> |
    <a href="/admin/" style="color: #ffffff;"><?php echo $lang['admin'];?></a> |
    <a href="/admin/power.php" style="color: #ffffff;"><?php echo $lang['power'];?></a> |
    <a href="/admin/config_backup.php" style="color: #ffffff;"><?php echo $lang['backup_restore'];?></a> |
    <a href="/admin/configure.php" style="color: #ffffff;"><?php echo $lang['configuration'];?></a>
  </p>
  </div>
  <div class="contentwide">
  <table width="100%" border="0">
  <tr><th colspan="2">Pi-Star System Information</th></tr>
<?php
// Platform information
echo "  <tbody>\n";
echo "  <tr><th><b>System</b></th><th><b>Version</b></th></tr>\n";
$uname=php_uname('r')." ".php_uname('v')." ".$debian;
echo "  <tr><td align=\"left\">$platform</td><td align=\"left\">$uname</td></tr>\n";
$rel = $configPistarRelease['Pi-Star']['Version'].":";
$ver = substr($version,4,2)."/".substr($version,6,2)."/".substr($version,2,2);
echo "  <tr><td align=\"left\">$MYHOST</td><td align=\"left\">$rel $ver</td></tr>\n";
$python=exec('python -V 3>&1 1>&2 2>&3 3>&1 1>&2');
echo "  <tr><td align=\"left\">Python</td><td align=\"left\">$python</td></tr>\n";
$nginx=exec('nginx -v 3>&1 1>&2 2>&3 | sed "s/nginx version: \(.*\)/\u\1/g"');
echo "  <tr><td align=\"left\">Nginx</td><td align=\"left\">$nginx</td></tr>\n";
$php=exec('php -v | sed -n "s/^\(PHP .* \)(c.*/\1/p"');
echo "  <tr><td align=\"left\">PHP</td><td align=\"left\">$php</td></tr>\n";
$git=exec('git --version | sed "s/git version/Git/g"');
echo "  <tr><td align=\"left\">GIT</td><td align=\"left\">$git</td></tr>\n";
if (is_executable('/usr/sbin/cupsd')) {
  $cups=exec("sudo dpkg -l cups 2>/dev/null | tail -n 1 | awk '{print \$3}'");
  echo "  <tr><td align=\"left\">CUPS</td><td align=\"left\">$cups</td></tr>\n";
}
echo "  </tbody>\n";
// Ram information
if ($system['mem_info']) {
    echo "  <tbody>\n";
    echo "  <tr><th><b>Memory</b></th><th><b>Stats</b></th></tr>\n";
    $sysRamUsed = $system['mem_info']['MemTotal'] - $system['mem_info']['MemFree'] - $system['mem_info']['Buffers'] - $system['mem_info']['Cached'];
    $sysRamPercent = sprintf('%.2f',($sysRamUsed / $system['mem_info']['MemTotal']) * 100);
    echo "  <tr><td align=\"left\">RAM</td><td align=\"left\"><div class='progress progress-info' style='margin-bottom: 0;'><div class='bar' style='width: ".$sysRamPercent."%;'>Used&nbsp;".$sysRamPercent."%</div></div>";
    echo "  <b>Total:</b> ".formatSize($system['mem_info']['MemTotal'])."<b> - Used:</b> ".formatSize($sysRamUsed)."<b> - Free:</b> ".formatSize($system['mem_info']['MemTotal'] - $sysRamUsed)."</td></tr>\n";
    echo "  </tbody>\n";
}
// Connection information
echo "  <tbody>\n";
echo "  <tr><th><b>Connection</b></th><th><b>Addresses</b></th></tr>\n";
$conn = exec('echo $(route | grep default | awk \'{ print $8 }\') $(sudo wpa_cli -i wlan0 list_networks 2>/dev/null | sed -n \'s/\[CURRENT\]//p\' | awk \'{ print " (" $2 ")"}\')');
$ipaddrs = exec('echo "$(hostname -I) ($(route | grep default | awk \'{ print $2 }\')) --> $(dig +short myip.opendns.com @resolver1.opendns.com)"');
echo "  <tr><td align=\"left\">$conn</td><td align=\"left\">$ipaddrs</td></tr>\n";
echo "  </tbody>\n";
// Filesystem Information
if (count($system['partitions']) > 0) {
    echo "  <tbody>\n";
    echo "  <tr><th><b>Filesystem</b></th><th><b>Stats</b></th></tr>\n";
    foreach($system['partitions'] as $fs) {
        if ($fs['Used']['value'] > 0 && $fs['FileSystem']['text']!= "none" && $fs['FileSystem']['text']!= "udev") {
            $diskFree = $fs['Free']['value'];
            $diskTotal = $fs['Size']['value'];
            $diskUsed = $fs['Used']['value'];
            $diskPercent = sprintf('%.2f',($diskUsed / $diskTotal) * 100);

            echo "  <tr><td align=\"left\">".$fs['Partition']['text']."</td><td align=\"left\"><div class='progress progress-info' style='margin-bottom: 0;'><div class='bar' style='width: ".$diskPercent."%;'>Used&nbsp;".$diskPercent."%</div></div>";
            echo "  <b>Total:</b> ".formatSize($diskTotal)."<b> - Used:</b> ".formatSize($diskUsed)."<b> - Free:</b> ".formatSize($diskFree)."</td></tr>\n";
        }
    }
    echo "  </tbody>\n";
}
// Binary Information
echo "  <tbody>\n";
echo "  <tr><th><b>Modules</b></th><th><b>Version</b></th></tr>\n";
if (is_executable('/usr/local/bin/MMDVMHost')) {
    $MMDVMHost_Ver = exec('/usr/local/bin/MMDVMHost -v | cut -d\' \' -f 3-');
    echo "  <tr><td align=\"left\">MMDVMHost</td><td align=\"left\">".$MMDVMHost_Ver."</td></tr>\n";
}
if (is_executable('/usr/local/bin/DMRGateway')) {
    $DMRGateway_Ver = exec('/usr/local/bin/DMRGateway -v | cut -d\' \' -f 3-');
    echo "  <tr><td align=\"left\">DMRGateway</td><td align=\"left\">".$DMRGateway_Ver."</td></tr>\n";
}
if (is_executable('/usr/local/bin/DMR2YSF')) {
    $DMR2YSF_Ver = exec('/usr/local/bin/DMR2YSF -v | cut -d\' \' -f 3-');
    echo "  <tr><td align=\"left\">DMR2YSF</td><td align=\"left\">".$DMR2YSF_Ver."</td></tr>\n";
}
if (is_executable('/usr/local/bin/DMR2NXDN')) {
    $DMR2NXDN_Ver = exec('/usr/local/bin/DMR2NXDN -v | cut -d\' \' -f 3-');
    echo "  <tr><td align=\"left\">DMR2NXDN</td><td align=\"left\">".$DMR2NXDN_Ver."</td></tr>\n";
}
if (is_executable('/usr/local/bin/YSFGateway')) {
    $YSFGateway_Ver = exec('/usr/local/bin/YSFGateway -v | cut -d\' \' -f 3-');
    echo "  <tr><td align=\"left\">YSFGateway</td><td align=\"left\">".$YSFGateway_Ver."</td></tr>\n";
}
if (is_executable('/usr/local/bin/YSF2DMR')) {
    $YSF2DMR_Ver = exec('/usr/local/bin/YSF2DMR -v | cut -d\' \' -f 3-');
    echo "  <tr><td align=\"left\">YSF2DMR</td><td align=\"left\">".$YSF2DMR_Ver."</td></tr>\n";
}
if (is_executable('/usr/local/bin/YSF2P25')) {
    $YSF2P25_Ver = exec('/usr/local/bin/YSF2P25 -v | cut -d\' \' -f 3-');
    echo "  <tr><td align=\"left\">YSF2P25</td><td align=\"left\">".$YSF2P25_Ver."</td></tr>\n";
}
if (is_executable('/usr/local/bin/YSF2NXDN')) {
    $YSF2NXDN_Ver = exec('/usr/local/bin/YSF2NXDN -v | cut -d\' \' -f 3-');
    echo "  <tr><td align=\"left\">YSF2NXDN</td><td align=\"left\">".$YSF2NXDN_Ver."</td></tr>\n";
}
if (is_executable('/usr/local/bin/P25Gateway')) {
    $P25Gateway_Ver = exec('/usr/local/bin/P25Gateway -v | cut -d\' \' -f 3-');
    echo "  <tr><td align=\"left\">P25Gateway</td><td align=\"left\">".$P25Gateway_Ver."</td></tr>\n";
}
if (is_executable('/usr/local/bin/NXDNGateway')) {
    $NXDNGateway_Ver = exec('/usr/local/bin/NXDNGateway -v | cut -d\' \' -f 3-');
    echo "  <tr><td align=\"left\">NXDNGateway</td><td align=\"left\">".$NXDNGateway_Ver."</td></tr>\n";
}
if (is_executable('/usr/local/bin/NXDN2DMR')) {
    $NXDN2DMR_Ver = exec('/usr/local/bin/NXDN2DMR -v | cut -d\' \' -f 3-');
    echo "  <tr><td align=\"left\">NXDN2DMR</td><td align=\"left\">".$NXDN2DMR_Ver."</td></tr>\n";
}
if (is_executable('/usr/local/bin/DAPNETGateway')) {
    $DAPNETGateway_Ver = exec('/usr/local/bin/DAPNETGateway -v | cut -d\' \' -f 3-');
    echo "  <tr><td align=\"left\">DAPNETGateway</td><td align=\"left\">".$DAPNETGateway_Ver."</td></tr>\n";
}
if (is_executable('/usr/local/bin/APRSGateway')) {
    $APRSGATEWAY_Ver = exec('/usr/local/bin/APRSGateway -v | cut -d\' \' -f 3-');
    echo "  <tr><td align=\"left\">APRSGateway</td><td align=\"left\">".$APRSGATEWAY_Ver."</td></tr>\n";
}
if (is_executable('/usr/local/bin/M17Gateway')) {
    $M17Gateway = exec('/usr/local/bin/M17Gateway -v | cut -d\' \' -f 3-');
    echo "  <tr><td align=\"left\">M17Gateway</td><td align=\"left\">".$M17Gateway."</td></tr>\n";
}
if (is_executable('/usr/local/bin/DGIdGateway')) {
    $DGIdGateway = exec('/usr/local/bin/DGIdGateway -v | cut -d\' \' -f 3-');
    echo "  <tr><td align=\"left\">DGIdGateway</td><td align=\"left\">".$DGIdGateway."</td></tr>\n";
}
if (is_executable('/usr/sbin/gpsd')) {
    $GPSD = exec('/usr/sbin/gpsd -V | cut -d\':\' -f 2');
    echo "  <tr><td align=\"left\">GPSD</td><td align=\"left\">".$GPSD."</td></tr>\n";
}
if (is_executable('/usr/local/bin/NextionDriver')) {
    $NEXTIONDRIVER_Ver = exec('/usr/local/bin/NextionDriver -V | head -n 2 | cut -d\' \' -f 3-');
    echo "  <tr><td align=\"left\">NextionDriver</td><td align=\"left\">".$NEXTIONDRIVER_Ver."</td></tr>\n";
}
$Firmware = isset($_SESSION['Firmware']) ? $_SESSION['Firmware'] : "";
if (isset($Firmware)) {
    $hat = exec('sed -n "s/Hardware=\(.*\)/\1/p" /etc/dstar-radio.mmdvmhost');
    $hatx = exec('grep -sh "MMDVM protocol version" /var/log/pi-star/MMDVM* | tail -n 1');
    $TCXOFreq = exec('echo "'.$hatx.'" | sed -n "s/.*\( [0-9]\{2\}\.[0-9]\{3,4\}\)M[Hh]z .*/\1 MHz/p"');
    echo "  <tr><td align=\"left\">".$hat."</td><td align=\"left\">".$Firmware." - ".$TCXOFreq."</td></tr>\n";
}
echo "  </tbody>\n";
?>
  </table>
  </div>
  <div class="footer">
  Pi-Star web config, &copy; Andy Taylor (MW0MWZ) 2014-<?php echo date("Y"); ?>.<br />
  Need help? Click <a style="color: #ffffff;" href="https://www.facebook.com/groups/pistarusergroup/" target="_new">here for the Support Group</a><br />
  Get your copy of Pi-Star from <a style="color: #ffffff;" href="http://www.pistar.uk/downloads/" target="_blank">here</a>.<br />
  <br />
  </div>
  </div>
  </body>
  </html>
