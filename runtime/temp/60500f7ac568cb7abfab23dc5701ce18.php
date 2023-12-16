<?php /*a:2:{s:75:"/home/gemxpbra/public_html/application/admin/view/member/member_detail.html";i:1697722712;s:89:"/home/gemxpbra/public_html/application/admin/view/../../../public/static/common/base.html";i:1697722706;}*/ ?>
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
	.node_item {
		clear: both;
		box-sizing: border-box;
		height: 24px;
		padding: 4px;
		cursor:pointer
	}
	.node_item .select_box {
		box-sizing: border-box;
		float: left;
		width: 16px;
		height: 16px;
		border: 1px solid #cfcfcf;
		margin-right: 10px;
	}
	.node_item .select_box.have {
		background-color: grey;
	}
	.node_item .select_box.chose {
		background-color: #22af22;
	}
	.node_item .node_name {
		float: left;
		height: 16px;
		line-height: 16px;
	}
	.node_item .tab {
		float: left;
		height: 16px;
		width: 26px;
	}
</style>

</head>
<body>

<div class="layui-fluid">
	<div class="layui-row layui-col-space15" >
		<div class="layui-col-md12">
			<div class="layui-card">
				<div class="layui-card-body ">
					<ul class="layui-tab-title">

						<li id="module_1" onclick="changeModule('1',<?php echo htmlentities($id); ?>)">基本信息</li>
						<!-- <li id="module_12" onclick="changeModule('12',<?php echo htmlentities($id); ?>)">实名信息</li> -->
						<li id="module_2" onclick="changeModule('2',<?php echo htmlentities($id); ?>)">产品列表</li>
					</ul>
					<div class="layui-tab-content">
						<div class="layui-tab-item" id="module_menus_box_1">
							<div class="layui-form-item">
								<!--基本信息start-->
								<div class="layui-input-inline">
									账号 ：<span style="color: darkgreen;"><?php echo htmlentities($user['tel']); ?></span>
								</div>
								<div class="layui-input-inline">
								昵称 ：<span style="color: darkgreen;"><?php echo htmlentities($user['user']); ?></span>
								</div>
								<div class="layui-input-inline">
									状态 ：
									<?php switch($user['status']): case "1": ?><span style="color: darkgreen;">未激活</span><?php break; case "2": ?><span style="color: orangered;">已激活</span><?php break; case "3": ?><span style="color: grey;">冻结</span><?php break; default: ?><span style="color: grey;">错误状态</span>
									<?php endswitch; ?>
								</div>
								<!-- <div class="layui-input-inline">
									实名 ：
									<?php switch($user['real_name_status']): case "0": ?><span style="color: darkgreen;">未申请</span><?php break; case "1": ?><span style="color: darkgreen;">已实名</span><?php break; case "2": ?><span style="color: orangered;">已提交</span><?php break; case "3": ?><span style="color: grey;">已驳回</span><?php break; default: ?><span style="color: grey;">未知</span>
									<?php endswitch; ?>
								</div> -->
								<div class="layui-input-inline">
									推荐人 ：<span style="color: darkgreen;"><?php echo htmlentities($user['f_tel']); ?></span>
								</div>
								<div class="layui-input-inline">
									推荐码 ：<span style="color: darkgreen;"><?php echo htmlentities($user['guid']); ?></span>
								</div>
								<div class="layui-input-inline">
									注册IP ：<span style="color: darkgreen;"><?php echo htmlentities($user['register_ip']); ?></span>
								</div>
								<div class="layui-input-inline">
									登录IP ：<span style="color: darkgreen;"><?php echo htmlentities($user['last_ip']); ?></span>
								</div><div class="layui-input-inline">
									会员等级 ：<span style="color: darkgreen;"><?php echo htmlentities($user['level_name']); ?></span>
								</div>
								<!--基本信息end-->
								<!--资金相关start-->
								<div class="layui-input-inline">
									可提现余额 ：<span style="color: darkgreen;"><?php echo htmlentities($user['balance']); ?></span>
								</div>
								<div class="layui-input-inline">
									充值余额 ：<span style="color: darkgreen;"><?php echo htmlentities($user['coin']); ?></span>
								</div>
								<!-- <div class="layui-input-inline">
									收益转存 ：<span style="color: darkgreen;"><?php echo htmlentities($user['profit_deposit']); ?></span>
								</div> -->
								<div class="layui-input-inline">
									推荐收益 ：<span style="color: darkgreen;"><?php echo htmlentities($user['profit_recom']); ?></span>
								</div>
								<!-- <div class="layui-input-inline">
									团队收益 ：<span style="color: darkgreen;"><?php echo htmlentities($user['profit_team']); ?></span>
								</div> -->

								<!--资金信息end-->

								<!--团队相关start-->
								<div class="layui-input-inline">
									有效直推 ：<span style="color: darkgreen;"><?php echo htmlentities($user['zt_yx_num']); ?></span>
								</div>
								<!-- <div class="layui-input-inline">
									有效团队 ：<span style="color: darkgreen;"><?php echo htmlentities($user['yx_team']); ?></span>
								</div> -->

								<!--团队相关end-->

							</div>
						</div>
						<!-- 实名信息-->
						<div class="layui-tab-item" id="module_menus_box_12">
						<?php if($user['real_name_status'] == 1): ?>
							<div class="layui-form-item">
								<label for="cycle" class="layui-form-label" style="width: 150px">
									<span class="x-red">*</span>真实姓名
								</label>
								<div class="layui-input-inline">
									<input type="text" value="<?php echo htmlentities($user['real_name']); ?>" readonly autocomplete="off" class="layui-input">
								</div>
							</div>
							<div class="layui-form-item">
								<label for="cycle" class="layui-form-label"  readonly style="width: 150px">
									<span class="x-red">*</span>身份证号
								</label>
								<div class="layui-input-inline">
									<input type="text" value="<?php echo htmlentities($user['idcard']); ?>" readonly  autocomplete="off" class="layui-input">
								</div>
							</div>
							<div class="layui-form-item">
								<label for="cycle" class="layui-form-label" style="width: 150px">
									<span class="x-red">*</span>联系方式
								</label>
								<div class="layui-input-inline">
									<input type="text"  value="<?php echo htmlentities($user['tel']); ?>" readonly autocomplete="off" class="layui-input">
								</div>
							</div>
							<div class="layui-form-item">
								<label for="cycle" class="layui-form-label" style="width: 150px">
									<span class="x-red">*</span>开户行
								</label>
								<div class="layui-input-inline">
									<input type="text"  value="<?php echo htmlentities($payment[3]['account_num']); ?>" readonly autocomplete="off" class="layui-input">
								</div>
							</div>
							<div class="layui-form-item">
								<label for="cycle" class="layui-form-label" style="width: 150px">
									<span class="x-red">*</span>开户名
								</label>
								<div class="layui-input-inline">
									<input type="text"  value="<?php echo htmlentities($payment[3]['name']); ?>" readonly autocomplete="off" class="layui-input">
								</div>
							</div>
							<div class="layui-form-item">
								<label for="cycle" class="layui-form-label" style="width: 150px">
									<span class="x-red">*</span>银行卡号
								</label>
								<div class="layui-input-inline">
									<input type="text"  value="<?php echo htmlentities($payment[3]['bank_num']); ?>" readonly autocomplete="off" class="layui-input">
								</div>
							</div>

							<div class="layui-form-item">
								<label for="thumbnail" class="layui-form-label" style="width: 150px">
									<span class="x-red">*</span>支付宝收款码
								</label>
								<div class="layui-input-block">
									<div class="thumbnail_box" id="thumbnail_box">
										<?php if($payment[1]['receive_qrcode'] != ''): ?>
										<img id="thumbnail_img" src="<?php echo htmlentities($payment[1]['receive_qrcode']); ?>" alt="" width="100" onclick="xadmin.open('收款码','<?php echo htmlentities($payment[1]['receive_qrcode']); ?>',600,600)">
										<?php else: ?>
										<img id="thumbnail_img" src="/../upload/404_img.jpg" alt="" width="100" onclick="xadmin.open('收款码','/../upload/404_img.jpg',750,600)">
										<?php endif; ?>	
									</div>
								</div>
							</div>
							<div class="layui-form-item">
								<label for="thumbnail" class="layui-form-label" style="width: 150px">
									<span class="x-red">*</span>微信收款码
								</label>
								<div class="layui-input-block">
									<div class="thumbnail_box" id="thumbnail_box">
										<?php if($payment[2]['receive_qrcode'] != ''): ?>
										<img id="thumbnail_img" src="<?php echo htmlentities($payment[2]['receive_qrcode']); ?>" alt="" width="100" onclick="xadmin.open('收款码','<?php echo htmlentities($payment[2]['receive_qrcode']); ?>',600,600)">
										<?php else: ?>
										<img id="thumbnail_img" src="/../upload/404_img.jpg" alt="" width="100" onclick="xadmin.open('收款码','/../upload/404_img.jpg',750,600)">
										<?php endif; ?>
									</div>
								</div>
							</div>
							<!-- <div class="layui-form-item">
								<label for="thumbnail" class="layui-form-label" style="width: 150px">
									<span class="x-red">*</span>USDT收款码
								</label>
								<div class="layui-input-block">
									<div class="thumbnail_box" id="thumbnail_box">
										<?php if($payment[4]['receive_qrcode'] != null): ?>
										<img id="thumbnail_img" src="<?php echo htmlentities($payment[4]['receive_qrcode']); ?>" alt="" width="100" onclick="xadmin.open('收款码','<?php echo htmlentities($payment[4]['receive_qrcode']); ?>',600,600)">
										<?php else: ?>
										<img id="thumbnail_img" src="/../upload/404_img.jpg" alt="" width="100" onclick="xadmin.open('收款码','/../upload/404_img.jpg',750,600)">
										<?php endif; ?>
									</div>
								</div>
							</div> -->
						<?php else: ?>
							用户暂未实名
						<?php endif; ?>

						</div>
						<div class="layui-tab-item" id="module_menus_box_2">
							<div class="layui-card-body layui-card-table">
								<table class="layui-table">
									<thead>
									<tr>
										<th style="min-width: 120px;">产品名称</th>
										<th style="min-width: 120px;">订单编号</th>
										<th style="min-width: 120px;">产品编号</th>
										<th style="min-width: 120px;">价值</th>
										<th style="min-width: 120px;">升值天数</th>
										<th style="min-width: 120px;">价值比例(%)</th>
									</tr>
									</thead>
									<tbody>
									<?php if(!empty($mutualaid->items())): if(is_array($mutualaid) || $mutualaid instanceof \think\Collection || $mutualaid instanceof \think\Paginator): $i = 0; $__LIST__ = $mutualaid;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$mutu): $mod = ($i % 2 );++$i;?>
									<tr>
										<td><?php echo htmlentities($mutu['name']); ?></td>
										<td><?php echo htmlentities($mutu['orderNo']); ?></td>
										<td><?php echo htmlentities($mutu['purchase_no']); ?></td>
										<td><?php echo htmlentities($mutu['new_price']); ?></td>
										<td><?php echo htmlentities($mutu['days']); ?></td>
										<td><?php echo htmlentities($mutu['rate']); ?></td>

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
									<?php echo $mutualaid; ?>
								</div>
							</div>
						</div>

					</div>
				</div>
			</div>
		</div>
	</div>
