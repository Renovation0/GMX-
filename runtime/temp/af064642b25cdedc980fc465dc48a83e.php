<?php /*a:2:{s:70:"/home/gemxpbra/public_html/application/admin/view/admin/role_list.html";i:1697722712;s:89:"/home/gemxpbra/public_html/application/admin/view/../../../public/static/common/base.html";i:1697722706;}*/ ?>
<!DOCTYPE html>
<html class="x-admin-sm">
<head>
    <meta charset="UTF-8">
    <title>角色列表</title>
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
    .role_user {
        display: inline-block;
        margin:0 5px 5px 0;
        padding: 5px;
        background-color: #f0f0f0;
    }
    .layui-fluid{
        min-width:800px;
    }
</style>

</head>
<body>

<div class="x-nav">
    <span class="layui-breadcrumb">
        <a href="javascript:;">系统</a>
        <a href="javascript:;">权限分配</a>
        <a><cite>角色列表</cite></a>
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
                                <select name="status">
                                    <option value="-1" <?php if($param_status == -1) echo 'selected';?>>使用状态</option>
                                    <option value="1" <?php if($param_status == 1) echo 'selected';?>>已启用</option>
                                    <option value="2" <?php if($param_status == 2) echo 'selected';?>>已禁用</option>
                                </select>
                            </div>
                            <div class="layui-input-inline layui-show-xs-block">
                                <input type="text" name="name" value="<?php echo htmlentities($param_name); ?>" placeholder="角色名" autocomplete="off" class="layui-input">
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
                    <?php if(in_array('/admin/roleadd', (array)session('power_action'))): ?> <!-- <?php echo url('admin/roleAdd'); ?> '/admin/admin/roleAdd' -->
                    <button class="layui-btn layui-btn-normal" onclick="xadmin.open('添加角色','<?php echo url('admin/roleAdd'); ?>',600,400)">
                        <i class="layui-icon"></i>添加
                    </button>
                    <?php else: ?>
                    <button class="layui-btn layui-btn-disabled">
                        <i class="layui-icon"></i>添加
                    </button>
                    <?php endif; ?>
                </div>
                <div class="layui-card-body ">
                    <table class="layui-table layui-form">
                        <thead>
                        <tr>
                            <th style="width: 20px;">ID</th>
                            <th style="width: 100px;">角色名</th>
                            <th>描述</th>
                            <th>授权用户</th>
                            <th style="width: 40px;">状态</th>
                            <th style="width: 290px;">操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if(!empty($list->items())): if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$role): $mod = ($i % 2 );++$i;?>
                        <tr>
                            <td><?php echo htmlentities($role['id']); ?></td>
                            <td><?php echo htmlentities($role['name']); ?></td>
                            <td><?php echo htmlentities($role['message']); ?></td>
                            <td>
                                <?php if(is_array($role['users']) || $role['users'] instanceof \think\Collection || $role['users'] instanceof \think\Paginator): $i = 0; $__LIST__ = $role['users'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$user): $mod = ($i % 2 );++$i;?>
                                    <span class="role_user" data-id="<?php echo htmlentities($user['id']); ?>"><?php echo htmlentities($user['username']); ?></span>
                                <?php endforeach; endif; else: echo "" ;endif; ?>
                            </td>
                            <td>
                                <?php switch($role['status']): case "1": ?><span style="color: darkgreen;">已启用</span><?php break; case "2": ?><span style="color: orangered;">已禁用</span><?php break; case "3": ?><span style="color: grey;">已删除</span><?php break; default: ?><span style="color: grey;">未知</span>
                                <?php endswitch; ?>
                            </td>
                            <td class="td-manage">
                                <!--不是超级管理员-->
                                <?php if($role['id'] != 1): ?>
                                <!--权限-->
                                <?php if(in_array('/admin/rolepower', (array)session('power_action'))): ?>	<!-- /admin/admin/rolePower/id/<?php echo htmlentities($role['id']); ?> -->
                                <button class="layui-btn" onclick="xadmin.open('权限分配: <?php echo htmlentities($role['name']); ?> [id=><?php echo htmlentities($role['id']); ?>]','<?php echo url('admin/rolePower'); ?>?id='+<?php echo htmlentities($role['id']); ?>,900,700)">
                                    <i class="iconfont">&#xe6ab;</i> 权限
                                </button>
                                <?php else: ?>
                                <button class="layui-btn layui-btn-disabled">
                                    <i class="iconfont">&#xe6ab;</i> 权限
                                </button>
                                <?php endif; ?>
                                <!--编辑-->
                                <?php if(in_array('/admin/roleedit', (array)session('power_action'))): ?>	<!-- '/admin/admin/roleEdit/id/<?php echo htmlentities($role['id']); ?>' -->
                                <button class="layui-btn layui-btn-normal" onclick="xadmin.open('编辑','<?php echo url('admin/roleEdit'); ?>?id='+<?php echo htmlentities($role['id']); ?>,600,400)">
                                    <i class="iconfont">&#xe69e;</i> 编辑
                                </button>
                                <?php else: ?>
                                <button class="layui-btn layui-btn-disabled">
                                    <i class="iconfont">&#xe69e;</i> 编辑
                                </button>
                                <?php endif; ?>
                                <!--修改状态-->
                                <?php if(in_array('/admin/rolestatus', (array)session('power_action'))): switch($role['status']): case "2": ?>
                                <button class="layui-btn" onclick="role_status(this, '<?php echo htmlentities($role['id']); ?>', 1)">
                                    <i class="iconfont">&#xe6af;</i> 启用
                                </button>
                                <?php break; case "1": ?>
                                <button class="layui-btn layui-btn-warm" onclick="role_status(this, '<?php echo htmlentities($role['id']); ?>', 2)">
                                    <i class="iconfont">&#xe69c;</i> 停用
                                </button>
                                <?php break; default: ?><span style="color: grey;">未知</span>
                                <?php endswitch; else: switch($role['status']): case "2": ?>
                                <button class="layui-btn">
                                    <i class="iconfont">&#xe6af;</i> 启用
                                </button>
                                <?php break; case "1": ?>
                                <button class="layui-btn layui-btn-disabled">
                                    <i class="iconfont">&#xe69c;</i> 停用
                                </button>
                                <?php break; default: ?><span style="color: grey;">未知</span>
                                <?php endswitch; ?>
                                <?php endif; ?>
                                <!--删除-->
                                <?php if(in_array('/admin/roledelete', (array)session('power_action'))): ?>
                                <button class="layui-btn layui-btn-danger" onclick="role_del(this, '<?php echo htmlentities($role['id']); ?>')">
                                    <i class="iconfont">&#xe69d;</i> 删除
                                </button>
                                <?php else: ?>
                                <button class="layui-btn layui-btn-disabled">
                                    <i class="iconfont">&#xe69d;</i> 删除
                                </button>
                                <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; endif; else: echo "" ;endif; else: ?>
                        <tr>
                            <td colspan="6" class="nodata_td">无记录</td>
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

    // 修改角色状态
    function role_status(obj, id, status) {
        if(status !== 1 && status !== 2){
            layer.msg('错误的操作');
            return false;
        }
        layer.confirm(status === 1 ? '确定启用角色？' : '确定停用角色？', function(index) {
            $.ajax({
                url: "<?php echo url('admin/roleStatus'); ?>",//'/admin/admin/roleStatus',
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
    }

    // 删除角色
    function role_del(obj, id) {
        layer.confirm('确认要删除吗？', function(index) {
            $.ajax({
                url: "<?php echo url('admin/roleDelete'); ?>",//'/admin/admin/roleDelete',
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
</script>

</html>