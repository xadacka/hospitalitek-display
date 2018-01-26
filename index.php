<?php

// Is password disabled?
$unlockPwdHashSaved = trim(file_get_contents("/iwk/iwk.adminPasswd"));
if ($unlockPwdHashSaved==trim(md5("no-passwd"))) $passwordDisabled = true;
else $passwordDisabled = false;

// ************************************************************************************************ ?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <title>Hospitalitek Digital Signage</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

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
        dojo.require("dojo.data.ItemFileReadStore");
        dojo.require("dojo.data.ItemFileWriteStore");
        dojo.require("dijit.form.ComboBox");
        dojo.require("dijit.TitlePane");
        dojo.require("dijit.layout.ContentPane");
        dojo.require("dijit.layout.TabContainer");
        dojo.require("dijit.form.FilteringSelect");
        dojo.require("dijit.form.Form");
        dojo.require("dojox.grid.DataGrid");
        dojo.require("dojox.grid.cells.dijit");
        dojo.require("dojox.layout.FloatingPane");

        // Grid layout.
        layoutWifiNetworks = [
                         {name:'SSID', headerStyles:'text-align:center;', field:'ssid', styles:';', width:'40%'},
                         {name:'BSSID', headerStyles:'text-align:center;', field:'bssid', styles:';', width:'30%'},
                         {name:'Rate', headerStyles:'text-align:center;', field:'rate', styles:';'},
                         {name:'Security', headerStyles:'text-align:center;', field:'security', styles:';'},
                         {name:'Signal', headerStyles:'text-align:center;', field:'signal', styles:';'}
                         ];

        dojo.addOnLoad(function()
            {
            dojo.connect(dijit.byId('wifiNetworksGrid'), "onClick", function(item)
                {
                if (item.grid)
                    {
                    var valItem = item.grid.selection.getSelected()[0];
                    document.getElementById('netSSID').value = valItem.ssid;
                    document.getElementById('netSecurity').value = valItem.security;
                    }
                });

            // Initialize network with selected method (if none: DHCP).
            networkInit();
            });

        // JSON store retrivals.
        function __setStore(gridType)
            {
            if (gridType=="wifiNetworks") var uri = "backend.php?target=network&action=wifi-list";
            else return true;

            var store = new dojo.data.ItemFileWriteStore
                ({
                url: uri,
                urlPreventCache: true,
                });

            return store;
            }

        function networkInit()
            {
            waitingState("on");

            dojo.xhr("GET", {
                url: "backend.php?target=network&action=init",
                preventCache: true,
                load: function(data,args)
                    {
                    viewNetworkInfo();
                    },
                timeout: 32000,
                error: function(error,args)
                    {
                    viewNetworkInfo();
                    }
                });
            }

        function networkSet(/* String */ action, /* String */ ip, /* String */ mask, /* String */ gateway, /* String */ dns, /* String */ ssid, /* String */ password, /* String */ security)
            {
            var unlockpwd = document.getElementById('adminPassword').value;
            if (!unlockpwd)
                {
                alert("In order to change settings, please enter unlock password. ");
                return false;
                }

            waitingState("on");
            document.getElementById('networkLog').innerHTML = "Please wait, trying to initialize network...";

            dojo.xhr("GET", {
                url: "backend.php?target=network&action="+action+"&netIP="+ip+"&netMask="+mask+"&netGateway="+gateway+"&netDNS="+dns+"&netSSID="+encodeURIComponent(ssid)+"&netPassword="+encodeURIComponent(password)+"&netSecurity="+security+"&unlockPwd="+encodeURIComponent(unlockpwd),
                preventCache: true,
                load: function(data,args)
                    {
                    viewNetworkInfo();
                    },
                timeout: 32000,
                error: function(error,args)
                    {
                    viewNetworkInfo();
                    }
                });
            }

        function viewNetworkInfo()
            {
            dojo.xhr("GET", {
                url: "backend.php?target=network&action=info",
                preventCache: true,
                load: function(data,args)
                    {
                    if (data!="")
                        {
                        document.getElementById('networkLog').innerHTML = data;

                        // Autostart on Internet connection OK, go to (other) settings page.
                        if (Utils.strpos(data,"up and running")>0)
                            {
                            document.getElementById('networkTabLeft').style.display = "none";
                            window.setTimeout("document.location.href='settings.php';",8000);
                            }
                        else waitingState("off");
                        }
                    },
                timeout: 30000,
                error: function(error,args) { ; }
                });
            }

        function wifiNetworkList()
            {
            jsonStoreWifiNetworks = __setStore("wifiNetworks");
            dijit.byId('wifiNetworksGrid').setStore(jsonStoreWifiNetworks);
            }

        function waitingState(/* String */ action)
            {
            if (action=="on")
                {
                document.getElementById('networkTabLeft').style.visibility = "hidden";
                document.body.style.cursor = "wait";
                }
            else
                {
                document.getElementById('networkTabLeft').style.visibility = "visible";
                document.body.style.cursor = "default";
                }
            }
    </script>
