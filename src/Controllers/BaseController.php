<?php

namespace Andruby\Login\Controllers;

use App\Http\Controllers\Controller;

class BaseController extends Controller
{
    /**
     * 成功code码
     */
    public $STATUS_SUCCESS = 0;

    /**
     * 失败code码
     */
    public $STATUS_FAILED = 1;

    protected function responseJson($status = -1, $msg = null, $data = null)
    {
        $response["status"] = $status;
        $response["msg"] = $msg;
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

