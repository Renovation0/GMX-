<?php /*a:2:{s:72:"/home/gemxpbra/public_html/application/admin/view/member/level_edit.html";i:1697722712;s:89:"/home/gemxpbra/public_html/application/admin/view/../../../public/static/common/base.html";i:1697722706;}*/ ?>
<!DOCTYPE html>
<html class="x-admin-sm">
<head>
    <meta charset="UTF-8">
    <title>等级编辑</title>
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
</style>

</head>
<body>

<div class="layui-fluid">
    <div class="layui-row">
        <form action="" method="post" class="layui-form layui-form-pane">
            <div class="layui-form-item">
                <label for="name" class="layui-form-label" style="width: 180px">
                    <span class="x-red">*</span>等级名
                </label>
                <div class="layui-input-inline">
                    <input type="text" id="name" name="name" value="<?php echo htmlentities($level['name']); ?>" lay-verify="required" autocomplete="off" class="layui-input">
                </div>
            </div>

            <div class="layui-form-item">
                <label for="thumbnail" class="layui-form-label" style="width: 180px">
                    logo
                </label>
                <div class="layui-input-block">
                    <button type="button" class="layui-btn" id="thumbnail">
                        <i class="layui-icon">&#xe67c;</i>上传图片
                    </button>
                    <div class="thumbnail_box" id="thumbnail_box">
                        <img id="thumbnail_img" src="<?php echo htmlentities($level['level_logo']); ?>" alt="" width="100" onclick="$('#thumbnail').click()">
                        <input type="button" class="layui-btn layui-btn-danger" onclick="delete_thumbnail()" value="删除">
                    </div>
                    <input type="hidden" name="thumbnail" value="<?php echo htmlentities($level['level_logo']); ?>" id="thumbnail_value">
                </div>
            </div>             

<!--             <div class="layui-form-item">
                <label for="sell_rate" class="layui-form-label" style="width: 180px">
                    <span class="x-red">*</span>交易手续费率
                </label>
                <div class="layui-input-inline">
                    <input type="number" id="sell_rate" name="sell_rate" value="<?php echo htmlentities($level['sell_rate']); ?>" autocomplete="off" class="layui-input">
                </div>
            </div>   -->         

            <div class="layui-form-item">
                <label for="direct_push" class="layui-form-label" style="width: 180px">
                    <span class="x-red">*</span>直推要求
                </label>
                <div class="layui-input-inline">
                    <input type="number" id="direct_push" name="direct_push" value="<?php echo htmlentities($level['direct_push']); ?>" lay-verify="required" autocomplete="off" class="layui-input">
                </div>
            </div>

            <div class="layui-form-item">
                <label for="pet_assets" class="layui-form-label" style="width: 180px">
                    <span class="x-red">*</span>累计购买金额要求
                </label>
                <div class="layui-input-inline">
                    <input type="number" id="pet_assets" name="pet_assets" value="<?php echo htmlentities($level['pet_assets']); ?>" lay-verify="required" autocomplete="off" class="layui-input">
                </div>
            </div>
            <!--<div class="layui-form-item">-->
            <!--    <label for="one_era" class="layui-form-label" style="width: 180px">-->
            <!--        <span class="x-red">*</span>一代收益比例-->
            <!--    </label>-->
            <!--    <div class="layui-input-inline">-->
            <!--        <input type="number" id="one_era" name="one_era" value="<?php echo htmlentities($level['one_era']); ?>" lay-verify="required" autocomplete="off" class="layui-input">-->
            <!--    </div>-->
            <!--</div>-->
            <!--<div class="layui-form-item">-->
            <!--    <label for="two_era" class="layui-form-label" style="width: 180px">-->
            <!--        <span class="x-red">*</span>二代收益比例-->
            <!--    </label>-->
            <!--    <div class="layui-input-inline">-->
            <!--        <input type="number" id="two_era" name="two_era" value="<?php echo htmlentities($level['two_era']); ?>" lay-verify="required" autocomplete="off" class="layui-input">-->
            <!--    </div>-->
            <!--</div>-->
            <!--<div class="layui-form-item">-->
            <!--    <label for="three_era" class="layui-form-label" style="width: 180px">-->
            <!--        <span class="x-red">*</span>三代收益比例-->
            <!--    </label>-->
            <!--    <div class="layui-input-inline">-->
            <!--        <input type="number" id="three_era" name="three_era" value="<?php echo htmlentities($level['three_era']); ?>" lay-verify="required" autocomplete="off" class="layui-input">-->
            <!--    </div>-->
            <!--</div>-->
            
            <!-- <div class="layui-form-item">
                <label for="team_income_ratio" class="layui-form-label" style="width: 180px">
                    <span class="x-red">*</span>团队收益比例
                </label>
                <div class="layui-input-inline">
                    <input type="number" id="team_income_ratio" name="team_income_ratio" value="<?php echo htmlentities($level['team_income_ratio']); ?>" lay-verify="required" autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-form-item">
                <label for="jf_limit" class="layui-form-label" style="width: 180px">
                    <span class="x-red">*</span>订单显示或隐藏
                </label>
                <div class="layui-input-inline">
                     <input type="checkbox" lay-filter="my_checkbox" name="order_hide" value="2" lay-skin="switch" <?php if($level['order_hide'] == 2): ?> checked <?php endif; ?>>
                </div>
            </div> -->
            
            
            <div class="layui-input-block">
                                </div>
            <div class="layui-form-item">
                <button class="layui-btn" lay-submit lay-filter="role_add">提交</button>
            </div>
        </form>
    </div>
</div>

</body>

<script>
	//初始化图片
	$(function () {
	    if($("#thumbnail_value").val() != ''){
	        $("#thumbnail").hide();
	        $("#thumbnail_box").show();
	    }else{
	    	$("#delete_img").hide();
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



    $(function  () {
        layui.use('form', function(){
            let form = layui.form;
            form.on('submit(role_add)', function(data){
                $.ajax({
                    url: "<?php echo url('member/levelEdit'); ?>",//'/admin/member/levelEdit',
                    type: 'post',
                    data: {
                        id: <?php echo htmlentities($level['id']); ?>,
                        name: data.field.name,
                        thumbnail: data.field.thumbnail,
                        sell_rate: data.field.sell_rate,
                        one_era: data.field.one_era,
                        two_era: data.field.two_era,
                        three_era: data.field.three_era,
                        team_income_ratio: data.field.team_income_ratio,
                        pet_assets: data.field.pet_assets,
                        team_push: data.field.team_push,
                        direct_push: data.field.direct_push
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