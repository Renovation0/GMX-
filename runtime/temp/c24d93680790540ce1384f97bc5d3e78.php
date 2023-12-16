<?php /*a:2:{s:78:"/home/gemxpbra/public_html/application/admin/view/product/mutual_aid_list.html";i:1697722712;s:89:"/home/gemxpbra/public_html/application/admin/view/../../../public/static/common/base.html";i:1697722706;}*/ ?>
<!DOCTYPE html>
<html class="x-admin-sm">
<head>
    <meta charset="UTF-8">
    <title>互助列表</title>
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
        <a><cite>互助列表</cite></a>
    </span>
    <a class="layui-btn layui-btn-small" style="line-height:1.6em;margin-top:3px;float:right"
       onclick="location.reload()" title="刷新">
        <i class="layui-icon layui-icon-refresh" style="line-height:30px"></i>
    </a>
</div>
<div class="layui-card-header">
    <?php if(in_array('/product/mutualaidadd', (array)session('power_action'))): ?> <!-- /admin/mutualaid/mutualaidAdd -->
    <button class="layui-btn layui-btn-normal" onclick="xadmin.open('添加', '<?php echo url('product/mutualaidAdd'); ?>', 600, 600)">
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
                                    <input type="text" name="serach" value="<?php echo htmlentities($param_serach); ?>" placeholder="名称/收益天数" autocomplete="off" class="layui-input">
                                </div>
                                <div class="layui-input-inline layui-show-xs-block">
                                    <select name="status">
                                        <option value="0">状态</option>
                                        <option value="1"<?php if($param_status == 1) echo 'selected';?>>开启</option>
                                        <option value="2"<?php if($param_status == 2) echo 'selected';?>>关闭</option>
                                        <option value="3"<?php if($param_status == 3) echo 'selected';?>>待开启</option>
                                    </select>
                                </div>
 <!--                                <div class="layui-inline layui-show-xs-block">
                                    <input class="layui-input" autocomplete="off" placeholder="添加开始时间" name="add_time_s"
                                           value="<?php echo htmlentities($param_add_time_s); ?>" id="add_time_s">
                                </div>
                                <div class="layui-inline layui-show-xs-block">
                                    <input class="layui-input" autocomplete="off" placeholder="添加截至时间" name="add_time_e"
                                           value="<?php echo htmlentities($param_add_time_e); ?>" id="add_time_e">
                                </div> -->
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
                        <tr>
                            <th style="min-width: 30px;"><b>ID</b></th>
                            <th><b>名称</b></th>
                            <th style="width: 50px;"><b>logo</b></th>
                            <th style="min-width: 130px;"><b>价格</b></th>
                            <th style="min-width: 60px;"><b>升值天数</b></th>
                            <th style="min-width: 50px;"><b>受益 天%</b></th>
                            <th style="min-width: 100px;"><b>状态</b></th>
                            <th style="min-width: 100px;"><b>总份数</b></th>
                            <th style="min-width: 80px;"><b>等级</b></th>
                            <th style="min-width: 40px;"><b>排序</b></th>
                            <th style="min-width: 140px;"><b>操作</b></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if(!empty($list->items())): if(is_array($list) || $list instanceof \think\Collection || $list instanceof \think\Paginator): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$user): $mod = ($i % 2 );++$i;?>
                        <tr align="center">
                            <td><?php echo htmlentities($user['id']); ?></td>
                            <td><?php echo htmlentities($user['name']); ?></td>
                            <td onclick="confirm('<?php echo htmlentities($user['logo']); ?>')"><img src="<?php echo htmlentities($user['logo']); ?>" style="width: 30px;height:30px"></td>
                            <td><?php echo htmlentities($user['price']); ?></td>
                            <td><?php echo htmlentities($user['days']); ?></td>
                            <td><?php echo htmlentities($user['rate']); ?></td>
                            <td><!-- <?php echo htmlentities($user['status']); ?> -->
                            <input type="checkbox" value="1" data-id="status" data-ids="<?php echo htmlentities($user['id']); ?>" lay-skin="switch" <?php if($user['status'] == '1'): ?> checked <?php endif; ?>>
                            </td>
                            <td><?php echo htmlentities($user['zpurchaseNum']); ?></td>
							<td>
							<?php if(empty($user['level_name'])): if($user['level'] == -1): ?>
									<span style="color:red;">活动产品</span>
								<?php else: ?>
									普通产品
								<?php endif; else: ?>
								<?php echo htmlentities($user['level_name']); ?>
							<?php endif; ?>
							</td>
                            <td><?php echo htmlentities($user['sort']); ?></td>
                            <td>


                                <?php if(in_array('/product/mutualaidedit', (array)session('power_action'))): ?> <!-- '/admin/mutualaid/mutualaidEdit/id/<?php echo htmlentities($user['id']); ?>' -->
                                <button title="编辑" class="layui-btn layui-btn-normal" onclick="xadmin.open('编辑','<?php echo url('product/mutualaidEdit'); ?>?id='+<?php echo htmlentities($user['id']); ?>,600,600)">
                                    <i class="iconfont">&#xe69e;</i>
                                </button>
                                <?php else: ?>
                                <button title="编辑" class="layui-btn layui-btn-disabled" onclick="return false">
                                    <i class="iconfont">&#xe69e;</i>
                                </button>
                                <?php endif; if(in_array('/product/mutualaiddelete', (array)session('power_action'))): ?>
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
                            <td colspan="13" class="nodata_td">无记录</td>
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
                <img alt="" style="display:none" id="displayImg" src="" />
                
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
    
    layui.use('form', function () {
        let form = layui.form;
        form.render();
        form.on('switch', function(data){
            let data_id = $(data.elem).attr('data-id');
            let id = $(data.elem).attr('data-ids');
            if(data.elem.checked){
                $("#"+data_id).val(1);
                var status = '1';
            }else {
                $("#"+data_id).val(2);
                var status = '2';
            }
            
             $.ajax({
                url: "<?php echo url('product/mutualAidEditStatus'); ?>",//'/admin/mutualaid/mutualAidEditStatus',
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
	
	
    //删除
    function delete_confirm(obj, id) {
        layer.confirm('是否同意删除？', function(index) {
            $.ajax({
                url: "<?php echo url('product/mutualaiddelete'); ?>",//'/admin/mutualaid/mutualaiddelete',
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