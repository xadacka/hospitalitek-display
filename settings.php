<?php
// Defining variables: load system persistent state.

    // Strict kiosk URL.
    $iwkStrictModeURL = trim(file_get_contents("/iwk/iwk.strictKioskBaseURL"));
    if (!$iwkStrictModeURL || $iwkStrictModeURL=="http://www.binaryemotions.com") $iwkStrictModeURL = "http://";

    // Refresh timeout.
    $iwkRefreshTimeout = file_get_contents("/iwk/iwk.refreshTimeout");
    $iwkRefreshTimeout = (int)($iwkRefreshTimeout/60/1000);

    // Reload timeout.
    $iwkPageReloadTimeout = (int)file_get_contents("/iwk/iwk.strictKioskReloadPageTimer");

    // Video rotation info.
    $iwkVideoRotationHow = file_get_contents("/iwk/iwk.screenRotate");
    if (!$iwkVideoRotationHow || trim($iwkVideoRotationHow)=="normal") $iwkVideoRotationNormal = "checked";
    if (trim($iwkVideoRotationHow)=="left") $iwkVideoRotationLeft = "checked";
    if (trim($iwkVideoRotationHow)=="right") $iwkVideoRotationRight = "checked";
    if (trim($iwkVideoRotationHow)=="inverted") $iwkVideoRotationReverse = "checked";

    // Virtual keyboard.
    $iwkVirtualKeyboardFileOn = "";
    $iwkVirtualKeyboardFileOff = "";
    if (file_exists("/iwk/iwk.virtualKeyboardDisplay")) $iwkVirtualKeyboardFileOn = "checked";
    else $iwkVirtualKeyboardFileOff = "checked";

    // Proxy.
    $iwkAppProxy = trim(file_get_contents("/iwk/iwk.applicationProxy"));

    // System halt time.
    if (file_exists("/iwk/iwk.systemHaltAt")) $iwkSystemHaltContent = trim(file_get_contents("/iwk/iwk.systemHaltAt"));

    if ($iwkSystemHaltContent) $iwkSystemHaltHour = $iwkSystemHaltContent;
    else $iwkSystemHaltHour = 0;

    // Disable mouse/keyboard input.
    $iwkDisabledInput = "";
    if (file_exists("/iwk/iwk.systemDisableInput")) $iwkDisabledInput = "checked";

    // Add token (MAC address) at the end of the target URL.
    $iwkAddToken = "";
    if (file_exists("/iwk/iwk.strictKioskAddMacAddress")) $iwkAddToken = "checked";

    // Is password disabled?
    $unlockPwdHashSaved = trim(file_get_contents("/iwk/iwk.adminPasswd"));
    if ($unlockPwdHashSaved==trim(md5("no-passwd"))) $passwordDisabled = true;
    else $passwordDisabled = false;

