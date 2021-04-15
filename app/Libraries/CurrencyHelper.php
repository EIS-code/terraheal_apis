<?php

namespace App\Libraries;

use App\Currency;

class CurrencyHelper
{
    const EUROPEAN_EURO     = 'EUR';
    const US_DOLLAR         = 'USD';
    const JAPANESE_YEN      = 'JPY';
    const BULGARIAN_LEV     = 'BGN';
    const CZECH_KORUNA      = 'CZK';
    const DANISH_KRONE      = 'DKK';
    const POUND_STERLING    = 'GBP';
    const HUNGARIAN_FORINT  = 'HUF';
    const LITHUANIAN_LITAS  = 'LTL';
    const LATVIAN_LATS      = 'LVL';
    const POLISH_ZLOTY      = 'PLN';
    const NEW_ROMANIAN_LEU  = 'RON';
    const SWEDISH_KRONA     = 'SEK';
    const SWISS_FRANC       = 'CHF';
    const NORWEIGIAN_KRONE  = 'NOK';
    const CROATIAN_KUNA     = 'HRK';
    const RUSSIAN_ROUBLE    = 'RUB';
    const TURKISH_LIRA      = 'TRY';
    const AUSTRALIAN_DOLLAR = 'AUD';
    const BRASILIAN_REAL    = 'BRL';
    const CANADIAN_DOLLAR   = 'CAD';
    const CHINESE_YUAN_RENMINBI = 'CNY';
    const HONG_KONG_DOLLAR  = 'HKD';
    const INDONESIAN_RUPIAH = 'IDR';
    const ISRAELI_SHEKEL    = 'ILS';
    const INDIAN_RUPEE      = 'INR';
    const SOUTH_KOREAN_WON  = 'KRW';
    const MEXICAN_PESO      = 'MXN';
    const MALAYSIAN_RINGGIT = 'MYR';
    const NEW_ZEALAND_DOLLAR = 'NZD';
    const PHILIPPINE_PESO   = 'PHP';
    const SINGAPORE_DOLLAR  = 'SGD';
    const THAI_BAHT         = 'THB';
    const SOUTH_AFRICAN_RAND = 'ZAR';

    const USE_CENTRAL_EUROPEAN_BANK = 'BCE';
    const USE_GOOGLE = 'Google';
    const USE_YAHOO = 'Yahoo';
    public $useCurrencyTable = '';

    /**
     * Constants to return info of a currency code
     */
    const CURRENCY_INFO_NAME = 0;
    const CURRENCY_INFO_HEX = 1;
    const CURRENCY_INFO_ALL = 2;
    const CURRENCY_NO_INFO = -1;

    /**
     *
     * @var string $uri the URL to access the daily rates from
     */
    protected $uri = 'http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';

    /**
     *
     * @var string $yahoo the Yahoo URL to make the conversion
     */
    protected $yahoo = 'http://quote.yahoo.com/d/quotes.csv?s={FROM}{TO}=X&f=l1&e=.csv';

    /**
     *
     * @var string $google the Google URL to make the conversion
     */
    protected $google = 'http://www.google.com/ig/calculator?hl=en&q={AMOUNT}{FROM}=?{TO}';

    /**
     *
     * @var array $currencies the loaded currency rates
     */
    protected $currencies = array();

