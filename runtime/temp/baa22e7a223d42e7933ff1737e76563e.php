<?php /*a:2:{s:65:"/home/gemxpbra/public_html/application/admin/view/system/log.html";i:1697722712;s:89:"/home/gemxpbra/public_html/application/admin/view/../../../public/static/common/base.html";i:1697722706;}*/ ?>
<!DOCTYPE html>
<html class="x-admin-sm">
<head>
    <meta charset="UTF-8">
    <title>系统日志</title>
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
    .layui-table td, .layui-table th {
        min-width: unset !important;
    }
</style>

</head>
<body>

<div class="x-nav">
    <span class="layui-breadcrumb">
        <a href="javascript:;">系统</a>
        <a href="javascript:;">系统设置</a>
        <a><cite>系统日志</cite></a>
    </span>
    <a class="layui-btn layui-btn-small" style="line-height:1.6em;margin-top:3px;float:right" onclick="location.reload()" title="刷新">
        <i class="layui-icon layui-icon-refresh" style="line-height:30px"></i>
    </a>
</div>
<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-body ">
                    <div class="layui-tab-content">
                        <form class="layui-form layui-col-space5">
                            <div class="layui-input-inline layui-show-xs-block">
                                <input type="text" name="u_id" value="<?php echo htmlentities($param_u_id); ?>" placeholder="管理员id" autocomplete="off" class="layui-input">
                            </div>
                            <div class="layui-input-inline layui-show-xs-block">
                                <button class="layui-btn" lay-submit="" lay-filter="sreach">
                                    <i class="layui-icon">&#xe615;</i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="layui-card-header">

                    <?php if(in_array('/system/logdelete', (array)session('power_action'))): ?>
                    <button class="layui-btn layui-btn-danger" onclick="delAll()">
                        <i class="layui-icon"></i>批量删除
                    </button>
                    <?php else: ?>
                    <button class="layui-btn layui-btn-disabled">
                        <i class="layui-icon"></i>批量删除
                    </button>
                    <?php endif; ?>
                </div>

                <div class="layui-card-body ">
                    <table class="layui-table layui-form">
                        <thead>
                        <tr>
                            <th style="width: 20px;"><input type="checkbox" lay-filter="checkall" name="" lay-skin="primary"></th>
                            <th style="width: 20px;">ID</th>
                            <th style="width: 50px;">管理员id</th>
                            <th style="width: 100px;">管理员</th>
                            <th>日志</th>
                            <th style="width: 130px;">时间</th>
                            <th style="width: 60px;">操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if(!empty($list->items())): if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$log): $mod = ($i % 2 );++$i;?>
                        <tr>
                            <td><input class="check_item" type="checkbox" name="id" value="<?php echo htmlentities($log['id']); ?>" lay-skin="primary"></td>
                            <td><?php echo htmlentities($log['id']); ?></td>
                            <td><?php echo htmlentities($log['u_id']); ?></td>
                            <td><?php echo htmlentities($log['username']); ?></td>
                            <td><?php echo htmlentities($log['log']); ?></td>
                            <td><?php echo htmlentities(date('Y-m-d H:i:s',!is_numeric($log['time'])? strtotime($log['time']) : $log['time'])); ?> </td>
                            <td class="td-manage">
                                <?php if(in_array('/system/logdelete', (array)session('power_action'))): ?>
                                <button class="layui-btn layui-btn-danger" onclick="log_del(this, '<?php echo htmlentities($log['id']); ?>')">
                                    <i class="iconfont">&#xe69d;</i> 删除
                                </button>
                                <?php else: ?>
                                <button class="layui-btn layui-btn-disabled">
                                    <i class="iconfont">&#xe69d;</i> 删除
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; endif; else: echo "" ;endif; else: ?>
                        <tr>
                            <td colspan="7" class="nodata_td">无记录</td>
                        </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="layui-card-body ">
                    <div class="page">
                        <?php echo $list; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>

<script>
    layui.use('form', function () {
       let form = layui.form;
       form.render();
        // 监听全选
        form.on('checkbox(checkall)', function(data){
            if(data.elem.checked){
                $('tbody input').prop('checked',true);
            }else{
                $('tbody input').prop('checked',false);
            }
            form.render('checkbox');
        });
    });

    // 删除日志
    function log_del(obj, id) {
        layer.confirm('确认要删除吗？', function(index) {
            $.ajax({
                url: "<?php echo url('system/logDelete'); ?>",//'/admin/system/logDelete',
                type: 'post',
                data: {
                    id: id,
                },
                success: function (data) {
                    if(data.code === 1){
                        layer.msg(data.msg);
                        setTimeout(function(){
                            window.location.reload();
                        }, 500);
                    }else {
                        layer.msg(data.msg);
                    }
                }
            });
        });
    }

    // 批量删除
    function delAll (argument) {
        var ids = [];
        // 获取选中的id
        $('tbody input').each(function(index, el) {
            if($(this).prop('checked')){
                ids.push($(this).val())
            }
        });
        //+ids.toString()
        layer.confirm('确认要删除吗？',function(index){
            $.ajax({
                url: "<?php echo url('system/logDelete'); ?>",//'/admin/system/logDelete',
                type: 'post',
                data: {
                    id: ids,
                },
                success: function (data) {
                    if(data.code === 1){
                        layer.msg(data.msg);
                        setTimeout(function(){
                            window.location.reload();
                        }, 500);
                    }else {
                        layer.msg(data.msg);
                    }
                }
            });
        });
    }
</script>

</html>