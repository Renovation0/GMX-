<?php /*a:2:{s:73:"/home/gemxpbra/public_html/application/admin/view/wallet/tmtypecheck.html";i:1697722712;s:89:"/home/gemxpbra/public_html/application/admin/view/../../../public/static/common/base.html";i:1697722706;}*/ ?>
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
        <form class="layui-form" action="<?php echo url('wallet/tmtypecheck'); ?>">
            <input type="hidden" name="id" value="<?php echo htmlentities($member['id']); ?>">

            <div class="layui-form-item">
                <label for="tel" class="layui-form-label">
                    提现金额
                </label>
                <div class="layui-input-inline">
                    <input type="num" id="num" name="num" value="<?php echo htmlentities($member['num']); ?>"  disabled autocomplete="off" class="layui-input" style="background:#E0E0E0;">
                </div>
                <div class="layui-form-mid layui-word-aux">
                    
                </div>
            </div>
            
            <div class="layui-form-item">
                <label class="layui-form-label">选择支付通道<span class="x-red">*</span></label>
                <div class="layui-input-inline">
                    <select name="type" id="type">
                        <option value="0">无</option>
                        <?php if(is_array($channelList) || $channelList instanceof \think\Collection || $channelList instanceof \think\Paginator): $i = 0; $__LIST__ = $channelList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$channel): $mod = ($i % 2 );++$i;?>
                            <option value="<?php echo htmlentities($channel['id']); ?>" <?php if($member['type'] == $channel['id']){echo "selected";}?> ><?php echo htmlentities($channel['name']); ?></option>
                        <?php endforeach; endif; else: echo "" ;endif; ?>
                        <!--<option value="1"<?php if($member['type'] == 1){echo "selected";}?>><?php echo htmlentities($pay1); ?></option>-->
                        <!--<option value="2"<?php if($member['type'] == 2){echo "selected";}?>><?php echo htmlentities($pay2); ?></option>-->
                        <!--<option value="3"<?php if($member['type'] == 3){echo "selected";}?>><?php echo htmlentities($pay3); ?></option>-->
                        <!--<option value="4"<?php if($member['type'] == 4){echo "selected";}?>><?php echo htmlentities($pay4); ?></option>-->
                    </select>
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



    layui.use(['form', 'layer'], function() {
        $ = layui.jquery;
        let form = layui.form,
            layer = layui.layer;

		
        //监听提交
        form.on('submit(user_edit)', function(data) {	
            $.ajax({
                url: data.form.action,
                type: 'post',
                data: {
                    id: data.field.id,
                    type: data.field.type
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