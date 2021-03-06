<?php

namespace Andruby\Login\Services;

use Andruby\Login\Libs\Verify\ImgCode;
use Andruby\Login\Libs\Verify\Verify;
use Andruby\Login\Models\VerifyCode;
use Andruby\Login\Services\Interfaces\ISmsService;

/**
 * @method static SmsService instance()
 *
 * Class SmsService
 * @package Andruby\Login\Services
 */
class SmsService implements ISmsService
{
    public static function __callStatic($method, $params): SmsService
    {
        return new self();
    }

    public function sendVerifyCode($mobile, $smsAppId = 'default_app_id', $client_ip = null)
    {
        $smsRecord = VerifyCode::where('mobile', $mobile)
            ->where('created_at', '>=', strtotime(date('Y-m-d')))
            ->orderBy('id', 'desc')->first();
        $expired = $smsRecord && $smsRecord['status'] == '1'
            && (time() - $smsRecord['created_at']) < config('deep_login.sms_resend_time');
        if ($expired) {
            return true;
        } else {
            if (in_array($mobile, config('deep_login.ignore_mobile')) || env('SMS_DEV')) {
                $codeInfo['status'] = 200;
                $codeInfo['sms_code'] = config('deep_login.default_sms_code');
            } else {
                $smsSend = config('deep_login.sms_send');
                $smsSend = new $smsSend;
                $codeInfo = $smsSend->sendSMSCode($mobile, $smsAppId);
            }

            if ($codeInfo['status'] == 200) {
                $data = array('mobile' => $mobile, 'code' => $codeInfo['sms_code'],
                    'sendStatus' => 0, 'sendTime' => date('Y-m-d H:i:s'), 'client_ip' => $client_ip);
                VerifyCode::create($data);
                $sendResult = true;
            } else {
                $sendResult = false;
            }
        }
        return $sendResult;
    }


    public function verifyCode($mobile, $verify_code)
    {
        $codeId = VerifyCode::query()->whereRaw('(' . time() . '- created_at)<= 60')
            ->where(['mobile' => $mobile, 'code' => $verify_code, 'status' => VerifyCode::STATUS_WAIT_USE])
            ->value('id');
        if (empty($codeId)) {
            $check_result = false;
            $update_data = array('status' => VerifyCode::STATUS_UNUSED);
            VerifyCode::where(['id' => $codeId])->update($update_data);
        } else {
            $update_data = array('status' => VerifyCode::STATUS_USED);
            VerifyCode::where(['id' => $codeId])->update($update_data);
            $check_result = true;
        }

        return $check_result;
    }

    public function getImgCode($id)
    {
        // $id = md5(config('deep_login.aes_key') . $username);
        ImgCode::get_img_code($id);
    }

    public function isImgCode($mobile)
    {
        return false;
    }

    public function verifyImgCode($id, $img_code)
    {
        $id = md5(config('deep_login.aes_key') . $id);

        $Verify = new Verify();
        return $Verify->check($img_code, $id);
    }
}
