<?php

namespace Andruby\Login\Services\Interfaces;

interface ISmsService
{
    public function sendVerifyCode($mobile, $smsAppId);

    public function getImgCode($username);

    public function verifyCode($mobile, $verify_code);

    public function verifyImgCode($mobile, $img_code);

    public function isImgCode($mobile);
}
