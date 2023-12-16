<?php /*a:1:{s:66:"/home/gemxpbra/public_html/application/admin/view/login/login.html";i:1697722712;}*/ ?>
<!doctype html>
<html  class="x-admin-sm">
<head>
    <meta charset="UTF-8">
    <title>后台登录—<?php echo htmlentities($log_title); ?></title>
    <meta name="renderer" content="webkit|ie-comp|ie-stand">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width,user-scalable=yes, minimum-scale=0.4, initial-scale=0.8,target-densitydpi=low-dpi" />
    <meta http-equiv="Cache-Control" content="no-siteapp" />
    <link rel="stylesheet" href="/../static/css/font.css">
    <link rel="stylesheet" href="/../static/css/login.css">
    <link rel="stylesheet" href="/../static/css/xadmin.css">
    <script type="text/javascript" src="/../static/js/jquery.min.js"></script>
    <script src="/../static/lib/layui/layui.js" charset="utf-8"></script>
    <!--[if lt IE 9]>
    <script src="/../static/js/html5.min.js"></script>
    <script src="/../static/js/respond.min.js"></script>
    <![endif]-->
</head>
<body class="login-bg">
<div class="login layui-anim layui-anim-up">
    <div class="message"><?php echo htmlentities($log_title); ?></div>
    <div id="darkbannerwrap"></div>
    <form method="post" class="layui-form" action="do_login">
        <input name="username" placeholder="用户名"  type="text" lay-verify="required" class="layui-input" >
        <hr class="hr15">
        <input name="password" placeholder="密码" type="password" lay-verify="required" class="layui-input">
        <hr class="hr15">
        <input value="登录" lay-submit lay-filter="do_login" style="width:100%;" type="submit">
        <hr class="hr20" >
    </form>
</div>
<script>
    $(function  () {
        layui.use('form', function(){
            let form = layui.form;
            form.on('submit(do_login)', function(data){
                $.ajax({
                    url: data.form.action,
                    type: 'post',
                    data: {
                        username: data.field.username,
                        password: data.field.password,
                    },
                    success: function (data) {
                        if(data.code === 1){
                            layer.msg(data.msg);
                            setTimeout(function(){
                                //location.href='../index/index';
                                //location.href="'".U('/index/index')."'";
    	                		location.href = "<?php echo url('index/index'); ?>";
                            }, 500);
                        }else {
                            layer.msg(data.msg);
                        }
                    }
                });
                return false;
            });
        });
    });
</script>
</body>
</html>