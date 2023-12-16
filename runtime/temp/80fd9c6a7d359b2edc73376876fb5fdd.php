<?php /*a:2:{s:85:"/home/gemxpbra/public_html/application/admin/view/product/mutual_aid_member_list.html";i:1697722712;s:89:"/home/gemxpbra/public_html/application/admin/view/../../../public/static/common/base.html";i:1697722706;}*/ ?>
<!DOCTYPE html>
<html class="x-admin-sm">
<head>
    <meta charset="UTF-8">
    <title>会员互助</title>
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
        <a href="javascript:;">互助管理</a>
        <a><cite>会员互助</cite></a>
    </span>
    <a class="layui-btn layui-btn-small" style="line-height:1.6em;margin-top:3px;float:right"
       onclick="location.reload()" title="刷新">
        <i class="layui-icon layui-icon-refresh" style="line-height:30px"></i>
    </a>
</div>
<div class="layui-card-header">
    <?php if(in_array('/product/mutualaidmemberadd', (array)session('power_action'))): ?>	<!-- '/admin/mutualaid/mutualaidMemberAdd' -->
    <button class="layui-btn layui-btn-normal" onclick="xadmin.open('添加', '<?php echo url('product/mutualaidMemberAdd'); ?>', 500, 500)">
        <i class="layui-icon"></i>添加
    </button>
    <?php endif; ?>
