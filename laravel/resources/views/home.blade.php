<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>首页 - 毕业作品管理系统</title>
    <style>
        * {margin:0;padding:0;box-sizing:border-box;}
        .header {
            padding:20px;background:#2d8cf0;color:#fff;
            display:flex;justify-content:space-between;align-items:center;
        }
        .content {padding:30px;font-size:18px;color:#333;}
        .btn-logout {
            padding:8px 16px;background:#fff;color:#2d8cf0;
            border:none;border-radius:4px;cursor:pointer;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>毕业作品管理系统</h1>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-logout">登出</button>
        </form>
    </div>
    <div class="content">
        <p>欢迎 {{ Auth::user()->name }}，登录成功！</p>
    </div>
</body>
</html>