</div>

</body>

<script>
	var active_module = '<?php echo htmlentities($my_active_module); ?>'; // 当前模块
	$("#module_"+active_module).addClass('layui-this'); // 初始化系统模块
	$("#module_menus_box_"+active_module).addClass('layui-show'); // 初始化系统模块菜单

	// 切换模块
	function changeModule(module_id,id) {
		if(module_id != active_module){
			/*active_module = module_id; // 切换模块
            $(".layui-tab-item").removeClass('layui-show'); // 先隐藏所有菜单
            $("#module_menus_box_"+active_module).addClass('layui-show'); // 显示当前模块菜单*/
			//window.location.href="/admin/member/memberDetail/id/"+<?php echo htmlentities($id); ?>+"/my_active_module/"+module_id;
            window.location.href="<?php echo url('member/memberDetail'); ?>?id="+id+"&my_active_module="+module_id;
		}
	}


	layui.use(['form'], function(){
		let form = layui.form;
		form.render();
		form.on('submit(role_power)', function(data){
			$.ajax({
				url: data.form.action,
				type: 'post',
				data: {
					id: data.field.id,
					power: chose_ids
				},
				success: function (data) {
					if(data.code === 1){
						layer.msg(data.msg);
						setTimeout(function(){
							parent.location.reload();
						}, 500);
					}else {
						layer.msg(data.msg);
					}
				}
			});
			return false;
		});
	});
</script>

</html>