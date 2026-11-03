<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>毕业作品管理系统 - 登录</title>
    <style>
        * {margin:0;padding:0;box-sizing:border-box;font-family:"Microsoft Yahei";}
        .login-wrap {
            width:100vw;height:100vh;
            display:flex;justify-content:center;align-items:center;
            background:#f5f7fa;
        }
        .login-box {
            width:420px;padding:30px;
            background:#fff;border-radius:8px;
            box-shadow:0 2px 12px rgba(0,0,0,0.1);
        }
        .login-box h2 {text-align:center;margin-bottom:30px;color:#333;}
        .form-group {margin-bottom:20px;}
        .form-group label {
            display:block;margin-bottom:8px;
            color:#666;font-size:14px;
        }
        .form-group input {
            width:100%;height:44px;
            padding:0 15px;border:1px solid #e6e6e6;
            border-radius:4px;font-size:14px;
            transition:border-color 0.3s;
        }
        .form-group input:focus {
            border-color:#2d8cf0;outline:none;
        }
        .captcha-group {display:flex;gap:10px;align-items:center;}
        .captcha-group input {flex:1;}
        .captcha-img {
            width:120px;height:44px;
            cursor:pointer;border-radius:4px;
            border:1px solid #e6e6e6;
        }
        .remember-group {
            display:flex;align-items:center;
            margin-bottom:20px;color:#666;
        }
        .remember-group input {width:auto;margin-right:8px;}
        .btn-login {
            width:100%;height:44px;
            background:#2d8cf0;color:#fff;
            border:none;border-radius:4px;
            font-size:16px;cursor:pointer;
        }
        .btn-login:hover {background:#1c7ed6;}
        .error-tip {color:#f56c6c;margin-bottom:15px;padding:10px;background:#fef0f0;border-radius:4px;}
        .success-tip {color:#67c23a;margin-bottom:15px;padding:10px;background:#f0f9eb;border-radius:4px;}
    </style>
</head>
<body>
    <div class="login-wrap">
        <div class="login-box">
            <h2>毕业作品管理系统</h2>

            <!-- 提示信息 -->
            @if (session('success'))
                <div class="success-tip">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="error-tip">
                    @foreach ($errors->all() as $error)
                        {{ $error }}<br>
                    @endforeach
                </div>
            @endif

            <!-- 登录表单 -->
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="form-group">
                    <label for="username">用户名 *</label>
                    <input type="text" id="username" name="username"
                           value="{{ old('username') }}" placeholder="请输入用户名">
                </div>
                <div class="form-group">
                    <label for="password">密码 *</label>
                    <input type="password" id="password" name="password" placeholder="请输入密码">
                </div>
                <div class="form-group">
                    <label for="captcha">验证码 *</label>
                    <div class="captcha-group">
                        <input type="text" id="captcha" name="captcha" placeholder="请输入验证码">
                        <!-- 验证码图片：直接请求后端接口，点击刷新 -->
                        <img src="{{ route('captcha') }}" alt="验证码" class="captcha-img"
                             onclick="this.src='{{ route('captcha') }}?'+Math.random()">
                    </div>
                </div>
                <div class="remember-group">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">记住我</label>
                </div>
                <button type="submit" class="btn-login">登录</button>
            </form>
        </div>
    </div>
</body>
</html>
