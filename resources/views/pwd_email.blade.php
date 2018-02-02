<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font: 14px "microsoft yahei", Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #35ADDC;
        }
        article, aside, dialog, footer, header, section, footer, nav, figure, menu {
            display: block
        }

        ul, p, h1, h2, h3, h4, h5, h6, dl, dd {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        table {
            border-collapse: collapse;
            border-spacing: 0
        }

        html, body {
            height: 100%;
            width: 100%;
            overflow: hidden;
        }

        * {
            box-sizing: border-box;
        }

        .container {
            width: 100%;
            height: 100%;
        }

        .email-main {
            width: 512px;
            height: 507px;
        }
        .email-head{
            width: 100%;
            height:82px;
            background-color: #191e27;
            border-bottom: 1px solid #5B6271;
        }
        .email-body{
            padding-top:50px;
            padding-left:45px;
            width: 100%;
            height: 430px;
            background-color:#242E42 ;
        }
        .head-text{
            font-size: 36px;
            color: #35ADDC;
            line-height: 82px;
            margin-left: 47px;
        }
        .lebo-msg{
            color: #fff;
        }
        .top{
            margin-top: 20px;
        }
        .top2{
            margin-top: 20px;
        }
        .top3{
            margin-top: 80px;
        }

    </style>
</head>
<body>
<div class="container">
    <div class="email-main">
        <div class="email-head">
            <b class="head-text">LEBO</b>
        </div>
        <div class="email-body">
            <div class="email-content">
                <div class="lebo-msg">
                    <div>
                        <b style="font-size: 18px;">您好 <?php echo $user_name;?> </b>
                    </div>
                    <div class="top">
                        <span> 我们收到了你的请求，请使用下面的验证码来找回你的密码。</span><br/><br/>
                        <span>以下是你的验证码：</span>
                    </div>
                    <div class="top2">
                        <b style="font-size: 20px; color: #35ADDC;margin-left:130px;"><?php echo $code;?></b>
                    </div>
                    <div class="top">
                        <span> 请注意：该验证码将会在60分钟后过期！</span>
                    </div>
                </div>
                <div class="top3">
                    <b style="font-size: 20px;color: #35ADDC;">LEBO团队</b>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>