</head>

<body class="tundra" style="height:88%;">
    <div id="content" style="height:100%;">
         <div dojoType="dijit.layout.TabContainer" style="height:100%;">
             <!-- NETWORK TAB --><img src="logo.png">
             <div dojoType="dijit.layout.ContentPane" href="" title="<strong>Network settings</strong>" refreshOnShow="false" style="padding:15px;">
                 <div id="networkLog" class="title">
                     Please wait, trying to initialize network...
                 </div>

                 <div id="networkTabLeft">
                     <!-- Wired network settings - DHCP/Static -->
                     <div class="title">
                         <strong>Wired network (Ethernet)</strong>
                     </div>
                     <div style="margin-bottom:10px;">
                         <p>Set <strong>wired network</strong>:</p>
                         <p>
                             <ul>
                                 <li>
                                     <a href="#." onClick="document.getElementById('staticIpTable').style.display='none'; networkSet('dhcp','','','','','','','');">use DHCP (default)</a> - system aquires network informations from your router/DHCP server;
                                 </li>
                             </ul>
                             <ul>
                                 <li>
                                     <a href="#." onClick="document.getElementById('staticIpTable').style.display='block';">assing and use a static IP</a>.
                                 </li>
                             </ul>
                         </p>

                         <div id="staticIpTable" style="display:none;">
                             <div style="padding-left:16px;">
                                 <table style="text-align:right;">
                                     <tr>
                                         <td>IP: </td>
                                         <td><input id="netIP" type="text"></td>
                                     </tr>
                                     <tr>
                                         <td>Mask: </td>
                                         <td><input id="netMask" type="text" value="255.255.255.0"></td>
                                     </tr>
                                     <tr>
                                         <td>Gateway: </td>
                                         <td><input id="netGateway" type="text"></td>
                                     </tr>
                                     <tr>
                                         <td>DNS: </td>
                                         <td><input id="netDNS" type="text" value="8.8.8.8"></td>
                                     </tr>
                                 </table>
                             </div>
                             <p>
                                 <ul style="padding-left:63px;">
                                     <li><a href="#." onClick="if (document.getElementById('netIP').value!='' && document.getElementById('netMask').value!='' && document.getElementById('netGateway').value!='' && document.getElementById('netDNS').value!='') { networkSet('static',document.getElementById('netIP').value,document.getElementById('netMask').value,document.getElementById('netGateway').value,document.getElementById('netDNS').value,'','',''); } else alert('Incomplete data. ');">use the above parameters</a>.</li>
                                 </ul>
                             </p>
                          </div>
                     </div>

                     <!-- Wireless network settings -->
                     <div class="title">
                         <strong>Wireless networks (802.11) near you</strong>
                     </div>
                     <div style="margin-bottom:10px;">
                         <ul>
                             <li>
                                 <a href="#." onClick="wifiNetworkList('');">List wireless networks</a>:
                             </li>
                         </ul>
                         <div dojoType="dojox.grid.DataGrid" jsid="wifiNetworksGrid" id="wifiNetworksGrid" query="{ bssid: '*' }" rowsPerPage="5" style="width:95%; height:120px; margin-top:5px;" structure="layoutWifiNetworks"></div>

                         <p>Please click on your network in the above list and input connection password:</p>
                         <p style="margin-top:20px;">
                             <input id="netSSID" type="text" style="width:150px;" readonly> &nbsp;
                             <input id="netSecurity" type="text" style="width:50px;" readonly> &nbsp;
                             <input id="netPassword" type="text" style="width:150px;"> &nbsp; | &nbsp;
                             <a href="#." onClick="if (document.getElementById('netSSID').value!='') { networkSet('wifi','','','','',document.getElementById('netSSID').value,document.getElementById('netPassword').value,document.getElementById('netSecurity').value); }">connect</a>
                         </p>
                     </div>
                 </div>
             </div>
        </div>

        <div style="padding-top:10px; color:white;">
            <strong>&raquo; Unlock settings modify</strong>: <input id="adminPassword" type="password" value="<?php if ($passwordDisabled) echo "no-passwd";?>" style="padding-left:5px; height:20px; width:105px;" <?php if ($passwordDisabled) echo "disabled";?>>.
        </div>
    </div>
</body>
</html>
