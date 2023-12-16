<?php /*a:2:{s:72:"/home/gemxpbra/public_html/application/admin/view/member/member_add.html";i:1697722712;s:89:"/home/gemxpbra/public_html/application/admin/view/../../../public/static/common/base.html";i:1697722706;}*/ ?>
<!DOCTYPE html>
<html class="x-admin-sm">
<head>
    <meta charset="UTF-8">
    <title>角色添加</title>
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
        <form action="" method="post" class="layui-form layui-form-pane">
            <div class="layui-form-item">
                <label for="name" class="layui-form-label" style="width: 150px">
                    <span class="x-red">*</span>用户名
                </label>
                <div class="layui-input-inline">
                    <input type="text" id="name" name="name" required="" lay-verify="required" autocomplete="off" class="layui-input">
                </div>
            </div>

            <div class="layui-form-item">
                <label for="name" class="layui-form-label" style="width: 150px">
                    <span class="x-red">*</span>手机号
                </label>
                <div class="layui-input-inline">
                    <input type="text" id="tel" name="tel" required="" lay-verify="required" autocomplete="off" class="layui-input">
                </div>
            </div>

            <div class="layui-form-item">
                <label for="name" class="layui-form-label" style="width: 150px">
                    <span class="x-red">*</span>推荐人(留空无推荐)
                </label>
                <div class="layui-input-inline">
                    <input type="text" id="parent_tel" name="parent_tel"  autocomplete="off" class="layui-input">
                </div>
            </div>

            <div class="layui-form-item">
                <label for="name" class="layui-form-label" style="width: 150px">
                    <span class="x-red">*</span>密码
                </label>
                <div class="layui-input-inline">
                    <input type="text" id="pass" name="pass" required="" lay-verify="required" autocomplete="off" class="layui-input">
                </div>
            </div>

            <div class="layui-form-item">
                <label for="name" class="layui-form-label" style="width: 150px">
                    <span class="x-red">*</span>支付密码(6位数字)
                </label>
                <div class="layui-input-inline">
                    <input type="text" id="pay_pass" name="pay_pass" required="" lay-verify="required" autocomplete="off" class="layui-input">
                </div>
            </div>

            <div class="layui-form-item">
                <button class="layui-btn" lay-submit lay-filter="role_add">增加</button>
            </div>
        </form>
    </div>
</div>

</body>

<script>
    $(function  () {
        layui.use('form', function(){
            let form = layui.form;
            form.on('submit(role_add)', function(data){
                $.ajax({
                    url: "<?php echo url('member/roleAddPost'); ?>",//'/admin/member/roleAddPost',
                    type: 'post',
                    data: {
                        name: data.field.name,
                        tel: data.field.tel,
                        parent_tel: data.field.parent_tel,
                        pass: data.field.pass,
                        pay_pass: data.field.pay_pass,
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
    });
</script>

</html>