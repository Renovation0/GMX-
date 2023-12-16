<?php /*a:2:{s:68:"/home/gemxpbra/public_html/application/admin/view/index/welcome.html";i:1697722712;s:89:"/home/gemxpbra/public_html/application/admin/view/../../../public/static/common/base.html";i:1697722706;}*/ ?>
<!DOCTYPE html>
<html class="x-admin-sm">
<head>
    <meta charset="UTF-8">
    <title>欢迎页</title>
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
    
<style type="text/css">

.tab-addtabs .tab-pane {
    height: 100%;
    width: 100%;
}
.tab-content > .active {
    display: block;
}
.panel-body {
    padding: 15px;
}
.panel-title {
    margin-top: 0;
    margin-bottom: 0;
    font-size: 14px;
    color: inherit;
}
.text-gray {
    color: #d2d6de !important;
}
small, .small {
    font-size: 91%;
}
.col-xs-6 {
    width: 20%;
	float: left;
    min-height: 1px;
    padding-left: 15px;
    padding-right: 15px;
}
.panel {
    margin-bottom: 17px;
    border: 1px solid transparent;
    border-radius: 3px;
    box-shadow: 0 1px 1px rgb(0 0 0 / 5%);
}
.row {
    margin-left: -15px;
    margin-right: -15px;
}

    .sm-st {
        background: #fff;
        padding: 20px;
        -webkit-border-radius: 3px;
        -moz-border-radius: 3px;
        border-radius: 3px;
        margin-bottom: 20px;
        -webkit-box-shadow: 0 1px 0px rgba(0, 0, 0, 0.05);
        box-shadow: 0 1px 0px rgba(0, 0, 0, 0.05);
    }

    .sm-st-icon {
        width: 60px;
        height: 60px;
        display: inline-block;
        line-height: 60px;
        text-align: center;
        font-size: 30px;
        background: #eee;
        -webkit-border-radius: 5px;
        -moz-border-radius: 5px;
        border-radius: 5px;
        float: left;
        margin-right: 10px;
        color: #fff;
    }

    .sm-st-info {
        font-size: 12px;
        padding-top: 2px;
    }

    .sm-st-info span {
        display: block;
        font-size: 24px;
        font-weight: 600;
    }

    .orange {
        background: #fa8564 !important;
    }

    .tar {
        background: #45cf95 !important;
    }

    .sm-st .green {
        background: #86ba41 !important;
    }

    .pink {
        background: #AC75F0 !important;
    }

    .yellow-b {
        background: #fdd752 !important;
    }

    .stat-elem {

        background-color: #fff;
        padding: 18px;
        border-radius: 40px;

    }

    .stat-info {
        text-align: center;
        background-color: #fff;
        border-radius: 5px;
        margin-top: -5px;
        padding: 8px;
        -webkit-box-shadow: 0 1px 0px rgba(0, 0, 0, 0.05);
        box-shadow: 0 1px 0px rgba(0, 0, 0, 0.05);
        font-style: italic;
    }

    .stat-icon {
        text-align: center;
        margin-bottom: 5px;
    }

    .st-red {
        background-color: #F05050;
    }

    .st-green {
        background-color: #27C24C;
    }

    .st-violet {
        background-color: #7266ba;
    }

    .st-blue {
        background-color: #23b7e5;
    }

    .stats .stat-icon {
        color: #28bb9c;
        display: inline-block;
        font-size: 26px;
        text-align: center;
        vertical-align: middle;
        width: 50px;
        float: left;
    }

    .stat {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: inline-block;
        margin-right: 10px;
    }

    .stat .value {
        font-size: 20px;
        line-height: 24px;
        overflow: hidden;
        text-overflow: ellipsis;
        font-weight: 500;
    }

    .stat .name {
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .stat.lg .value {
        font-size: 26px;
        line-height: 28px;
    }

    .stat.lg .name {
        font-size: 16px;
    }

    .stat-col .progress {
        height: 2px;
    }

    .stat-col .progress-bar {
        line-height: 2px;
        height: 2px;
    }

    .item {
        padding: 30px 0;
    }
div {
    display: block;
}
</style>

</head>
<body>


		<link rel="stylesheet" href="/../static/bootstrap.css">
		<link rel="stylesheet" href="/../static/fastadmin.css">
<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-body ">
                    <blockquote class="layui-elem-quote">欢迎管理员：
                        <span class="x-red"><?php echo htmlentities($username); ?></span>！当前时间: <?php echo htmlentities($now_time); ?>
                    </blockquote>
                </div>
            </div>
        </div>
    </div>
  		<div class="layui-card-body ">
			<ul class="layui-tab-title">

				<li id="module_1" onclick="changeModule('1')">数据信息</li>
				<!-- <li id="module_2" onclick="changeModule('2')">宠物列表</li> -->
			</ul>
    
            <div class="layui-tab-item" id="module_menus_box_1">	
                <div class="tab-pane fade active in" id="one">

                <div class="row" style="margin-top:15px;">
                    <div class="col-lg-12">
                    </div>
                    
                    <div class="col-xs-6 col-md-3">
                        <div class="panel bg-blue" style="
    background: -webkit-gradient(linear,left bottom,left top,color-stop(0,#13c4fc),color-stop(1,#2fe5c2)) !important;   
">
                            <div class="panel-body">
                                <div class="panel-title">
                                    <span class="label label-success pull-right">实时</span>
                                    <h5>总会员</h5>
                                </div>
                                <div class="panel-content">
                                    <h1 class="no-margins"><?php echo htmlentities($member['member_num']); ?></h1>
                                    <!-- <div class="stat-percent font-bold text-gray"><i class="fa fa-commenting"><?php echo htmlentities($member['member_ral_name_num']); ?></i></div>
                                    <small>实名会员</small> -->
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xs-6 col-md-3" >
                        <div class="panel bg-blue" style="
    background: -webkit-gradient(linear,left bottom,left top,color-stop(0,#13c4fc),color-stop(1,#2fe5c2)) !important;
">
                            <div class="panel-body">
                                <div class="panel-title">
                                    <span class="label label-success pull-right">实时</span>
                                    <h5>今日新增会员</h5>
                                </div>
                                <div class="panel-content">
                                    <h1 class="no-margins"><?php echo htmlentities($member['member_today']); ?></h1>
                                    <!-- <div class="stat-percent font-bold text-gray"><i class="fa fa-commenting"><?php echo htmlentities($member['member_disabled_num']); ?></i></div>
                                    <small>冻结会员</small> -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xs-6 col-md-3">
                        <div class="panel bg-blue" style="
    background: -webkit-gradient(linear,left bottom,left top,color-stop(0,#13c4fc),color-stop(1,#2fe5c2)) !important;
">
                            <div class="panel-body">
                                <div class="panel-title">
                                    <span class="label label-success pull-right">实时</span>
                                    <h5>总订单</h5>
                                </div>
                                <div class="panel-content">
                                    <h1 class="no-margins"><?php echo htmlentities($member['totalorder']); ?></h1>
                                    <!-- <div class="stat-percent font-bold text-gray"><i class="fa fa-commenting"><?php echo htmlentities($member['reward_census']); ?></i></div>
                                    <small>累计收益</small> -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-6 col-md-3">
                        <div class="panel bg-blue" style="
    background: -webkit-gradient(linear,left bottom,left top,color-stop(0,#13c4fc),color-stop(1,#2fe5c2)) !important;
">
                            <div class="panel-body">
                                <div class="panel-title">
                                    <span class="label label-success pull-right">实时</span>
                                    <h5>今日新增订单</h5>
                                </div>
                                <div class="panel-content">
                                    <h1 class="no-margins"><?php echo htmlentities($member['totalpurchase']); ?></h1>
                                    <!-- <div class="stat-percent font-bold text-gray"><i class="fa fa-commenting"><?php echo htmlentities($member['hadtotalpurchase']); ?></i></div>
                                    <small>有效已转让宠物</small> -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xs-6 col-md-3">
                        <div class="panel bg-blue" style="
    background: -webkit-gradient(linear,left bottom,left top,color-stop(0,#13c4fc),color-stop(1,#2fe5c2)) !important;
">
                            <div class="panel-body">
                                <div class="panel-title">
                                    <span class="label label-success pull-right">实时</span>
                                    <h5>总充值金额</h5>
                                </div>
                                <div class="panel-content">
                                    <h1 class="no-margins"><?php echo htmlentities($member['member_ykb']); ?></h1>
                                    <!-- <div class="stat-percent font-bold text-gray"><i class="fa fa-commenting"><?php echo htmlentities($member['totalorderprice']); ?></i></div>
                                    <small>宠物资产和</small> -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-6 col-md-3">
                        <div class="panel bg-blue" style="
    background: -webkit-gradient(linear,left bottom,left top,color-stop(0,#13c4fc),color-stop(1,#2fe5c2)) !important;
">
                            <div class="panel-body">
                                <div class="panel-title">
                                    <span class="label label-success pull-right">实时</span>
                                    <h5>今日新增充值</h5>
                                </div>
                                <div class="panel-content">
                                    <h1 class="no-margins"><?php echo htmlentities($member['syschangenum']); ?></h1>
                                    <!-- <div class="stat-percent font-bold text-gray"><i class="fa fa-commenting"><?php echo htmlentities($member['shialluser_purchase']); ?></i></div>
                                    <small>失效宠物订单总数</small> -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xs-6 col-md-3">
                        <div class="panel bg-blue" style="
    background: -webkit-gradient(linear,left bottom,left top,color-stop(0,#13c4fc),color-stop(1,#2fe5c2)) !important;
">
                            <div class="panel-body">
                                <div class="panel-title">
                                    <span class="label label-success pull-right">实时</span>
                                    <h5>总提现金额</h5>
                                </div>
                                <div class="panel-content">
                                    <h1 class="no-margins"><?php echo htmlentities($member['member_jhb']); ?></h1>
                                    <!-- <div class="stat-percent font-bold text-gray"><i class="fa fa-commenting"><?php echo htmlentities($member['syschangecountsy']); ?></i> </div>
                                    <small>后端充值次数</small> -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xs-6 col-md-3">
                        <div class="panel bg-blue" style="
    background: -webkit-gradient(linear,left bottom,left top,color-stop(0,#13c4fc),color-stop(1,#2fe5c2)) !important;
">
                            <div class="panel-body">
                                <div class="panel-title">
                                    <span class="label label-success pull-right">实时</span>
                                    <h5>今日新增提现</h5>
                                </div>
                                <div class="panel-content">
                                    <h1 class="no-margins"><?php echo htmlentities($member['syswithdrawnum']); ?></h1>
                                    <!-- <div class="stat-percent font-bold text-gray"><i class="fa fa-commenting"><?php echo htmlentities($member['syschangecount']); ?>---<?php echo htmlentities($member['syschangenum']); ?></i> </div>
                                    <small>后端充值次数与总值</small> -->
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xs-6 col-md-3">
                        <div class="panel bg-blue" style="
    background: -webkit-gradient(linear,left bottom,left top,color-stop(0,#13c4fc),color-stop(1,#2fe5c2)) !important;
">
                            <div class="panel-body">
                                <div class="panel-title">
                                    <span class="label label-success pull-right">实时</span>
                                    <h5>当日首冲人数</h5>
                                </div>
                                <div class="panel-content">
                                    <h1 class="no-margins"><?php echo htmlentities($member['firstrechargenum']); ?></h1>
                                    <!-- <div class="stat-percent font-bold text-gray"><i class="fa fa-commenting"><?php echo htmlentities($member['syschangecountsy']); ?></i> </div>
                                    <small>后端充值次数</small> -->
                                </div>
                            </div>
                        </div>
                    </div>
                    

<!--                     <div class="col-xs-6 col-md-3">
                        <div class="panel bg-blue" style="
    background: -webkit-gradient(linear,left bottom,left top,color-stop(0,#505f2e),color-stop(1,#4b820f)) !important;
">
                            <div class="panel-body">
                                <div class="panel-title">
                                    <span class="label label-success pull-right">实时</span>
                                    <h5>充值<?php echo htmlentities($config_val[1]); ?>总额</h5>
                                </div>
                                <div class="panel-content">
                                    <h1 class="no-margins"><?php echo htmlentities($member['jhbtotalnum']); ?></h1>
                                    <div class="stat-percent font-bold text-gray"><i class="fa fa-commenting"><?php echo htmlentities($member['member_jhb']); ?></i> </div>
                                    <small>剩余<?php echo htmlentities($config_val[1]); ?></small>
                                </div>
                            </div>
                        </div>
                    </div> -->

                </div>
            </div>
        </div>
        
        
        				<div class="layui-tab-item" id="module_menus_box_2">
							<div class="layui-card-body layui-card-table">
								<table class="layui-table">
									<thead>
									<tr>
										<th style="min-width: 120px;">宠物名称</th>
										<th style="min-width: 120px;">升值中</th>
										<th style="min-width: 120px;">待转让</th>
										<th style="min-width: 120px;">已失效</th>
										<th style="min-width: 120px;">今日预约</th>
										<th style="min-width: 120px;">今日抢购</th>
										<th style="min-width: 120px;">今日转让成功</th>
										<th style="min-width: 120px;">今日新增</th>
										<!-- <th style="min-width: 120px;">待上架统计</th>
										<th style="min-width: 120px;">今日指定转让成功</th> -->
										<th style="min-width: 120px;">总价值</th>
									</tr>
									</thead>
									<tbody>
									<?php if(is_array($mutu_list) || $mutu_list instanceof \think\Collection || $mutu_list instanceof \think\Paginator): $i = 0; $__LIST__ = $mutu_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$list): $mod = ($i % 2 );++$i;?>
									<tr>
										<td><?php echo htmlentities($list['name']); ?></td>
										<td><?php echo htmlentities($list['revalue_in']); ?></td>
										<td><?php echo htmlentities($list['holdon_transfer']); ?></td>
										<td><?php echo htmlentities($list['invalid']); ?></td>
										<td><?php echo htmlentities($list['today_reserve']); ?></td>
										<td><?php echo htmlentities($list['today_snapup']); ?></td>
										<td><?php echo htmlentities($list['transfer_CG']); ?></td>
										<td><?php echo htmlentities($list['newly_added']); ?></td>
										<!-- <td><?php echo htmlentities($list['holdon_shelves']); ?></td>
										<td><?php echo htmlentities($list['appoint_CG']); ?></td> -->
										<td><?php echo htmlentities($list['total_value']); ?></td>
									</tr>
									<?php endforeach; endif; else: echo "" ;endif; ?>
									</tbody>
								</table>
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
	function changeModule(module_id) {
		if(module_id != active_module){
			/*active_module = module_id; // 切换模块
            $(".layui-tab-item").removeClass('layui-show'); // 先隐藏所有菜单 /admin
            $("#module_menus_box_"+active_module).addClass('layui-show'); // 显示当前模块菜单
			window.location.href="/index/welcome/my_active_module/"+module_id;*/
			window.location.href="<?php echo url('index/welcome'); ?>?my_active_module="+module_id;
			
			/* if(module_id == 2){
				window.location.href="<?php echo url('index/welcome',array('my_active_module'=>2)); ?>";
			}else{
				window.location.href="<?php echo url('index/welcome',array('my_active_module'=>1)); ?>";
			} */
		}
	}
</script>

</html>