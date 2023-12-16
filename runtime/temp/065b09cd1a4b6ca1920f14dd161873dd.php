<?php /*a:2:{s:73:"/home/gemxpbra/public_html/application/admin/view/member/member_edit.html";i:1697722712;s:89:"/home/gemxpbra/public_html/application/admin/view/../../../public/static/common/base.html";i:1697722706;}*/ ?>
<!DOCTYPE html>
<html class="x-admin-sm">
<head>
    <meta charset="UTF-8">
    <title>管理员编辑</title>
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
        <form class="layui-form" action="<?php echo url('member/userEditPost'); ?>">
            <input type="hidden" name="id" value="<?php echo htmlentities($member['id']); ?>">
            
            <div class="layui-form-item">
                <label for="thumbnail" class="layui-form-label">
                    头像
                </label>
                <div class="layui-input-block">
                    <button type="button" class="layui-btn" id="thumbnail">
                        <i class="layui-icon">&#xe67c;</i>上传图片
                    </button>
                    <div class="thumbnail_box" id="thumbnail_box">
                        <img id="thumbnail_img" src="<?php echo htmlentities($member['u_img']); ?>" alt="" width="100" onclick="$('#thumbnail').click()"><br/>
                        <input id="delete_img" type="button" class="layui-btn layui-btn-danger" onclick="delete_thumbnail()" value="删除">
                    </div>
                    <input type="hidden" name="thumbnail" value="<?php echo htmlentities($member['u_img']); ?>" id="thumbnail_value">            
                </div>
            </div>
            
            <div class="layui-form-item">
                <label class="layui-form-label">等级<span class="x-red">*</span></label>
                <div class="layui-input-inline">
                    <select name="level" id="level">
                        <option value="0">精灵</option>
                        <?php if(is_array($level) || $level instanceof \think\Collection || $level instanceof \think\Paginator): $i = 0; $__LIST__ = $level;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$level): $mod = ($i % 2 );++$i;?>
                        <option value="<?php echo htmlentities($level['id']); ?>" <?php if($level['id'] == $member['level']){echo "selected";}?>><?php echo htmlentities($level['name']); ?></option>
                        <?php endforeach; endif; else: echo "" ;endif; ?>
                    </select>
                </div>
            </div>
            
            <div class="layui-form-item">
                <label for="tel" class="layui-form-label">
                    手机号码
                </label>
                <div class="layui-input-inline">
                    <input type="text" id="tel" name="tel" value="<?php echo htmlentities($member['tel']); ?>"  disabled autocomplete="off" class="layui-input" style="background:#E0E0E0;">
                </div>
                <div class="layui-form-mid layui-word-aux">
                    
                </div>
            </div>
            <div class="layui-form-item">
                <label for="yhuser" class="layui-form-label">
                    <span class="x-red">*</span>昵称
                </label>
                <div class="layui-input-inline">
                    <input type="text" id="yhuser" name="yhuser" value="<?php echo htmlentities($member['user']); ?>" lay-verify="required|yhuser" autocomplete="off" class="layui-input">
                </div>
                <div class="layui-form-mid layui-word-aux">
                   
                </div>
            </div>
            
            <div class="layui-form-item">
                <label for="dlpassword" class="layui-form-label">
                    密码
                </label>
                <div class="layui-input-inline">
                    <input type="password" id="dlpassword" name="dlpassword" lay-verify="dlpassword" autocomplete="off" class="layui-input">
                </div>
                <div class="layui-form-mid layui-word-aux">
                    留空不修改，修改需8到16个字符
                </div>
            </div>
            
            <div class="layui-form-item">
                <label for="rpassword" class="layui-form-label">
                    支付密码
                </label>
                <div class="layui-input-inline">
                    <input type="password" id="rpassword" name="rpassword" lay-verify="rpassword" autocomplete="off" class="layui-input">
                </div>
                <div class="layui-form-mid layui-word-aux">
                    留空不修改，修改需8到16个字符
                </div>
            </div>
            
            <div class="layui-form-item">
                <label for="last_ip" class="layui-form-label">
                    上次登录ip
                </label>
                <div class="layui-input-inline">
                    <input type="text" id="last_ip" name="last_ip" value="<?php echo htmlentities($member['last_ip']); ?>"  disabled class="layui-input"  style="background:#E0E0E0;">
                </div>
                <div class="layui-form-mid layui-word-aux">
                    
                </div>
            </div>
            <div class="layui-form-item">
                <label for="last_time" class="layui-form-label">
                    上次登录时间
                </label>
                <div class="layui-input-inline">
                    <input type="text" id="last_time" name="last_time" value="<?php echo htmlentities($member['last_time']); ?>"  disabled class="layui-input"  style="background:#E0E0E0;">
                </div>
                <div class="layui-form-mid layui-word-aux">
                    
                </div>
            </div>

            <div class="layui-form-item">
                <label for="status" class="layui-form-label">
                    状态
                </label>
                <div class="layui-input-inline" style="width:100px;">                   
                    <input type="radio" name="status" value="1" <?php if($member['status'] == '1'): ?> checked <?php endif; ?>>未激活
       				<input type="radio" name="status" value="2" <?php if($member['status'] == '2'): ?> checked <?php endif; ?>>正常 
       				<input type="radio" name="status" value="3" <?php if($member['status'] == '3'): ?> checked <?php endif; ?>>冻结              
                </div>
                <div class="layui-form-mid layui-word-aux">                   
                </div>
            </div>            
            
            
            <div class="layui-form-item">
                <label class="layui-form-label">
                </label>
                <button  class="layui-btn" lay-filter="user_edit" lay-submit="">
                    提交
                </button>
            </div>
        </form>
    </div>