    /**
     *
     * @var array currency information for currency symbols
     */
    protected static $countriesCurrrency = array(
        'ALL' => array('Albania Lek', '&#76;&#101;&#107;'),
        'AFN' => array('Afghanistan Afghani', '&#1547;'),
        'ARS' => array('Argentina Peso', '&#36;'),
        'AWG' => array('Aruba Guilder', '&#402;'),
        'AUD' => array('Australia Dollar', '&#36;'),
        'AZN' => array('Azerbaijan New Manat', '&#1084;&#1072;&#1085;'),
        'BSD' => array('Bahamas Dollar', '&#36;'),
        'BBD' => array('Barbados Dollar', '&#36;'),
        'BYR' => array('Belarus Ruble', '&#112;&#46;'),
        'BZD' => array('Belize Dollar', '&#66;&#90;&#36;'),
        'BMD' => array('Bermuda Dollar', '&#36;'),
        'BOB' => array('Bolivia Boliviano', '&#36;&#98;'),
        'BAM' => array('Bosnia and Herzegovina Convertible Marka', '&#75;&#77;'),
        'BWP' => array('Botswana Pula', '&#80;'),
        'BGN' => array('Bulgaria Lev', '&#1083;&#1074;'),
        'BRL' => array('Brazil Real', '&#82;&#36;'),
        'BND' => array('Brunei Darussalam Dollar', '&#36;'),
        'KHR' => array('Cambodia Riel', '&#6107;'),
        'CAD' => array('Canada Dollar', '&#36;'),
        'KYD' => array('Cayman Islands Dollar', '&#36;'),
        'CLP' => array('Chile Peso', '&#36;'),
        'CNY' => array('China Yuan Renminbi', '&#165;'),
        'COP' => array('Colombia Peso', '&#36;'),
        'CRC' => array('Costa Rica Colon', '&#8353;'),
        'HRK' => array('Croatia Kuna', '&#107;&#110;'),
        'CUP' => array('Cuba Peso', '&#8369;'),
        'CZK' => array('Czech Republic Koruna', '&#75;&#269;'),
        'DKK' => array('Denmark Krone', '&#107;&#114;'),
        'DOP' => array('Dominican Republic Peso', '&#82;&#68;&#36;'),
        'XCD' => array('East Caribbean Dollar', '&#36;'),
        'EGP' => array('Egypt Pound', '&#163;'),
        'SVC' => array('El Salvador Colon', '&#36;'),
        'EEK' => array('Estonia Kroon', '&#107;&#114;'),
        'EUR' => array('Euro Member Countries', '&#8364;'),
        'FKP' => array('Falkland Islands (Malvinas) Pound', '&#163;'),
        'FJD' => array('Fiji Dollar', '&#36;'),
        'GHC' => array('Ghana Cedis', '&#162;'),
        'GIP' => array('Gibraltar Pound', '&#163;'),
        'GTQ' => array('Guatemala Quetzal', '&#81;'),
        'GGP' => array('Guernsey Pound', '&#163;'),
        'GYD' => array('Guyana Dollar', '&#36;'),
        'HNL' => array('Honduras Lempira', '&#76;'),
        'HKD' => array('Hong Kong Dollar', '&#36;'),
        'HUF' => array('Hungary Forint', '&#70;&#116;'),
        'ISK' => array('Iceland Krona', '&#107;&#114;'),
        'INR' => array('India Rupee', '₹'),
        'IDR' => array('Indonesia Rupiah', '&#82;&#112;'),
        'IRR' => array('Iran Rial', '&#65020;'),
        'IMP' => array('Isle of Man Pound', '&#163;'),
        'ILS' => array('Israel Shekel', '&#8362;'),
        'JMD' => array('Jamaica Dollar', '&#74;&#36;'),
        'JPY' => array('Japan Yen', '&#165;'),
        'JEP' => array('Jersey Pound', '&#163;'),
        'KZT' => array('Kazakhstan Tenge', '&#1083;&#1074;'),
        'KPW' => array('Korea (North) Won', '&#8361;'),
        'KRW' => array('Korea (South) Won', '&#8361;'),
        'KGS' => array('Kyrgyzstan Som', '&#1083;&#1074;'),
        'LAK' => array('Laos Kip', '&#8365;'),
        'LVL' => array('Latvia Lat', '&#76;&#115;'),
        'LBP' => array('Lebanon Pound', '&#163;'),
        'LRD' => array('Liberia Dollar', '&#36;'),
        'LTL' => array('Lithuania Litas', '&#76;&#116;'),
        'MKD' => array('Macedonia Denar', '&#1076;&#1077;&#1085;'),
        'MYR' => array('Malaysia Ringgit', '&#82;&#77;'),
        'MUR' => array('Mauritius Rupee', '&#8360;'),
        'MXN' => array('Mexico Peso', '&#36;'),
        'MNT' => array('Mongolia Tughrik', '&#8366;'),
        'MZN' => array('Mozambique Metical', '&#77;&#84;'),
        'NAD' => array('Namibia Dollar', '&#36;'),
        'NPR' => array('Nepal Rupee', '&#8360;'),
        'ANG' => array('Netherlands Antilles Guilder', '&#402;'),
        'NZD' => array('New Zealand Dollar', '&#36;'),
        'NIO' => array('Nicaragua Cordoba', '&#67;&#36;'),
        'NGN' => array('Nigeria Naira', '&#8358;'),
        'KPW' => array('Korea (North) Won', '&#8361;'),
        'NOK' => array('Norway Krone', '&#107;&#114;'),
        'OMR' => array('Oman Rial', '&#65020;'),
        'PKR' => array('Pakistan Rupee', '&#8360;'),
        'PAB' => array('Panama Balboa', '&#66;&#47;&#46;'),
        'PYG' => array('Paraguay Guarani', '&#71;&#115;'),
        'PEN' => array('Peru Nuevo Sol', '&#83;&#47;&#46;'),
        'PHP' => array('Philippines Peso', '&#8369;'),
        'PLN' => array('Poland Zloty', '&#122;&#322;'),
        'QAR' => array('Qatar Riyal', '&#65020;'),
        'RON' => array('Romania New Leu', '&#108;&#101;&#105;'),
        'RUB' => array('Russia Ruble', '&#1088;&#1091;&#1073;'),
        'SHP' => array('Saint Helena Pound', '&#163;'),
        'SAR' => array('Saudi Arabia Riyal', '&#65020;'),
        'RSD' => array('Serbia Dinar', '&#1044;&#1080;&#1085;&#46;'),
        'SCR' => array('Seychelles Rupee', '&#8360;'),
        'SGD' => array('Singapore Dollar', '&#36;'),
        'SBD' => array('Solomon Islands Dollar', '&#36;'),
        'SOS' => array('Somalia Shilling', '&#83;'),
        'ZAR' => array('South Africa Rand', '&#82;'),
        'LKR' => array('Sri Lanka Rupee', '&#8360;'),
        'SEK' => array('Sweden Krona', '&#107;&#114;'),
        'SLL' => array('Sierra Leona Lions', 'Le'),
        'CHF' => array('Switzerland Franc', '&#67;&#72;&#70;'),
        'SRD' => array('Suriname Dollar', '&#36;'),
        'SYP' => array('Syria Pound', '&#163;'),
        'TWD' => array('Taiwan New Dollar', '&#78;&#84;&#36;'),
        'THB' => array('Thailand Baht', '&#3647;'),
        'TTD' => array('Trinidad and Tobago Dollar', '&#84;&#84;&#36;'),
        'TRY' => array('Turkey Lira', '&#84;&#76;'),
        'TRL' => array('Turkey Lira', '&#8356;'),
        'TVD' => array('Tuvalu Dollar', '&#36;'),
        'UAH' => array('Ukraine Hryvna', '&#8372;'),
        'GBP' => array('United Kingdom Pound', '&#163;'),
        'USD' => array('United States Dollar', '&#36;'),
        'UYU' => array('Uruguay Peso', '&#36;&#85;'),
        'UZS' => array('Uzbekistan Som', '&#1083;&#1074;'),
        'VEF' => array('Venezuela Bolivar Fuerte', '&#66;&#115;'),
        'VND' => array('Viet Nam Dong', '&#8363;'),
        'YER' => array('Yemen Rial', '&#65020;'),
        'ZWD' => array('Zimbabwe Dollar', '&#90;&#36;')
    );

