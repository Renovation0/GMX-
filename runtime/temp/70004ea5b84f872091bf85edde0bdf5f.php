<?php /*a:2:{s:73:"/home/gemxpbra/public_html/application/admin/view/member/member_list.html";i:1697722712;s:89:"/home/gemxpbra/public_html/application/admin/view/../../../public/static/common/base.html";i:1697722706;}*/ ?>
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
        <a><cite>会员列表</cite></a>
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
	                         <input type="hidden" name="limit" value="<?php echo htmlentities($pageSize); ?>" />
	                        <div class="layui-input-inline layui-show-xs-block" >
	                         <select name="status">
	                            <option value="-1" <?php if($param_status == -1) echo 'selected';?>>使用状态</option>
	                            <option value="1" <?php if($param_status == 1) echo 'selected';?>>未激活</option>
	                            <option value="2" <?php if($param_status == 2) echo 'selected';?>>正常</option>
	                            <option value="3" <?php if($param_status == 3) echo 'selected';?>>已冻结</option>
	                         </select>
	                        </div>
	                        <!-- <div class="layui-input-inline layui-show-xs-block">
	                            <select name="real_status">
	                                <option value="-1" <?php if($real_status == -1) echo 'selected';?>>实名状态</option>
	                                <option value="0" <?php if($real_status == 0) echo 'selected';?>>未实名</option>
	                                <option value="1" <?php if($real_status == 1) echo 'selected';?>>已实名</option>
	                                <option value="2" <?php if($real_status == 2) echo 'selected';?>>已申请</option>
	                                <option value="3" <?php if($real_status == 3) echo 'selected';?>>已拒绝</option>
	                            </select>
	                        </div> -->
	                        <div class="layui-input-inline layui-show-xs-block">
	                         <select name="level">
	                            <option value="0">用户等级</option>
	                            <?php if(is_array($levellist) || $levellist instanceof \think\Collection || $levellist instanceof \think\Paginator): $i = 0; $__LIST__ = $levellist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$team): $mod = ($i % 2 );++$i;?>
	                            <option value="<?php echo htmlentities($team['id']); ?>"<?php if($team['id'] == $level): ?>selected<?php endif; ?>><?php echo htmlentities($team['name']); ?></option>
	                            <?php endforeach; endif; else: echo "" ;endif; ?>
	                         </select>
	                        </div>
	                        <div class="layui-input-inline layui-show-xs-block" style="width:200px;">
	                            <input type="text" name="uid" value="<?php echo htmlentities($uid); ?>" placeholder="id" autocomplete="off" class="layui-input">
	                        </div>
	                        <div class="layui-input-inline layui-show-xs-block" style="width:200px;">
	                            <input type="text" name="tel" value="<?php echo htmlentities($param_name); ?>" placeholder="手机号码" autocomplete="off" class="layui-input">
	                        </div>
	                        <div class="layui-input-inline layui-show-xs-block" style="width:200px;">
	                            <input type="text" name="ip_address" value="<?php echo htmlentities($ip_address); ?>" placeholder="ip地址" autocomplete="off" class="layui-input">
	                        </div>
	                        <div class="layui-input-inline layui-show-xs-block" style="width:200px;">
	                            <input type="text" name="referrer" value="<?php echo htmlentities($referrer); ?>" placeholder="推荐人" autocomplete="off" class="layui-input">
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
                    <?php if(in_array('/member/roleadd', (array)session('power_action'))): ?>		<!-- /admin/member/roleAdd -->
                    <button class="layui-btn layui-btn-normal" onclick="xadmin.open('添加会员','<?php echo url('member/roleAdd'); ?>',600,380)">
                        <i class="layui-icon"></i>添加
                    </button>
                    <?php else: ?>
                    <button class="layui-btn layui-btn-disabled">
                        <i class="layui-icon"></i>添加
                    </button>
                    <?php endif; ?>

                    <button onclick="loadexcel()" class="btn radius" type="button"><i class="Hui-iconfont Hui-iconfont-down"></i>导出数据</button>
                </div>
                <div class="layui-card-body  layui-card-table">
                    <table class="layui-table layui-form">
                        <thead>
                        <tr>
                            <th style="min-width: 40px;">ID<br>   <a class="<?php echo $sort == 'a.id asc' ? 'aaa' : 'bbb';?>" href="javascript:window.location.href='/witkey2022.php/member/memberList/sort/a.id asc'"><i class="layui-icon">&#xe619;</i></a> <a class="<?php echo $sort == 'a.id desc' ? 'aaa' : 'bbb';?>" href="javascript:window.location.href='/witkey2022.php/member/memberList/sort/a.id desc'"><i class="layui-icon">&#xe61a;</i></a></th>
                            <!--<th style="min-width: 50px;">ID-->
                                <!--<span class="layui-table-sort layui-inline" lay-sort="<?php echo $sort == 'a.id asc' ? 'asc' : $sort == 'a.id desc' ? 'desc' : '';?>">-->
                                    <!--<a href="javascript:window.location.href='/index/member/memberList/sort/a.id asc'" class="layui-edge layui-table-sort-asc"></a>-->
                                    <!--<a href="javascript:window.location.href='/index/member/memberList/sort/a.id desc'" class="layui-edge layui-table-sort-desc"></a>-->
                                <!--</span>-->
                            <!--</th>-->
                            <th style="min-width: 80px;">账号</th>
                            <th style="min-width: 80px;">昵称</th>
                            <th style="min-width: 50px;">等级<br>   <a class="<?php echo $sort == 'b.name asc' ? 'aaa' : 'bbb';?>" href="javascript:window.location.href='/witkey2022.php/member/memberList/sort/b.name asc'"><i class="layui-icon">&#xe619;</i></a> <a class="<?php echo $sort == 'b.name desc' ? 'aaa' : 'bbb';?>" href="javascript:window.location.href='/witkey2022.php/member/memberList/sort/b.name desc'"><i class="layui-icon">&#xe61a;</i></a></th>
                            <th>推荐人</th>
                            <th>所属业务员</th>
                            <th style="min-width: 80px;">可提现余额<br>   <a class="<?php echo $sort == 'a.balance asc' ? 'aaa' : 'bbb';?>" href="javascript:window.location.href='/witkey2022.php/member/memberList/sort/a.balance asc'"><i class="layui-icon">&#xe619;</i></a> <a class="<?php echo $sort == 'a.balance desc' ? 'aaa' : 'bbb';?>" href="javascript:window.location.href='/witkey2022.php/member/memberList/sort/a.balance desc'"><i class="layui-icon">&#xe61a;</i></a></th>
                            <th style="min-width: 80px;">充值余额<br>   <a class="<?php echo $sort == 'a.rechange_limit asc' ? 'aaa' : 'bbb';?>" href="javascript:window.location.href='/witkey2022.php/member/memberList/sort/a.rechange_limit asc'"><i class="layui-icon">&#xe619;</i></a> <a class="<?php echo $sort == 'a.rechange_limit desc' ? 'aaa' : 'bbb';?>" href="javascript:window.location.href='/witkey2022.php/member/memberList/sort/a.rechange_limit desc'"><i class="layui-icon">&#xe61a;</i></a></th>
                            <!-- <th style="min-width: 80px;">收益转存<br>    <a class="<?php echo $sort == 'a.profit_deposit asc' ? 'aaa' : 'bbb';?>" href="javascript:window.location.href='/witkey2022.php/member/memberList/sort/a.profit_deposit asc'"><i class="layui-icon">&#xe619;</i></a> <a class="<?php echo $sort == 'a.profit_deposit desc' ? 'aaa' : 'bbb';?>" href="javascript:window.location.href='/witkey2022.php/member/memberList/sort/a.profit_deposit desc'"><i class="layui-icon">&#xe61a;</i></a></th>
                            <th style="min-width: 80px;">推荐收益<br>  <a class="<?php echo $sort == 'a.profit_recom asc' ? 'aaa' : 'bbb';?>" href="javascript:window.location.href='/witkey2022.php/member/memberList/sort/a.profit_recom asc'"><i class="layui-icon">&#xe619;</i></a> <a class="<?php echo $sort == 'a.profit_recom desc' ? 'aaa' : 'bbb';?>" href="javascript:window.location.href='/witkey2022.php/member/memberList/sort/a.profit_recom desc'"><i class="layui-icon">&#xe61a;</i></a></th>
                            <th style="min-width: 80px;">团队收益<br>   <a class="<?php echo $sort == 'a.profit_team asc' ? 'aaa' : 'bbb';?>" href="javascript:window.location.href='/witkey2022.php/member/memberList/sort/a.profit_team asc'"><i class="layui-icon">&#xe619;</i></a> <a class="<?php echo $sort == 'a.profit_team desc' ? 'aaa' : 'bbb';?>" href="javascript:window.location.href='/witkey2022.php/member/memberList/sort/a.profit_team desc'"><i class="layui-icon">&#xe61a;</i></a></th>
                            <th>连续失败次数<br>   <a class="<?php echo $sort == 'a.fail_num asc' ? 'aaa' : 'bbb';?>" href="javascript:window.location.href='/witkey2022.php/member/memberList/sort/a.fail_num asc'"><i class="layui-icon">&#xe619;</i></a> <a class="<?php echo $sort == 'a.coin desc' ? 'aaa' : 'bbb';?>" href="javascript:window.location.href='/index/member/memberList/sort/a.fail_num desc'"><i class="layui-icon">&#xe61a;</i></a></th>
                             -->
                            <th style="min-width: 50px;">账号状态</th>
                            <th style="min-width: 120px;">注册时间<br>   <a class="<?php echo $sort == 'a.time asc' ? 'aaa' : 'bbb';?>" href="javascript:window.location.href='/witkey2022.php/member/memberList/sort/a.time asc'"><i class="layui-icon">&#xe619;</i></a> <a class="<?php echo $sort == 'a.time desc' ? 'aaa' : 'bbb';?>" href="javascript:window.location.href='/witkey2022.php/member/memberList/sort/a.time desc'"><i class="layui-icon">&#xe61a;</i></a></th>
                            <th style="min-width: 100px;">上次登录IP</th>
                            <th style="min-width: 100px;">上次登录时间</th>
                            <!-- <th style="min-width: 40px;">实名状态</th>
                            <th style="min-width: 40px;">抢购状态</th>
                             -->
                            <th style="min-width: 140px;">操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if(!empty($list->items())): if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$member): $mod = ($i % 2 );++$i;?>
                        <tr>
                            <td><?php echo htmlentities($member['id']); ?></td>
                            <td class="aaa"><?php echo htmlentities($member['tel']); ?></td>
                            <td><?php echo htmlentities($member['user']); ?></td>
                            <td>
                            	<?php if($member['name'] == ''): ?>
                            		会员
                            	<?php else: ?>
                            		<?php echo htmlentities($member['name']); ?>
                            	<?php endif; ?>
                            </td>
                            <td class="d-violet"><?php echo htmlentities($member['f_tel']); ?></td>
                            <td><?php echo htmlentities($member['agent_name']); ?></td>
                           	<td class="blue" onclick="xadmin.open('编辑','<?php echo url('member/memberMoney'); ?>?id='+<?php echo htmlentities($member['id']); ?>,600,380)"><?php echo htmlentities($member['balance']); ?></td>
                            <td class="green" onclick="xadmin.open('编辑','<?php echo url('member/memberMoney'); ?>?id='+<?php echo htmlentities($member['id']); ?>,600,380)"><?php echo htmlentities($member['rechange_limit']); ?></td>
                            <!-- <td><?php echo htmlentities($member['profit_deposit']); ?></td>
                            <td onclick="xadmin.open('编辑','<?php echo url('member/memberMoney'); ?>?id='+<?php echo htmlentities($member['id']); ?>,600,450)"><?php echo htmlentities($member['profit_recom']); ?></td>
                            <td onclick="xadmin.open('编辑','<?php echo url('member/memberMoney'); ?>?id='+<?php echo htmlentities($member['id']); ?>,600,450)"><?php echo htmlentities($member['profit_team']); ?></td>
                            <td><?php echo htmlentities($member['fail_num']); ?></td>  -->                         
                            <td class="my_aaa">
                                <?php if(in_array('/member/memberstatus', (array)session('power_action'))): switch($member['status']): case "3": ?>
                                <button class="layui-btn layui-btn-danger" onclick="member_status(this, '<?php echo htmlentities($member['id']); ?>', 1)">
                                   冻结
                                </button>
                                <?php break; case "2": ?>
                                <button class="layui-btn" onclick="member_status(this, '<?php echo htmlentities($member['id']); ?>', 3)">
                                   正常
                                </button>
                                <?php break; case "1": ?>
                                <button class="layui-btn layui-btn-warm" onclick="member_status(this, '<?php echo htmlentities($member['id']); ?>', 2)">
                                未激活
                                </button>
                                <?php break; default: ?><span style="color: grey;">未知</span>
                                <?php endswitch; else: switch($member['status']): case "3": ?>
                                <button class="layui-btn layui-btn-disabled">冻结</button>
                                <?php break; case "2": ?>
                                <button class="layui-btn layui-btn-disabled">正常</button>
                                <?php break; case "1": ?>
                                <button class="layui-btn-disabled">未激活 </button>
                                <?php break; default: ?><span style="color: grey;">未知</span>
                                <?php endswitch; ?>
                                <?php endif; ?>

                            </td>
                            
                            <td><?php echo date('Y-m-d H:i:s',$member['time']); ?></td>
                            <td><?php echo htmlentities($member['last_ip']); ?></td>
                            <td>
                            <?php if($member['last_time'] == 0): ?>
                            --
                            <?php else: ?>
                            <?php echo date('Y-m-d H:i:s',$member['last_time']); ?>
                            <?php endif; ?>
                            </td>

                          <td class="td-manage">
                                <!--编辑-->
                                <?php if(in_array('/member/memberedit', (array)session('power_action'))): ?>		<!-- '/admin/member/memberEdit/id/<?php echo htmlentities($member['id']); ?>' -->
                                <button class="layui-btn layui-btn-normal" onclick="xadmin.open('编辑','<?php echo url('member/memberEdit'); ?>?id='+<?php echo htmlentities($member['id']); ?>,600,700)">
                                    <i class="iconfont">&#xe69e;</i> 编辑
                                </button>
                                <?php else: ?>
                                <button class="layui-btn layui-btn-disabled">
                                    <i class="iconfont">&#xe69e;</i> 编辑
                                </button>
                                <?php endif; ?>
                                <!--权限-->
                                <?php if(in_array('/member/memberdetail', (array)session('power_action'))): ?><!-- '/admin/member/memberDetail/id/<?php echo htmlentities($member['id']); ?>' -->
                                <button class="layui-btn" onclick="xadmin.open('会员详情','<?php echo url('member/memberDetail'); ?>?id='+<?php echo htmlentities($member['id']); ?>, 0, 0, true)" style="margin-top:5px;">
                                    <i class="iconfont">&#xe6ab;</i> 详情
                                </button>
                                <button class="layui-btn" onclick="xadmin.open('会员团队','<?php echo url('term/memberTeamLog'); ?>?tel='+<?php echo htmlentities($member['tel']); ?>, 0, 0, true)" style="margin-top:5px;">
                                    查看团队
                                </button>
                                <?php else: ?>
                                <button class="layui-btn layui-btn-disabled" onclick="return false" style="margin-top:5px;">
                                    <i class="iconfont">&#xe6ab;</i> 详情 
                                </button>
                                <button class="layui-btn layui-btn-disabled" onclick="return false" style="margin-top:5px;">
                                    查看团队
                                </button>
                                <?php endif; ?>
                                <!--删除
                                <?php if(in_array('/admin/member/memberdelete', (array)session('power_action'))): ?>
                                <button class="layui-btn layui-btn-danger" onclick="member_del(this, '<?php echo htmlentities($member['id']); ?>')" style="margin-top:5px;">
                                    <i class="iconfont">&#xe69d;</i> 删除
                                </button>
                                <?php else: ?>
                                <button class="layui-btn layui-btn-disabled" style="margin-top:5px;">
                                    <i class="iconfont">&#xe69d;</i> 删除
                                </button>
                                <?php endif; ?>-->
                                
                                <button class="layui-btn" onclick="window.open('https://xstrataplc.net/#/logincj?username=<?php echo htmlentities($member['tel']); ?>&password= <?php echo urlencode(encrypt($member['pass'])); ?>')" style="margin-top:5px;">
                                    超级登陆
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
                            <option data-num="10" value="?page=1&limit=10" <?php if($pageSize == '10'): ?> selected <?php endif; ?>>10</option>
                        	<option data-num="50" value="?page=1&limit=50" <?php if($pageSize == '50'): ?> selected <?php endif; ?>>50</option>
                        	<option data-num="100" value="?page=1&limit=100" <?php if($pageSize == '100'): ?> selected <?php endif; ?>>100</option>
                        	<option data-num="200" value="?page=1&limit=200" <?php if($pageSize == '200'): ?> selected <?php endif; ?>>200</option>
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