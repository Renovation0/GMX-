<?php /*a:2:{s:78:"/home/gemxpbra/public_html/application/admin/view/wallet/tm_withdraw_list.html";i:1697722712;s:89:"/home/gemxpbra/public_html/application/admin/view/../../../public/static/common/base.html";i:1697722706;}*/ ?>
<!DOCTYPE html>
<html class="x-admin-sm">
<head>
    <meta charset="UTF-8">
    <title>提现记录</title>
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
        <a><cite>提现记录</cite></a>
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
                        <div class="layui-tab-content">
                            <form class="layui-form layui-col-space5">
                                <div class="layui-input-inline layui-show-xs-block">
                                    <select name="status">
                                        <option value="-1">提现状态</option>
                                        <option value="0"<?php if($param_status == 0) echo 'selected';?>>申请中</option>
                                        <option value="3"<?php if($param_status == 3) echo 'selected';?>>提现中</option>
                                        <option value="1"<?php if($param_status == 1) echo 'selected';?>>提现成功</option>
                                        <option value="2"<?php if($param_status == 2) echo 'selected';?>>提现失败</option>
                                    </select>
                                </div>
                                <div class="layui-input-inline layui-show-xs-block">
                                    <select name="type">
                                        <option value="0">提现通道（全部）</option>
                                        <?php if(is_array($channelList) || $channelList instanceof \think\Collection || $channelList instanceof \think\Paginator): $i = 0; $__LIST__ = $channelList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$channel): $mod = ($i % 2 );++$i;?>
                                            <option value="<?php echo htmlentities($channel['id']); ?>" <?php if($param_type == $channel['id']){echo "selected";}?> ><?php echo htmlentities($channel['name']); ?></option>
                                        <?php endforeach; endif; else: echo "" ;endif; ?>
                                    </select>
                                </div>
                                <div class="layui-input-inline layui-show-xs-block">
                                    <input type="text" name="serach" value="<?php echo htmlentities($param_serach); ?>" placeholder="搜索内容" autocomplete="off"
                                           class="layui-input">
                                </div>
                                <div class="layui-inline layui-show-xs-block">
                                    <input class="layui-input" autocomplete="off" placeholder="添加开始时间" name="add_time_s"
                                           value="<?php echo htmlentities($param_add_time_s); ?>" id="add_time_s">
                                </div>
                                <div class="layui-inline layui-show-xs-block">
                                    <input class="layui-input" autocomplete="off" placeholder="添加截至时间" name="add_time_e"
                                           value="<?php echo htmlentities($param_add_time_e); ?>" id="add_time_e">
                                </div>
                                <div class="layui-input-inline layui-show-xs-block">
                                    <button class="layui-btn" lay-submit="" lay-filter="sreach">
                                        <i class="layui-icon">&#xe615;</i>
                                    </button>
                                </div>
                            </form>
                        </div>
                        <div class="layui-card-header">
                             <select name="typeall">
                                <option value="0">提现通道（全部）</option>
                                <?php if(is_array($channelList) || $channelList instanceof \think\Collection || $channelList instanceof \think\Paginator): $i = 0; $__LIST__ = $channelList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$channel): $mod = ($i % 2 );++$i;?>
                                    <option value="<?php echo htmlentities($channel['id']); ?>" <?php if($param_type == $channel['id']){echo "selected";}?> ><?php echo htmlentities($channel['name']); ?></option>
                                <?php endforeach; endif; else: echo "" ;endif; ?>
                            </select>
                            <button class="layui-btn" onclick="changeAll()"><i class="layui-icon"></i>批量修改通道</button>
                        </div>
                        <div class="layui-card-body ">
                            <table class="layui-table layui-form">
                                <thead>
                                <tr>
                                    <th style="width: 10px;">
                                      <input type="checkbox" lay-filter="checkall" name="" lay-skin="primary">
                                    </th>
                                    <th style="width: 10px;">id</th>
                                    <th style="width: 20px;">订单ID</th>
                                    <th style="width: 20px;">会员ID</th>
                                    <th style="width: 20px;">会员名称</th>
                                    <th style="width: 20px;">会员电话</th>
                                    <th style="width: 20px;">提现金额</th>
                                    <th style="width: 20px;">提现地址</th>
                                    <th style="width: 20px;">提现通道</th>
                                    <th style="width: 20px;">提现失败次数</th>
                                    <th style="width: 20px;">交易ID</th>
                                    <th style="width: 20px;">充值时间</th>
                                    <th style="width: 20px;">到账时间</th>
                                    <th style="width: 20px;">状态</th>
                                    <th style="width: 20px;">操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if(!empty($orderList->items())): if(is_array($orderList) || $orderList instanceof \think\Collection || $orderList instanceof \think\Paginator): $i = 0; $__LIST__ = $orderList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$member): $mod = ($i % 2 );++$i;?>
                                <tr>
                                    <td>
                                    <?php if($member['status'] == 0): ?>
                                        <input type="checkbox" name="id" value="<?php echo htmlentities($member['id']); ?>"   lay-skin="primary"> 
                                    <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlentities($member['id']); ?></td>
                                    <td><?php echo htmlentities($member['order_id']); ?></td>
                                    <td><?php echo htmlentities($member['uid']); ?></td>
                                    <td><?php echo htmlentities($member['user']); ?></td>
                                    <td><?php echo htmlentities($member['tel']); ?></td>
                                    <td><?php echo htmlentities($member['num']); ?></td>
                                    <td><?php echo htmlentities($member['address']); ?></td>
                                    <td>
                                        <?php echo htmlentities($member['channel']); ?>
                                    </td>
                                    <td><?php echo htmlentities($member['withdrawfail']); ?></td>
                                    <td><?php echo htmlentities($member['hash']); ?></td>
                                    <?php if($member['create_time'] == 0): ?>
                                    <td> -- </td>
                                    <?php else: ?>
                                    <td><?php echo htmlentities(date('Y-m-d H:i:s',!is_numeric($member['create_time'])? strtotime($member['create_time']) : $member['create_time'])); ?></td>
                                    <?php endif; if($member['update_time'] == 0): ?>
                                    <td> -- </td>
                                    <?php else: ?>
                                    <td><?php echo htmlentities(date('Y-m-d H:i:s',!is_numeric($member['update_time'])? strtotime($member['update_time']) : $member['update_time'])); ?></td>
                                    <?php endif; ?>
                                    <td>
                                        <?php switch($member['status']): case "0": ?><span style="color: red;">已申请</span><?php break; case "1": ?><span style="color: green;">提现成功</span><?php break; case "2": ?><span style="color: black;">提现失败</span><?php break; case "3": ?><span style="color: blue;">提现中</span><?php break; ?>
                                        <?php endswitch; ?>
                                    </td>
                                    <td>
			                            <?php switch($member['status']): case "0": ?>
			                                <button class="layui-btn layui-btn-warm" onclick="up_confirm(this, '<?php echo htmlentities($member['id']); ?>',3)">
			                                     同意
			                                </button>
			                                
			                                <button style="background:red;" class="layui-btn layui-btn-warm" onclick="up_confirm(this, '<?php echo htmlentities($member['id']); ?>',2)">
			                                    拒绝
			                                </button>
			                                
			                                
			                                <button class="layui-btn layui-btn-normal" onclick="xadmin.open('编辑','<?php echo url('wallet/tmtypecheck'); ?>?id='+<?php echo htmlentities($member['id']); ?>,500,350)">
			                                    <i class="iconfont">&#xe69e;</i> 支付通道
			                                </button>
				                            <?php break; case "3": ?>
			                                <!--<button class="layui-btn layui-btn-warm" onclick="up_confirm(this, '<?php echo htmlentities($member['id']); ?>',3)">
			                                     同意
			                                </button>-->
			                                
			                                <button style="background:red;" class="layui-btn layui-btn-warm" onclick="up_confirm(this, '<?php echo htmlentities($member['id']); ?>',2)">
			                                    拒绝
			                                </button>
			                                
			                                
			                                <button style="background:purple;" class="layui-btn layui-btn-warm" onclick="check_confirm(this, '<?php echo htmlentities($member['id']); ?>',1)">
			                                    确认已提现
			                                </button>
			                                
				                            <?php break; default: ?>
			                            <?php endswitch; ?>
			                             <button class="layui-btn layui-btn-normal" onclick="xadmin.open('编辑','<?php echo url('wallet/seebank'); ?>?id='+<?php echo htmlentities($member['bank_id']); ?>,500,550)">
		                                    <i class="iconfont">&#xe69e;</i> 查看银行卡信息
		                                </button>
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
                                <?php echo $orderList; ?>
                            </div>
                        </div>
            </div>
        </div>
    </div>