// ************************************************************************************************ ?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <title>Hospitalitek Digital Signage - Settings</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="Author" content="ing. Marco Buratto, ing.marcoburatto@gmail.com">
    <meta name="Comment" content="This is a dirty no-framework, js-in-pages quick and fast coding style.">

    <link type="text/css" href="Styles/standard.css" rel="stylesheet">
    <link type="text/css" href="JavaScript/dojo/dijit/themes/tundra/tundra.css" rel="stylesheet">
    <link type="text/css" href="JavaScript/dojo/dojox/grid/resources/tundraGrid.css" rel="stylesheet">

    <style>
        @font-face {
            font-family: 'Economica';
            font-style: normal;
            font-weight: 700;
            src: local('Economica Bold'), local('Economica-Bold'), url(Styles/WebFonts/economica_2.woff) format('woff');
        }
    </style>

    <script type="text/javascript" src="JavaScript/Utils.object.js"></script>
    <script type="text/javascript" src="JavaScript/dojo/dojo/dojo.js" djConfig="isDebug:false, parseOnLoad:true"></script>

    <script type="text/javascript">
       dojo.require("dojo.parser");
       dojo.require("dojo.date.locale");
       dojo.require("dijit.TitlePane");
       dojo.require("dijit.layout.ContentPane");
       dojo.require("dijit.layout.TabContainer");
       dojo.require("dojox.layout.FloatingPane");

        dojo.addOnLoad(function()
            {
            // Start kiosk mode in seconds if no user interaction detected.
            document.getElementById('KioskModeTabContent2').style.display = "none";
            document.getElementById('KioskModeTabContent1').innerHTML = "<img src=\"logo.png\"><br><br><br><br><br><br><br><br><p class='title'>Hospitalitek Display Manager will start in 15 seconds.<br><br><a href='#.' onClick='showAdmin();'>Click here for settings modify</a>.</p>";

            tOut = window.setTimeout("kioskModeSet(true);",15000);
            });

        function showAdmin()
            {
            clearTimeout(tOut);

            document.getElementById('KioskModeTabContent2').style.display = "block";
            document.getElementById('KioskModeTabContent1').style.display = "none";
            }

        // XHR calls and others.
        function localeSet(/* String */ lang)
            {
            var unlockpwd = document.getElementById('adminPassword').value;
            if (!unlockpwd) 
                {
                alert("In order to change settings, please enter unlock password. ");
                document.getElementById('languageSelector').selectedIndex = 0;
                return false;
                }

            dojo.xhr("GET", {
                url: "backend.php?target=locale&lang="+lang+"&unlockPwd="+encodeURIComponent(unlockpwd),
                preventCache: true,
                load: function(data,args)
                    {
                    if (data!="")
                        alert(data);
                    },
                timeout: 30000,
                error: function(error,args)
                    {
                    alert("Cannot change language.");
                    }
                });
            }

        function getAvailableResolutions()
            {
            dojo.xhr("GET", {
                url: "backend.php?target=video&action=getResolutions",
                preventCache: true,
                load: function(data,args)
                    {
                    if (data!="")
                        alert(data);
                    },
                timeout: 30000,
                error: function(error,args) { ; }
                });
            }

        function changeResolution()
            {
            var resolutionW = parseInt(document.getElementById('resolutionW').value);
            var resolutionH = parseInt(document.getElementById('resolutionH').value);

            if ((resolutionW>0) && (resolutionW>0))
                {
                var unlockpwd = document.getElementById('adminPassword').value;
                if (!unlockpwd)
                    {
                    alert("In order to change settings, please enter unlock password. ");
                    return false;
                    }

                dojo.xhr("GET", {
                    url: "backend.php?target=video&action=changeResolution&w="+resolutionW+"&h="+resolutionH+"&unlockPwd="+encodeURIComponent(unlockpwd),
                    preventCache: true,
                    load: function(data,args)
                        {
                        if (data!="")
                            console.log(data);
                        },
                    timeout: 30000,
                    error: function(error,args) { ; }
                    });
                }
            }

        function soundSet()
            {
            var unlockpwd = document.getElementById('adminPassword').value;
            if (!unlockpwd)
                {
                alert("In order to change settings, please enter unlock password. ");
                return false;
                }

            dojo.xhr("GET", {
                url: "backend.php?target=sound&unlockPwd="+encodeURIComponent(unlockpwd),
                preventCache: true,
                load: function(data,args)
                    {
                    if (data!="")
                        console.log(data);
                    },
                timeout: 30000,
                error: function(error,args) { ; }
                });
            }

        function modifyPassword(/* Boolean */ dontuse)
            {
            var unlockpwd = document.getElementById('adminPassword').value;
            if (!unlockpwd)
                {
                alert("In order to change password, please enter the current admin password. ");
                return false;
                }

            if (!dontuse) var newAdminPassword = prompt("Insert new password"); // prompt for password.
            else var newAdminPassword = "no-passwd";

            if (newAdminPassword)
                {
                dojo.xhr("GET", {
                    url: "backend.php?target=modifyPasword&newPassword="+encodeURIComponent(newAdminPassword)+"&unlockPwd="+encodeURIComponent(unlockpwd),
                    preventCache: true,
                    load: function(data,args)
                        {
                        if (data!="")
                            {
                            alert(data);
                            document.location.reload();
                            }
                        },
                    timeout: 30000,
                    error: function(error,args) { ; }
                    });
                }
            }

        function makeBrowserHomePersistent(/* Boolean */ doPersist)
            {
            if (doPersist)
                {
                var toggle = "on";
                if (!confirm("Are you sure you want to replace default Raspberry Digital Signage Chrome settings with this browser snapshot? "))
                    return false;
                }
            else
                {
                var toggle = "off";
                if (!confirm("Replace your own Chrome settings with default ones? "))
                    return false;
                }

            if (confirm("PLEASE WAIT UNTIL SYSTEM RESTARTS, OPERATION MAY TAKE MINUTES."))
                {
                var unlockpwd = document.getElementById('adminPassword').value;
                if (!unlockpwd)
                    {
                    alert("Please enter admin password, first. ");
                    return false;
                    }

                dojo.xhr("GET", {
                    url: "backend.php?target=browser&action=makeHomePersistent&toggle="+toggle+"&unlockPwd="+unlockpwd,
                    preventCache: true,
                    load: function(data,args)
                        {
                        if (data!="")
                            console.log(data);
                        },
                    timeout: 30000,
                    error: function(error,args) { ; }
                    });
                }
            }

        function videoRotationSet()
            {
            var unlockpwd = document.getElementById('adminPassword').value;
            if (!unlockpwd)
                {
                alert("In order to change settings, please enter unlock password. ");
                return false;
                }

            if (!confirm("This will reboot system now, continue? ")) return true;

            if (document.getElementById('videoRotationRadioNormal').checked) var rotation = "normal";
            if (document.getElementById('videoRotationRadioLeft').checked) var rotation = "left";
            if (document.getElementById('videoRotationRadioRight').checked) var rotation = "right";
            if (document.getElementById('videoRotationRadioReverse').checked) var rotation = "inverted";

            dojo.xhr("GET", {
                url: "backend.php?target=video&action=rotate&rotation="+rotation+"&unlockPwd="+encodeURIComponent(unlockpwd),
                preventCache: true,
                load: function(data,args)
                    {
                    if (data!="")
                        console.log(data);
                    },
                timeout: 30000,
                error: function(error,args) { ; }
                });
            }

        function kioskModeSet(/* Boolean */ directBoot)
            {
            var unlockpwd = document.getElementById('adminPassword').value;
            if (!directBoot)
                {
                if (!unlockpwd)
                    {
                    alert("In order to change settings, please enter unlock password. ");
                    return false;
                    }
                }

            var skmURL = document.getElementById('skmPage').value;
            var skmTimeout = document.getElementById('skmInactivityBeforeReload').value;
            var skmPageTimeout = document.getElementById('skmForcePageReload').value;
            var iwkScreenblankingTime = 0; // future use.
            var skmSystemHalt = document.getElementById('skmSystemHalt').value;
            var skmCacheURL = false; //document.getElementById('skmCacheURL').checked;
            var skmVirtualKeyboard = document.getElementById('virtualKeyboardSetOn').checked;
            var skmProxy = document.getElementById('skmHTTPProxy').value;
            var skmDisableAllInput = document.getElementById('skmDisableAllInput').checked;
            var skmAddMacAddress = document.getElementById('skmAddMacAddress').checked;

            if (directBoot) var directStart = "y";
            else var directStart = "n";

            if (skmCacheURL) var skmCache = "y";
            else var skmCache = "n";

            if (skmVirtualKeyboard) var skmVirtualKeyboard = "on";
            else var skmVirtualKeyboard = "off";

            if (skmDisableAllInput) var skmDisableAllInput = "on";
            else var skmDisableAllInput = "off";

            if (skmAddMacAddress) var skmAddMacAddress = "on";
            else var skmAddMacAddress = "off";

            var uri = "backend.php?target=strictKiosk&url="+encodeURIComponent(skmURL)+"&token="+skmAddMacAddress+"&directStart="+directStart+"&timeout="+skmTimeout+"&cache="+skmCache+"&keyboard="+skmVirtualKeyboard+"&pageReload="+skmPageTimeout+"&haltAt="+skmSystemHalt+"&blankingTime="+iwkScreenblankingTime+"&disableInput="+skmDisableAllInput+"&proxy="+encodeURIComponent(skmProxy)+"&unlockPwd="+encodeURIComponent(unlockpwd);

            dojo.xhr("GET", {
                url: uri,
                preventCache: true,
                load: function(data,args)
                    {
                    if (data!="")
                        console.log(data);
                    },
                timeout: 30000,
                error: function(error,args) { ; }
                });
            }

        function viewSystemInformations()
            {
            dojo.xhr("GET", {
                url: "backend.php?target=system-infos",
                preventCache: true,
                load: function(data,args)
                    {
                    if (data!="")
                        document.getElementById('systemLog').innerHTML = data;
                    },
                timeout: 30000,
                error: function(error,args) { ; }
                });
            }
    </script>
