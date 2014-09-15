<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Session
 *
 */
class rpSession
{

    /**
     * Retrieve an entry from the flobal session
     * 
     * @param string $key
     * @return string
     */
    public static function getSessionEntry($key)
    {
        return array_key_exists($key, $_SESSION) ? $_SESSION[$key] : null;
    }

    /**
     * Set a ratepay session entry
     * 
     * @param string $key
     * @param string $value
     */
    public static function setRpSessionEntry($key, $value)
    {
        $_SESSION['piRP'][$key] = $value;
    }

    /**
     * Retrieve a ratepay session entry
     * 
     * @param string $key
     * @return string
     */
    public static function getRpSessionEntry($key)
    {
        if (!array_key_exists('piRP', $_SESSION)) {
            $_SESSION['piRP'] = array();
        }

        return array_key_exists($key, $_SESSION['piRP']) ? $_SESSION['piRP'][$key] : null;
    }

    /**
     * Unset the ratepay session
     */
    public static function cleanRpSession()
    {
        unset($_SESSION['piRP']);
    }

    /**
     * Retrieve the selected lang code
     * 
     * @return string
     */
    public static function getLang()
    {
        $lang = 'english';
        if (array_key_exists('lang', $_SESSION)) {
            $lang = $_SESSION['lang'];
        }

        return $lang;
    }

}
