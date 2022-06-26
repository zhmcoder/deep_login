<?php

namespace Andruby\Login\Libs\Utils;

class HttpUtil
{

    /**
     * $_requestUrl
     * 请求url
     *
     * @var string
     */
    protected static $_requestUrl = '';

    /**
     * $_rawResponse
     * 原始的返回信息
     *
     * @var string
     */
    protected static $_rawResponse = '';

    /**
     * $_statusCode
     * 返回http状态码
     *
     * @var string
     */
    protected static $_statusCode = '';

    /**
     * $_reponseHeader
     * 返回http header
     *
     * @var string
     */
    protected static $_reponseHeader = '';

    /**
     * $_timeOut
     * 设置连接主机的超时时间
     *
     * @var int 数量级：秒
     *
     */
    protected static $_timeOut = 10;

    public static function getStatusCode()
    {
        return self::$_statusCode;
    }

    public static function getRequestUrl()
    {
        return self::$_requestUrl;
    }

    public static function getResponseHeader()
    {
        return self::$_reponseHeader;
    }

    /**
     * _sendRequest
     *
     * @param string $url
     *            请求url
     * @param array $paramArray
     *            请求参数
     * @param string $method
     *            请求方法
     * @return
     */
    protected static function _sendRequest($url, $paramArray = null, $method = 'POST',
                                           $header, $userPwd = null)
    {
        $ch = curl_init();

        if ($paramArray) {
            if ($method == 'POST') {
                $paramArray = is_array($paramArray) ? http_build_query($paramArray) : $paramArray;
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $paramArray);
            } else {
                $url .= '?' . http_build_query($paramArray);
            }
        }


        self::$_requestUrl = $url;

        if (!empty($userPwd)) {
            curl_setopt($ch, CURLOPT_USERPWD, $userPwd);
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::$_timeOut);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if (!empty($header)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }

        if (false !== strpos($url, "https")) {
            // 证书
            // curl_setopt($ch,CURLOPT_CAINFO,"ca.crt");
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        $resultStr = curl_exec($ch);
        self::$_statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        self::$_rawResponse = $resultStr;

        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        self::$_reponseHeader = substr($resultStr, 0, $headerSize);
        $body = substr($resultStr, $headerSize);
        curl_close($ch);
        return $body;
    }

    public static function httpGet($url, $paramArray = null, $header = null)
    {
        return self::_sendRequest($url, $paramArray, 'GET', $header);
    }

    public static function httpPost($url, $paramArray = null, $header = null)
    {
        return self::_sendRequest($url, $paramArray, 'POST', $header);
    }

    public static function httpAuth2($url, $paramArray = null, $header = null, $userPwd = null)
    {
        return self::_sendRequest($url, $paramArray, 'POST', $header, $userPwd);
    }
}



