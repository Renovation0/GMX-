<?php /*a:2:{s:69:"/home/gemxpbra/public_html/application/admin/view/system/sms_log.html";i:1697722712;s:89:"/home/gemxpbra/public_html/application/admin/view/../../../public/static/common/base.html";i:1697722706;}*/ ?>
<!DOCTYPE html>
<html class="x-admin-sm">
<head>
    <meta charset="UTF-8">
    <title>用户短信记录</title>
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
</style>

</head>
<body>

<div class="x-nav">
    <span class="layui-breadcrumb">
        <a href="javascript:;">系统</a>
        <a href="javascript:;">系统设置</a>
        <a><cite>短信记录</cite></a>
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
                                <input type="text" name="tel" value="<?php echo htmlentities($tel); ?>" placeholder="手机号" autocomplete="off"
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
                <div class="layui-card-body ">
                    <table class="layui-table layui-form">
                        <thead>
                        <tr>
                            <th style="width: 20px;">ID</th>
                            <th style="width: 20px;">用户</th>
                            <th style="width: 140px;">验证码</th>
                            <th style="width: 50px;">说明</th>
                            <th style="width: 50px;">时间</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if(!empty($list->items())): if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$user): $mod = ($i % 2 );++$i;?>
                        <tr>
                            <td><?php echo htmlentities($user['id']); ?></td>
                            <td><?php echo htmlentities($user['tel']); ?></td>
                            <td><?php echo htmlentities($user['code']); ?></td>
                            <td><?php echo htmlentities($user['type']); ?></td>
                            <td><?php echo htmlentities(date('Y-m-d H:i:s',!is_numeric($user['time'])? strtotime($user['time']) : $user['time'])); ?></td>
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
                        <?php echo $list; ?>
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
</script>

</html>