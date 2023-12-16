<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

function xdPaySign($data,$key)
{
    ksort($data);
    $str = '';
    foreach ($data as $k => $v)
    {
        if($k != 'sign' && !empty($v)){
            if ($str != '') {
                $str .= '&';
            }
            $str .= $k.'='.$v;
        }
    }
    $str .= "&key={$key}";
    return strtolower(md5($str));
}

//不排序进行md5加密
function sign($signSource,$key) {
    if (!empty($key)) {
         $signSource = $signSource."&key=".$key;
    }
    return     md5($signSource);
}

// 应用公共文件
function md5key($sign_str,$key){
    $sign_str = $sign_str.'&key='.$key;
    return md5($sign_str);
}

//ASCII 升序
function asc_sort($params = array())
{
    if (!empty($params)) {
        $p = ksort($params);
        if ($p) {
            $str = '';
            foreach ($params as $k => $val) {
                $str .= $k . '=' . $val . '&';
            }
            $strs = rtrim($str, '&');
            return $strs;
        }
    }
    return false;
}

function curlpostform($url,$data){
    $ch = curl_init();    
    curl_setopt($ch,CURLOPT_URL,$url); //支付请求地址
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));  
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response=curl_exec($ch);
    curl_close($ch);
    return $response;
}

function curlpostjson($url,$data){
    $ch = curl_init();    
    curl_setopt($ch,CURLOPT_URL,$url); //支付请求地址
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen(json_encode($data)))
    );
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response=curl_exec($ch);
    curl_close($ch);
    return $response;
}

function curlpostjsonheader($url,$data,$header){
    $ch = curl_init();    
    curl_setopt($ch,CURLOPT_URL,$url); //支付请求地址
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response=curl_exec($ch);
    curl_close($ch);
    return $response;
}

// 加密后转为base64编码
function encrypt($originalData)
{
    $rsa_public ='-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCykiRqdO5yiodzEaRLGAXqiUJi
S3xTgvbLbi2Jswba9PqRmG/SFV6WEZLIlxdJUntkNZZ6vJIvfgD4jll2+scz3KiH
QelwSxi6FNFm8r4AOJz5Wd6nDrlkPJx3OIzOJsiwVZ16C3TtyzVFQXuc9i4vPu/L
c2Ygnv3Zd+JiciL16wIDAQAB
-----END PUBLIC KEY-----';
    $crypto = '';
    foreach (str_split($originalData, 117) as $chunk)
    {
        openssl_public_encrypt($chunk, $encryptData, $rsa_public);
        $crypto .= $encryptData;
    }
    return base64_encode($crypto);
}

// base64 post 过来后 '+' 号变成 空格
function decrypt($encryptData)
{
    $rsa_private = '-----BEGIN RSA PRIVATE KEY-----
MIICWwIBAAKBgQCykiRqdO5yiodzEaRLGAXqiUJiS3xTgvbLbi2Jswba9PqRmG/S
FV6WEZLIlxdJUntkNZZ6vJIvfgD4jll2+scz3KiHQelwSxi6FNFm8r4AOJz5Wd6n
DrlkPJx3OIzOJsiwVZ16C3TtyzVFQXuc9i4vPu/Lc2Ygnv3Zd+JiciL16wIDAQAB
AoGAehTpqpJYtpw4sBWekexRlx/R76uVyu5dVYT0wxBPHoCfkcx6nlEXwlcvV83c
ELfAPilYwH6NHsTxWvzO80XNy2WvhUe6R2D9ZEcDfcwdP6fjhw49hSJqCM6X3pec
C+pHt1QyrXt1bKdDboB84YWNdJaAFvoD4I+zRYhT2qM8VXECQQDYlPHzo7U69VtX
R0/4kzWvdKow+7hw4TjS44gbhI2oLurkN/yh0NcIAe/guwAqePWNTmA7RGr0j7BR
SHPtFB5jAkEA0xItiHZzTE9Vx3FGxi2sEd9haohxYp2jQM8OZJSiHbXyjvP+7clo
6kUcKXQZ2Q4cngvGkYc7bizV1Lj2dhI82QJADMDAensySbV22m3NjLKGX7175AR+
eM8aPHi/Y/drK/MPS77sNk8IymTqzg3U1atnshliWzsNHTd0x2R/xv7/RwJAHf2X
OqyZ9V3QcmZGCCK1MFTtIpYAhmKfr7W79c6oulAABw/kSSU1IxRuy/UTNyQqLMq/
jC4K47y7JV6ipmQxAQJAb0g1mh2SUeQvzQ/QkzpKx5Dh0hrojFQ+3a1fvzypMA5q
iaRYe+4DuDXzbWsBmYeyQfbfPE3iZ2zkNIJCQSgN8g==
-----END RSA PRIVATE KEY-----';
	$private_key = openssl_pkey_get_private($rsa_private);
	$crypto = '';
	foreach (str_split(base64_decode($encryptData), 128) as $chunk)
	{
		openssl_private_decrypt($chunk, $decryptData, $private_key);
		$crypto .= $decryptData;
	}
	return $crypto;
}
/**
 * @description  RSA公钥加密 私钥解密
 * @param string $data 待加解密数据
 * @param string $operate 操作类型 encode:加密 decode:解密
 * @return string 返回加密内容/解密内容
 */
