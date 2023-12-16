<?php /*a:2:{s:74:"/home/gemxpbra/public_html/application/admin/view/channel/channellist.html";i:1697722712;s:89:"/home/gemxpbra/public_html/application/admin/view/../../../public/static/common/base.html";i:1697722706;}*/ ?>
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
    /*.layui-table td, .layui-table th {*/
        /*min-width: unset !important;*/
    /*}*/
    .role_user {
        display: inline-block;
        margin-right: 5px;
        padding: 5px;
        background-color: #f0f0f0;
    }
    .my_aaa {
        /*padding: 9px 0 !important;*/
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
        <a href="javascript:;">会员中心</a>
        <a><cite>通道管理</cite></a>
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
	                      
	                    </form>
	                </div>
                </div>
                <div class="layui-card-header">
                     <button class="layui-btn layui-btn-normal" onclick="xadmin.open('添加通道','<?php echo url('channel/add'); ?>')">
                        <i class="layui-icon"></i>添加
                    </button>
                </div>
                <div class="layui-card-body  layui-card-table">
                    <table class="layui-table layui-form">
                        <thead>
                        <tr>
                            <th style="min-width: 80px;">ID</th>
                            <th style="min-width: 80px;">名称</th>
                            <th style="min-width: 80px;">备注名称</th>
                            <th style="min-width: 80px;">充值开关</th>
                            <th style="min-width: 80px;">提现开关</th>
                            <th style="min-width: 80px;">充值排序（越大越靠前）</th>
                            <th style="min-width: 80px;">提现排序（越大越靠前）</th>
                            <th style="min-width: 140px;">操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if(!empty($list->items())): if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$channel): $mod = ($i % 2 );++$i;?>
                        <tr>
                            <td><?php echo htmlentities($channel['id']); ?></td>
                            <td><?php echo htmlentities($channel['name']); ?></td>
                            <td><?php echo htmlentities($channel['bname']); ?></td>
                            <td>
                                <?php switch($channel['recharge_status']): case "1": ?>
                                <button class="layui-btn layui-btn-danger" >
                                   开启 
                                </button>
                                <?php break; case "0": ?>
                                <button class="layui-btn" >
                                   关闭
                                </button>
                                <?php break; ?>
                                <?php endswitch; ?>
                            </td>
                            <td>
                                <?php switch($channel['withdraw_status']): case "1": ?>
                                <button class="layui-btn layui-btn-danger" >
                                   开启 
                                </button>
                                <?php break; case "0": ?>
                                <button class="layui-btn" >
                                   关闭
                                </button>
                                <?php break; ?>
                                <?php endswitch; ?>
                            </td>
                            <td><?php echo htmlentities($channel['recharge_order']); ?></td>
                            <td><?php echo htmlentities($channel['withdraw_order']); ?></td>
                            <td class="td-manage">
                                <button class="layui-btn layui-btn-normal" onclick="xadmin.open('编辑','<?php echo url('channel/edit'); ?>?id='+<?php echo htmlentities($channel['id']); ?>)">
                                    <i class="iconfont">&#xe69e;</i> 编辑
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; endif; else: echo "" ;endif; else: ?>
                        <tr>
                            <td colspan="17" class="nodata_td">无记录</td>
                        </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="layui-card-body ">
                    <div class="page">
                        <?php echo $list; ?> 
                    </div>
                    <div class="pull-right margin-5">
                    	<span>共 <?php echo htmlentities($total); ?> 条记录，每页显示 
                        <select onchange="location.href=this.options[this.selectedIndex].value" data-auto-none="">
                            <option data-num="10" value="?page=1&limit=10">10</option>
                        	<option data-num="50" value="?page=1&limit=50">50</option>
                        	<option data-num="100" value="?page=1&limit=100">100</option>
                        	<option data-num="200" value="?page=1&limit=200">200</option>
                        </select> 条，共 <?php echo htmlentities($pages); ?> 页当前显示第 <?php echo htmlentities($currentPage); ?> 页。</span>
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
    function member_status(obj, id, status) {
        if(status !== 1 && status !== 2 && status !== 3){
            layer.msg('错误的操作');
            return false;
        }
        console.log(status);
        let str = '';
        if(status == 1){
        	str = '确定未激活角色？';
        }
        if(status == 2){
        	str = '确定激活角色？';
        }
        if(status == 3){
        	str = '确定冻结角色？';
        }//status === 1 ? '确定启用角色？' : '确定冻结角色？'
        layer.confirm(str, function(index) {
            $.ajax({
                url: "<?php echo url('member/memberStatus'); ?>",//'/admin/member/memberStatus',
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

    // 控制卖出状态
    function member_control(obj, id, status) {
        if(status !== 1 && status !== 2){
            layer.msg('错误的操作');
            return false;
        }
        layer.confirm(status === 1 ? '确定控制角色卖出？' : '确定解控角色卖出？', function(index) {
            $.ajax({
                url: "<?php echo url('member/memberControl'); ?>",//'/admin/member/memberControl',
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
    
    // 控制特权账户
    function member_privilege(obj, id, status) {
        if(status !== 1 && status !== 2 && status !== 3){
            layer.msg('错误的操作');
            return false;
        }
        let str = '';
        if(status == 1){
        	str = '确定切换成必中？';
        }
        if(status == 2){
        	str = '确定切换成随机？';
        }
        if(status == 3){
        	str = '确定切换成必不中？';
        }
        layer.confirm(str, function(index) {
            $.ajax({
                url: "<?php echo url('member/memberPrivilege'); ?>",//'/admin/member/memberPrivilege',
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
                url: "<?php echo url('member/memberDelete'); ?>",//'/admin/member/memberDelete',
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
    
    // 排序
    function member_order(id) {
    	alert(id);
    	$.ajax({
            url: "<?php echo url('member/memberList'); ?>",//'/admin/member/memberList', //<?php echo url('member/pub_excel'); ?>
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
    }
    
  //导出数据
    function loadexcel(){
    	location.href = "<?php echo url('member/pub_excel'); ?>";
    }
</script>

</html>