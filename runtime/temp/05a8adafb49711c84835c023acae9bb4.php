<?php /*a:2:{s:68:"/home/gemxpbra/public_html/application/admin/view/system/config.html";i:1697722712;s:89:"/home/gemxpbra/public_html/application/admin/view/../../../public/static/common/base.html";i:1697722706;}*/ ?>
<!DOCTYPE html>
<html class="x-admin-sm">
<head>
    <meta charset="UTF-8">
    <title>系统参数配置</title>
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
    .x-admin-sm .layui-form-pane .layui-form-label {
        height: 38px !important;
        line-height: 20px;
    }
    .x-admin-sm .layui-input, .x-admin-sm .layui-select, .x-admin-sm .layui-textarea {
        height: 38px;
        border-radius: 2px;
    }
    .layui-form-pane .layui-form-item[pane] {
        border-style: unset;
    }
    .layui-form-pane .layui-form-item{
        display:flex;
        align-items:center;
    }
    .layui-form-pane .layui-form-label {
        position:static !important;
        width:auto !important;
        max-width:350px;
        min-width:200px;
        border-width:0 !important;
        flex:1;
        align-self:flex-start;
    }
    .layui-form-pane .layui-form-item[pane]{
    }
    .layui-form-item:first-child .layui-form-label{
        background: #ffffff;
    }
    .layui-tab-content .layui-input-inline {
        min-width:200px;
        margin:0 !important;
        flex:2;
        text-align:center;
        left:0 !important;
    }
    .layui-tab-content .layui-input-inline:last-child{
        align-self:flex-start;
        height: 38px;
        line-height:38px;
    }
    .layui-tab-content .thumbnail_box{
        display:flex;
        flex-direction:column;
        /*padding-left:20px;*/
        align-items:center;
    }
    .layui-tab-content .thumbnail_box .layui-btn{
        width:80px;
        margin-top:10px;
    }
    .layui-tab-content{
        overflow-x: scroll;
    }
    .layui-tab-content .layui-form-item  .layui-input-block{
        width:100%;
        margin:0;
    }
    .layui-input-block .layui-btn{
        width:80px;
        margin:0;
    }
    .layui-card-body{
        overflow-x:scroll;
    }
    .layui-tab-title,.layui-form{
        width:100%;
    }
</style>

</head>
<body>

<div class="x-nav">
    <span class="layui-breadcrumb">
        <a href="javascript:;">系统</a>
        <a href="javascript:;">系统设置</a>
        <a><cite>参数配置</cite></a>
    </span>
    <a class="layui-btn layui-btn-small" style="line-height:1.6em;margin-top:3px;float:right" onclick="location.reload()" title="刷新">
        <i class="layui-icon layui-icon-refresh" style="line-height:30px"></i>
    </a>
