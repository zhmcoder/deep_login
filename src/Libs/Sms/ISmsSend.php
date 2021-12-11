<?php


namespace Andruby\Login\Libs\Sms;


interface ISmsSend
{
    function sendSMSCode($mobile, $app_id = 'default_app_id');
}
