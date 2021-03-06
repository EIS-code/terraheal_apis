<?php

namespace App\Libraries;

/*
 *
 *  QR Image Generator Bundle for Laravel
 * 
 *  This class will allow for easy manipulation and creation of QR codes
 *  @author Tony Lea
 *  @version 1.0
 *
 */

final class QR {

    /**
     * Google API URL
     */
    const API_URL_GOOGLE = "https://chart.googleapis.com/chart?";

    /**
     * Code data
     *
     * @var string $_data
     */
    private $_data;

    /**
     * Bookmark code
     *
     * @param string $title
     * @param string $url
     */
    public function bookmark($title = null, $url = null)
    {
        $this->_data = "MEBKM:TITLE:{$title};URL:{$url};;";
    }

    /**
     * MECARD code
     *
     * @param string $name
     * @param string $address
     * @param string $phone
     * @param string $email
     */
    public function contact($name = null, $address = null, $phone = null, $email = null)
    {
        $this->_data = "MECARD:N:{$name};ADR:{$address};TEL:{$phone};EMAIL:{$email};;";
    }

    /**
     * Create code with GIF, JPG, etc.
     *
     * @param string $type
     * @param string $size
     * @param string $content
     */
    public function content($type = null, $size = null, $content = null)
    {
        $this->_data = "CNTS:TYPE:{$type};LNG:{$size};BODY:{$content};;";
    }

    /**
     * Create code with JSON.
     *
     * @param string $type
     * @param string $size
     * @param string $content
     */
    public function json(string $content = null)
    {
        $this->_data = "{$content}";
    }

    /**
     * Generate QR code image
     *
     * @param int $size
     * @return image output
     */
    public function draw($size = 150)
    {
        $url = self::API_URL_GOOGLE . "choe=UTF-8&chs={$size}x{$size}&cht=qr&chl=" . urlencode($this->_data);

        return '<img src="'.$url.'">';
    }

    /**
     * Generate QR code image
     *
     * @param int $size
     * @return image output
     */
    public function getUrl($size = 150)
    {
        $url = self::API_URL_GOOGLE . "choe=UTF-8&chs={$size}x{$size}&cht=qr&chl=" . urlencode($this->_data);

        return $url;
    }

    /**
     * Email address code
     *
     * @param string $email
     * @param string $subject
     * @param string $message
     */
    public function email($email = null, $subject = null, $message = null)
    {
        $this->_data = "MATMSG:TO:{$email};SUB:{$subject};BODY:{$message};;";
    }

    /**
     * Geo location code
     *
     * @param string $lat
     * @param string $lon
     * @param string $height
     */
    public function geo($lat = null, $lon = null, $height = null)
    {
        $this->_data = "GEO:{$lat},{$lon},{$height}";
    }

    /**
     * Telephone number code
     *
     * @param string $phone
     */
    public function phone($phone = null)
    {
        $this->_data = "TEL:{$phone}";
    }

    /**
     * SMS code
     *
     * @param string $phone
     * @param string $text
     */
    public function sms($phone = null, $text = null)
    {
        $this->_data = "SMSTO:{$phone}:{$text}";
    }

    /**
     * Text code
     *
     * @param string $text
     */
    public function text($text = null)
    {
        $this->_data = $text;
    }

    /**
     * URL code
     *
     * @param string $url
     */
    public function url($url = null)
    {
        $this->_data = preg_match("~^(?:f|ht)tps?://~i", $url) ? $url : "http://{$url}";
    }

    /**
     * Wifi code
     *
     * @param string $type
     * @param string $ssid
     * @param string $password
     */
    public function wifi($type = null, $ssid = null, $password = null)
    {
        $this->_data = "WIFI:T:{$type};S{$ssid};{$password};;";
    }
}
