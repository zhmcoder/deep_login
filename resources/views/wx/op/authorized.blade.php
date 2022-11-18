<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>公众号授权</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<style>
    .card {
        margin-top: -300px;
    }

    .card .list-group {
        padding: 50px 100px;
    }

    .text-sm {
        font-size: 14px;
    }

    .mt-10 {
        margin-top: 10px;
    }

    .text-large {
        font-size: 24px;
    }

    .color-orange {
        color: #ee5716;
    }

    .bg-blue {
        background: #3e7bf0;
    }

    .color-blue {
        color: #3e7bf0;
    }

    .color-white {
        color: white !important;
    }

    .border-round {
        border-radius: 50%;
        overflow: hidden;
    }

    .round {
        height: 30px;
        width: 30px;
        text-align: center;
        line-height: 30px;
        box-sizing: border-box;
    }

    .bg-gray {
        background: rgba(164, 163, 163, 0.7);
    }

    .mr-10 {
        margin-right: 10px;
    }

    .progress {
        padding: 30px;
        height: 80px;
        display: flex;
        justify-content: flex-start;
        align-content: center;
        align-items: center;
    }

    .status {
        margin-bottom: 30px;
    }

    .tips-img {
        width: 200px;
        height: 45px;
        /*border: 1px solid #ddd;*/
        margin: 0 auto;
        border-radius: 5px;
        overflow: hidden;
    }
</style>
<body>
<div class="container d-flex align-items-center justify-content-center vh-100">
    <div class="card vw-100">
        <div class="card-header">公众号授权设置</div>
        <ul class="list-group list-group-flush justify-content-center">
            @if(isset($click))
                <span class="text-sm">为确保推广安全性，请同时授权主域名系统和备用域名系统</span>
                <div class="progress mt-10">
                    <div class="round bg-blue border-round color-white mr-10">1</div>
                    <div class="color-blue mr-10">{{ $op }} (主) 授权</div>
                    <div class="mr-10 color-blue"> -------></div>
                    <div class="round bg-gray border-round color-white  mr-10">2</div>
                    <div>完成</div>
                </div>

                <div class="text-bold mt-10">注意事项:</div>
                <view class="tips text-sm">
                    1. 必须是<span class="text-large color-orange">已认证</span> 的 <span
                        class="text-large color-orange">服务号</span>。 <br>
                    2. 使用公众平台绑定的管理员或者长期运营者个人微信号扫码。<br>
                    3. 如有任何疑问，请先联系我们的商务。
                </view>

                <a style="margin-top: 20px; width: 100px"
                   href="{{ $click ?? '#' }}"
                   class="btn btn-sm btn-primary">点击授权</a>
            @else
                <div class="result text-center justify-content-center">
                    <h6 class="status color-orange">{{ $status ? '授权成功' : '授权失败'  }}</h6>

                    <div class="mt-10 tips-img text-center">
                        @if (!$status)
                            <img style="height: 100%;"
                                 src="data:image/png;base64,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"
                                 alt="">
                        @else
                            <img style="height: 100%;"
                                 src="data:image/png;base64,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"
                                 alt="">
                        @endif
                    </div>

                    <div class="mt-10">
                        @if(!empty($title) && !$status)
                            <div class="text-sm" style="margin: 10px 0px">{{ $title }}</div>
                            @if(!empty($tips))
                                <div class="text-sm" style="margin-top: 8px"><small>{{ $tips }}</small></div>
                            @endif
                        @endif

                        <a style="margin-top: 10px; width: 120px" href="{{ $dash_url ?? '#' }}"
                           class="btn btn-sm btn-primary">返回管理后台</a>
                    </div>
                </div>
            @endif
        </ul>
    </div>
</div>
</body>
</html>
