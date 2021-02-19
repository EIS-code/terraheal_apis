<?php

function createToken()
{
    return bin2hex(random_bytes(16));
}

function jsonDecode($string, $assoc = FALSE)
{
    if (!empty($string)) {
        $result = json_decode($string, $assoc);

        if (json_last_error() === JSON_ERROR_NONE || json_last_error() === 0) {
            return $result;
        }
    }

    return false;
}

function isMultidimentional(array $input)
{
    $check = array_filter($input, 'is_array');

    if (count($check) > 0) {
        return true;
    }

    return false;
}

function inArrayRecursive(string $needle, array $haystack, bool $strict = false, bool $returnKey = false)
{
    if (empty($haystack) || !is_array($haystack)) {
        return false;
    }

    foreach ($haystack as $index => $item) {
        if (
            ($strict ? $item === $needle : $item == $needle) || 
            (is_array($item) && inArrayRecursive($needle, $item, $strict))
        ) {
            return ($returnKey) ? [$index => $item] : $item;
        }
    }

    return false;
}

function toMailContentsUrl($notifiable, $token)
{
    if (!empty(env('APP_URL', false))) {
        $url = rtrim(env('APP_URL'), '/') . '/password/reset/' . $token . '?email=' . $notifiable->getEmailForPasswordReset() . '&model=' . $notifiable::getTableName();
    } else {
        $url = url(route('password.reset', [
            'token' => $token,
            'email' => $notifiable->getEmailForPasswordReset(),
            'model' => $notifiable::getTableName()
        ], false));
    }

    return $url;
}

function cleanUrl(string $url)
{
    if (empty($url)) {
        return $url;
    }

    $url = trim($url, '/');

    // If scheme not included, prepend it
    if (!preg_match('#^http(s)?://#', $url)) {
        $url = 'http://' . $url;
    }

    $urlParts = parse_url($url);

    // Remove trailing or inside multiple slashes.
    $url = preg_replace('/(\/+)/','/',$urlParts['path']);

    // Remove www
    $url = $urlParts['scheme'] . "://" . preg_replace('/^www\./', '', $urlParts['host']) . $urlParts['path'];

    // Replace forward slashes to backward slashes.
    $url = str_ireplace('\\', '/', $url);

    return $url;
}

function generateRandomString($length = 10) {
    $characters         = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    $charactersLength   = strlen($characters);

    $randomString       = '';

    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }

    return $randomString;
}