function RSA_openssl($data, $operate = 'encode'){
    //RSA 公钥
    $rsa_public = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDaExgNKAVVuwfQvB04mtwX0g6GeTwKHk2wZpJtS5qM22udvVXZ8nx9Et/YoSsQuuT4Q3ZtqjTIFqmLDRsOL7q7ffYe3G92xFThCiAUXBMuIrbgwcRJNj6vmheVRVov6CndorjW4NOUDlhvbttK0NkMhk87haFPL4W9FN2zjKqGSQIDAQAB';

    //RSA 私钥
    $rsa_private = 'MIICeAIBADANBgkqhkiG9w0BAQEFAASCAmIwggJeAgEAAoGBANoTGA0oBVW7B9C8HTia3BfSDoZ5PAoeTbBmkm1Lmozba529VdnyfH0S39ihKxC65PhDdm2qNMgWqYsNGw4vurt99h7cb3bEVOEKIBRcEy4ituDBxEk2Pq+aF5VFWi/oKd2iuNbg05QOWG9u20rQ2QyGTzuFoU8vhb0U3bOMqoZJAgMBAAECgYEAtuO6kRYWW07vAAUz8IwXt7aITgkQ7F97wkxT02vLowRGXdUzUgTGmNKifvizuGU1sGxLvy828vPmnuKP5TbsP9L8Fkj+6bM1boKkk40Tl74uyv/dJel78YE+LOMGpd2Ju6GdEjx6jg9OrSYyPcXjP/bL2SInrKDN+2UV37qyff0CQQD8McuLpeFUvrJbBXXCV3+miUqQZz5tzTaNzzDUSB65Wd1iWPfJEzzswGWo0n5eCZHBIoE1d0IZmXhdQ8CCfdVnAkEA3V1/IndFHklH3iMGlue+ewegMcgt2QLj6bN1MvZsnx2ccMFUVVOqNfyHtpB8fB790rcoszq1PoaV8TAKDipIzwJBAIl3bxbozYGPDNM2j7DmVutlDKLX1Byv7luwI1KjGTQ5OsZf7njJJr16Ri+WxVDm8G8RKtME9Z/UmtpjkuzOQGMCQQCjH5DeLxHp/YpOMXVboq6FLttnk+HlNvIId0v4IAtvPXzYwj6JGjwlyE+hwttZA+V7b6k4WhzRVJANyZ6/TX2VAkB4a42DSFF77lZ5N6yt1+bCIjlC8luDr/0vcdacUoDWp4ahcLadJJn3MsrPITgqwIoXjHPITLlzBuZSiLsuv5ON';

    //RSA 公钥加密
    if ('encode' == $operate) {
        $public_key = "-----BEGIN PUBLIC KEY-----\n" . wordwrap($rsa_public, 64, "\n", true) . "\n-----END PUBLIC KEY-----";
        $key = openssl_pkey_get_public($public_key);
        if (!$key) {
            echo '公钥不可用';
            return '';
        }
        $return_en = openssl_public_encrypt($data, $crypted, $key);
        if (!$return_en) {
            echo '公钥加密失败';
            return '';
        }
        return urlencode(base64_encode($crypted));
    }

    //RSA 私钥解密
    if ('decode' == $operate) {
        $private_key = "-----BEGIN PRIVATE KEY-----\n" . wordwrap($rsa_private, 64, "\n", true) . "\n-----END PRIVATE KEY-----";
        $key = openssl_pkey_get_private($private_key);
        if (!$key) {
            echo '私钥不可用';
            return '';
        }
        $return_de = openssl_private_decrypt(base64_decode($data), $decrypted, $key);
        if (!$return_de) {
            echo '私钥解密失败';
            return '';
        }
        return $decrypted;
    }
    return '';
} 

function AjaxReturn($err_code, $data = []){

    $rs = ['code'=>$err_code,'message'=>getErrorInfo($err_code)];
    $rs['data'] = $data;
    
    return $rs;
}

function getIndaiTime($time){
    return $time-9000;
}

function getValue($name, $type = 'str')
{
    $data = array(' ', '\'', '<', '>', '"', '&lt;', '&gt;', '&quot;', 'script', 'insert', 'delete', 'update', 'select', 'drop', 'exec', 'and', 'or', 'eval');
    
    if ($type == 'array') {
        $value = $name;
        foreach ($value as $key => $i) {
            $value[$key] = str_ireplace($data, '', $i);
        }
    }
    else {
        $value = str_ireplace($data, '', $name);
        
        switch ($type) {
            case 'str':
                $value = strval($value);
                break;
                
            case 'int':
                $value = intval($value);
                break;
                
            case 'float':
                $value = floatval($value);
                break;
        }
    }
    
    return $value;
}