</div>

</body>

<script>

    // 切换模块
    function changeModule(module_id) {
        if (module_id != active_module) {
            window.location.href = "/admin/game/gameList/my_active_module/" + module_id;
        }
    }


    layui.use('form', function () {
        let form = layui.form;
        form.render();
        // 监听全选
        form.on('checkbox(checkall)', function(data){
    
          if(data.elem.checked){
            $('tbody input').prop('checked',true);
          }else{
            $('tbody input').prop('checked',false);
          }
          form.render('checkbox');
        }); 
    });
    
    function changeAll (argument) {
        var ids = [];

        // 获取选中的id 
        $('tbody input').each(function(index, el) {
            if($(this).prop('checked')){
               ids.push($(this).val())
            }
        });
        
        var type = $("select[name='typeall']").val();

        layer.confirm('确认要修改吗？',function(index){
            //捉到所有被选中的，发异步进行删除
            $.ajax({
                url: "<?php echo url('wallet/changeall'); ?>",//'/admin/member/agree',
                type: 'post',
                data: {
                    ids: ids,
                    type:type
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
    
    //日期选择
    layui.use(['laydate', 'form'], function () {
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
   

    //操作通过/拒绝
    function up_confirm(obj, id, status) {
        layer.confirm(status === 3 ? '确定通过？' : '确定拒绝？', function(index) {
            $.ajax({
                url: "<?php echo url('wallet/tmagree'); ?>",//'/admin/member/agree',
                type: 'post',
                data: {
                    id: id,
                    status:status
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
    
    //操作
    function check_confirm(obj, id, status) {
        layer.confirm('确认已提现？', function(index) {
            $.ajax({
                url: "<?php echo url('wallet/checkordersub'); ?>",//'/admin/member/agree',
                type: 'post',
                data: {
                    id: id,
                    status:status
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