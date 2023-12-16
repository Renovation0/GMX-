<?php /*a:2:{s:70:"/home/gemxpbra/public_html/application/admin/view/admin/user_list.html";i:1697722712;s:89:"/home/gemxpbra/public_html/application/admin/view/../../../public/static/common/base.html";i:1697722706;}*/ ?>
<!DOCTYPE html>
<html class="x-admin-sm">
<head>
    <meta charset="UTF-8">
    <title>管理员列表</title>
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
        margin-right: 5px;
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
        <a><cite>管理员列表</cite></a>
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
                                <option value="0" <?php if($param_status == 0) echo 'selected';?>>状态</option>
                                <option value="1" <?php if($param_status == 1) echo 'selected';?>>已启用</option>
                                <option value="2" <?php if($param_status == 2) echo 'selected';?>>已禁用</option>
                            </select>
                        </div>
                        <div class="layui-input-inline layui-show-xs-block">
                            <input type="text" name="username" value="<?php echo htmlentities($param_username); ?>" placeholder="管理员昵称" autocomplete="off" class="layui-input">
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
                    <?php if(in_array('/admin/useradd', (array)session('power_action'))): ?>
                    <button class="layui-btn layui-btn-normal" onclick="xadmin.open('添加管理员', '<?php echo url('admin/userAdd'); ?>', 600, 500)">
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
                            <th style="width: 100px;">管理员</th>
                            <th>授权角色</th>
                            <th style="width: 40px;">状态</th>
                            <th style="width: 290px;">操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if(!empty($list->items())): if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$user): $mod = ($i % 2 );++$i;?>
                        <tr>
                            <td><?php echo htmlentities($user['id']); ?></td>
                            <td><?php echo htmlentities($user['username']); ?></td>
                            <td>
                                <?php if(is_array($user['role']) || $user['role'] instanceof \think\Collection || $user['role'] instanceof \think\Paginator): $i = 0; $__LIST__ = $user['role'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$role): $mod = ($i % 2 );++$i;?>
                                <span class="role_user" data-id="<?php echo htmlentities($role['id']); ?>"><?php echo htmlentities($role['name']); ?></span>
                                <?php endforeach; endif; else: echo "" ;endif; ?>
                            </td>
                            <td>
                                <?php switch($user['status']): case "1": ?><span style="color: darkgreen;">已启用</span><?php break; case "2": ?><span style="color: orangered;">已禁用</span><?php break; case "3": ?><span style="color: grey;">已删除</span><?php break; default: ?><span style="color: grey;">未知</span>
                                <?php endswitch; ?>
                            </td>
                            <td class="td-manage">
                                <!--不是超级管理员-->
                                <?php if($user['id'] != 1): ?>
                                <!--编辑-->
                                <?php if(in_array('/admin/useredit', (array)session('power_action'))): ?> <!-- ，'/admin/admin/userEdit/id/<?php echo htmlentities($user['id']); ?> -->
                                <button class="layui-btn layui-btn-normal" onclick="xadmin.open('编辑','<?php echo url('admin/userEdit'); ?>?id='+<?php echo htmlentities($user['id']); ?>,600,500)">
                                    <i class="iconfont">&#xe69e;</i> 编辑
                                </button>
                                <?php else: ?>
                                <button class="layui-btn layui-btn-disabled">
                                    <i class="iconfont">&#xe69e;</i> 编辑
                                </button>
                                <?php endif; ?>
                                <!--修改状态-->
                                <?php if(in_array('/admin/userstatus', (array)session('power_action'))): switch($user['status']): case "2": ?>
                                <button class="layui-btn" onclick="role_status(this, '<?php echo htmlentities($user['id']); ?>', 1)">
                                    <i class="iconfont">&#xe6af;</i> 启用
                                </button>
                                <?php break; case "1": ?>
                                <button class="layui-btn layui-btn-warm" onclick="role_status(this, '<?php echo htmlentities($user['id']); ?>', 2)">
                                    <i class="iconfont">&#xe69c;</i> 停用
                                </button>
                                <?php break; default: ?><span style="color: grey;">未知</span>
                                <?php endswitch; else: switch($user['status']): case "2": ?>
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
                                <?php if(in_array('/admin/userdelete', (array)session('power_action'))): ?>
                                <button class="layui-btn layui-btn-danger" onclick="role_del(this, '<?php echo htmlentities($user['id']); ?>')">
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
                            <td colspan="5" class="nodata_td">无记录</td>
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

    // 修改管理员状态
    function role_status(obj, id, status) {
        if(status !== 1 && status !== 2){
            layer.msg('错误的操作');
            return false;
        }
        layer.confirm(status === 1 ? '确定启用管理员？' : '确定停用管理员？', function(index) {
            $.ajax({
                url: "<?php echo url('admin/userStatus'); ?>",//'/admin/admin/userStatus',
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

    // 删除管理员
    function role_del(obj, id) {
        layer.confirm('确认要删除吗？', function(index) {
            $.ajax({
                url: "<?php echo url('admin/userDelete'); ?>",//'/admin/admin/userDelete',
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