</head>

<body class="tundra" style="height:88%;">
    <div id="content" style="height:100%;">
        <div dojoType="dijit.layout.TabContainer" style="height:100%;">
             <!-- DIGITAL SIGNAGE TAB --><center>
             <div dojoType="dijit.layout.ContentPane" href="" title="<strong>Hospitalitek Display Manager</strong>" refreshOnShow="false" style="padding:15px;">
                 <div id="KioskModeTabContent1"></div>
                 <div id="KioskModeTabContent2">
                     <div>
                         <p>
                             Welcome to <strong>Hospitalitek Display Manager</strong>. These settings can not be changed without the authroization of Hospitalitek, they're shown here for refrence. <br>
                         </p>
                         <div style="margin-top:20px;">
                             <div><img src="logo.png"><br><br>
                                 <!-- Language -->
                             <!--    <div class="title">
                                     <strong>Locale and keyboard</strong>
                                 </div>
                                 <div style="margin-bottom:20px;">
                                     <p>Keyboard layout.</p>
                                     <p>
                                         Set system <strong>localization</strong>: &nbsp;
                                         <select id="languageSelector" onChange="if (this.options[this.selectedIndex].value) localeSet(this.options[this.selectedIndex].value);" style="width:130px;">
                                             <option value=""> Select your locale...</option>
                                             <option value="cn"> Chinese (use Google Input Tools)</option>
                                             <option value="cz"> Czech</option>
                                             <option value="dk"> Danish</option>
                                             <option value="gb"> English (GB)</option>
                                             <option value="us"> English (US) (default)</option>
                                             <option value="fr"> French</option>
                                             <option value="de"> German</option>
                                             <option value="it"> Italian</option>
                                             <option value="jp"> Japanese (use Google Input Tools)</option>
                                             <option value="br"> Portuguese (BR)</option>
                                             <option value="pt"> Portuguese (PT)</option>
                                             <option value="ru"> Russian</option>
                                             <option value="es"> Spanish</option>
                                             <option value="sk"> Slovak</option>
                                         </select>
                                     </p>
                                 </div>