</div>
<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-card">
                         <div class="layui-tab-content">
                            <form class="layui-form layui-col-space5">
                                <div class="layui-input-inline layui-show-xs-block">
                                    <div class="input-lable">关键词搜索:</div>
                                    <input type="text" name="serach" value="<?php echo htmlentities($param_serach); ?>" placeholder="账号/订单号" autocomplete="off" class="layui-input">
                                </div>
                                <!--<div class="layui-input-inline layui-show-xs-block">-->
                                <!--    <select name="type">-->
                                <!--        <option value="0">类型</option>-->
                                <!--        <option value="1"<?php if($param_type == 1) echo 'selected';?>>升值中</option>-->
                                <!--        <option value="2"<?php if($param_type == 2) echo 'selected';?>>升值结束</option>-->
                                <!--        <option value="4"<?php if($param_type == 4) echo 'selected';?>>发布申请</option>-->
                                <!--        <option value="7"<?php if($param_type == 7) echo 'selected';?>>拆分获得</option>-->
                                <!--    </select>-->
                                <!--</div>-->
                                <div class="layui-input-inline layui-show-xs-block">
                                    <select name="status">
                                        <option value="0">状态</option>
                                        <option value="1"<?php if($param_status == 1) echo 'selected';?>>升值中</option>
                                        <option value="2"<?php if($param_status == 2) echo 'selected';?>>已完结</option>
                                        <!--<option value="3"<?php if($param_status == 3) echo 'selected';?>>转让中</option>-->
                                        <!--<option value="4"<?php if($param_status == 4) echo 'selected';?>>已转让</option>-->
                                    </select>
                                </div>
                                <div class="layui-input-inline layui-show-xs-block">
                                    <select name="name">
                                        <option value="">选择产品</option>
                                        <?php if(is_array($info) || $info instanceof \think\Collection || $info instanceof \think\Paginator): $i = 0; $__LIST__ = $info;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$aid): $mod = ($i % 2 );++$i;?>
				                        <option value="<?php echo htmlentities($aid['id']); ?>" <?php if($param_name == $aid['name']) echo 'selected';?>><?php echo htmlentities($aid['name']); ?></option>
				                        <?php endforeach; endif; else: echo "" ;endif; ?>
                                    </select>
                                </div>
                                <div class="layui-inline layui-show-xs-block">
                                    <input class="layui-input" autocomplete="off" placeholder="升值开始时间" name="add_time_s"
                                           value="<?php echo htmlentities($param_add_time_s); ?>" id="add_time_s">
                                </div>
                                <div class="layui-inline layui-show-xs-block">
                                    <input class="layui-input" autocomplete="off" placeholder="升值结束时间" name="add_time_e"
                                           value="<?php echo htmlentities($param_add_time_e); ?>" id="add_time_e">
                                </div>
                                <div class="layui-input-inline layui-show-xs-block">
                                    <button class="layui-btn" lay-submit="" lay-filter="sreach">
                                        <i class="layui-icon">&#xe615;</i>
                                    </button>
                                </div>
                            </form>
                        </div>
                <div class="layui-card-body layui-card-table">
                    <table class="layui-table layui-form">
                        <thead>
                        <tr align="center">
                            <th style="min-width: 50px;"><b>ID</b></th>
                            <th style="min-width: 50px;"><b>会员ID</b></th>
                            <th><b>账号</b></th>
                            <!--<th style="min-width: 140px;"><b>订单编号</b></th>-->
                            <th style="min-width: 100px;"><b>产品</b></th>
                            <!--<th style="min-width: 110px;"><b>宠物编号</b></th>-->
                            <th><b>获得时价格</b></th>
                            <th><b>升值后价格</b></th>
                            <th style="min-width: 50px;"><b>升值天数</b></th>
                            <th style="min-width: 50px;"><b>已升值<br/>次数</b></th>
                            <th style="min-width: 80px;"><b>升值比例 &nbsp;&nbsp;&nbsp;(天%)</b></th>
                            <th><b>类型</b></th>
                            <th style="min-width: 60px;"><b>状态</b></th>
                            <th style="min-width: 50px;"><b>是否失效</b></th>
                            <th style="min-width: 100px;"><b>升值开始时间</b></th>
                            <th style="min-width: 100px;"><b>升值结束时间</b></th>
                            <th style="min-width: 140px;"><b>操作</b></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if(!empty($list->items())): if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$user): $mod = ($i % 2 );++$i;?>
                        <tr align="center">
                            <td><?php echo htmlentities($user['id']); ?></td>
                            <td><?php echo htmlentities($user['uid']); ?></td>
                            <td><?php echo htmlentities($user['tel']); if($user['real_name_log'] != ''): ?>(<?php echo htmlentities($user['real_name_log']); ?>)<?php endif; ?></td>
                            <!--<td><?php echo htmlentities($user['orderNo']); ?></td>-->
                            <td><?php echo htmlentities($user['name']); ?></td>
                            <!--<td><?php echo htmlentities($user['purchase_no']); ?></td>-->
                            <td><?php echo htmlentities($user['get_price']); ?></td>
                            <td><?php echo htmlentities($user['new_price']); ?></td>
                            <td><?php echo htmlentities($user['days']); ?></td>
                            <td><?php echo htmlentities($user['up_time']); ?></td>
                            <td><?php echo htmlentities($user['rate']); ?></td>
                            
                            <?php switch($user['deal_type']): case "1": ?><td style="color: green"><span style="font-size:20px">●</span>升值中</td><?php break; case "2": ?><td style="color: red"><span style="font-size:20px">●</span>已完结</td><?php break; case "4": ?><td style="color: orange"><span style="font-size:20px">●</span>发布申请</td><?php break; default: ?>
                            	<td style="color: violet"><span style="font-size:20px">●</span>购买获得</td>
                            <?php endswitch; switch($user['status']): case "1": ?><td style="color: green"><span style="font-size:20px">●</span>升值中</td><?php break; case "2": ?><td style="color: blue"><span style="font-size:20px">●</span>已完结</td><?php break; ?>
                            <!--<?php case "3": ?><td style="color: orange"><span style="font-size:20px">●</span>转让中</td><?php break; ?>-->
                            <!--<?php case "4": ?><td style="color: red"><span style="font-size:20px">●</span>已转让</td><?php break; ?>-->
                            <?php default: ?>
                            <td style="color: black"><span style="font-size:20px">●</span>已完结</td>
                            <?php endswitch; switch($user['is_exist']): case "1": ?><td style="color: green"><span style="font-size:20px">●</span>正常</td><?php break; case "0": ?><td style="color: black"><span style="font-size:20px">●</span>失效</td><?php break; default: ?>
                            <?php endswitch; ?>
                            
                            <td><?php echo date('m-d H:i:s',$user['sta_time']); ?></td>
                            <?php if(($user['end_time'] == 0)): ?>
                            <td> -- </td>
                            <?php else: ?>
                            <td><?php echo date('m-d H:i:s',$user['end_time']); ?></td>
                            <?php endif; ?>
                            
                            <td>

                                <?php if(in_array('/product/mutualaidmemberedit', (array)session('power_action'))): ?>	<!-- '/admin/mutualaid/mutualaidMemberEdit/id/<?php echo htmlentities($user['id']); ?>' -->
                                <button title="编辑" class="layui-btn layui-btn-normal" onclick="xadmin.open('编辑','<?php echo url('product/mutualaidMemberEdit'); ?>?id='+<?php echo htmlentities($user['id']); ?>,600,600)">
                                    <i class="iconfont">&#xe69e;</i> 
                                </button>
                                <?php else: ?>
                                <button title="编辑" class="layui-btn layui-btn-disabled" onclick="return false">
                                    <i class="iconfont">&#xe69e;</i> 
                                </button>
                                <?php endif; if(in_array('/product/mutualaidmemberdelete', (array)session('power_action'))): ?>
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
                        <?php endforeach; endif; else: echo "" ;endif; else: ?>
                        <tr>
                            <td colspan="15" class="nodata_td">无记录</td>
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
layui.use(['laydate', 'form'], function () {
    var laydate = layui.laydate;
    laydate.render({
        elem: '#add_time_s',
        type: 'datetime' ,
        range: '~',
        format: 'yyyy-MM-dd HH:mm:ss'
    });
    laydate.render({
        elem: '#add_time_e',
        type: 'datetime' ,
        range: '~',
        format: 'yyyy-MM-dd HH:mm:ss'
    });
});

    //删除
    function delete_confirm(obj, id) {
        layer.confirm('是否同意删除？', function(index) {
            $.ajax({
                url: "<?php echo url('product/mutualaidmemberdelete'); ?>",//'/admin/mutualaid/mutualaidmemberdelete',
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