    protected $currencyRepo;

    /**
     * Class constructor
     */
    public function __construct()
    {
       $this->currencyRepo      = new Currency();
       $this->useCurrencyTable  = Currency::getTableName();
    }

    public function getDefultCurrency()
    {
        return $this->currencyRepo->getDefultCurrency();
    }

    public function getDefaultShopCurrency($userId, $isId = false)
    {
        return $this->currencyRepo->getDefaultShopCurrency($userId, $isId);
    }

    public function getCodeFromId($id)
    {
        return $this->currencyRepo->getCodeFromId($id);
    }

    /**
     * Returns a specific rate value
     */
    public function getRate($currencyId)
    {
        return $this->currencyRepo->getRate($currencyId);
    }

    /**
     * Returns loaded currency rates
     */
    public function getRates()
    {}

    /**
     * Loads daily currency conversion rates from the European Central Bank
     * @return boolean true if successful, false otherwise
     */
    public function load()
    {}

    /**
     * Converts one currency to another based on the EURO conversion rate
     * @param string $from the currency code to convert from
     * @param string $to the currency code to convert to
     * @param float $amount the amount to convert
     * @return float the converted amount
     */
    public function convert($amount, $exchangeRate, $toId = 0, $fromId = 1, $costPrice = false, $from = '', $to = '', $engine = NULL)
    {
        $engine = (empty($engine)) ? $this->useCurrencyTable : $engine;

        if (
            $engine !== self::USE_CENTRAL_EUROPEAN_BANK 
            && $engine !== self::USE_GOOGLE
            && $engine !== self::USE_YAHOO 
            && $engine !== $this->useCurrencyTable 
            ) {
                // throw new CException ('ECurrencyHelper', 'Unsupported conversion engine');
                return false;
        }

        $result = 0;
        
        if ($engine === self::USE_CENTRAL_EUROPEAN_BANK) {
            $result = $this->euroConvert ($from, $to, $amount);
        } elseif ($engine === $this->useCurrencyTable) {
            // $fromId = $this->getDefultCurrency();
            $result = $this->currencyTableConvert ($fromId, $toId, $amount, $exchangeRate, $costPrice);
        }  elseif ($engine === self::USE_GOOGLE) {
            $result = $this->googleConvert ($from, $to, $amount);
        } else {
            $result = $this->yahooConvert ($from, $to, $amount);
        }

        return $result;
    }

