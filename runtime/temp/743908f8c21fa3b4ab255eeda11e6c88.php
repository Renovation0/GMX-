<?php /*a:2:{s:71:"/home/gemxpbra/public_html/application/admin/view/notice/cat_lists.html";i:1697722712;s:89:"/home/gemxpbra/public_html/application/admin/view/../../../public/static/common/base.html";i:1697722706;}*/ ?>
<!DOCTYPE html>
<html class="x-admin-sm">
<head>
    <meta charset="UTF-8">
    <title>资讯分类</title>
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
        /* min-width: unset !important; */
    }
</style>

</head>
<body>

<div class="x-nav">
    <span class="layui-breadcrumb">
        <a href="javascript:;">资讯管理</a>
        <a><cite>资讯分类</cite></a>
    </span>
    <a class="layui-btn layui-btn-small" style="line-height:1.6em;margin-top:3px;float:right"
       onclick="location.reload()" title="刷新">
        <i class="layui-icon layui-icon-refresh" style="line-height:30px"></i>
    </a>
</div>
<div class="layui-card-header">
    <?php if(in_array('/notice/catadd', (array)session('power_action'))): ?> <!-- '/admin/notice/catAdd' -->
    <button class="layui-btn layui-btn-normal" onclick="xadmin.open('添加分类', '<?php echo url('notice/catAdd'); ?>', 600, 500)">
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
                    <div class="layui-tab-content">
	                    <form class="layui-form layui-col-space5">
	                        <div class="layui-input-inline layui-show-xs-block">
	                            <input type="text" name="title" value="<?php echo htmlentities($param_title); ?>" placeholder="类名" autocomplete="off"
	                                   class="layui-input">
	                        </div>
	                        <div class="layui-inline layui-show-xs-block">
	                            <input class="layui-input"  autocomplete="off" placeholder="添加开始时间" name="add_time_s" value="<?php echo htmlentities($param_add_time_s); ?>" id="add_time_s">
	                        </div>
	                        <div class="layui-inline layui-show-xs-block">
	                            <input class="layui-input"  autocomplete="off" placeholder="添加截至时间" name="add_time_e" value="<?php echo htmlentities($param_add_time_e); ?>" id="add_time_e">
	                        </div>
	                        <div class="layui-input-inline layui-show-xs-block">
	                            <button class="layui-btn" lay-submit="" lay-filter="sreach">
	                                <i class="layui-icon">&#xe615;</i>
	                            </button>
	                        </div>
	                    </form>
	                </div>
                </div>
                <div class="layui-card-body ">
                    <table class="layui-table layui-form">
                        <thead>
                        <tr>
                            <th style="width: 20px;">ID</th>
                            <th style="width: 40px;">图标</th>
                            <th style="width: 40px;">类名</th>
                            <th style="width: 120px;">添加时间</th>
                            <th style="width: 120px;">修改时间</th>
                            <th style="width: 140px;">操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if(!empty($list->items())): if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$user): $mod = ($i % 2 );++$i;?>
                        <tr>
                            <td><?php echo htmlentities($user['lx_id']); ?></td>
                            <td><img src="<?php echo htmlentities($user['img']); ?>"></td>
                            <td><?php echo htmlentities($user['lx_title']); ?></td>
                            <td><?php echo htmlentities(date('Y-m-d H:i:s',!is_numeric($user['time'])? strtotime($user['time']) : $user['time'])); ?></td>
                            <?php if($user['up_time'] != ''): ?>
                            <td><?php echo htmlentities(date('Y-m-d H:i:s',!is_numeric($user['up_time'])? strtotime($user['up_time']) : $user['up_time'])); ?></td>
                            <?php else: ?>
                            <td>--</td>
                            <?php endif; ?>
                            <td>
                                <?php if(in_array('/notice/catedit', (array)session('power_action'))): ?> <!-- '/admin/notice/catEdit/lx_id/<?php echo htmlentities($user['lx_id']); ?>' -->
                                <button class="layui-btn layui-btn-normal" onclick="xadmin.open('编辑','<?php echo url('notice/catEdit'); ?>?lx_id='+<?php echo htmlentities($user['lx_id']); ?>,600,500)">
                                    <i class="iconfont">&#xe69e;</i> 编辑
                                </button>
                                <?php else: ?>
                                <button class="layui-btn layui-btn-disabled" onclick="return false">
                                    <i class="iconfont">&#xe69e;</i> 编辑
                                </button>
                                <?php endif; if(in_array('/notice/catdelete', (array)session('power_action'))): ?>
                                <button class="layui-btn layui-btn-danger" onclick="delete_confirm(this, '<?php echo htmlentities($user['lx_id']); ?>')">
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

    //日期选择
    layui.use(['laydate','form'], function(){
        var laydate = layui.laydate;
        laydate.render({
            elem: '#add_time_s',
            type: 'datetime'
        });
        laydate.render({
            elem: '#add_time_e',
            type: 'datetime'
        });

    });

    // 同意申请
    function delete_confirm(obj, id) {
        layer.confirm('是否删除？', function(index) {
            $.ajax({
                url: "<?php echo url('notice/catDelete'); ?>",//'/admin/notice/catDelete',
                type: 'post',
                data: {
                    lx_id: id,
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