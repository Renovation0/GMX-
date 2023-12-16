<?php /*a:2:{s:70:"/home/gemxpbra/public_html/application/admin/view/admin/role_edit.html";i:1697722712;s:89:"/home/gemxpbra/public_html/application/admin/view/../../../public/static/common/base.html";i:1697722706;}*/ ?>
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
    <div class="layui-row"><!-- /admin/admin/roleEditPost -->
        <form action="<?php echo url('admin/roleEditPost'); ?>" method="post" class="layui-form layui-form-pane">
            <input type="hidden" name="id" value="<?php echo htmlentities($role_info['id']); ?>">
            <div class="layui-form-item">
                <label for="name" class="layui-form-label">
                    <span class="x-red">*</span>角色名
                </label>
                <div class="layui-input-inline">
                    <input type="text" id="name" name="name" value="<?php echo htmlentities($role_info['name']); ?>" required="" lay-verify="required" autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-form-item">
                <label for="message" class="layui-form-label">
                    描述
                </label>
                <div class="layui-input-inline layui-input-textarea">
                    <textarea placeholder="请输入内容" id="message" name="message" class="layui-textarea"><?php echo htmlentities($role_info['message']); ?></textarea>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label"></label>
                <button class="layui-btn" lay-submit lay-filter="role_edit">提交</button>
            </div>
        </form>
    </div>
</div>

</body>

<script>
    $(function  () {
        layui.use('form', function(){
            let form = layui.form;
            form.on('submit(role_edit)', function(data){
                $.ajax({
                    url: data.form.action,
                    type: 'post',
                    data: {
                        id: data.field.id,
                        name: data.field.name,
                        message: data.field.message
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