</div>

</body>

<script>
// 初始化图片
$(function () {
    if($("#thumbnail_value").val() != ''){
        $("#thumbnail").hide();
        $("#thumbnail_box").show();
    }else{
    	$("#delete_img").hide();
    }
    
    if($("#thumbnail_value1").val() != ''){
        $("#thumbnail1").hide();
        $("#thumbnail_box1").show();
    }else{
    	$("#delete_img1").hide();
    }

    if($("#thumbnail_value2").val() != ''){
        $("#thumbnail2").hide();
        $("#thumbnail_box2").show();
    }else{
    	$("#delete_img2").hide();
    }
});


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
            }/*,
            email: function (value) {
                if(value !== ''){
                    if(!/^([a-zA-Z]|[0-9])(\w|\-)+@[a-zA-Z0-9]+\.([a-zA-Z]{2,4})$/.test(value)){
                        return '邮箱格式不正确';
                    }
                }
            },
            password:function (value) {
                if(value !== ''){
                    if(!/(.+){6,12}$/.test(value)){
                        return '密码必须6到12位';
                    }
                }
            },
            rpassword: function(value) {
                if ($('#password').val() != $('#rpassword').val()) {
                    return '两次密码不一致';
                }
            }*/
        });
        
        
        // 图片上传
        layui.use('upload', function(){
            var upload = layui.upload;
            var uploadInst = upload.render({
                elem: '#thumbnail',
                field: 'image',
                accept: 'images',
                url: "<?php echo url('base/uploadImage'); ?>",//'/admin/base/uploadImage',
                data: {module: 'machine', folder: '/machine/thumbnail'},
                done: function(res){
                	console.log(res);
                	
                    if(res.code == 1){
                        layer.msg(res.msg);
                        $("#thumbnail_img").attr('src',res.url);
                        $("#thumbnail_value").val(res.url);
                        $("#thumbnail").hide();
                        $("#thumbnail_box").show();
                    }else {
                        layer.msg(res.msg);
                    }
                },
                error: function(){
                    //请求异常回调
                }
            });
        });

        // 删除缩略图
        function delete_thumbnail()
        {
            $("#thumbnail_box").hide();
            $("#thumbnail").show();
            $("#thumbnail_img").attr('src', '');
            $("#thumbnail_value").val('');
            return false;
        }
        
     // 图片上传
        layui.use('upload', function(){
            var upload = layui.upload;
            var uploadInst = upload.render({
                elem: '#thumbnail1',
                field: 'image',
                accept: 'images',
                url: "<?php echo url('base/uploadImage'); ?>",//'/admin/base/uploadImage',
                data: {module: 'machine', folder: '/machine/thumbnail'},
                done: function(res){
                	console.log(res);
                    if(res.code == 1){
                        layer.msg(res.msg);
                        $("#thumbnail_img1").attr('src',res.url);
                        $("#thumbnail_value1").val(res.url);
                        $("#thumbnail1").hide();
                        $("#thumbnail_box1").show();
                    }else {
                        layer.msg(res.msg);
                    }
                },
                error: function(){
                    //请求异常回调
                }
            });
        });

        // 删除缩略图
        function delete_thumbnail1()
        {
            $("#thumbnail_box1").hide();
            $("#thumbnail1").show();
            $("#thumbnail_img1").attr('src', '');
            $("#thumbnail_value1").val('');
            return false;
        }
        
        
     // 图片上传
        layui.use('upload', function(){
            var upload = layui.upload;
            var uploadInst = upload.render({
                elem: '#thumbnail2',
                field: 'image',
                accept: 'images',
                url: "<?php echo url('base/uploadImage'); ?>",//'/admin/base/uploadImage',
                data: {module: 'machine', folder: '/machine/thumbnail'},
                done: function(res){
                	console.log(res);
                	
                    if(res.code == 1){
                        layer.msg(res.msg);
                        $("#thumbnail_img2").attr('src',res.url);
                        $("#thumbnail_value2").val(res.url);
                        $("#thumbnail2").hide();
                        $("#thumbnail_box2").show();
                    }else {
                        layer.msg(res.msg);
                    }
                },
                error: function(){
                    //请求异常回调
                }
            });
        });

        // 删除缩略图
        function delete_thumbnail2()
        {
            $("#thumbnail_box2").hide();
            $("#thumbnail2").show();
            $("#thumbnail_img2").attr('src', '');
            $("#thumbnail_value2").val('');
            return false;
        }


        
        
		
        //监听提交
        form.on('submit(user_edit)', function(data) {
            let role = [];
            $("input:checkbox[name='role']:checked").each(function(i){
                role[i] = $(this).val();
            });		
            $.ajax({
                url: data.form.action,
                type: 'post',
                data: {
                    id: data.field.id,
                    yhuser: data.field.yhuser,
                    u_img: data.field.thumbnail,
                    cardImg1: data.field.thumbnail1,
                    cardImg2: data.field.thumbnail2,
                    real_name_status: data.field.real_name_status,
                    purchase_status: data.field.purchase_status,
                    status: data.field.status,
                    level: data.field.level,
                    dlpassword: data.field.dlpassword,
                    rpassword: data.field.rpassword,
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