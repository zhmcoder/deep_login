<?php

namespace Andruby\Login\Controllers;

use App\Http\Controllers\Controller;

class BaseController extends Controller
{
    /**
     * 成功code码
     */
    const STATUS_SUCCESS = 200;

    /**
     * 失败code码
     */
    const STATUS_FAILED = -1;

    protected function responseJson($code = self::STATUS_SUCCESS, $message = null, $data = null)
    {
        $response["code"] = $code;
        $response["message"] = $message;
        if (!empty($data)) {
            $response["data"] = $data;
        }
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

