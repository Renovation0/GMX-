<?php /*a:2:{s:67:"/home/gemxpbra/public_html/application/admin/view/member/level.html";i:1697722712;s:89:"/home/gemxpbra/public_html/application/admin/view/../../../public/static/common/base.html";i:1697722706;}*/ ?>
<!DOCTYPE html>
<html class="x-admin-sm">
<head>
    <meta charset="UTF-8">
    <title>等级配置</title>
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

</style>

</head>
<body>

<div class="x-nav">
    <span class="layui-breadcrumb">
        <a href="javascript:;">用户</a>
        <a href="javascript:;">等级配置</a>
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
               <div class="layui-card-header">
                  <?php if(in_array('/admin/roleadd', (array)session('power_action'))): ?>	<!-- '/admin/member/levelAdd' -->
                    <button class="layui-btn layui-btn-normal" onclick="xadmin.open('添加等级','<?php echo url('member/levelAdd'); ?>',465,580)">
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
                            <th style="width: 13px;">ID</th>
                            <th style="width: 20px;">等级名称</th>
                            <th style="width: 20px;">logo</th>
                            <!-- <th style="width: 10px;">交易手续费</th> -->
                            <th style="width: 15px;">有效直推要求</th>
                            <!-- <th style="width: 15px;">有效团队要求</th> -->
                            <th style="width: 15px;">累计购买金额要求</th>
                            <!--<th style="width: 10px;">一代收益比例</th>-->
                            <!--<th style="width: 15px;">二代收益比例</th>-->
                            <!--<th style="width: 15px;">三代收益比例</th>-->
                            <!-- <th style="width: 15px;">团队收益比例</th> -->
                            <th style="width: 30px;">操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if(!empty($levels)): if(is_array($levels) || $levels instanceof \think\Collection || $levels instanceof \think\Paginator): $i = 0; $__LIST__ = $levels;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$level): $mod = ($i % 2 );++$i;?>
                        <tr align="center">
                            <td><?php echo htmlentities($level['id']); ?></td>
                            <td><?php echo htmlentities($level['name']); ?></td>
                            <td onclick="confirm('<?php echo htmlentities($level['level_logo']); ?>')"><img src="<?php echo htmlentities($level['level_logo']); ?>" style="width: 30px;height:30px"></td>
                            <!-- <td><?php echo htmlentities($level['sell_rate']); ?></td> -->
                            <td><?php echo htmlentities($level['direct_push']); ?></td>
                            <!-- <td><?php echo htmlentities($level['team_push']); ?></td> -->
                            <td><?php echo htmlentities($level['pet_assets']); ?></td>
                            <!--<td><?php echo htmlentities($level['one_era']); ?></td>-->
                            <!--<td><?php echo htmlentities($level['two_era']); ?></td>-->
                            <!--<td><?php echo htmlentities($level['three_era']); ?></td>-->
                            <!-- <td><?php echo htmlentities($level['team_income_ratio']); ?></td> -->
                            <!-- <td><?php echo htmlentities($level['era']); ?></td> -->
                            <td>																			<!-- '/admin/member/levelEdit/id/<?php echo htmlentities($level['id']); ?>' -->
                                <button class="layui-btn layui-btn-warm" onclick="xadmin.open('编辑等级','<?php echo url('member/levelEdit'); ?>?id='+<?php echo htmlentities($level['id']); ?>,465,580)">
                                    <i class="iconfont">&#xe69c;</i> 编辑
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; endif; else: echo "" ;endif; else: ?>
                        <tr>
                            <td colspan="27" class="nodata_td">无记录</td>
                        </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
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
    
    
    function confirm(url) {   	
        $("#displayImg").attr("src", url);
        var width = 'auto';
        var height = 'auto';
        
        layer.open({
            type: 1,
            title: false,
            closeBtn: 0,
            shadeClose: true,
            area: [width + 'px', height + 'px'], //宽高
            content: "<img src=" + url + " />"
        });
    }


    // 控制卖出状态
    function level_sta(obj, id, status) {
        if(status !== 1 && status !== 2){
            layer.msg('错误的操作');
            return false;
        }
        layer.confirm(status === 2 ? '确定该等级订单显示？' : '确定该等级订单隐藏？', function(index) {
            $.ajax({
                url: "<?php echo url('base/uploadImage'); ?>",//'/admin/member/level_hide',
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

</script>

</html>