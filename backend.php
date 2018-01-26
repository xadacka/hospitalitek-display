<?php
// Lazy loading auto-inclusions.
function __autoload($className)
    {
    @include "Server/".$className.".class.php";
    @include $className;
    }

$target = strip_tags(trim($_GET['target']));

// **************************************************************************************************************************

// User inserted password (cleartext).
$unlockPwd = strip_tags(trim($_GET['unlockPwd']));

// Saved password.
if (file_exists("/iwk/iwk.adminPasswd")) // it always exists.
    {
    // Read saved password (hashed).
    $unlockPwdHashSaved = trim(file_get_contents("/iwk/iwk.adminPasswd"));
    }

// Enable modify if inserted password (hashed) matches saved password or saved password = "no-passwd" (disabled).
if (trim(md5($unlockPwd))==$unlockPwdHashSaved || trim(md5("no-passwd"))==$unlockPwdHashSaved) $MODIFY_ENABLED = true;
else $MODIFY_ENABLED = false;

// **************************************************************************************************************************

switch ($target)
    {
    case "modifyPasword":
        if ($MODIFY_ENABLED)
            {
            $newPassword = strip_tags(trim($_GET['newPassword']));

            if ($newPassword)
                {
                if (Misc::modifyPassword($newPassword)) echo "Password changed.";
                else echo "Error changing password.";
                }
            else echo "Error changing password.";
            }
        break;


    case "locale":
        if ($MODIFY_ENABLED)
            {
            $language = strip_tags(trim($_GET['lang']));
            if (!Locale::setLocale($language))
                {
                echo "Error setting language.";
                }
            }
        break;


    case "network":
        $action = strip_tags(trim($_GET['action']));

        if ($action=="wifi-list")
            {
            $json = new Services_JSON();
            $jsonString = "{identifier:'bssid',label:'bssid',items:[ ";

            $wiFiArray = Network::wifiNetworksList();
            while (list(,$v) = @each($wiFiArray)) $jsonString .= $json->encode($v).",";

            header("Content-Type: text/html; charset=UTF-8");
            echo substr($jsonString,0,strlen($jsonString)-1)." ]}";
            }
        else if ($action=="view-network-hardware")
            {
            $netHardware = Network::listAllInterfaces();
            print_r($netHardware);
            }
        else if ($action=="info")
            {
            echo str_replace("\n","<br>",Network::getNetworkInformations());
            }
        else if ($action=="init")
            {
            Network::initNetwork();
            }
        else if ($action=="wifi-disconnect")
            {
            if ($MODIFY_ENABLED) Network::wifiDisconnect(); // unused.
            }
        else
            {
            $netIP = strip_tags(trim($_GET['netIP']));
            $netMask = strip_tags(trim($_GET['netMask']));
            $netGateway = strip_tags(trim($_GET['netGateway']));
            $netDNS = strip_tags(trim($_GET['netDNS']));

            $netSSID = strip_tags(trim($_GET['netSSID']));
            $netPassword = strip_tags(trim($_GET['netPassword']));
            $netSecurity = strip_tags(trim($_GET['netSecurity']));

            if ($MODIFY_ENABLED) Network::setNetwork($action,$netIP,$netMask,$netGateway,$netDNS,$netSSID,$netPassword,$netSecurity);
            }
        break;


    case "video":
        $action = strip_tags(trim($_GET['action']));
        if ($action=="getResolutions")
            {
            echo Misc::getAvailableResolutions();
            }

        if ($action=="changeResolution")
            {
            echo Misc::changeResolution((int)$_GET['w'],(int)$_GET['h']);
            }

        if ($action=="rotate")
            {
            if ($MODIFY_ENABLED) Misc::videoRotate(strip_tags(trim($_GET['rotation'])));
            }
        break;


    case "sound":
        if ($MODIFY_ENABLED) Misc::setSound();
        break;


    case "browser":
        $action = strip_tags(trim($_GET['action']));
        $toggle = strip_tags(trim($_GET['toggle']));

        if ($MODIFY_ENABLED)
            {
            if ($action=="makeHomePersistent")
                Misc::makeBrowserHomePersistent($toggle);
            }
        break;


    case "strictKiosk":
        if ($_GET['directStart']=="y") $MODIFY_ENABLED = true;
        if ($MODIFY_ENABLED)
            {
            Misc::setStrickKioskMode($_GET['url'],$_GET['token'],$_GET['timeout'],$_GET['cache'],$_GET['keyboard'],$_GET['pageReload'],$_GET['haltAt'],$_GET['blankingTime'],$_GET['disableInput'],$_GET['proxy']);
            }
        break;


    case "system-infos":
        Misc::viewSystemInformations();
        break;

    }

?>