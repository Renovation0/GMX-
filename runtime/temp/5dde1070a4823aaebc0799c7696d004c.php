<?php /*a:2:{s:66:"/home/gemxpbra/public_html/application/admin/view/notice/edit.html";i:1697722712;s:89:"/home/gemxpbra/public_html/application/admin/view/../../../public/static/common/base.html";i:1697722706;}*/ ?>
<!DOCTYPE html>
<html class="x-admin-sm">
<head>
    <meta charset="UTF-8">
    <title>资讯编辑</title>
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
    .layui-form-selected dl{
        z-index: 99999;
    }
</style>

</head>
<body>

<div class="layui-fluid">
    <div class="layui-row">
        <form class="layui-form" action="" method="post">
            <div class="layui-form-item">
                <label class="layui-form-label">标题<span class="x-red">*</span></label>
                <div class="layui-input-block">
                    <input type="text" name="title" value="<?php echo htmlentities($noticeInfo['n_title']); ?>" required  lay-verify="required|title" placeholder="请输入标题" autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">分类<span class="x-red">*</span></label>
                <div class="layui-input-block">
                    <select name="type" id="type">
                        <option value="0">请选择类型</option>
                        <?php if(is_array($catList) || $catList instanceof \think\Collection || $catList instanceof \think\Paginator): $i = 0; $__LIST__ = $catList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$cat): $mod = ($i % 2 );++$i;?>
                        <option value="<?php echo htmlentities($cat['lx_id']); ?>" <?php if($cat['lx_id'] == $noticeInfo['type']){echo "selected";}?>><?php echo htmlentities($cat['lx_title']); ?></option>
                        <?php endforeach; endif; else: echo "" ;endif; ?>
                    </select>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">缩略图<span class="x-red">*</span></label>
                <div class="layui-input-block">
                    <button type="button" class="layui-btn" id="thumbnail">
                        <i class="layui-icon">&#xe67c;</i>上传图片
                    </button>
                    <div class="thumbnail_box" id="thumbnail_box">
                        <img id="thumbnail_img" src="<?php echo htmlentities($noticeInfo['img']); ?>" alt="" width="100" onclick="$('#thumbnail').click()">
                        <input type="button" class="layui-btn layui-btn-danger" onclick="delete_thumbnail()" value="删除">
                    </div>
                    <input type="hidden" name="thumbnail" value="<?php echo htmlentities($noticeInfo['img']); ?>" id="thumbnail_value">
                </div>
            </div>
            
            <div class="layui-form-item layui-form-text">
                <label for="message" class="layui-form-label">
                    描述
                </label>
                <div class="layui-input-block">
                    <textarea id="description" name="description" class="layui-textarea" required lay-verify="required|description" placeholder="请输入标题"><?php echo htmlentities($noticeInfo['description']); ?></textarea>
                </div>
            </div>
            
            <div class="layui-form-item layui-form-text">
                <label class="layui-form-label">正文<span class="x-red">*</span></label>
                <div class="layui-input-block">
                    <textarea name="content" placeholder="请输入正文" id="content" lay-verify="content"><?php echo htmlentities($noticeInfo['n_text']); ?></textarea>
                </div>
            </div>
            <div class="layui-form-item">
                <div class="layui-input-block">
                    <button  class="layui-btn" lay-filter="news_add" lay-submit="">提交</button>
                    <button type="reset" class="layui-btn layui-btn-primary">重置</button>
                </div>
            </div>
        </form>
    </div>
</div>

</body>

<!-- uedit配置文件 -->
<script type="text/javascript" src="/../static/ueditor/ueditor.config.js"></script>
<!-- 编辑器源码文件 -->
<script type="text/javascript" src="/../static/ueditor/ueditor.all.js"></script>
<script>
    var ue = UE.getEditor('content');

    // 初始化图片
    $(function () {
        if($("#thumbnail_value").val() != ''){
            $("#thumbnail").hide();
            $("#thumbnail_box").show();
        }
    });

    // 图片上传
    layui.use('upload', function(){
        var upload = layui.upload;
        var uploadInst = upload.render({
            elem: '#thumbnail',
            field: 'image',
            accept: 'images',
            url: "<?php echo url('base/uploadImage'); ?>",//'/admin/base/uploadImage',
            data: {module: 'news', folder: '/news/thumbnail'},
            done: function(res){
                if(res.code == 1){
                    layer.msg(res.msg);
                    $("#thumbnail_img").attr('src', res.url);
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

    //日期选择
    layui.use(['laydate','form'], function(){
        var laydate = layui.laydate;
        laydate.render({
            elem: '#show_time',
            type: 'datetime'
        });

    });

    // 表单提交
    layui.use(['form', 'layer'], function() {
        $ = layui.jquery;
        let form = layui.form,
            layer = layui.layer;

        //自定义验证规则
        form.verify({
            title: function(value) {
                if (value.length < 2) {
                    return '标题至少2个字符';
                }
            },
            content: function(value) {
                if (value.length < 2) {
                    return '正文至少2个字符';
                }
            },
        });

        //监听提交
        form.on('submit(news_add)', function(data) {
            let cat = [];
            $("input:checkbox[name='cat']:checked").each(function(i){
                cat[i] = $(this).val();
            });
            $.ajax({
                url: "<?php echo url('notice/edit'); ?>",//data.form.action,
                type: 'post',
                data: {
                    n_id:<?php echo htmlentities($noticeInfo['n_id']); ?>,
                    n_title: data.field.title,
                    type:data.field.type,
                    img: data.field.thumbnail,
                    description: data.field.description,
                    n_text: data.field.content,
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