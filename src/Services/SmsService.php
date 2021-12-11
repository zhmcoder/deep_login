<?php


namespace Andruby\Login\Services;


use Andruby\Login\Libs\Sms\AliSms;
use Andruby\Login\Models\VerifyCode;
use Andruby\Login\Services\Interfaces\ISmsService;

/**
 * @method static SmsService instance()
 *
 * Class ChargeService
 * @package App\Api\Services
 */
class SmsService implements ISmsService
{
    public static function __callStatic($method, $params): SmsService
    {
        return new self();
    }


    public function sendVerifyCode($mobile, $app_id = 'default_app_id', $client_ip = null)
    {
        $smsRecord = VerifyCode::where('mobile', $mobile)
            ->where('created_at', '>=', strtotime(date('Y-m-d')))
            ->orderBy('id', 'desc')->first();
        $expired = $smsRecord && $smsRecord['status'] == '1'
            && (time() - $smsRecord['created_at']) < config('deep_login.sms_resend_time');
        if ($expired) {
            return true;
        } else {
            if (in_array($mobile, config('deep_login.ignore_mobile'))) {
                $codeInfo['status'] = 200;
                $codeInfo['sms_code'] = config('deep_login.default_sms_code');
            } else {
                $smsSend = config('deep_login.sms_send');
                $smsSend = new $smsSend;
                $codeInfo = $smsSend->sendSMSCode($mobile, $app_id);
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

    public function genImgCode($mobile)
    {
        // TODO: Implement genImgCode() method.
    }

    public function isImgCode($mobile)
    {
        return false;
    }

    public function verifyImgCode($mobile, $img_code)
    {
        // TODO: Implement verifyImgCode() method.
    }
}