-->
                                 <!-- Video -->
                                 <div class="title">
                                     <strong>Video</strong>
                                 </div>
                                 <div style="margin-bottom:20px;">
                                     <p style="margin-top:10px;">
                                         <p><strong>View screen resolutions</strong>: <a href="#." onClick="getAvailableResolutions();">show available</a>.</p>
                                         <p>
                                             <strong>Set resolution</strong>:
                                             <input type="text" id="resolutionW" style="width:40px;"> x <input type="text" id="resolutionH" style="width:40px;"> &nbsp;
                                             <a href="#." onClick="changeResolution();"><br>save resolution</a>.
                                         </p>
                                     </p>
                                     <p style="margin-top:10px;">
                                         <strong>Video rotation</strong>:
                                         <input type="radio" name="videoRotationRadio" id="videoRotationRadioNormal" style="width:15px;" <?php echo $iwkVideoRotationNormal;?>> normal &nbsp; | &nbsp;
                                         <input type="radio" name="videoRotationRadio" id="videoRotationRadioLeft" style="width:15px;" <?php echo $iwkVideoRotationLeft;?>> left &nbsp; | &nbsp;
                                         <input type="radio" name="videoRotationRadio" id="videoRotationRadioRight" style="width:15px;" <?php echo $iwkVideoRotationRight;?>> right &nbsp; | &nbsp;
                                         <input type="radio" name="videoRotationRadio" id="videoRotationRadioReverse" style="width:15px;" <?php echo $iwkVideoRotationReverse;?>> reverse &nbsp; | &nbsp;
                                         <br><a href="#." onClick="videoRotationSet();">apply rotation</a>
                                      </p>
                                 </div>

                                 <!-- Sound -->
                                 <div class="title">
                                     <strong>Sound</strong>
                                 </div>
                                 <div style="margin-bottom:20px;">
                                     <p>Set <strong>sound volume</strong>: <br><a href="#." onClick="soundSet();">open mixer</a>.</p>
                                 </div>

                                 <!-- Browser -->
                                 <div class="title">
                                     <strong>Chrome settings</strong>
                                 </div>
                                 <div style="margin-bottom:20px;">
                                     <p><b>Note: Persist can only be applied on localhost.</b></p>
                                     <p>Make all Chromium settings <a href="#." onClick="makeBrowserHomePersistent(true);">persistent</a>. &nbsp; | &nbsp; Reset to <a href="#." onClick="makeBrowserHomePersistent(false);">default</a></p>
                                 </div>

                                 <!-- Digital Signage -->
                                 <div class="title">
                                     <strong>System load settings.</strong>
                                 </div>

                                 <div style="margin-top:10px;">
                                     <table>
                                         <tr>
                                             <td>Display the following <strong>URL</strong>:</td>
                                             <td>&nbsp;</td>
                                             <td><input id="skmPage" type="text" value="<?php echo $iwkStrictModeURL;?>" style="width:180px;"> &nbsp; | &nbsp; Internet/LAN URL.</td>
                                         </tr>
                                         <tr>
                                             <td>Add MAC address at URL end:</td>
                                             <td>&nbsp;</td>
                                             <td><input id="skmAddMacAddress" type="checkbox" style="width:15px;" <?php echo $iwkAddToken;?>> <span style="margin-left:167px;"> &nbsp; | &nbsp; (<a title="This allows multiple deploys pointing just one target/server URL. For example: http://yourserver.com?id=mac-address. It is up to your local/remote server logic to return the appropriate content.">?</a>)</span>.</td>
                                         </tr>
                                         <tr>
                                             <td><strong>Reset browser</strong> after user inactivity of:</td>
                                             <td>&nbsp;</td>
                                             <td><input id="skmInactivityBeforeReload" type="text" value="<?php echo $iwkRefreshTimeout;?>" style="width:180px;"> &nbsp; | &nbsp; minutes. Zero (0) to never reset.</td>
                                         </tr>
                                         <tr>
                                             <td>Force <strong>reloading of web page</strong> every:</td>
                                             <td>&nbsp;</td>
                                             <td><input id="skmForcePageReload" type="text" value="<?php echo $iwkPageReloadTimeout;?>" style="width:180px;"> &nbsp; | &nbsp; seconds (min 5s). Zero (0) to never reload.</td>
                                         </tr>
                                         <tr>
                                             <td><strong>Disable mouse/keyboard</strong> input:</td>
                                             <td>&nbsp;</td>
                                             <td><input id="skmDisableAllInput" type="checkbox" style="width:15px;" <?php echo $iwkDisabledInput;?>> <span style="margin-left:167px;"> &nbsp; | &nbsp; setting will be applied after 2 miutes of display.</span></td>
                                         </tr>
                                         <tr>
                                             <td>Set <strong>virtual keyboard</strong>, US layout:</td>
                                             <td>&nbsp;</td>
                                             <td>
                                                 <input type="radio" name="virtualKeyboardRadio" id="virtualKeyboardSetOff" style="width:15px;" <?php echo $iwkVirtualKeyboardFileOff;?>> Off &nbsp;&nbsp;&nbsp;
                                                 <input type="radio" name="virtualKeyboardRadio" id="virtualKeyboardSetOn" style="width:15px;" <?php echo $iwkVirtualKeyboardFileOn;?>> On <span style="margin-left: 104px;">| &nbsp; </span>
                                             </td>
                                         </tr>
                                         <tr>
                                             <td>Set <strong>HTTP proxy</strong> URL:</td>
                                             <td>&nbsp;</td>
                                             <td><input id="skmHTTPProxy" type="text" value="<?php echo $iwkAppProxy;?>" style="width:180px;"> &nbsp; | &nbsp; specify port, not protocol. Ex: 10.11.12.13:80.</td>
                                         </tr>
                                         <tr>
                                             <td><strong>Halt</strong> system every day at:</td>
                                             <td>&nbsp;</td>
                                             <td><input id="skmSystemHalt" type="text" value="<?php echo $iwkSystemHaltHour;?>" style="width:180px;"> &nbsp; | &nbsp; UTC 24h, hh:mm. Zero (0) to never halt.</td>
                                         </tr>
                                         <!--
                                         <tr>
                                             <td>Cache URL into inserted USB key:</td>
                                             <td>&nbsp;</td>
                                             <td><input id="skmCacheURL" type="checkbox" style="width:12px;"> &nbsp; | &nbsp; while displaying URL, Raspberry Digital Signage will mirror remote site into the first available USB key.</td>
                                         </tr>
                                         <tr>
                                             <td>Play URL from USB if no network available:</td>
                                             <td>&nbsp;</td>
                                             <td><input id="" type="checkbox" style="width:12px;" disabled> &nbsp; | &nbsp; if no network available, Raspberry Digital Signage can display the above URL/domain from cached content. Unimplemented here.</td>
                                         </tr>
                                         -->
                                     </table>
                                 </div>

                                 <!--<div style="margin-top:10px;">
                                     <div class="title" style="margin-top:10px;">Hints</div>
                                     <ul>
                                         <li>
                                             In order to properly function, digital signage resources should not open popups (in this case a full-screen popup would be displayed, with no way to close it).
                                             You can install <a href="https://chrome.google.com/webstore/detail/tabtiles/aaeapgfkbbbdpbfjmpcblemfajmkiddh" target="_blank">tabtiles</a> Chrome extension and persist Chrome settings: it will show an useful pseudo-navigation menu.
                                         </li>
                                     </ul>
                                 </div>-->
                             </div>

                             <p style="margin-top:20px; margin-bottom:10px; height:26px; padding-top:10px; text-align:center;">
                                 <a href="#." onClick="kioskModeSet(false); document.getelementById('kmsStart').style.display='none';"><span id="kmsStart" class="title" style="font-size:30px;">Start Hospitalitek Display Manager.</span></a>
                             </p>
                         </div>
                     </div>
                 </div>
		</div></center>

             <!-- DONATION TAB
             <div id="donationTab">
                 <div dojoType="dijit.layout.ContentPane" href="" title="<strong><span style='color:#D00;'>Full version</span></strong>" refreshOnShow="false" style="padding:15px;">
                     <div class="title">
                         <strong>Raspberry Digital Signage donors version</strong>
                     </div>
                     <div style="margin-top:10px;">
                         <p><img src="Images/paypal.jpg"></p>
                         <p>If you like this project, please donate, within <a href="http://www.binaryemotions.com/raspberry-digital-signage-download" target="_blank">project page</a>. Project donors will receive a <strong>special build</strong> which features:</p>
                         <p><strong>[SYSTEM]</strong></p>
                         <p>
                            <ul>
                                <li><strong>no screensaver or screen blanking</strong> active on system by default;</li>
                                <li><strong>screen resolution</strong> settings;</li>
                                <li><strong>screen rotation</strong> (normal, inverted, left, right) for supported hardware;</li>
                                <li>admin <strong>password (with password management)</strong>, which protects the modify of the operating system settings at boot in the admin interface (this one).</li>
                                <li>SSH <strong>remote management</strong> (root password = admin password);</li>
                            </ul>
                        </p>
                        <p><strong>[WEB KIOSK]</strong></p>
                        <p>
                            <ul>
                                <li><strong>reset browser</strong> after specified user inactivity, <strong>force reloading</strong> of web page content and <strong>system halt</strong> enabled;</li>
                                <li>customizable HTTP <strong>proxy</strong>;</li>
                                <li><strong>persistent Chrome view</strong> (you can add extensions and save settings and cookies for this kiosk view, so that you can fully personalize browser behaviour).</li>
                             </ul>
                        </p>
                     </div>
                 </div>
             </div> -->
        </div>

        <div style="padding-top:10px; color:white;">
            <strong>&raquo; Unlock settings modify</strong>: <input id="adminPassword" type="password" value="<?php if ($passwordDisabled) echo "no-passwd";?>" style="padding-left:5px; height:20px; width:105px;" <?php if ($passwordDisabled) echo "disabled";?>> &nbsp; | &nbsp; <a href="#." onClick="modifyPassword(false);">change password</a> | <a href="#." onClick="modifyPassword(true);">remove password</a>.
        </div>
    </div>
</body>
</html>