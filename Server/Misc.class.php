<?php

class Misc
    {
    // ***************************************************************************************************************************
    // PUBLIC METHODS
    // ***************************************************************************************************************************

    // ;

    // ***************************************************************************************************************************
    // PUBLIC STATIC METHODS
    // ***************************************************************************************************************************

    public static function modifyPassword(/* String */ $newPassword) /* Boolean */
        {
        $newPassword = trim($newPassword);

        $newPasswordHashed = trim(md5($newPassword));
        if (!Utils::writeFile("/iwk/iwk.adminPasswd",$newPasswordHashed,"700")) return false;

        // Also change root password for SSH remote management.
        $changePwdCommand = "passwd --quiet pi <<EOF\n".$newPassword."\n".$newPassword."\nEOF\n";
        if (!Utils::writeFile("/tmp/changeRootPwd.sh",$changePwdCommand,"777")) return false;
        @shell_exec("sudo sh /tmp/changeRootPwd.sh");
        @unlink("/tmp/changeRootPwd.sh");

        return true;
        }



    public static function viewSystemInformations() /* String */
        {
        $batteryStatus = trim(shell_exec("acpi"));
        if (!$batteryStatus) $batteryStatus = "no battery";

        echo "<div><strong>Date and time</strong>: ".trim(shell_exec("date")).".</div>";
        echo "<div><strong>Battery status</strong>: ".$batteryStatus.".</div>";
        }



    public static function setSound() /* Void */
        {
        shell_exec("export DISPLAY=:0; sudo su - pi -c qasmixer");
        }



    public static function markVirtualKeyboard(/* String */ $action) /* Void */
        {
        $iwkVirtualKeyboardFile = "/iwk/iwk.virtualKeyboardDisplay";

        if ($action=="on")
            {
            Utils::writeFile($iwkVirtualKeyboardFile,"","777"); // just write to signal to .xinitrc.
            }
        else
            {
            @unlink($iwkVirtualKeyboardFile);
            }
        }



    public static function setReloadTimer(/* Integer */ $timeout) /* Boolean */
        {
        $timeout = (int)$timeout; // minutes.
        $timeout = $timeout*60*1000; // ms.

        $iwkStrictModeRefreshTimeout = "/iwk/iwk.refreshTimeout"; // used by /home/pi/.xinitrc.

        if (!Utils::writeFile($iwkStrictModeRefreshTimeout,$timeout,"777")) return false; // idle timeout (milliseconds): after that > reload browser.
        return true;
        }



    public static function makeBrowserHomePersistent(/* String */ $doPersist) /* Void */
        {
        if ($doPersist=="on")
            {
            // Save browser HOME and signal.
            @shell_exec("sudo killall chromium >/dev/null 2>&1");
            @shell_exec("sudo killall chromium-browser >/dev/null 2>&1");
            @shell_exec("sudo /usr/bin/killall xinit >/dev/null 2>&1");
            @shell_exec("sudo rm -fR /iwk/chromium-webkiosk-user.tar.gz >/dev/null 2>&1");

            @shell_exec("sleep 2");
            @shell_exec("sudo rm -fR /home/pi/.config/chromium/chromium >/dev/null 2>&1");
            @shell_exec("sed -i 's,\"exited_cleanly\": false,\"exited_cleanly\": true,' '/home/pi/.config/chromium/Default/Preferences'");

            @shell_exec("sudo cp -a /home/pi/.config/chromium chromium");
            @shell_exec("sudo tar -cf chromium-webkiosk-user.tar chromium");
            @shell_exec("sudo gzip -5 chromium-webkiosk-user.tar");
            @shell_exec("sudo mv chromium-webkiosk-user.tar.gz /iwk/");

            @shell_exec("sudo rm -fR chromium");

            @shell_exec("sudo chmod 755 /iwk/chromium-webkiosk-user.tar.gz");
            @shell_exec("sudo chown www-data:www-data /iwk/chromium-webkiosk-user.tar.gz");

            Utils::writeFile("/iwk/iwk.doPersistHome","","755"); // last thing to do.
            }
        else
            {
            @shell_exec("sudo rm -fR /iwk/chromium-webkiosk-user.tar.gz >/dev/null 2>&1");
            @unlink("/iwk/iwk.doPersistHome");
            }

        // Relaunch X (xinit).
        @shell_exec("sudo /usr/bin/killall xinit >/dev/null 2>&1"); // kill X.
        @shell_exec("sleep 2");
        @shell_exec("sudo /bin/sh /etc/rc.local >/dev/null 2>&1 &");
        }



    public static function videoRotate(/* String */ $rotation) /* Void */
        {
        // left | right | inverted | normal.
        $content = file_get_contents("/boot/config.txt");

        $content = str_replace("display_rotate","#display_rotate",$content);
        if ($rotation=="normal") $content .= "\n#Rotation\ndisplay_rotate=0\n";
        if ($rotation=="right") $content .= "\n#Rotation\ndisplay_rotate=1\n";
        if ($rotation=="inverted") $content .= "\n#Rotation\ndisplay_rotate=2\n";
        if ($rotation=="left") $content .= "\n#Rotation\ndisplay_rotate=3\n";

        $iwkRotatePersistent = "/iwk/iwk.screenRotate";
        Utils::writeFile($iwkRotatePersistent,trim($rotation),"777");

        Utils::writeFile("/tmp/config.txt",$content,"");
        shell_exec("sudo mv -f /tmp/config.txt /boot/config.txt");

        shell_exec("sleep 1; sudo reboot");
        }




    public static function getAvailableResolutions() /* String */
        {
        return shell_exec("sudo xrandr -d :0");
        }



    public static function changeResolution(/* Integer */ $w,/* Integer */ $h) /* Void */
        {
        // Just save & relaunch X.Org. Info used by rc.local.
        Utils::writeFile("/iwk/iwk.screenResolutionW",trim($w),"777");
        Utils::writeFile("/iwk/iwk.screenResolutionH",trim($h),"777");

        // Relaunch X (xinit).
        $killCommand = "sudo /usr/bin/killall xinit >/dev/null 2>&1";
        $sleepCommand = "sleep 3";
        $rebornCommand = "sudo /bin/sh /etc/rc.local >/dev/null 2>&1 &";

        shell_exec($killCommand);
        shell_exec($sleepCommand);
        shell_exec($rebornCommand);
        }



    public static function setStrickKioskMode(/* String */ $url,/* String */ $token,/* Integer */ $timeout,/* String */ $cache, /* String */ $keyboard, /* Integer */ $pageReload,/* String */ $haltAt,/* String */ $blankingTime,/* String */ $disableInput,/* String */ $proxy) /* Void */
        {
        $url = trim(strip_tags($url));
        if ($url=="" || $url=="http://") $baseUrlToLoad = "http://www.binaryemotions.com"; // binaryemotions URL in case of $url is empty.
        else $baseUrlToLoad = $url; // user selected URL.

        // MAC address.
        if (trim($token)=="on")
            {
            // Append MAC to URL.
            if (strpos($baseUrlToLoad,"?")===false) $urlToLoad = $baseUrlToLoad."?id=".self::__getMacAddress();
            else $urlToLoad = $baseUrlToLoad."&id=".self::__getMacAddress();
            }
        else $urlToLoad = $baseUrlToLoad; // con't append token.

        // Write files (some persistent; used by /etc/rc.local or /home/pi/.xinitrc).
        $iwkStrictModeFile = "/tmp/iwk.strictKioskMode";
        $iwkStrictModeURL = "/iwk/iwk.strictKioskURL";
        $iwkStrictModeBaseURL = "/iwk/iwk.strictKioskBaseURL";
        $iwkStrictModeCacheURL = "/tmp/iwk.strictKioskCacheURL";
        $iwkStrictModeReloadPageTimer = "/iwk/iwk.strictKioskReloadPageTimer";
        $iwkStrictModeProxy = "/iwk/iwk.applicationProxy";
        $iwkStrictModeURLToken = "/iwk/iwk.strictKioskAddMacAddress";
        $iwkScreenblankingTime = "/iwk/iwk.screenblankingTime";
        $iwkSystemHaltAt = "/iwk/iwk.systemHaltAt";
        $iwkSystemDisableInput = "/iwk/iwk.systemDisableInput";

        Utils::writeFile($iwkStrictModeFile,"-",""); // just write to signal.
        Utils::writeFile($iwkStrictModeURL,trim($urlToLoad),"777"); // URL to be launched in kiosk mode.
        Utils::writeFile($iwkStrictModeBaseURL,trim($baseUrlToLoad),"777"); // URL to be launched in kiosk mode (base URL for optional mirroring with wget).
        Utils::writeFile($iwkStrictModeCacheURL,"-",""); // just write to signal.
        Utils::writeFile($iwkStrictModeReloadPageTimer,trim($pageReload),"777");
        Utils::writeFile($iwkScreenblankingTime,(int)$blankingTime,"777");

        // Disable WP if not used.
        shell_exec("grep -q 'http://rds.local' ".$iwkStrictModeURL." && sudo a2ensite local && sudo systemctl reload apache2");
        shell_exec("! grep -q 'http://rds.local' ".$iwkStrictModeURL." && sudo a2dissite local && sudo systemctl reload apache2");

        if (trim($proxy)) Utils::writeFile($iwkStrictModeProxy,$proxy,"777");
        else @unlink($iwkStrictModeProxy);

        if (trim($haltAt)!="0" && trim($haltAt)!="") Utils::writeFile($iwkSystemHaltAt,trim($haltAt),"777");
        else @unlink($iwkSystemHaltAt);

        if (trim($disableInput)=="on") Utils::writeFile($iwkSystemDisableInput,"",""); // just write to signal.
        else @unlink($iwkSystemDisableInput);

        if (trim($token)=="on") Utils::writeFile($iwkStrictModeURLToken,"",""); // just write to signal.
        else @unlink($iwkStrictModeURLToken);

        self::setReloadTimer($timeout);
        self::markVirtualKeyboard($keyboard);

        // Relaunch X (xinit).
        shell_exec("sudo /usr/bin/killall chromium >/dev/null 2>&1");
        shell_exec("sudo /usr/bin/killall chromium-browser >/dev/null 2>&1");
        shell_exec("sudo /usr/bin/killall xinit >/dev/null 2>&1"); // www-data has root rights via sudo (no password specify required).
        shell_exec("sleep 3");
        shell_exec("sudo /bin/sh /etc/rc.local >/dev/null 2>&1 &");
        }

    // ***************************************************************************************************************************
    // PRIVATE STATIC METHODS
    // ***************************************************************************************************************************

    private static function __getMacAddress() /* String */
        {
        $ifConfigOutput = shell_exec("sudo ifconfig -a");
        $ifConfigOutputArray = explode("\n",$ifConfigOutput);

        $mac = $ifConfigOutputArray[0];
        $mac = trim(str_ireplace(array("eth0","eth1","link","encap","ethernet","hwaddr",":"),"",$mac));

        return $mac;
        }

    }
?>

