<?php /*a:2:{s:69:"/home/gemxpbra/public_html/application/admin/view/wallet/seebank.html";i:1697722712;s:89:"/home/gemxpbra/public_html/application/admin/view/../../../public/static/common/base.html";i:1697722706;}*/ ?>
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
        <form class="layui-form" action="<?php echo url('bing/channelEditPost'); ?>">
            <input type="hidden" name="id" value="<?php echo htmlentities($bind['id']); ?>">
            <div class="layui-form-item">
                <label for="tel" class="layui-form-label">
                    收款人姓名
                </label>
                <div class="layui-input-inline">
                    <input type="text" id="name" name="name" value="<?php echo htmlentities($bind['name']); ?>"  autocomplete="off" class="layui-input">
                </div>
                <div class="layui-form-mid layui-word-aux">
                    
                </div>
            </div>
            <div class="layui-form-item">
                <label for="tel" class="layui-form-label">
                    所属银行
                </label>
                <div class="layui-input-inline layui-show-xs-block">
                    <select name="bankid">
                        <?php if(is_array($banklist) || $banklist instanceof \think\Collection || $banklist instanceof \think\Paginator): $i = 0; $__LIST__ = $banklist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$bank): $mod = ($i % 2 );++$i;?>
                            <option value="<?php echo htmlentities($bank['id']); ?>" <?php if($bind['bank_code'] == $bank['code']){echo "selected";}?> ><?php echo htmlentities($bank['bankname']); ?></option>
                        <?php endforeach; endif; else: echo "" ;endif; ?>
                    </select>
                </div>
            </div>
            
            <div class="layui-form-item">
                <label for="name" class="layui-form-label">
                    <span class="x-red"></span>银行卡号
                </label>
                <div class="layui-input-inline">
                    <input type="text" id="bank_num" name="bank_num" value="<?php echo htmlentities($bind['bank_num']); ?>"  autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-form-item">
                <label for="name" class="layui-form-label">
                    <span class="x-red"></span>手机号
                </label>
                <div class="layui-input-inline">
                    <input type="text" id="tel" name="tel" value="<?php echo htmlentities($bind['tel']); ?>"  autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-form-item">
                <label for="name" class="layui-form-label" >
                    <span class="x-red"></span>IFSC
                </label>
                <div class="layui-input-inline">
                    <input type="text" id="ifsc" name="ifsc" value="<?php echo htmlentities($bind['ifsc']); ?>"  autocomplete="off" class="layui-input">
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
                    bankid: data.field.bankid,
                    bank_num: data.field.bank_num,
                    tel: data.field.tel,
                    ifsc: data.field.ifsc,
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