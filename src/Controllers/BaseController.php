<?php

namespace Andruby\Login\Controllers;

use App\Http\Controllers\Controller;

class BaseController extends Controller
{
    const CODE_SHOW_MSG = 2001; // 弹窗提示
    const CODE_ERROR_CODE = -1; // 失败code码
    const CODE_SUCCESS_CODE = 200; // 成功code码
    const CODE_TOKEN = 400; // token 过期, 需要重新登录

    protected function responseJson($code = self::CODE_SUCCESS_CODE, $message = null, $data = null)
    {
        $response["code"] = $code;
        $response["msg"] = $message;
        if (!empty($data)) {
            $response["data"] = $data;
        }

        $result = " url: " . request()->getUri();
        $result .= " params: " . json_encode(request()->except(['api_sign']));
        $result .= " result: " . json_encode($response);
        deep_login_info($result);

        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode($response));
    }

    protected function genListData($pageNum, $item_data)
    {
        $data["pageNum"] = $pageNum;
        $data["items"] = $item_data;
        return $data;
    }

    protected function response($status)
    {
        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode($status));
    }
}

