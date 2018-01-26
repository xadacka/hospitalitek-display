<?php

class Locale
    {
    // ***************************************************************************************************************************
    // PUBLIC METHODS
    // ***************************************************************************************************************************

    // ;

    // ***************************************************************************************************************************
    // PUBLIC STATIC METHODS
    // ***************************************************************************************************************************

    public static function setLocale(/* String */ $locale) /* Boolean */
        {
        // Persistence files.
        $iwkI18nFile = "/iwk/iwk.systemI18n";
        $iwkForceLocale = "/iwk/iwk.localeVars";

        // Write Instant WebKiosk locale "switch" (used by /etc/rc.local).
        if (!self::__setLocaleSwitch($locale)) return false;

        // This will fill /etc/default/locale file (used by /etc/rc.local).
        if ($locale=="cn") $i18n = "zh_CN";
        if ($locale=="cz") $i18n = "cs_CZ";
        if ($locale=="de") $i18n = "de_DE";
        if ($locale=="dk") $i18n = "da_DK";
        if ($locale=="en") $i18n = "en_US";
        if ($locale=="es") $i18n = "es_ES";
        if ($locale=="fr") $i18n = "fr_FR";
        if ($locale=="gb") $i18n = "en_GB";
        if ($locale=="it") $i18n = "it_IT";
        if ($locale=="jp") $i18n = "ja_JP";
        if ($locale=="br") $i18n = "pt_BR";
        if ($locale=="pt") $i18n = "pt_PT";
        if ($locale=="ru") $i18n = "ru_RU";
        if ($locale=="sk") $i18n = "sk_SK";
        if (!Utils::writeFile($iwkI18nFile,"LANG=".$i18n.".UTF-8\n","777")) return false;

        // Force environment variables for non-reboot changements.
        $iwkForceLocaleContent = "export LANG=".$i18n.".UTF-8\n";
        $iwkForceLocaleContent .= "export LC_CTYPE=".$i18n.".UTF-8\n";
        $iwkForceLocaleContent .= "export LC_NUMERIC=".$i18n.".UTF-8\n";
        $iwkForceLocaleContent .= "export LC_TIME=".$i18n.".UTF-8\n";
        $iwkForceLocaleContent .= "export LC_COLLATE=".$i18n.".UTF-8\n";
        $iwkForceLocaleContent .= "export LC_MONETARY=".$i18n.".UTF-8\n";
        $iwkForceLocaleContent .= "export LC_MESSAGES=".$i18n.".UTF-8\n";
        $iwkForceLocaleContent .= "export LC_PAPER=".$i18n.".UTF-8\n";
        $iwkForceLocaleContent .= "export LC_NAME=".$i18n.".UTF-8\n";
        $iwkForceLocaleContent .= "export LC_ADDRESS=".$i18n.".UTF-8\n";
        $iwkForceLocaleContent .= "export LC_TELEPHONE=".$i18n.".UTF-8\n";
        $iwkForceLocaleContent .= "export LC_MEASUREMENT=".$i18n.".UTF-8\n";
        $iwkForceLocaleContent .= "export LC_IDENTIFICATION=".$i18n.".UTF-8\n";

        // Write file (used by /home/pi/.xinitrc).
        if (!Utils::writeFile($iwkForceLocale,$iwkForceLocaleContent,"777")) return false;

        // Relaunch X (xinit).
        $killCommand = "sudo /usr/bin/killall xinit >/dev/null 2>/dev/null"; // www-data has root rights via sudo (no password specify required).
        $sleepCommand = "sleep 3";
        $rebornCommand = "sudo /bin/sh /etc/rc.local >/dev/null 2>/dev/null &";

        @shell_exec($killCommand);
        @shell_exec($sleepCommand);
        @shell_exec($rebornCommand);

        return true;
        }



    private static function __setLocaleSwitch(/* String */ $locale) /* Boolean */
        {
        // Persistence files.
        $localeFile = "/iwk/iwk.locale";

        if (!Utils::writeFile($localeFile,$locale,"777")) return false;

        return true;
        }
    }
?>

