<?php /*a:2:{s:67:"/home/gemxpbra/public_html/application/admin/view/channel/edit.html";i:1697722712;s:89:"/home/gemxpbra/public_html/application/admin/view/../../../public/static/common/base.html";i:1697722706;}*/ ?>
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
        <form class="layui-form" action="<?php echo url('channel/channelEditPost'); ?>">
            <input type="hidden" name="id" value="<?php echo htmlentities($channel['id']); ?>">
            <div class="layui-form-item">
                <label for="tel" class="layui-form-label">
                    名称
                </label>
                <div class="layui-input-inline">
                    <input type="text" id="name" name="name" value="<?php echo htmlentities($channel['name']); ?>"  autocomplete="off" class="layui-input">
                </div>
                <div class="layui-form-mid layui-word-aux">
                    
                </div>
            </div>
            <div class="layui-form-item">
                <label for="tel" class="layui-form-label">
                    备注名称
                </label>
                <div class="layui-input-inline">
                    <input type="text" id="bname" name="bname" value="<?php echo htmlentities($channel['bname']); ?>"  autocomplete="off" class="layui-input" >
                </div>
                <div class="layui-form-mid layui-word-aux">
                    
                </div>
            </div>
            <div class="layui-form-item">
                <label for="withdraw_status" class="layui-form-label">
                    <span class="x-red">*</span>充值开关
                </label>
                <div class="layui-input-inline layui-show-xs-block">
                    <select name="recharge_status" id="recharge_status" lay-verify="required">
                        <option value="1" <?php if($channel['recharge_status'] == '1'): ?> selected <?php endif; ?>>开启</option>
                        <option value="0" <?php if($channel['recharge_status'] == '0'): ?> selected <?php endif; ?>>关闭</option>
                    </select>
                </div>
            </div>
            <div class="layui-form-item">
                <label for="withdraw_status" class="layui-form-label">
                    <span class="x-red">*</span>提现开关
                </label>
                <div class="layui-input-inline layui-show-xs-block">
                    <select name="withdraw_status" id="withdraw_status" lay-verify="required">
                        <option value="1" <?php if($channel['withdraw_status'] == '1'): ?> selected <?php endif; ?>>开启</option>
                        <option value="0" <?php if($channel['withdraw_status'] == '0'): ?> selected <?php endif; ?>>关闭</option>
                    </select>
                </div>
            </div>
            
            <div class="layui-form-item">
                <label for="name" class="layui-form-label">
                    <span class="x-red"></span>充值排序
                </label>
                <div class="layui-input-inline">
                    <input type="text" id="recharge_order" name="recharge_order" value="<?php echo htmlentities($channel['recharge_order']); ?>"  autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-form-item">
                <label for="name" class="layui-form-label">
                    <span class="x-red"></span>提现排序
                </label>
                <div class="layui-input-inline">
                    <input type="text" id="withdraw_order" name="withdraw_order" value="<?php echo htmlentities($channel['withdraw_order']); ?>"  autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-form-item">
                <label for="name" class="layui-form-label" >
                    <span class="x-red"></span>通道url
                </label>
                <div class="layui-input-inline">
                    <input type="text" id="channel_url" name="channel_url" value="<?php echo htmlentities($channel['channel_url']); ?>"  autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-form-item">
                <label for="name" class="layui-form-label" >
                    <span class="x-red"></span>商户号
                </label>
                <div class="layui-input-inline">
                    <input type="text" id="channel_merchant" name="channel_merchant" value="<?php echo htmlentities($channel['channel_merchant']); ?>"  autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-form-item">
                <label for="name" class="layui-form-label" >
                    <span class="x-red"></span>通道编码
                </label>
                <div class="layui-input-inline">
                    <input type="text" id="channel_type" name="channel_type" value="<?php echo htmlentities($channel['channel_type']); ?>"   autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-form-item">
                <label for="name" class="layui-form-label" >
                    <span class="x-red"></span>MD5KEY
                </label>
                <div class="layui-input-inline">
                    <input type="text" id="channel_md5key" name="channel_md5key" value="<?php echo htmlentities($channel['channel_md5key']); ?>"   autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-form-item">
                <label for="name" class="layui-form-label" >
                    <span class="x-red"></span>代付URL
                </label>
                <div class="layui-input-inline">
                    <input type="text" id="df_url" name="df_url" value="<?php echo htmlentities($channel['df_url']); ?>"   autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-form-item">
                <label for="name" class="layui-form-label" >
                    <span class="x-red"></span>代付通道编码
                </label>
                <div class="layui-input-inline">
                    <input type="text" id="df_type" name="df_type" value="<?php echo htmlentities($channel['df_type']); ?>"   autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-form-item">
                <label for="name" class="layui-form-label" >
                    <span class="x-red"></span>代付MD5KEY
                </label>
                <div class="layui-input-inline">
                    <input type="text" id="df_md5key" name="df_md5key" value="<?php echo htmlentities($channel['df_md5key']); ?>"   autocomplete="off" class="layui-input">
                </div>
            </div>
            
            <div class="layui-form-item">
                <label for="name" class="layui-form-label" >
                    <span class="x-red"></span>充值单比最小额度（0无限制）
                </label>
                <div class="layui-input-inline">
                    <input type="text" id="recharge_min" name="recharge_min" value="<?php echo htmlentities($channel['recharge_min']); ?>"   autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-form-item">
                <label for="name" class="layui-form-label" >
                    <span class="x-red"></span>充值单比最大额度（0无限制）
                </label>
                <div class="layui-input-inline">
                    <input type="text" id="recharge_max" name="recharge_max" value="<?php echo htmlentities($channel['recharge_max']); ?>"  autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-form-item">
                <label for="name" class="layui-form-label" >
                    <span class="x-red"></span>提现单比最小额度（0无限制）
                </label>
                <div class="layui-input-inline">
                    <input type="text" id="withdraw_min" name="withdraw_min" value="<?php echo htmlentities($channel['withdraw_min']); ?>" autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-form-item">
                <label for="name" class="layui-form-label">
                    <span class="x-red"></span>提现单比最大额度（0无限制）
                </label>
                <div class="layui-input-inline">
                    <input type="text" id="withdraw_max" name="withdraw_max"  value="<?php echo htmlentities($channel['withdraw_max']); ?>"  autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-form-item">
                <label for="name" class="layui-form-label">
                    <span class="x-red"></span>处理文件
                </label>
                <div class="layui-input-inline">
                    <input type="text" id="bingfile" name="bingfile"  value="<?php echo htmlentities($channel['bingfile']); ?>"  autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">
                </label>
                <button  class="layui-btn" lay-filter="channel_edit" lay-submit="">
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
        form.on('submit(channel_edit)', function(data) {
            $.ajax({
                url: data.form.action,
                type: 'post',
                data: {
                    id: data.field.id,
                    name: data.field.name,
                    bname: data.field.bname,
                    recharge_status: data.field.recharge_status,
                    withdraw_status: data.field.withdraw_status,
                    recharge_order: data.field.recharge_order,
                    withdraw_order: data.field.withdraw_order,
                    channel_url: data.field.channel_url,
                     channel_type: data.field.channel_type,
                      channel_merchant: data.field.channel_merchant,
                       channel_md5key: data.field.channel_md5key,
                        recharge_min: data.field.recharge_min,
                         recharge_max: data.field.recharge_max,
                          withdraw_min: data.field.withdraw_min,
                           withdraw_max: data.field.withdraw_max,
                           bingfile:data.field.bingfile,
                           df_url:data.field.df_url,
                               df_type:data.field.df_type,
                               df_md5key:data.field.df_md5key
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