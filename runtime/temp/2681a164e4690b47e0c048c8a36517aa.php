<?php /*a:2:{s:69:"/home/gemxpbra/public_html/application/admin/view/task/task_list.html";i:1697722712;s:89:"/home/gemxpbra/public_html/application/admin/view/../../../public/static/common/base.html";i:1697722706;}*/ ?>
<!DOCTYPE html>
<html class="x-admin-sm">
<head>
    <meta charset="UTF-8">
    <title>互助列表</title>
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

    .role_user {
        display: inline-block;
        margin-right: 5px;
        padding: 5px;
        background-color: #f0f0f0;
    }
</style>

</head>
<body>

<div class="x-nav">
    <span class="layui-breadcrumb">
        <a href="javascript:;">任务管理</a>
        <a><cite>任务列表</cite></a>
    </span>
    <a class="layui-btn layui-btn-small" style="line-height:1.6em;margin-top:3px;float:right"
       onclick="location.reload()" title="刷新">
        <i class="layui-icon layui-icon-refresh" style="line-height:30px"></i>
    </a>
</div>
<div class="layui-card-header">
    <?php if(in_array('/task/taskadd', (array)session('power_action'))): ?>
    <button class="layui-btn layui-btn-normal" onclick="xadmin.open('添加', '<?php echo url('task/taskAdd'); ?>', 600, 600)">
        <i class="layui-icon"></i>添加
    </button>
    <?php endif; ?>
</div>
<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-body layui-card-table">
                    <table class="layui-table layui-form">
                        <thead>
                        <tr>
                            <th style="min-width: 30px;"><b>ID</b></th>
                            <th><b>任务名称</b></th>
                            <th style="min-width: 60px;"><b>邀请人数</b></th>
                            <th style="min-width: 60px;"><b>奖励金额</b></th>
                            <th style="min-width: 60px;"><b>状态</b></th>
                            <th style="min-width: 140px;"><b>操作</b></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$user): $mod = ($i % 2 );++$i;?>
                        <tr align="center">
                            <td><?php echo htmlentities($user['id']); ?></td>
                            <td><?php echo htmlentities($user['task_name']); ?></td>
                            <td><?php echo htmlentities($user['yq_num']); ?></td>
                            <td><?php echo htmlentities($user['jl_num']); ?></td>
                            <td>
                            <input type="checkbox" value="1" data-id="status" data-ids="<?php echo htmlentities($user['id']); ?>" lay-skin="switch" <?php if($user['status'] == '1'): ?> checked <?php endif; ?>>
                            </td>

                            <td>
                                <?php if(in_array('/task/taskedit', (array)session('power_action'))): ?> <!-- '/admin/mutualaid/mutualaidEdit/id/<?php echo htmlentities($user['id']); ?>' -->
                                <button title="编辑" class="layui-btn layui-btn-normal" onclick="xadmin.open('编辑','<?php echo url('task/taskedit'); ?>?id='+<?php echo htmlentities($user['id']); ?>,600,600)">
                                    <i class="iconfont">&#xe69e;</i>
                                </button>
                                <?php else: ?>
                                <button title="编辑" class="layui-btn layui-btn-disabled" onclick="return false">
                                    <i class="iconfont">&#xe69e;</i>
                                </button>
                                <?php endif; if(in_array('/task/taskdelete', (array)session('power_action'))): ?>
                                <button title="删除" class="layui-btn layui-btn-danger" onclick="delete_confirm(this, '<?php echo htmlentities($user['id']); ?>')">
                                    <i class="iconfont">&#xe69d;</i>
                                </button>
                                <?php else: ?>
                                <button title="删除" class="layui-btn layui-btn-disabled" onclick="return false">
                                    <i class="iconfont">&#xe69d;</i>
                                </button>
                                <?php endif; ?>

                            </td>
                        </tr>
                        <?php endforeach; endif; else: echo "" ;endif; ?>
                        </tbody>
                    </table>
                </div>
                <img alt="" style="display:none" id="displayImg" src="" />
                
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
    
    layui.use('form', function () {
        let form = layui.form;
        form.render();
        form.on('switch', function(data){
            let data_id = $(data.elem).attr('data-id');
            let id = $(data.elem).attr('data-ids');
            if(data.elem.checked){
                $("#"+data_id).val(1);
                var status = '1';
            }else {
                $("#"+data_id).val(2);
                var status = '2';
            }
            
             $.ajax({
                url: "<?php echo url('task/taskEditStatus'); ?>",//'/admin/mutualaid/mutualAidEditStatus',
                type: 'post',
                data: {
                    id: id,
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
    });

    function confirm(url) {   	
        $("#displayImg").attr("src", url);
        var width = 'auto';
        var height = 'auto';
        
        layer.open({
            type: 1,
            title: false,
            closeBtn: 0,
            shadeClose: true,
            area: [width + 'px', height + 'px'], //宽高
            content: "<img src=" + url + " />"
        });
    }
	
	
    //删除
    function delete_confirm(obj, id) {
        layer.confirm('是否同意删除？', function(index) {
            $.ajax({
                url: "<?php echo url('task/taskdelete'); ?>",//'/admin/mutualaid/mutualaiddelete',
                type: 'post',
                data: {
                	mu_id: id,
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