</div>
<div class="layui-fluid">
    <div class="layui-row layui-col-space15" >
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-body ">
                    <ul class="layui-tab-title">
                        <li id="module_1" onclick="changeModule('1')">基本设置</li>
                        <li id="module_2" onclick="changeModule('2')">参数配置</li>
                        <li id="module_3" onclick="changeModule('3')">注册设置</li>
                        <!-- <li id="module_4" onclick="changeModule('4')">交易所配置</li> -->
                        <li id="module_5" onclick="changeModule('5')">实名支付</li>
                        <!-- <li id="module_6" onclick="changeModule('6')">转盘配置</li>
                        <li id="module_7" onclick="changeModule('7')">转让</li> -->
                        <li id="module_8" onclick="changeModule('8')">直推收益</li>
                        <li id="module_9" onclick="changeModule('9')">图片配置</li>
                        <!-- <li id="module_10" onclick="changeModule('10')">USDT配置</li> -->
                        <li id="module_11" onclick="changeModule('11')">短信配置</li>
                    </ul>
                    <form class="layui-form layui-form-pane" method="post" action="<?php echo url('system/configEdit'); ?>"> <!-- /admin/system/configEdit -->
                    <div class="layui-tab-content">
                        <div class="layui-tab-item" id="module_menus_box_<?php echo htmlentities($my_active_module); ?>">
                            <div class="layui-form-item" pane>
                                <label class="layui-form-label"><b style="font-size:15px;">变量标题</b></label>
                                <div class="layui-input-inline"><b style="font-size:15px;">变量值</b></div>
                                <div class="layui-input-inline"><b style="font-size:15px;">变量名</b></div>
                            </div>
                            <?php if(is_array($configs) || $configs instanceof \think\Collection || $configs instanceof \think\Paginator): $i = 0; $__LIST__ = $configs;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$config): $mod = ($i % 2 );++$i;?>
                            <div class="layui-form-item" pane>
                                <label class="layui-form-label"><?php echo htmlentities($config['name']); ?></label>
                                <!--整形或浮点型--> <!-- lay-verify="required" -->
                                <?php if($config['type'] == 1 || $config['type'] == 2): ?>
                                <div class="layui-input-inline">
                                    <input type="text" name="<?php echo htmlentities($config['key']); ?>" value="<?php echo htmlentities($config['value']); ?>" placeholder="请输入<?php echo htmlentities($config['name']); ?>" autocomplete="off" class="layui-input">
                                </div>
                                <div class="layui-input-inline">
                                    <?php echo htmlentities($config['key']); ?>
                                </div>
                                <!-- <div class="layui-form-mid layui-word-aux">
                                    <?php echo htmlentities($config['unit']); ?>
                                </div> -->
                                <!--字符串型-->
                                <?php elseif($config['type'] == 3): ?>
                                <div class="layui-input-inline"><!-- block -->
                                    <input type="text" name="<?php echo htmlentities($config['key']); ?>" value="<?php echo htmlentities($config['value']); ?>"  placeholder="请输入<?php echo htmlentities($config['name']); ?>" autocomplete="off" class="layui-input">
                                </div>
                                <div class="layui-input-inline">
                                    <?php echo htmlentities($config['key']); ?>
                                </div>
                                <!--开关型-->
                                <?php elseif($config['type'] == 4): ?>
                                <div class="layui-input-inline">
                                    <input type="hidden" name="<?php echo htmlentities($config['key']); ?>" id="<?php echo htmlentities($config['key']); ?>" value="<?php echo htmlentities($config['value']); ?>">
                                    <input type="checkbox" value="1" data-id="<?php echo htmlentities($config['key']); ?>" lay-skin="switch" <?php if($config['value'] == '1'): ?> checked <?php endif; ?>>
                                </div>
                                <div class="layui-input-inline">
                                    <?php echo htmlentities($config['key']); ?>
                                </div>
                                <!--百分百-->
                                <?php elseif($config['type'] == 5): ?>
                                <div class="layui-input-inline">
                                    <input type="text" name="<?php echo htmlentities($config['key']); ?>" value="<?php echo htmlentities($config['value']); ?>"  placeholder="请输入<?php echo htmlentities($config['name']); ?>" autocomplete="off" class="layui-input">
                                </div>
                                <div class="layui-input-inline">
                                    <?php echo htmlentities($config['key']); ?>
                                </div>
                                <!-- 时间  -->
                                <?php elseif($config['type'] == 8): ?>
                                <div class="layui-input-inline">
				                    <input class="layui-input" id="time_<?php echo htmlentities($config['key']); ?>" name="<?php echo htmlentities($config['key']); ?>" value="<?php echo htmlentities(date('H:i:s',!is_numeric($config['value'])? strtotime($config['value']) : $config['value'])); ?>" autocomplete="off" placeholder="请输入<?php echo htmlentities($config['name']); ?>">
				                </div>
                                <div class="layui-input-inline">
                                    <?php echo htmlentities($config['key']); ?>
                                </div>
                                <?php elseif($config['type'] == 9): ?>
                                    <div class="layui-input-inline">
                                        <button type="button" class="layui-btn" id="thumbnail_<?php echo htmlentities($config['key']); ?>">
                                            <i class="layui-icon">&#xe67c;</i>上传图片
                                        </button>
                                        <div class="thumbnail_box" id="thumbnail_box_<?php echo htmlentities($config['key']); ?>">
                                            <?php if($config['value'] != ''): ?>
                                            <img id="thumbnail_img_<?php echo htmlentities($config['key']); ?>" src="<?php echo htmlentities($config['value']); ?>" alt="" width="100" onclick="$('#thumbnail_<?php echo htmlentities($config['key']); ?>').click()">
                                            <?php else: ?>
                                            <img id="thumbnail_img_<?php echo htmlentities($config['key']); ?>" src="/../upload/default/default.jpg" alt="" width="100" onclick="$('#thumbnail_<?php echo htmlentities($config['key']); ?>').click()">
                                            <?php endif; ?>
                                            <input type="button" class="layui-btn layui-btn-danger" onclick="delete_thumbnail('<?php echo htmlentities($config['key']); ?>')" value="删除">
                                            <input type="hidden" name="<?php echo htmlentities($config['key']); ?>" value="<?php echo htmlentities($config['value']); ?>" id="thumbnail_value_<?php echo htmlentities($config['key']); ?>">
                                        </div>
                                    </div>
                                    <div class="layui-input-inline">
                                        <?php echo htmlentities($config['key']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; endif; else: echo "" ;endif; ?>
                        </div>
                        <div class="layui-form-item">
                            <div class="layui-input-block">
                                <?php if(in_array('/system/configedit', (array)session('power_action'))): ?>
                                <button class="layui-btn" lay-submit lay-filter="formDemo">提交</button>
                                <?php else: ?>
                                <button class="layui-btn layui-btn-disabled" disabled>提交</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>

<script>
    var active_module = '<?php echo htmlentities($my_active_module); ?>'; // 当前模块
    $("#module_"+active_module).addClass('layui-this'); // 初始化系统模块
    $("#module_menus_box_"+active_module).addClass('layui-show'); // 初始化系统模块菜单

    // 切换模块
    function changeModule(module_id) {
        if(module_id != active_module){
            window.location.href="<?php echo url('system/config'); ?>?my_active_module="+module_id;
            	//"/admin/system/config/my_active_module/"+module_id;
        }
    }

    layui.use('form', function () {
        let form = layui.form;
        form.render();
        form.on('switch', function(data){
            let data_id = $(data.elem).attr('data-id');
            if(data.elem.checked){
                $("#"+data_id).val(1);
            }else {
                $("#"+data_id).val(2);
            }
        });
    });
    

    <?php if(is_array($configs) || $configs instanceof \think\Collection || $configs instanceof \think\Paginator): $i = 0; $__LIST__ = $configs;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$config): $mod = ($i % 2 );++$i;if($config['type'] == 8): ?>
		//日期选择
		layui.use(['laydate','form'], function(){
		    var laydate = layui.laydate;
		    laydate.render({
		        elem: '#time_<?php echo htmlentities($config['key']); ?>',
		        type: 'time',
		        format: 'HH:mm:ss'
		    });
		});
    <?php endif; if($config['type'] == 9): ?>
    // 初始化图片
    $(function () {
        if($("#thumbnail_value_<?php echo htmlentities($config['key']); ?>").val() != ''){
            $("#thumbnail_<?php echo htmlentities($config['key']); ?>").hide();
            $("#thumbnail_box_<?php echo htmlentities($config['key']); ?>").show();
        }

        // 图片上传
        layui.use('upload', function(){
            var upload = layui.upload;
            var uploadInst = upload.render({
                elem: '#thumbnail_<?php echo htmlentities($config['key']); ?>',
                field: 'image',
                accept: 'images',
                url: "<?php echo url('base/uploadImage'); ?>",//'/admin/base/uploadImage',
                data: {module: 'config', folder: '/config', type: '2'},
                done: function(res){
                    if(res.code == 1){
                        layer.msg(res.msg);
                        $("#thumbnail_img_<?php echo htmlentities($config['key']); ?>").attr('src', res.url);
                        $("#thumbnail_value_<?php echo htmlentities($config['key']); ?>").val(res.url);
                        $("#thumbnail_<?php echo htmlentities($config['key']); ?>").hide();
                        $("#thumbnail_box_<?php echo htmlentities($config['key']); ?>").show();
                    }else {
                        layer.msg(res.msg);
                    }
                },
                error: function(){
                    //请求异常回调
                }
            });
        });

    });
    <?php endif; ?>
    <?php endforeach; endif; else: echo "" ;endif; ?>

    // 删除缩略图
    function delete_thumbnail(key)
    {
        $("#thumbnail_box_"+key).hide();
        $("#thumbnail_"+key).show();
        $("#thumbnail_img_"+key).attr('src', '');
        $("#thumbnail_value_"+key).val('');
        return false;
    }
</script>

</html>