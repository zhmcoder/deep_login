<?php


namespace Andruby\Login\Services\Interfaces;


interface ISmsService
{


    public function sendVerifyCode($mobile);

    public function genImgCode($mobile);

    public function verifyCode($mobile, $verify_code);

    public function verifyImgCode($mobile, $img_code);

    public function isImgCode($mobile);
}