    public function convertToDefaultShopCurrency(int $userId, float $amount, int $fromId)
    {
        $shopCurrencyId = $this->getDefaultShopCurrency($userId, true);
        $exchangeRate   = $this->getRate($shopCurrencyId);

        return $this->convert($amount, $exchangeRate, $shopCurrencyId, $fromId);
    }

    /**
     * Returns specific currency information
     */
    public static function currencyInfo($code, $info = self::CURRENCY_INFO_HEX)
    {}

    /**
     * Converts one currency to another based on the EURO conversion rate
     * @param string $from the currency code to convert from
     * @param string $to the currency code to convert to
     * @param float $amount the amount to convert
     * @return float the converted amount
     */
    protected function euroConvert($from, $to, $amount)
    {
        // load euro exchange rate if needed
        if ($this->currencies == array()) {
            $this->load();  
        }

        if (!array_key_exists($from, $this->currencies) || !array_key_exists($to, $this->currencies)) {
            // throw new CException('ECurrencyHelper','Unsupported currency type');
            return false;
        }

        if ($to === 'EUR') {
            return (float) $amount / $this->currencies[$from];
        }

        if ($from === 'EUR') {
            return (float) $amount * $this->currencies[$to];
        }

        return (float) ($amount / $this->currencies[$from]) * $this->currencies[$to];
    }

    /**
     * Converts one currency to another based on T4F local currency table 
     * notify!! we must create a cron job update exchange rate by hours or minutes
     */
    protected function currencyTableConvert($fromId, $toId, $amount, $exchangeRate, $costPrice = false)
    {
        if ($fromId == $toId) {
            return $amount;
        }

        $fromCurrency = $this->currencyRepo->getCurrencyById($fromId);
        $toCurrency   = $this->currencyRepo->getCurrencyById($toId);

        if ($fromCurrency == null || $toCurrency == null) {
            throw new Exception('unsupported currency code.', 999);
        }

        $fromCurrencyXchangeRate = $fromCurrency->exchange_rate;
        $valueInDefaultCurrency  = bcdiv(floatval($amount) , floatval($fromCurrencyXchangeRate), 4);

        if ($toId == Currency::$defaultCurrencyId) {
            $value = $valueInDefaultCurrency;
        } else {
            $toCurrencyXchangeRate = $toCurrency->exchange_rate;
            // $toCurrencyXchangeRate = $toCurrency['xchg_rate'] * (1 + $toCurrency['markup_rate']/100);
            // $toCurrencyXchangeRate = $amount * $toCurrencyXchangeRate;

            $toCurrencyXchangeRate = number_format($toCurrencyXchangeRate, 4, '.', '');
            $value                 = bcmul($valueInDefaultCurrency, floatval($toCurrencyXchangeRate), 4);
            // $value                 = number_format($value, 2, '.', '');
        }
        $value = number_format($value, 2, '.', '');

        return $value;
    }

