<?php /*a:2:{s:70:"/home/gemxpbra/public_html/application/admin/view/admin/menu_list.html";i:1697722712;s:89:"/home/gemxpbra/public_html/application/admin/view/../../../public/static/common/base.html";i:1697722706;}*/ ?>
<!DOCTYPE html>
<html class="x-admin-sm">
<head>
    <meta charset="UTF-8">
    <title>菜单列表</title>
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
    .my_aaa {
        padding: 9px 0 !important;
    }
    .aaa {
		color: red;
    }
    .bbb{
		color: blue;
    }
</style>

</head>
<body>

<div class="x-nav">
    <span class="layui-breadcrumb">
        <a href="javascript:;">系统</a>
        <a href="javascript:;">权限分配</a>
        <a><cite>菜单列表</cite></a>
    </span>
    <a class="layui-btn layui-btn-small" style="line-height:1.6em;margin-top:3px;float:right" onclick="location.reload()" title="刷新">
        <i class="layui-icon layui-icon-refresh" style="line-height:30px"></i>
    </a>
</div>
<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">
                    <?php if(in_array('/admin/admin/menuadd', (array)session('power_action'))): ?>
                    <button class="layui-btn layui-btn-normal" onclick="xadmin.open('添加菜单','/admin/admin/menuAdd',600,800)">
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
                            <th style="width: 50px;">标题</th>
                            <th style="width: 80px;">规则</th>
                            <th style="width: 10px;">状态</th>
                            <th style="width: 100px;">操作</th>
                        </tr>
                        </thead>
                        <tbody>
						<?php if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$user): $mod = ($i % 2 );++$i;?>
						<tr>
                            <td><?php echo htmlentities($user['id']); ?></td>
                            <td>
                            <?php switch($user['type']): case "2": ?>
                                 &nbsp;--
                                <?php break; case "3": ?>
                                 &nbsp;&nbsp;&nbsp;----
                                <?php break; default: ?>
                            <?php endswitch; ?>
                            <?php echo htmlentities($user['name']); ?>
                            </td>
                            <td><?php echo htmlentities($user['action']); ?></td>
                            <td>
                            <?php if(in_array('/admin/admin/menustatus', (array)session('power_action'))): switch($user['status']): case "2": ?>
                                <button class="layui-btn layui-btn-warm" onclick="menu_status(this, '<?php echo htmlentities($user['id']); ?>', 1)">
                                   开启
                                </button>
                                <?php break; case "1": ?>
                                <button class="layui-btn" onclick="menu_status(this, '<?php echo htmlentities($user['id']); ?>', 2)">
                                    关闭
                                </button>
                                <?php break; default: ?><span style="color: grey;">未知</span>
                                <?php endswitch; else: switch($user['status']): case "2": ?>
                                <button class="layui-btn layui-btn-warm" >
                                    <i class="iconfont">&#xe69d;</i> 开启
                                </button>
                                <?php break; ?>
                                {case 1}
                                <button class="layui-btn layui-btn-disabled">
                                    <i class="iconfont">&#xe69d;</i> 开启
                                </button>
                                <?php default: ?><span style="color: grey;">未知</span>
                                <?php endswitch; ?>
                              <?php endif; ?>
                            </td>
                            <td>
                                <!--编辑-->
                                <?php if(in_array('/admin/admin/menuedit', (array)session('power_action'))): ?>
                                <button class="layui-btn layui-btn-normal" onclick="xadmin.open('编辑','/admin/member/memberEdit/id/<?php echo htmlentities($user['id']); ?>',600,400)">
                                    <i class="iconfont">&#xe69e;</i> 编辑
                                </button>
                                <?php else: ?>
                                <button class="layui-btn layui-btn-disabled">
                                    <i class="iconfont">&#xe69e;</i> 编辑
                                </button>
                                <?php endif; ?>
                                
                                
                                <!--删除-->
                                <?php if(in_array('/admin/admin/menudelete', (array)session('power_action'))): ?>
                                <button class="layui-btn layui-btn-danger" onclick="member_del(this, '<?php echo htmlentities($user['id']); ?>')" style="margin-top:5px;">
                                    <i class="iconfont">&#xe69d;</i> 删除
                                </button>
                                <?php else: ?>
                                <button class="layui-btn layui-btn-disabled" style="margin-top:5px;">
                                    <i class="iconfont">&#xe69d;</i> 删除
                                </button>
                                <?php endif; ?>
                            
                            </td>
						</tr>
						<?php endforeach; endif; else: echo "" ;endif; ?>
                        <!-- <tr>
                            <td colspan="15" class="nodata_td">无记录</td>
                        </tr> -->

                        </tbody>
                    </table>
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

    // 修改菜单状态
    function menu_status(obj, id, status) {
    	alert(status);
        if(status !== 1 && status !== 2){
            layer.msg('错误的操作');
            return false;
        }
        layer.confirm(status === 1 ? '确定启用菜单？' : '确定停用菜单？', function(index) {
            $.ajax({
                url: '/admin/admin/menuStatus',
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
    function member_del(obj, id) {
        layer.confirm('确认要删除吗？', function(index) {
            $.ajax({
                url: '/admin/member/memberDelete',
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