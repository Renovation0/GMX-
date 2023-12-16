<?php /*a:2:{s:85:"/home/gemxpbra/public_html/application/admin/view/product/mutual_aid_member_edit.html";i:1697722712;s:89:"/home/gemxpbra/public_html/application/admin/view/../../../public/static/common/base.html";i:1697722706;}*/ ?>
<!DOCTYPE html>
<html class="x-admin-sm">
<head>
    <meta charset="UTF-8">
    <title>矿机添加</title>
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
        	<input type="num" hidden value="<?php echo htmlentities($info['id']); ?>" id= "order_id" name="order_id">

            <div class="layui-form-item">
                <label for="name" class="layui-form-label">
                    订单编号
                </label>
                <div class="layui-input-inline">
                    <input type="text" value="<?php echo htmlentities($info['orderNo']); ?>" disabled style="background:#C0C0C0;" autocomplete="off" class="layui-input">
                </div>
            </div>
            
            <div class="layui-form-item">
                <label for="tel" class="layui-form-label">
                   持有账号
                </label>
                <div class="layui-input-inline">
                    <input type="text" id="tel" name="tel" value="<?php echo htmlentities($info['tel']); ?>" autocomplete="off" class="layui-input">
                </div>
            </div>
            
            <div class="layui-form-item">
                <label for="get_price" class="layui-form-label">
                   购买时价格
                </label>
                <div class="layui-input-inline">
                    <input type="number" value="<?php echo htmlentities($info['get_price']); ?>" disabled style="background:#C0C0C0;"  autocomplete="off" class="layui-input">
                </div>
            </div>
            
            <div class="layui-form-item">
                <label for="new_price" class="layui-form-label">
                    升值后价格
                </label>
                <div class="layui-input-inline">
                    <input type="number" value="<?php echo htmlentities($info['new_price']); ?>" disabled style="background:#C0C0C0;"  autocomplete="off" class="layui-input">
                </div>
            </div>

            <div class="layui-form-item">
                <label for="rate" class="layui-form-label">
                    升值比例
                </label>
                <div class="layui-input-inline">
                    <input type="number"  value="<?php echo htmlentities($info['rate']); ?>" disabled style="background:#C0C0C0;"  autocomplete="off" class="layui-input">
                </div>
            </div>
            
            <div class="layui-form-item">
                <label for="up_time" class="layui-form-label">
                    已升值次数
                </label>
                <div class="layui-input-inline">
                    <input type="number"  value="<?php echo htmlentities($info['up_time']); ?>" disabled style="background:#C0C0C0;" autocomplete="off" class="layui-input">
                </div>
            </div>

            <div class="layui-form-item">
                <label for="sta_time" class="layui-form-label">
                    创建时间
                </label>
                <div class="layui-input-inline">
                    <input type="text" id="sta_time" name="sta_time" value="<?php if(($info['sta_time'] == 0)): ?>  '' <?php else: ?> <?php echo date('m-d H:i:s',$info['sta_time']); ?> <?php endif; ?>"  disabled style="background:#C0C0C0;" autocomplete="off" class="layui-input">
                </div>
            </div>

            <div class="layui-form-item">
                <label for="power" class="layui-form-label">
                    结束时间
                </label>
                <div class="layui-input-inline">
                    <input type="text" id="end_time" name="end_time" value="<?php if(($info['end_time'] == 0)): else: ?> <?php echo date('m-d H:i:s',$info['end_time']); ?> <?php endif; ?>" disabled style="background:#C0C0C0;" autocomplete="off" class="layui-input">
                </div>
            </div>

            <div class="layui-form-item">
                <label for="power" class="layui-form-label">
                    上次升值时间
                </label>
                <div class="layui-input-inline">
                    <input type="text" id="last_time" name="last_time" value="<?php if(($info['last_time'] == 0)): else: ?> <?php echo date('m-d H:i:s',$info['last_time']); ?> <?php endif; ?>" disabled style="background:#C0C0C0;" autocomplete="off" class="layui-input">
                </div>
            </div>

            <?php if(in_array('/mutualaid/mutualaidmemberedit', (array)session('power_action'))): ?>
            <div class="layui-form-item">
                <label class="layui-form-label"></label>
                <button class="layui-btn" lay-submit lay-filter="role_add">修改</button>
            </div>
            <?php endif; ?>
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
                    url: "<?php echo url('mutualaid/mutualAidMemberEditPost'); ?>",//'/admin/mutualaid/mutualAidMemberEditPost',
                    type: 'post',
                    data: {
                    	order_id: data.field.order_id,
                    	tel: data.field.tel
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