    /**
     *
     * Converts one currency to another based on Yahoo finance conversion rate
     * Special thanks to *Aphraoh* for its contribution
     * @param string $from the currency code to convert from
     * @param string $to the currency code to convert to
     * @param float $amount the amount to convert
     * @return float the converted amount false if not successful
     */
    protected function yahooConvert($from, $to, $amount)
    {
        $uri = str_replace(
            array('{FROM}', '{TO}'),
            array($from, $to), $this->yahoo
        );

        $rate = $this->_request($uri);
        return $rate ? (float) $rate * $amount : false;
    }

    /**
     * Converts one currency to another based on the Google conversion calculator
     * @param string $from the currency code to convert from
     * @param string $to the currency code to convert to
     * @param float $amount the amount to convert
     * @return float the converted amount
     */
    protected function googleConvert($from, $to, $amount)
    {
        $uri = str_replace(
            array('{FROM}', '{TO}', '{AMOUNT}'),
            array($from, $to, $amount), $this->google
        );

        $raw  = $this->_request($uri);
        $data = explode('"', $raw);
        $data = explode(' ', $data['3']);
        $v    = trim($data[0]);

        if ($v !== "") {
            return  (float)preg_replace('/[^0-9.]/','',$v);
        } else {
            return false;
        }
    }

    /**
     * Requests a specific url and returns its contents
     * @param string $url the uri to call
     * @return string raw contents
     */
    private function _request($url)
    {
        if (function_exists('curl_version')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER["HTTP_USER_AGENT"]);
            $raw_data = $this->_curl_exec_follow($ch);
            curl_close($ch);
        } else {
            $raw_data = file_get_contents($url);
        }

        return $raw_data;
    }

    /**
     * This function handles redirections with CURL if safe_mode or open_basedir
     * is enabled.
     * @param resource $h the curl handle
     * @param integer $maxredirections
     */
    private function _curl_exec_follow($ch, $maxredirections = 5)
    {
        if (ini_get('open_basedir') == '' && ini_get('safe_mode') == 'Off') {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $maxredirections > 0);
            curl_setopt($ch, CURLOPT_MAXREDIRS, $maxredirections);
        } else {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
            if ($maxredirections > 0)
            {
                $new_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

                $rch = curl_copy_handle($ch);
                curl_setopt($rch, CURLOPT_HEADER, true);
                curl_setopt($rch, CURLOPT_NOBODY, true);
                curl_setopt($rch, CURLOPT_FORBID_REUSE, false);
                curl_setopt($rch, CURLOPT_RETURNTRANSFER, true);
                do {
                    curl_setopt($rch, CURLOPT_URL, $new_url);
                    $header = curl_exec($rch);

                    if (curl_errno($rch)) {
                        $code = 0;
                    } else {
                        $code = curl_getinfo($rch, CURLINFO_HTTP_CODE);

                        if ($code == 301 || $code == 302) {
                            preg_match('/Location:(.*?)\n/', $header, $matches);
                            $new_url = trim(array_pop($matches));
                        } else {
                            $code = 0;
                        }
                    }
                } while ($code && --$maxredirections);

                curl_close($rch);

                if (!$maxredirections)
                {
                    if ($maxredirections === null) {
                        throw new CHttpException(301, 'Too many redirects. When following redirects, libcurl hit the maximum amount.');
                    } else {
                        $maxredirections = 0;
                    }

                    return false;
                }

                curl_setopt($ch, CURLOPT_URL, $new_url);
            }
        }

        return curl_exec($ch);
    }
}
