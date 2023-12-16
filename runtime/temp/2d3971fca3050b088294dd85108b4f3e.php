<?php /*a:2:{s:68:"/home/gemxpbra/public_html/application/admin/view/task/task_add.html";i:1697722712;s:89:"/home/gemxpbra/public_html/application/admin/view/../../../public/static/common/base.html";i:1697722706;}*/ ?>
<!DOCTYPE html>
<html class="x-admin-sm">
<head>
    <meta charset="UTF-8">
    <title>互助添加</title>
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
    
<style>
    .thumbnail_box {
        display: none;
    }
	.layui-laydate-content>.layui-laydate-list {
	    padding-bottom: 0px;
	    overflow: hidden;
	}
	.layui-laydate-content>.layui-laydate-list>li{
	    width:50%
	}
	.merge-box .scrollbox .merge-list {
	    padding-bottom: 5px;
	}
</style>

</head>
<body>

<div class="layui-fluid">
    <div class="layui-row">
        <form action="" method="post" class="layui-form layui-form-pane">
        
            <div class="layui-form-item">
                <label for="name" class="layui-form-label" style="width: 150px">
                    <span class="x-red">*</span>任务名称
                </label>
                <div class="layui-input-inline">
                    <input type="text" id="task_name" name="task_name" required="" lay-verify="required" autocomplete="off" class="layui-input">
                </div>
            </div>
            
            <div class="layui-form-item">
                <label for="min_price" class="layui-form-label" style="width: 150px">
                    <span class="x-red">*</span>邀请人数
                </label>
                <div class="layui-input-inline">
                    <input type="number" id="yq_num" name="yq_num"  lay-verify="required" autocomplete="off" class="layui-input">
                </div>
            </div>
            
            <div class="layui-form-item">
                <label for="max_price" class="layui-form-label" style="width: 150px">
                    <span class="x-red">*</span>奖励获得
                </label>
                <div class="layui-input-inline">
                    <input type="number" id="jl_num" name="jl_num"  lay-verify="required" autocomplete="off" class="layui-input">
                </div>
            </div>
            
            <div class="layui-form-item layui-form-text">
                <label for="task_info" class="layui-form-label">
                    描述
                </label>
                <div class="layui-input-block">
                    <textarea id="task_info" name="task_info" class="layui-textarea" required lay-verify="required|task_info" placeholder="请输入描述"></textarea>
                </div>
            </div>
            
            <div class="layui-form-item">
                <label for="status" class="layui-form-label" style="width: 150px">
                    <span class="x-red">*</span>状态
                </label>
                <div class="layui-input-inline layui-show-xs-block">
                    <select name="status" id="status" lay-verify="required">
                        <option value="">请选择</option>
                        <option value="1">开启</option>
                        <option value="2">关闭</option>
                        <option value="3">待开启</option>
                    </select>
                </div>
            </div>

            <?php if(in_array('/task/taskadd', (array)session('power_action'))): ?>
            <div class="layui-form-item">
                <label class="layui-form-label"></label>
                <button class="layui-btn" lay-submit lay-filter="role_add">增加</button>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>

</body>

<script>
	//日期选择
	layui.use(['laydate','form'], function(){
	    var laydate = layui.laydate;
	    laydate.render({
	        elem: '#sta_time',
	        type: 'time',
	        format: 'HH:mm'
	    });
	    laydate.render({
	        elem: '#end_time',
	        type: 'time',
	        format: 'HH:mm'
	    });
	
	});

    // 图片上传
    layui.use('upload', function(){
        var upload = layui.upload;
        var uploadInst = upload.render({
            elem: '#thumbnail',
            field: 'image',
            accept: 'images',
            url: "<?php echo url('base/uploadImage'); ?>",//'/admin/base/uploadImage',
            data: {module: 'mutualaid', folder: '/mutualaid/thumbnail'},
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



    $(function  () {
        layui.use('form', function(){
            let form = layui.form;
            form.on('submit(role_add)', function(data){
                $.ajax({
                    url: "<?php echo url('task/taskAdd'); ?>",//'/admin/mutualaid/mutualaidAddPost',
                    type: 'post',
                    data: {
                    	task_name: data.field.task_name,
                    	victory: data.field.victory,
                    	defeat: data.field.defeat,
                    	task_info: data.field.task_info,
                        status: data.field.status
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