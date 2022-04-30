<?php

namespace Andruby\Login\Libs\Verify;

class ImgCode
{
    public static function get_img_code($id = '')
    {
        $config = array(
            'seKey' => config('deep_login.aes_key'), // 验证码加密密钥
        );

        $Verify = new Verify($config);
        $Verify->entry($id);
    }

    public static function verify_img_code($id = '', $img_code = null): bool
    {
        $config = array(
            'seKey' => config('deep_login.aes_key'), // 验证码加密密钥
        );

        $Verify = new Verify($config);
        return $Verify->check($img_code, $id);
    }

}



