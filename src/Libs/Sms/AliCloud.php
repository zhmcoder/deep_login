<?php


namespace Andruby\Login\Libs\Sms;


use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;

class AliCloud
{
    public static function get_mobile($accessToken = '')
    {
        $mobile = null;
        try {
            AlibabaCloud::accessKeyClient(config('deep_login.ali_sms.key_id'),
                config('deep_login.ali_sms.key_secret'))
                ->regionId('cn-hangzhou')
                ->asDefaultClient();
            $result = AlibabaCloud::rpc()
                ->product('Dypnsapi')
                ->scheme('https') // https | http
                ->version('2017-05-25')
                ->action('GetMobile')
                ->method('POST')
                ->host('dypnsapi.aliyuncs.com')
                ->options([
                    'query' => [
                        'RegionId' => "cn-hangzhou",
                        'AccessToken' => $accessToken,
                    ],
                ])
                ->request();
            $result = $result->toArray();
            if ($result['Message'] == 'OK') {
                $mobile = $result['GetMobileResultDTO']['Mobile'];
            } else {
                error_log_info('ali login error result = ' . json_encode($result) . ' access_token = ' . $accessToken);
            }
        } catch (ClientException $e) {
            error_log_info('ali login error msg = ' . $e->getErrorMessage());
        } catch (ServerException $e) {
            error_log_info('ali login error msg = ' . $e->getErrorMessage());
        }
        return $mobile;
    }

    public static function send_sms($mobile, $sign_name, $template_code, $sms_code)
    {
        AlibabaCloud::accessKeyClient(config('deep_login.ali_sms.access_key_id'),
            config('deep_login.ali_sms.access_key_secret'))
            ->regionId('cn-hangzhou')
            ->asDefaultClient();

        $status['status'] = -1;

        try {

            $param['code'] = $sms_code;
            $result = AlibabaCloud::rpc()
                ->product('Dysmsapi')
                // ->scheme('https') // https | http
                ->version('2017-05-25')
                ->action('SendSms')
                ->method('POST')
                ->host('dysmsapi.aliyuncs.com')
                ->options([
                    'query' => [
                        'RegionId' => "cn-hangzhou",
                        'PhoneNumbers' => $mobile,
                        'SignName' => $sign_name,
                        'TemplateCode' => $template_code,
                        'TemplateParam' => json_encode($param),
                    ],
                ])
                ->request();
            if ($result) {
                $result = $result->toArray();
                if ($result['Code'] == 'OK') {
                    $status['status'] = '200';
                    $status['sms_code'] = $param['code'];
                } elseif ($result['code'] = 'isv.BUSINESS_LIMIT_CONTROL') {
                    error_log_info('ali send sms request limit ' . ' mobile = ' . $mobile);
                    $status['status'] = 4;
                } else {
                    error_log_info('ali send sms request msg = ' . json_encode($result) . ' mobile = ' . $mobile);
                    $status['status'] = -1;
                }
            } else {
                error_log_info('ali send sms request error msg' . ' mobile = ' . $mobile);
                $status['status'] = -1;
            }
        } catch (ClientException $e) {
            error_log_info('ali send sms error msg = ' . $e->getErrorMessage() . ' mobile = ' . $mobile);
        } catch (ServerException $e) {
            error_log_info('ali send sms error msg = ' . $e->getErrorMessage() . ' mobile = ' . $mobile);
        }
        return $status;
    }
}
