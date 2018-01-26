<?php

$url = file_get_contents("/iwk/iwk.strictKioskURL");

$pageRefreshTime = (int)file_get_contents("/iwk/iwk.strictKioskReloadPageTimer")*1000; // ms.
if ($pageRefreshTime<5000)
    {
    // Min 5 secs. refresh.
    $pageRefreshTime = 5000;
    }

// ************************************************************************************************ ?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <script type="text/javascript">
        function getFrame()
            {
            var frame = document.getElementById('r');
            return frame;
            }

        function reloadframe()
            {
            // Preventcache by Marc Giavarra.
            var rand = Math.random();
            var newUrl = '<?php echo $url;?>';
            var sep  = newUrl.indexOf("?") < 0 ? "?" : "&";
            newUrl += sep + rand;

            getFrame().src=newUrl;
            }

        window.setInterval("reloadframe()",<?php echo $pageRefreshTime;?>);
    </script>

    <frameset cols="0,*" frameborder="0">
        <frame id="l">
        <frame id="r" src="<?php echo $url;?>">
    </frameset>
</html>
