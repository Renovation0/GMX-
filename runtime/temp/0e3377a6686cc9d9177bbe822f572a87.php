<?php /*a:2:{s:80:"/home/gemxpbra/public_html/application/admin/view/member/member_balance_log.html";i:1697722712;s:89:"/home/gemxpbra/public_html/application/admin/view/../../../public/static/common/base.html";i:1697722706;}*/ ?>
<!DOCTYPE html>
<html class="x-admin-sm">
<head>
    <meta charset="UTF-8">
    <title>用户资金记录</title>
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
        <a href="javascript:;">用户</a>
        <a href="javascript:;">用户管理</a>
        <a><cite>资金记录</cite></a>
    </span>
    <a class="layui-btn layui-btn-small" style="line-height:1.6em;margin-top:3px;float:right"
       onclick="location.reload()" title="刷新">
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
	                            <select name="type">
	                                <option value="0" <?php if($param_type == 0) echo 'selected';?>>记录类型</option>
	                                <option value="1" <?php if($param_type == 2) echo 'selected';?>>充值余额</option>
	                                <option value="2" <?php if($param_type == 2) echo 'selected';?>>可提现余额</option>
	                                <option value="5" <?php if($param_type == 5) echo 'selected';?>>推荐收益</option>
	                                <!--<option value="4" <?php if($param_type == 4) echo 'selected';?>>收益转存</option>
	                                <option value="6" <?php if($param_type == 6) echo 'selected';?>>团队收益</option>
	                                <option value="11" <?php if($param_type == 11) echo 'selected';?>>辅币</option>
 	                                <option value="101" <?php if($param_type == 101) echo 'selected';?>>出售</option>
	                                <option value="102" <?php if($param_type == 102) echo 'selected';?>>买入</option> -->
	                            </select>
	                        </div>
	                        <div class="layui-input-inline layui-show-xs-block">
	                            <input type="text" name="user_tel" value="<?php echo htmlentities($user_tel); ?>" placeholder="手机号" autocomplete="off"
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
                <div class="layui-card-body layui-card-table">
                    <table class="layui-table layui-form">
                        <thead>
                        <tr>
                            <th style="min-width: 50px;">ID</th>
                            <th>用户</th>
                            <th style="min-width: 120px;">变动金额</th>
                            <th style="min-width: 120px;">变动后金额</th>
                            <th style="min-width: 140px;">说明</th>
                            <th>类型</th>
                            <th style="min-width: 120px;">时间</th>
                            <th style="min-width: 50px;">操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if(!empty($list['list']->items())): if(is_array($list['list']) || $list['list'] instanceof \think\Collection || $list['list'] instanceof \think\Paginator): $i = 0; $__LIST__ = $list['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$user): $mod = ($i % 2 );++$i;?>
                        <tr>
                            <td><?php echo htmlentities($user['id']); ?></td>
                            <td><?php echo htmlentities($user['tel']); ?></td>
                            <td><?php echo htmlentities($user['change_money']); ?></td>
                            <td><?php echo htmlentities($user['after_money']); ?></td>
                            <td><?php echo htmlentities($user['message']); ?></td>
                            <td>
                            <?php switch($user['type']): case "1": ?>充值余额<?php break; case "2": ?>可提现余额<?php break; case "5": ?>推荐收益<?php break; default: ?>
                            未知
                            <?php endswitch; ?>                           
                            </td>
                            <td><?php echo htmlentities(date('Y-m-d H:i:s',!is_numeric($user['bo_time'])? strtotime($user['bo_time']) : $user['bo_time'])); ?></td>
                            <td>
	                            <!--删除-->
	                            <?php if(in_array("/member/memberbalancelogdel", (array)session('power_action'))): ?>
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
                        <?php echo $list['list']; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>

<script>

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

    layui.use('form', function () {
        let form = layui.form;
        form.render();
    });
    
    // 删除记录
    function member_del(obj, id) {
        layer.confirm('确认要删除吗？', function(index) {
            $.ajax({
                url: "<?php echo url('member/memberBalanceLogDel'); ?>",//'/admin/member/memberPayBindDelete',
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