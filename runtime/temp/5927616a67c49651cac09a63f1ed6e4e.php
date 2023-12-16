<?php /*a:2:{s:69:"/home/gemxpbra/public_html/application/admin/view/admin/user_add.html";i:1697722712;s:89:"/home/gemxpbra/public_html/application/admin/view/../../../public/static/common/base.html";i:1697722706;}*/ ?>
<!DOCTYPE html>
<html class="x-admin-sm">
<head>
    <meta charset="UTF-8">
    <title>管理员添加</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width,user-scalable=yes, minimum-scale=0.4, initial-scale=0.8,target-densitydpi=low-dpi" />
    <link rel="stylesheet" href="/../static/css/font.css">
    <link rel="stylesheet" href="/../static/css/xadmin.css">
    <script type="text/javascript" src="/../static/js/jquery.min.js"></script>
    <script src="/../static/lib/layui/layui.js" charset="utf-8"></script>
    <script type="text/javascript" src="/../static/js/xadmin.js"></script>
    <!--[if lt IE 9]>
    <script src="/../static/js/html5.min.js"></script>
    <script src="/../static/js/respond.min.js"></script>
    <![endif]-->
    <style>
        .nodata_td {
            text-align: center;
        }
    </style>
    
</head>
<body>

<div class="layui-fluid">
    <div class="layui-row">
        <form class="layui-form" action="<?php echo url('admin/userAddPost'); ?>">
            <div class="layui-form-item">
                <label for="username" class="layui-form-label">
                    <span class="x-red">*</span>昵称
                </label>
                <div class="layui-input-inline">
                    <input type="text" id="username" name="username" lay-verify="required|username" autocomplete="off" class="layui-input">
                </div>
                <div class="layui-form-mid layui-word-aux">
                    可用于后台登录
                </div>
            </div>
            <div class="layui-form-item">
                <label for="phone" class="layui-form-label">
                    <span class="x-red">*</span>手机
                </label>
                <div class="layui-input-inline">
                    <input type="text" id="phone" name="phone" lay-verify="required|phone" autocomplete="off" class="layui-input">
                </div>
                <div class="layui-form-mid layui-word-aux">
                    可用于后台登录
                </div>
            </div>
            <div class="layui-form-item">
                <label for="email" class="layui-form-label">
                    邮箱
                </label>
                <div class="layui-input-inline">
                    <input type="text" id="email" name="email" lay-verify="email" autocomplete="off" class="layui-input">
                </div>
                <div class="layui-form-mid layui-word-aux">
                    可用于后台登录
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">角色</label>
                <div class="layui-input-block">
                    <?php if(is_array($roles) || $roles instanceof \think\Collection || $roles instanceof \think\Paginator): $i = 0; $__LIST__ = $roles;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$role): $mod = ($i % 2 );++$i;if($role['id'] != 1): ?>
                    <input type="checkbox" name="role" lay-skin="primary" title="<?php echo htmlentities($role['name']); ?>" value="<?php echo htmlentities($role['id']); ?>">
                    <?php endif; ?>
                    <?php endforeach; endif; else: echo "" ;endif; ?>
                </div>
            </div>
            <div class="layui-form-item">
                <label for="password" class="layui-form-label">
                    <span class="x-red">*</span>密码
                </label>
                <div class="layui-input-inline">
                    <input type="password" id="password" name="password" lay-verify="required|password" autocomplete="off" class="layui-input">
                </div>
                <div class="layui-form-mid layui-word-aux">
                    6到16个字符
                </div>
            </div>
            <div class="layui-form-item">
                <label for="rpassword" class="layui-form-label">
                    <span class="x-red">*</span>确认密码
                </label>
                <div class="layui-input-inline">
                    <input type="password" id="rpassword" name="rpassword" lay-verify="required|rpassword" autocomplete="off" class="layui-input">
                </div>
                <div class="layui-form-mid layui-word-aux">
                    请确认密码
                </div>
            </div>
            
            <div class="layui-form-item">
                <label for="rpassword" class="layui-form-label">
                    绑定会员账号
                </label>
                <div class="layui-input-inline">
                    <input type="text" id="member_name" name="member_name" autocomplete="off" class="layui-input">
                </div>
                <div class="layui-form-mid layui-word-aux">
                    用于团队后台
                </div>
            </div>
            
            <div class="layui-form-item">
                <label class="layui-form-label">
                </label>
                <button  class="layui-btn" lay-filter="user_add" lay-submit="">
                    增加
                </button>
            </div>
        </form>
    </div>
</div>

</body>

<script>
    layui.use(['form', 'layer'], function() {
        $ = layui.jquery;
        let form = layui.form,
            layer = layui.layer;

        //自定义验证规则
        form.verify({
            username: function(value) {
                if (value.length < 2) {
                    return '昵称至少2个字符';
                }
            },
            email: function (value) {
                if(value !== ''){
                    if(!/^([a-zA-Z]|[0-9])(\w|\-)+@[a-zA-Z0-9]+\.([a-zA-Z]{2,4})$/.test(value)){
                        return '邮箱格式不正确';
                    }
                }
            },
            password: [/(.+){6,12}$/, '密码必须6到12位'],
            rpassword: function(value) {
                if ($('#password').val() != $('#rpassword').val()) {
                    return '两次密码不一致';
                }
            }
        });

        //监听提交
        form.on('submit(user_add)', function(data) {
            let role = [];
            $("input:checkbox[name='role']:checked").each(function(i){
                role[i] = $(this).val();
            });
            $.ajax({
                url: data.form.action,
                type: 'post',
                data: {
                    username: data.field.username,
                    phone: data.field.phone,
                    email: data.field.email,
                    role: role,
                    password: data.field.password,
                    rpassword: data.field.rpassword,
                    member_name: data.field.member_name
                },
                success: function (data) {
                    if(data.code === 1){
                        layer.msg(data.msg);
                        setTimeout(function(){
                            parent.location.reload();
                        }, 500);
                    }else {
                        layer.msg(data.msg);
                    }
                }
            });
            return false;
        });
    });
</script>

</html>