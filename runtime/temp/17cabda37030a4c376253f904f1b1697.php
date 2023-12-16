<?php /*a:2:{s:75:"/home/gemxpbra/public_html/application/admin/view/system/exchange_list.html";i:1697722712;s:89:"/home/gemxpbra/public_html/application/admin/view/../../../public/static/common/base.html";i:1697722706;}*/ ?>
<!DOCTYPE html>
<html class="x-admin-sm">
<head>
    <meta charset="UTF-8">
    <title>资讯管理</title>
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
        <a href="javascript:;">参数设置</a>
        <a><cite>客服列表</cite></a>
    </span>
    <a class="layui-btn layui-btn-small" style="line-height:1.6em;margin-top:3px;float:right"
       onclick="location.reload()" title="刷新">
        <i class="layui-icon layui-icon-refresh" style="line-height:30px"></i>
    </a>
</div>
<div class="layui-card-header">
    <?php if(in_array('/system/exchangeadd', (array)session('power_action'))): ?>
    <button class="layui-btn layui-btn-normal" onclick="xadmin.open('添加', '<?php echo url('system/exchangeAdd'); ?>', 0, 0, true)">
        <i class="layui-icon"></i>添加
    </button>
    <?php else: ?>
    <button class="layui-btn layui-btn-disabled" onclick="return false">
        <i class="layui-icon"></i>添加
    </button>
    <?php endif; ?>
</div>
<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-body ">
                    <table class="layui-table layui-form">
                        <thead>
                        <tr>
                            <th style="width: 10px;">ID</th>
                            <th style="width: 100px;">英文标题</th>
                            <th style="width: 130px;">链接</th>
                            <th style="width: 80px;">操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if(!empty($list->items())): if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$user): $mod = ($i % 2 );++$i;?>
                        <tr>
                            <td><?php echo htmlentities($user['id']); ?></td>
                            <!-- <td><?php echo htmlentities($user['c_name']); ?></td> -->
                            <td><?php echo htmlentities($user['e_name']); ?></td>
                            <td><?php echo htmlentities($user['url']); ?></td>
                            <td>
                                <?php if(in_array('/system/exchangeedit', (array)session('power_action'))): ?>
                                <button class="layui-btn layui-btn-normal" onclick="xadmin.open('编辑','<?php echo url('system/exchangeEdit'); ?>?id='+<?php echo htmlentities($user['id']); ?>,0,0, true)">
                                    <i class="iconfont">&#xe69e;</i> 编辑
                                </button>
                                <?php else: ?>
                                <button class="layui-btn layui-btn-disabled" onclick="return false">
                                    <i class="iconfont">&#xe69e;</i> 编辑
                                </button>
                                <?php endif; if(in_array('/system/exchangedel', (array)session('power_action'))): ?>
                                <button class="layui-btn layui-btn-danger" onclick="delete_confirm(this, '<?php echo htmlentities($user['id']); ?>')">
                                    <i class="iconfont">&#xe69d;</i> 删除
                                </button>
                                <?php else: ?>
                                <button class="layui-btn layui-btn-disabled" onclick="return false">
                                    <i class="iconfont">&#xe69d;</i> 删除
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; endif; else: echo "" ;endif; else: ?>
                        <tr>
                            <td colspan="11" class="nodata_td">无记录</td>
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
    });

    // 修改状态
    function setStatus(obj, id, status) {
        layer.confirm('是否'+(status == 1 ? '上架' : '下架')+'？', function(index) {
            $.ajax({
                url: '/admin/notice/status',
                type: 'post',
                data: {
                    n_id: id,
                    status: status
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

    // 删除
    function delete_confirm(obj, id) {
        layer.confirm('是否删除？', function(index) {
            $.ajax({
                url: "<?php echo url('system/exchangeDel'); ?>",//'/admin/system/exchangeDel',
                type: 'post',
                data: {
                    n_id: id,
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