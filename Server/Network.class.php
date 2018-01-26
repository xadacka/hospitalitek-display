<?php

class Network
    {
    // ***************************************************************************************************************************
    // PUBLIC METHODS
    // ***************************************************************************************************************************

    // ;

    // ***************************************************************************************************************************
    // PUBLIC STATIC METHODS
    // ***************************************************************************************************************************

    public static function initNetwork() /* Void */
        {
        $runFile = "/tmp/iwk.inetRun";

        // Try to initialize network with last used method (if any). Otherwise DHCP.
        // Only for the first run (operating system rebooted).
        if (!file_exists($runFile))
            {
            // Persistence files to look at.
            $interfacesFilePersistence = "/iwk/interfaces";
            $dnsFilePersistence = "/iwk/resolv.conf";

            $networkConnectionMethodPersistence = "/iwk/iwk.networkConnectionMethod";
            if (file_exists($networkConnectionMethodPersistence) && is_readable($networkConnectionMethodPersistence) && filesize($networkConnectionMethodPersistence)>1)
                $networkConnectionMethodPersistenceContent = trim(file_get_contents($networkConnectionMethodPersistence));
            else $networkConnectionMethodPersistenceContent = "";

            $networkInterfacePersistence = "/iwk/iwk.networkInterface";
            if (file_exists($networkInterfacePersistence) && is_readable($networkInterfacePersistence) && filesize($networkInterfacePersistence)>1) 
                $networkInterfacePersistenceContent = trim(file_get_contents($networkInterfacePersistence));
            else $networkInterfacePersistenceContent = "";

            self::__putInterfacesDown();

            if (!file_exists($interfacesFilePersistence))
                {
                // DHCP initialize.
                self::setNetwork("dhcp","","","","","","","");
                }
            else if ($networkConnectionMethodPersistenceContent=="static")
                {
                // Static init.
                shell_exec("cp -f ".$interfacesFilePersistence." /etc/network/interfaces");
                shell_exec("cp -f ".$dnsFilePersistence." /etc/resolv.conf");
                shell_exec("sudo ifup ".$networkInterfacePersistenceContent);
                }
            else
                {
                // 802.11 init.
                shell_exec("cp -f ".$interfacesFilePersistence." /etc/network/interfaces");

                // Bring up 802.11 interface!
                self::__setDhcpTimeout(30);
                shell_exec("sudo killall wpa_supplicant");
                shell_exec("sudo ifup ".$networkInterfacePersistenceContent);
                }

            Utils::writeFile($runFile,"","777"); // write "executed-once" file.
            }
        }



    public static function setNetwork(/* Strings */ $action,$netIP,$netMask,$netGateway,$netDNS,$netSSID,$netPassword,$netSecurity) /* Boolean */
        {
        $interfacesFile = "/etc/network/interfaces";
        $dnsFile = "/etc/resolv.conf";

        $interfacesFilePersistence = "/iwk/interfaces";
        $dnsFilePersistence = "/iwk/resolv.conf";
        $networkConnectionMethodPersistence = "/iwk/iwk.networkConnectionMethod";
        $networkInterfacePersistence = "/iwk/iwk.networkInterface";

        self::__putInterfacesDown();

        switch ($action)
            {
            case "dhcp":
                // Bring up lo + first Ethernet interface (using canonical interfaces file).
                $ifContent = "auto lo\niface lo inet loopback\n\n";
                $ifContent .= "auto eth0\nallow-hotplug eth0\niface eth0 inet dhcp\n\n";
                Utils::writeFile($interfacesFile,$ifContent,"");

                // Bring up!
                self::__setDhcpTimeout(30);
                shell_exec("sudo ifup eth0");

                // No need for persistence files.
                unlink($dnsFilePersistence);
                unlink($interfacesFilePersistence);
                unlink($networkConnectionMethodPersistence);
                unlink($networkInterfacePersistence);

                break;

            case "static":
                // Bring up lo + Ethernet interface.
                $ifContent = "auto lo\niface lo inet loopback\n\n";
                $ifContent .= "auto eth0\nallow-hotplug eth0\niface eth0 inet static\n";
                $ifContent .= "address ".$netIP."\n";
                $ifContent .= "netmask ".$netMask."\n";
                $ifContent .= "gateway ".$netGateway."\n\n";
                Utils::writeFile($interfacesFile,$ifContent,"");

                // DNS.
                $dnsFileContent = "nameserver ".$netDNS."\n";
                Utils::writeFile($dnsFile,$dnsFileContent,"");

                // Savings for persistence (persistence).
                Utils::writeFile($interfacesFilePersistence,$ifContent,"777");
                Utils::writeFile($dnsFilePersistence,$dnsFileContent,"777");
                Utils::writeFile($networkConnectionMethodPersistence,"static","777");
                Utils::writeFile($networkInterfacePersistence,"eth0","777");

                // Bring up!
                shell_exec("sudo ifup eth0");

                break;

            case "wifi":
                // Set lo + 802.11 interface.
                $ifContent = "auto lo\niface lo inet loopback\n\n";
                $ifContent .= "auto wlan0\nallow-hotplug wlan0\niface wlan0 inet dhcp\n";

                // Common data.
                if (stripos($netSecurity,"wpa")!==false)
                    {
                    $ifContent .= "wpa-ssid \"".$netSSID."\"\n";
                    $ifContent .= "wpa-psk \"".$netPassword."\"\n\n";
                    }
                else if ($netSecurity=="WEP")
                    {
                    $ifContent .= "wireless-essid ".$netSSID."\n";
                    $ifContent .= "wireless-key ".$netPassword."\n\n";
                    }
                else
                    {
                    $ifContent .= "wireless-essid ".$netSSID."\n";
                    }

                Utils::writeFile($interfacesFile,$ifContent,"");

                // Savings for persistence (persistence).
                unlink($dnsFilePersistence);
                Utils::writeFile($interfacesFilePersistence,$ifContent,"777");
                Utils::writeFile($networkConnectionMethodPersistence,"wifi","777");
                Utils::writeFile($networkInterfacePersistence,"wlan0","777");

                self::__setDhcpTimeout(30);

                // Bring up 802.11 interface!
                shell_exec("sudo killall wpa_supplicant");
                shell_exec("sudo ifup wlan0");
                shell_exec("sudo ifup wlan0");

                break;
            }

        return true;
        }



    public static function wifiDisconnect() /* Boolean */
        {
        self::__putInterfacesDown();

        // Delete network data (for the next boot).
        unlink("/iwk/interfaces");
        unlink("/iwk/iwk.networkConnectionMethod");
        unlink("/iwk/iwk.networkInterface");
        return true;
        }



    public static function getNetworkInformations() /* String */
        {
        $return = "";
        $strictKioskURL = "";
        $networkConnectionFileEth = "/sys/class/net/eth0/operstate";
        $networkConnectionFileWlan = "/sys/class/net/wlan0/operstate";

        $netOkFile = "/tmp/iwk.inetTest";
        if (file_exists($netOkFile)) unlink($netOkFile);

        // Check if net is up.
        if (trim(file_get_contents($networkConnectionFileEth))=="up" || trim(file_get_contents($networkConnectionFileWlan))=="up")
            {
            $return .= "<strong>Network is up and running</strong>.<br><br>";

            $ifConfigOutput = shell_exec("sudo ifconfig -a");
            $ifConfigOutput = str_replace("inet addr:","inet addr:<strong>",$ifConfigOutput);
            $ifConfigOutput = str_replace("Mask:","</strong>Mask:",$ifConfigOutput);
            $return .= "<br><span class='standard' style='color:#D00;'><strong>Kiosk mode will start with the following network settings</strong>.<br><br>If you want to change connection method, unplug network cable and reboot.<br>Your selected connection method will then presist across reboots. Default is DHCP.<br><br>".$ifConfigOutput."</span>";
            }
        else $return .= "Internet connection is down! Try using another connection method.<br><br>";

        return $return;
        }



    public static function wifiNetworksList() /* Array of Strings */
        {
        $j = 0;
        $info = array();

        $networksData = @shell_exec("sudo ifconfig wlan0 up; sudo iwlist scanning");
        $networksDataArray = @explode("\n",$networksData);
        while (list(,$line) = @each($networksDataArray))
            {
            if ($line)
                {
                $line = trim($line);

                preg_match_all("/ESSID:\"(.+?)\"/",$line,$matches);
                if ($matches && $matches[1] && trim($matches[1][0])) 
                    {
                    $info[$j]['ssid'] = trim($matches[1][0]); 
                    }

                preg_match_all("/Channel:(.+?)$/",$line,$matches);
                if ($matches && $matches[1] && trim($matches[1][0])) 
                    {
                    $info[$j]['freq'] = trim($matches[1][0]);
                    }

                preg_match_all("/Frequency:(.+?)$/",$line,$matches);
                if ($matches && $matches[1] && trim($matches[1][0])) 
                    {
                    $rate = trim($matches[1][0]);
                    preg_match_all("/\((.+?)\)/",$rate,$matches);
                    if ($matches && $matches[1] && trim($matches[1][0])) 
                        {
                        $info[$j]['rate'] = trim($matches[1][0]);
                        }
                    }

                preg_match_all("/Quality=(.+?)$/",$line,$matches);
                if ($matches && $matches[1] && trim($matches[1][0])) 
                    {
                    $signal = trim($matches[1][0]);
                    preg_match_all("/=(.+?)$/",$signal,$matches);
                    if ($matches && $matches[1] && trim($matches[1][0])) 
                        {
                        $info[$j]['signal'] = trim($matches[1][0]);
                        }
                    }

                preg_match_all("/IE: IEEE(.+?)$/",$line,$matches);
                if ($matches && $matches[1] && trim($matches[1][0])) 
                    {
                    $security = trim($matches[1][0]);
                    preg_match_all("/\/(.+?)$/",$security,$matches);
                    if ($matches && $matches[1] && trim($matches[1][0])) 
                        {
                        $info[$j]['security'] = str_replace("Version ","v",trim($matches[1][0]));
                        }
                    }

                if (strpos($line,"Cell ")!==false)
                    {
                    $j++;

                    $info[$j]['ssid'] = "";
                    $info[$j]['freq'] = "";
                    $info[$j]['rate'] = "";
                    $info[$j]['signal'] = "";
                    $info[$j]['seurity'] = "";

                    preg_match_all("/Address:(.+?)$/",$line,$matches);
                    if ($matches && $matches[1] && trim($matches[1][0]))
                        $info[$j]['bssid'] = trim($matches[1][0]);
                    else
                        $info[$j]['bssid'] = $j;
                    }
                }
            }

        return $info;
        }



/*    public static function listAllInterfaces()
        {
        $interfacesArray = array();
        $wiredInterfacesArray = array();
        $wifiInterfacesArray = array();

        $out = shell_exec("sudo ifconfig -a");

        if ($out)
            {
            $outArray = @explode("\n",$out);
            while (list(,$line) = @each($outArray))
                {
                $ifInfoArray = @explode(" ",$line);
                if ($ifInfoArray[0])
                    {
                    $if = trim($ifInfoArray[0]);
                    if (strtolower($if)!="iface" && strtolower($if)!="lo")
                        {
                        if (strpos($if,"eth")!==false)
                            {
                            // This is an Ethernet interface (double check).
                            if (self::__isEthernet($ifInfoArray[0]))
                                {
                                @array_push($wiredInterfacesArray,$ifInfoArray[0]);
                                }
                            }
                        else if (strpos($if,"wlan")!==false || strpos($if,"ra")!==false)
                            {
                            // This is a wireless interface (double check).
                            if (self::__isWireless($ifInfoArray[0]))
                                {
                                @array_push($wifiInterfacesArray,$ifInfoArray[0]);
                                }
                            }
                        }
                    }
                }

            $interfacesArray['wired'] = $wiredInterfacesArray;
            $interfacesArray['wifi'] = $wifiInterfacesArray;

            // (
            //   [wired]
            //     [0] => eth0
            //
            //   [wifi]
            //     [0] => wlan0
            //     [1] => wlan1
            // )
            }

        return $interfacesArray;
        }
*/

    // ***************************************************************************************************************************
    // PRIVATE STATIC METHODS
    // ***************************************************************************************************************************

    private static function __putInterfacesDown() /* Void */
        {
        @shell_exec("sudo ifdown eth0");
        @shell_exec("sleep 1");

        @shell_exec("sudo ifdown wlan0");
        @shell_exec("sleep 1");
        }



    private static function __isEthernet(/* String */ $interface) /* Boolean */
        {
        // Todo: check with "sudo nmcli dev status".
        return true;
        }



    private static function __isWireless(/* String */ $interface) /* Boolean */
        {
        // Todo: check with "sudo nmcli dev status".
        return true;
        }



    private static function __setDhcpTimeout(/* Integer */ $seconds) /* Void */
        {
        $dhcClientConfigFile = "/etc/dhcp/dhclient.conf";
        $content = file_get_contents($dhcClientConfigFile);

        $content = str_replace("#timeout","timeout",$content);
        $content = str_replace("timeout 60;","timeout ".$seconds.";",$content);
        $content = str_replace("timeout 30;","timeout ".$seconds.";",$content);

        Utils::writeFile($dhcClientConfigFile,$content,"");
        }
    }
?>
