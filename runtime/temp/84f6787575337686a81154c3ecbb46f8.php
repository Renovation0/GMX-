<?php /*a:2:{s:71:"/home/gemxpbra/public_html/application/admin/view/admin/role_power.html";i:1697722712;s:89:"/home/gemxpbra/public_html/application/admin/view/../../../public/static/common/base.html";i:1697722706;}*/ ?>
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
        <div class="layui-col-md12"><!-- /admin/admin/rolePowerPost -->
            <form action="<?php echo url('admin/rolePowerPost'); ?>" method="post" class="layui-form layui-form-pane">
                <input type="hidden" name="id" value="<?php echo htmlentities($id); ?>">
                <input type="hidden" name="old_power" id="old_power" value="<?php echo htmlentities($power_ids); ?>">
                <div class="layui-card">
                    <div class="layui-card-body ">
                        <ul class="layui-tab-title">
                            <?php if(is_array($nodes) || $nodes instanceof \think\Collection || $nodes instanceof \think\Paginator): $i = 0; $__LIST__ = $nodes;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$module_item): $mod = ($i % 2 );++$i;?>
                            <li id="module_<?php echo htmlentities($module_item['module']['id']); ?>" onclick="changeModule('<?php echo htmlentities($module_item['module']['id']); ?>')"><?php echo htmlentities($module_item['module']['name']); ?></li>
                            <?php endforeach; endif; else: echo "" ;endif; ?>
                        </ul>
                        <div class="layui-tab-content">
                            <?php if(is_array($nodes) || $nodes instanceof \think\Collection || $nodes instanceof \think\Paginator): $i = 0; $__LIST__ = $nodes;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$module): $mod = ($i % 2 );++$i;?>
                            <div class="layui-tab-item" id="module_menus_box_<?php echo htmlentities($module['module']['id']); ?>">
                                <?php if(!empty($module['menus'])): if(is_array($module['menus']) || $module['menus'] instanceof \think\Collection || $module['menus'] instanceof \think\Paginator): $i = 0; $__LIST__ = $module['menus'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$menu_main): $mod = ($i % 2 );++$i;?>
                                <div class="node_item node_item_main" son_num="0" chose_son_num="0" data-id="<?php echo htmlentities($menu_main['menu']['id']); ?>" onclick="chose_main('<?php echo htmlentities($menu_main['menu']['id']); ?>')">
                                    <div class="select_box"></div><div class="node_name"><?php echo htmlentities($menu_main['menu']['name']); ?></div>
                                </div>
                                <?php if(!empty($menu_main['menus'])): ?>
                                <div class="son_box">
                                    <?php if(is_array($menu_main['menus']) || $menu_main['menus'] instanceof \think\Collection || $menu_main['menus'] instanceof \think\Paginator): $i = 0; $__LIST__ = $menu_main['menus'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$menu_son): $mod = ($i % 2 );++$i;?>
                                    <div class="node_item node_item_son" son_num="<?php echo empty($menu_son['menus'])? 0 : count($menu_son['menus']); ?>" chose_son_num="0"  data-id="<?php echo htmlentities($menu_son['menu']['id']); ?>" onclick="chose_son('<?php echo htmlentities($menu_son['menu']['id']); ?>', '<?php echo htmlentities($menu_main['menu']['id']); ?>')">
                                        <div class="tab">&nbsp;</div><div class="select_box"></div><div class="node_name"><?php echo htmlentities($menu_son['menu']['name']); ?>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo htmlentities($menu_son['menu']['action']); ?></div>
                                    </div>
                                    <?php if(!empty($menu_son['menus'])): ?>
                                    <div class="grandson_box">
                                        <?php if(is_array($menu_son['menus']) || $menu_son['menus'] instanceof \think\Collection || $menu_son['menus'] instanceof \think\Paginator): $i = 0; $__LIST__ = $menu_son['menus'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$menu_grandson): $mod = ($i % 2 );++$i;?>
                                        <div class="node_item node_item_grandson" data-id="<?php echo htmlentities($menu_grandson['id']); ?>"  onclick="chose_grandson('<?php echo htmlentities($menu_grandson['id']); ?>', '<?php echo htmlentities($menu_son['menu']['id']); ?>', '<?php echo htmlentities($menu_main['menu']['id']); ?>')">
                                            <div class="tab">&nbsp;</div><div class="tab">&nbsp;</div><div class="select_box"></div><div class="node_name"><?php echo htmlentities($menu_grandson['name']); ?>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo htmlentities($menu_grandson['action']); ?></div>
                                        </div>
                                        <?php endforeach; endif; else: echo "" ;endif; ?>
                                    </div>
                                    <?php endif; ?>
                                    <?php endforeach; endif; else: echo "" ;endif; ?>
                                </div>
                                <?php endif; ?>
                                <?php endforeach; endif; else: echo "" ;endif; ?>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; endif; else: echo "" ;endif; ?>
                        </div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <button class="layui-btn" lay-submit lay-filter="role_power">提交</button>
                </div>
            </form>
        </div>
    </div>
</div>

</body>

<script>
    var active_module = '1'; // 当前模块
    $("#module_"+active_module).addClass('layui-this'); // 初始化系统模块
    $("#module_menus_box_"+active_module).addClass('layui-show'); // 初始化系统模块菜单

    // 切换模块
    function changeModule(module_id) {
        if(module_id != active_module){
            active_module = module_id; // 切换模块
            $(".layui-tab-item").removeClass('layui-show'); // 先隐藏所有菜单
            $("#module_menus_box_"+active_module).addClass('layui-show'); // 显示当前模块菜单
        }
    }

    // 初始化主菜单的孙菜单数
    $(".node_item_main").each(function () {
        let this_main = $(this);
        let son_num = 0;
        this_main.next('.son_box').find('.node_item_son').each(function () {
            son_num += 1;
            son_num += +parseInt($(this).attr('son_num'));
        });
        this_main.attr('son_num', son_num);
    });

    // 初始化之前的权限
    chose_ids = $("#old_power").val();
    chose_ids = chose_ids.replace("[","");
    chose_ids = chose_ids.replace("]","");
    if(chose_ids === ''){
        chose_ids = []
    }else {
        chose_ids = chose_ids.split(',');
    }
    // 初始化权限的选择
    if(chose_ids.length > 0){
        $.each(chose_ids, function (index, value) {
            if($(".node_item[data-id="+parseInt(value)+"]").hasClass('node_item_grandson')){ // 孙权限

                // 1.处理自己(孙)
                $(".node_item[data-id="+parseInt(value)+"]").find('.select_box').addClass('chose');
                // 2.处理上级(子)
                // 上级子菜单选中数量+1
                $(".node_item[data-id="+parseInt(value)+"]").parent('.grandson_box').prev('.node_item_son').attr('chose_son_num', (parseInt($(".node_item[data-id="+parseInt(value)+"]").parent('.grandson_box').prev('.node_item_son').attr('chose_son_num')) + 1));
                // 判断上级是全选还是部分选
                if(parseInt($(".node_item[data-id="+parseInt(value)+"]").parent('.grandson_box').prev('.node_item_son').attr('chose_son_num')) === parseInt($(".node_item[data-id="+parseInt(value)+"]").parent('.grandson_box').prev('.node_item_son').attr('son_num'))){
                    $(".node_item[data-id="+parseInt(value)+"]").parent('.grandson_box').prev('.node_item_son').find('.select_box').removeClass('have');
                    $(".node_item[data-id="+parseInt(value)+"]").parent('.grandson_box').prev('.node_item_son').find('.select_box').removeClass('chose');
                    $(".node_item[data-id="+parseInt(value)+"]").parent('.grandson_box').prev('.node_item_son').find('.select_box').addClass('chose');
                }else {
                    $(".node_item[data-id="+parseInt(value)+"]").parent('.grandson_box').prev('.node_item_son').find('.select_box').removeClass('have');
                    $(".node_item[data-id="+parseInt(value)+"]").parent('.grandson_box').prev('.node_item_son').find('.select_box').removeClass('chose');
                    $(".node_item[data-id="+parseInt(value)+"]").parent('.grandson_box').prev('.node_item_son').find('.select_box').addClass('have');
                }
                // 3.处理上上级(主)
                // 上上级子菜单选中数量+1
                $(".node_item[data-id="+parseInt(value)+"]").parent('.grandson_box').parent('.son_box').prev('.node_item_main').attr('chose_son_num', (parseInt($(".node_item[data-id="+parseInt(value)+"]").parent('.grandson_box').parent('.son_box').prev('.node_item_main').attr('chose_son_num')) + 1));
                // 判断上上级是全选还是部分选
                if(parseInt($(".node_item[data-id="+parseInt(value)+"]").parent('.grandson_box').parent('.son_box').prev('.node_item_main').attr('chose_son_num')) === parseInt($(".node_item[data-id="+parseInt(value)+"]").parent('.grandson_box').parent('.son_box').prev('.node_item_main').attr('son_num'))){
                    $(".node_item[data-id="+parseInt(value)+"]").parent('.grandson_box').parent('.son_box').prev('.node_item_main').find('.select_box').removeClass('have');
                    $(".node_item[data-id="+parseInt(value)+"]").parent('.grandson_box').parent('.son_box').prev('.node_item_main').find('.select_box').removeClass('chose');
                    $(".node_item[data-id="+parseInt(value)+"]").parent('.grandson_box').parent('.son_box').prev('.node_item_main').find('.select_box').addClass('chose');
                }else {
                    $(".node_item[data-id="+parseInt(value)+"]").parent('.grandson_box').parent('.son_box').prev('.node_item_main').find('.select_box').removeClass('have');
                    $(".node_item[data-id="+parseInt(value)+"]").parent('.grandson_box').parent('.son_box').prev('.node_item_main').find('.select_box').removeClass('chose');
                    $(".node_item[data-id="+parseInt(value)+"]").parent('.grandson_box').parent('.son_box').prev('.node_item_main').find('.select_box').addClass('have');
                }
            }else if($(".node_item[data-id="+parseInt(value)+"]").hasClass('node_item_son')){ // 子权限
                // 1.处理自己(子)
                // 判断自己是全选还是部分选
                if(parseInt($(".node_item[data-id="+parseInt(value)+"]").attr('chose_son_num')) === parseInt($(".node_item[data-id="+parseInt(value)+"]").attr('son_num'))){
                    $(".node_item[data-id="+parseInt(value)+"]").find('.select_box').removeClass('have');
                    $(".node_item[data-id="+parseInt(value)+"]").find('.select_box').removeClass('chose');
                    $(".node_item[data-id="+parseInt(value)+"]").find('.select_box').addClass('chose');
                }else {
                    $(".node_item[data-id="+parseInt(value)+"]").find('.select_box').removeClass('have');
                    $(".node_item[data-id="+parseInt(value)+"]").find('.select_box').removeClass('chose');
                    $(".node_item[data-id="+parseInt(value)+"]").find('.select_box').addClass('have');
                }
                // 2.处理上级(主)
                // 上级子菜单选中数量+1
                $(".node_item[data-id="+parseInt(value)+"]").parent('.son_box').prev('.node_item_main').attr('chose_son_num', (parseInt($(".node_item[data-id="+parseInt(value)+"]").parent('.son_box').prev('.node_item_main').attr('chose_son_num')) + 1));
                // 判断上级是全选还是部分选
                if(parseInt($(".node_item[data-id="+parseInt(value)+"]").parent('.son_box').prev('.node_item_main').attr('chose_son_num')) === parseInt($(".node_item[data-id="+parseInt(value)+"]").parent('.son_box').prev('.node_item_main').attr('son_num'))){
                    $(".node_item[data-id="+parseInt(value)+"]").parent('.son_box').prev('.node_item_main').find('.select_box').removeClass('have');
                    $(".node_item[data-id="+parseInt(value)+"]").parent('.son_box').prev('.node_item_main').find('.select_box').removeClass('chose');
                    $(".node_item[data-id="+parseInt(value)+"]").parent('.son_box').prev('.node_item_main').find('.select_box').addClass('chose');
                }else {
                    $(".node_item[data-id="+parseInt(value)+"]").parent('.son_box').prev('.node_item_main').find('.select_box').removeClass('have');
                    $(".node_item[data-id="+parseInt(value)+"]").parent('.son_box').prev('.node_item_main').find('.select_box').removeClass('chose');
                    $(".node_item[data-id="+parseInt(value)+"]").parent('.son_box').prev('.node_item_main').find('.select_box').addClass('have');
                }
            }else { // 主权限
                // 1.处理自己(主)
                // 判断自己是全选还是部分选
                if(parseInt($(".node_item[data-id="+parseInt(value)+"]").attr('chose_son_num')) === parseInt($(".node_item[data-id="+parseInt(value)+"]").attr('son_num'))){
                    $(".node_item[data-id="+parseInt(value)+"]").find('.select_box').removeClass('have');
                    $(".node_item[data-id="+parseInt(value)+"]").find('.select_box').removeClass('chose');
                    $(".node_item[data-id="+parseInt(value)+"]").find('.select_box').addClass('chose');
                }else {
                    $(".node_item[data-id="+parseInt(value)+"]").find('.select_box').removeClass('have');
                    $(".node_item[data-id="+parseInt(value)+"]").find('.select_box').removeClass('chose');
                    $(".node_item[data-id="+parseInt(value)+"]").find('.select_box').addClass('have');
                }
            }
        });
    }

    // 选择主菜单
    function chose_main(main_id) {
        if(parseInt($(".node_item[data-id="+parseInt(main_id)+"]").attr('chose_son_num')) > 0) { // 主菜单的子孙菜单有任一选中
            // 1.处理自己(主)
            chose_ids.splice(chose_ids.indexOf(main_id), 1);
            $(".node_item[data-id="+parseInt(main_id)+"]").attr('chose_son_num', 0);
            $(".node_item[data-id="+parseInt(main_id)+"]").find('.select_box').removeClass('have');
            $(".node_item[data-id="+parseInt(main_id)+"]").find('.select_box').removeClass('chose');
            // 2.处理下级(子)
            $(".node_item[data-id="+parseInt(main_id)+"]").next('.son_box').find('.node_item_son').each(function () {
                let this_ = $(this);
                if(chose_ids.indexOf(this_.attr('data-id')) !== -1){ // 对应子菜单已选中
                    chose_ids.splice(chose_ids.indexOf(this_.attr('data-id')), 1);// 取消选中对应子菜单
                }
                this_.attr('chose_son_num', 0);
                this_.find('.select_box').removeClass('have');
                this_.find('.select_box').removeClass('chose');
                // 3.处理下下级(孙)
                this_.next('.grandson_box').find('.node_item_grandson').each(function () {
                    if(chose_ids.indexOf($(this).attr('data-id')) !== -1){ // 对应孙菜单已选中
                        chose_ids.splice(chose_ids.indexOf($(this).attr('data-id')), 1);// 取消选中对应子菜单
                    }
                    $(this).find('.select_box').removeClass('chose');
                })
            });
        }else {
            if(parseInt($(".node_item[data-id="+parseInt(main_id)+"]").attr('son_num')) === 0){
                if($(".node_item[data-id="+parseInt(main_id)+"]").find('.select_box').hasClass('chose')){
                    // 执行只取消选中自己
                    // 1.处理自己(主)
                    chose_ids.splice(chose_ids.indexOf(main_id), 1);
                    $(".node_item[data-id="+parseInt(main_id)+"]").find('.select_box').removeClass('chose');
                }else {
                    // 执行只选中自己
                    // 1.处理自己(主)
                    chose_ids.push(main_id);
                    $(".node_item[data-id="+parseInt(main_id)+"]").find('.select_box').addClass('chose');
                }
            }else {
                if(!$(".node_item[data-id="+parseInt(main_id)+"]").find('.select_box').hasClass('have')) {
                    // 执行只选中自己
                    // 1.处理自己(主)
                    chose_ids.push(main_id);
                    $(".node_item[data-id="+parseInt(main_id)+"]").find('.select_box').addClass('have');
                }else {
                    // 执行全部选中
                    // 1.处理自己(主)
                    $(".node_item[data-id="+parseInt(main_id)+"]").attr('chose_son_num', (parseInt($(".node_item[data-id="+parseInt(main_id)+"]").attr('son_num'))));
                    $(".node_item[data-id="+parseInt(main_id)+"]").find('.select_box').addClass('chose');
                    // 2.处理下级(子)
                    $(".node_item[data-id="+parseInt(main_id)+"]").next('.son_box').find('.node_item_son').each(function () {
                        let this_ = $(this);
                        chose_ids.push(this_.attr('data-id'));
                        this_.attr('chose_son_num', parseInt($(this).attr('son_num')));
                        this_.find('.select_box').addClass('chose');
                        // 3.处理下下级(孙)
                        this_.next('.grandson_box').find('.node_item_grandson').each(function () {
                            chose_ids.push($(this).attr('data-id'));
                            $(this).find('.select_box').addClass('chose');
                        })
                    });
                }
            }
        }
    }

    // 选择子菜单
    function chose_son(son_id, main_id) {
        if(parseInt($(".node_item[data-id="+parseInt(son_id)+"]").attr('chose_son_num')) > 0){ // 子菜单的孙菜单有任何选中
            // 执行全部取消选中
            // 1.处理自己(子)
            chose_ids.splice(chose_ids.indexOf(son_id), 1);
            $(".node_item[data-id="+parseInt(son_id)+"]").attr('chose_son_num', 0);
            $(".node_item[data-id="+parseInt(son_id)+"]").find('.select_box').removeClass('chose');
            $(".node_item[data-id="+parseInt(son_id)+"]").find('.select_box').removeClass('have');
            // 2.处理下级(孙)
            if(parseInt($(".node_item[data-id="+parseInt(son_id)+"]").attr('son_num')) > 0){ // 有孙
                $(".node_item_son[data-id="+parseInt(son_id)+"]").next('.grandson_box').find('.node_item_grandson').each(function () {
                    if(chose_ids.indexOf($(this).attr('data-id')) !== -1){ // 对应“孙”菜单已选中
                        chose_ids.splice(chose_ids.indexOf($(this).attr('data-id')), 1);
                    }
                    $(this).find('.select_box').removeClass('chose');
                });
            }
            // 3.处理上级(主)
            let chose_son_num = 0;
            $(".node_item[data-id="+parseInt(main_id)+"]").next('.son_box').find('.node_item_son').each(function () {
                if($(this).find('.select_box').hasClass('chose') || $(this).find('.select_box').hasClass('have')){
                    chose_son_num += 1;
                }
                chose_son_num += parseInt($(this).attr('chose_son_num'));
            });
            $(".node_item[data-id="+parseInt(main_id)+"]").attr('chose_son_num', chose_son_num);
            if(parseInt($(".node_item[data-id="+parseInt(main_id)+"]").attr('chose_son_num')) === 0){
                // 全未选
                $(".node_item[data-id="+parseInt(main_id)+"]").find('.select_box').removeClass('have');
                $(".node_item[data-id="+parseInt(main_id)+"]").find('.select_box').removeClass('chose');
                chose_ids.splice(chose_ids.indexOf(main_id), 1);
            }else {
                if(parseInt($(".node_item[data-id="+parseInt(main_id)+"]").attr('son_num')) === parseInt($(".node_item[data-id="+parseInt(main_id)+"]").attr('chose_son_num'))){
                    // 已全选
                    $(".node_item[data-id="+parseInt(main_id)+"]").find('.select_box').removeClass('have');
                    $(".node_item[data-id="+parseInt(main_id)+"]").find('.select_box').removeClass('chose');
                    $(".node_item[data-id="+parseInt(main_id)+"]").find('.select_box').addClass('chose');
                }else { // 未全选
                    $(".node_item[data-id="+parseInt(main_id)+"]").find('.select_box').removeClass('chose');
                    $(".node_item[data-id="+parseInt(main_id)+"]").find('.select_box').removeClass('have');
                    $(".node_item[data-id="+parseInt(main_id)+"]").find('.select_box').addClass('have');
                }
            }
        }else {
            if(parseInt($(".node_item[data-id="+parseInt(son_id)+"]").attr('son_num')) === 0){
                if($(".node_item[data-id="+parseInt(son_id)+"]").find('.select_box').hasClass('chose')){
                    // 1.处理自己(子)
                    chose_ids.splice(chose_ids.indexOf(son_id), 1);
                    $(".node_item[data-id="+parseInt(son_id)+"]").find('.select_box').removeClass('chose');
                    // 2.处理上级(主)
                    let chose_son_num = 0;
                    $(".node_item[data-id="+parseInt(main_id)+"]").next('.son_box').find('.node_item_son').each(function () {
                        if($(this).find('.select_box').hasClass('chose') || $(this).find('.select_box').hasClass('have')){
                            chose_son_num += 1;
                        }
                        chose_son_num += parseInt($(this).attr('chose_son_num'));
                    });
                    $(".node_item[data-id="+parseInt(main_id)+"]").attr('chose_son_num', chose_son_num);
                    if(parseInt($(".node_item[data-id="+parseInt(main_id)+"]").attr('chose_son_num')) === 0){
                        // 全未选
                        $(".node_item[data-id="+parseInt(main_id)+"]").find('.select_box').removeClass('have');
                        $(".node_item[data-id="+parseInt(main_id)+"]").find('.select_box').removeClass('chose');
                        chose_ids.splice(chose_ids.indexOf(main_id), 1);
                    }else {
                        if(parseInt($(".node_item[data-id="+parseInt(main_id)+"]").attr('son_num')) === parseInt($(".node_item[data-id="+parseInt(main_id)+"]").attr('chose_son_num'))){
                            // 已全选
                            $(".node_item[data-id="+parseInt(main_id)+"]").find('.select_box').removeClass('have');
                            $(".node_item[data-id="+parseInt(main_id)+"]").find('.select_box').removeClass('chose');
                            $(".node_item[data-id="+parseInt(main_id)+"]").find('.select_box').addClass('chose');
                        }else { // 未全选
                            $(".node_item[data-id="+parseInt(main_id)+"]").find('.select_box').removeClass('chose');
                            $(".node_item[data-id="+parseInt(main_id)+"]").find('.select_box').removeClass('have');
                            $(".node_item[data-id="+parseInt(main_id)+"]").find('.select_box').addClass('have');
                        }
                    }
                }else {
                    // 执行只选中自己
                    // 1.处理自己(子)
                    chose_ids.push(son_id);
                    $(".node_item[data-id="+parseInt(son_id)+"]").find('.select_box').addClass('chose');
                    // 2.处理上级(主)
                    if(chose_ids.indexOf(main_id) === -1){ // 对应主菜单未选中
                        chose_ids.push(main_id); // 选中对应主菜单
                    }
                    $(".node_item[data-id="+parseInt(main_id)+"]").attr('chose_son_num', (parseInt($(".node_item[data-id="+parseInt(main_id)+"]").attr('chose_son_num')) + 1));
                    if(parseInt($(".node_item[data-id="+parseInt(main_id)+"]").attr('son_num')) === parseInt($(".node_item[data-id="+parseInt(main_id)+"]").attr('chose_son_num'))){
                        // 已全选
                        $(".node_item[data-id="+parseInt(main_id)+"]").find('.select_box').removeClass('have');
                        $(".node_item[data-id="+parseInt(main_id)+"]").find('.select_box').removeClass('chose');
                        $(".node_item[data-id="+parseInt(main_id)+"]").find('.select_box').addClass('chose');
                    }else { // 未全选
                        $(".node_item[data-id="+parseInt(main_id)+"]").find('.select_box').removeClass('chose');
                        $(".node_item[data-id="+parseInt(main_id)+"]").find('.select_box').removeClass('have');
                        $(".node_item[data-id="+parseInt(main_id)+"]").find('.select_box').addClass('have');
                    }
                }
            }else {
                if(!$(".node_item[data-id="+parseInt(son_id)+"]").find('.select_box').hasClass('have')) {
                    // 执行只选中自己
                    // 1.处理自己(子)
                    chose_ids.push(son_id);
                    $(".node_item[data-id="+parseInt(son_id)+"]").find('.select_box').addClass('have');
                    // 2.处理上级(主)
                    if(chose_ids.indexOf(main_id) === -1){ // 对应主菜单未选中
                        chose_ids.push(main_id); // 选中对应主菜单
                    }
                    $(".node_item[data-id="+parseInt(main_id)+"]").attr('chose_son_num', (parseInt($(".node_item[data-id="+parseInt(main_id)+"]").attr('chose_son_num')) + 1));
                    if(parseInt($(".node_item[data-id="+parseInt(main_id)+"]").attr('son_num')) === parseInt($(".node_item[data-id="+parseInt(main_id)+"]").attr('chose_son_num'))){
                        // 已全选
                        $(".node_item[data-id="+parseInt(main_id)+"]").find('.select_box').removeClass('have');
                        $(".node_item[data-id="+parseInt(main_id)+"]").find('.select_box').removeClass('chose');
                        $(".node_item[data-id="+parseInt(main_id)+"]").find('.select_box').addClass('chose');
                    }else { // 未全选
                        $(".node_item[data-id="+parseInt(main_id)+"]").find('.select_box').removeClass('chose');
                        $(".node_item[data-id="+parseInt(main_id)+"]").find('.select_box').removeClass('have');
                        $(".node_item[data-id="+parseInt(main_id)+"]").find('.select_box').addClass('have');
                    }
                }else {
                    // 执行全部选中
                    // 1.处理自己(子)
                    $(".node_item[data-id="+parseInt(son_id)+"]").attr('chose_son_num', (parseInt($(".node_item[data-id="+parseInt(son_id)+"]").attr('son_num'))));
                    $(".node_item[data-id="+parseInt(son_id)+"]").find('.select_box').addClass('chose');
                    // 2.处理下级(孙)
                    if(parseInt($(".node_item[data-id="+parseInt(son_id)+"]").attr('son_num')) > 0){ // 有孙
                        $(".node_item_son[data-id="+parseInt(son_id)+"]").next('.grandson_box').find('.node_item_grandson').each(function () {
                            chose_ids.push($(this).attr('data-id'));
                            $(this).find('.select_box').addClass('chose');
                        });
                    }
                    // 3.处理上级(主)
                    $(".node_item[data-id="+parseInt(main_id)+"]").attr('chose_son_num', (parseInt($(".node_item[data-id="+parseInt(main_id)+"]").attr('chose_son_num')) + parseInt($(".node_item[data-id="+parseInt(son_id)+"]").attr('chose_son_num'))));
                    if(parseInt($(".node_item[data-id="+parseInt(main_id)+"]").attr('son_num')) === parseInt($(".node_item[data-id="+parseInt(main_id)+"]").attr('chose_son_num'))){
                        // 已全选
                        $(".node_item[data-id="+parseInt(main_id)+"]").find('.select_box').removeClass('have');
                        $(".node_item[data-id="+parseInt(main_id)+"]").find('.select_box').removeClass('chose');
                        $(".node_item[data-id="+parseInt(main_id)+"]").find('.select_box').addClass('chose');
                    }else { // 未全选
                        $(".node_item[data-id="+parseInt(main_id)+"]").find('.select_box').removeClass('chose');
                        $(".node_item[data-id="+parseInt(main_id)+"]").find('.select_box').removeClass('have');
                        $(".node_item[data-id="+parseInt(main_id)+"]").find('.select_box').addClass('have');
                    }
                }
            }
        }
    }

    // 选择孙菜单
    function chose_grandson(grandson_id, son_id, main_id) {
        if(chose_ids.indexOf(grandson_id) === -1){ // 之前状态是未选中
            // 1. 选中目标孙菜单
            chose_ids.push(grandson_id); // 选中目标孙菜单
            $(".node_item[data-id="+parseInt(grandson_id)+"]").find('.select_box').addClass('chose');
            // 2. 处理子“子”菜单
            if(chose_ids.indexOf(son_id) === -1){ // 对应“子”菜单未选中
                chose_ids.push(son_id); // 选中对应“子”菜单
            }
            // 子菜单选中数量+1
            $(".node_item[data-id="+parseInt(son_id)+"]").attr('chose_son_num', (parseInt($(".node_item[data-id="+parseInt(son_id)+"]").attr('chose_son_num')) + 1));
            // 判断加数量后是否全选中
            if(parseInt($(".node_item[data-id="+parseInt(son_id)+"]").attr('son_num')) === parseInt($(".node_item[data-id="+parseInt(son_id)+"]").attr('chose_son_num'))){
                // 已全选
                $(".node_item[data-id="+parseInt(son_id)+"]").find('.select_box').removeClass('have');
                $(".node_item[data-id="+parseInt(son_id)+"]").find('.select_box').removeClass('chose');
                $(".node_item[data-id="+parseInt(son_id)+"]").find('.select_box').addClass('chose');
            }else { // 未全选
                $(".node_item[data-id="+parseInt(son_id)+"]").find('.select_box').removeClass('chose');
                $(".node_item[data-id="+parseInt(son_id)+"]").find('.select_box').removeClass('have');
                $(".node_item[data-id="+parseInt(son_id)+"]").find('.select_box').addClass('have');
            }
            // 3. 处理主菜单
            if(chose_ids.indexOf(main_id) === -1){ // 对应主菜单未选中
                chose_ids.push(main_id); // 选中对应主菜单
            }
            if(parseInt($(".node_item[data-id="+parseInt(son_id)+"]").attr('chose_son_num')) === 1){ // 子菜单是第一次选中
                // 主菜单选中数量+1
                $(".node_item[data-id="+parseInt(main_id)+"]").attr('chose_son_num', (parseInt($(".node_item[data-id="+parseInt(main_id)+"]").attr('chose_son_num')) + 1));
            }
            // 主菜单选中数量+1
            $(".node_item[data-id="+parseInt(main_id)+"]").attr('chose_son_num', (parseInt($(".node_item[data-id="+parseInt(main_id)+"]").attr('chose_son_num')) + 1));
            // 判断加数量后是否全选中
            if(parseInt($(".node_item[data-id="+parseInt(main_id)+"]").attr('son_num')) === parseInt($(".node_item[data-id="+parseInt(main_id)+"]").attr('chose_son_num'))){
                // 已全选
                $(".node_item[data-id="+parseInt(main_id)+"]").find('.select_box').removeClass('chose');
                $(".node_item[data-id="+parseInt(main_id)+"]").find('.select_box').removeClass('have');
                $(".node_item[data-id="+parseInt(main_id)+"]").find('.select_box').addClass('chose');
            }else { // 未全选
                $(".node_item[data-id="+parseInt(main_id)+"]").find('.select_box').removeClass('chose');
                $(".node_item[data-id="+parseInt(main_id)+"]").find('.select_box').removeClass('have');
                $(".node_item[data-id="+parseInt(main_id)+"]").find('.select_box').addClass('have');
            }
        }else { // 之前状态是选中
            // 1.取消选中当前菜单
            chose_ids.splice(chose_ids.indexOf(grandson_id), 1); // 取消选中目标菜单
            $(".node_item[data-id="+parseInt(grandson_id)+"]").find('.select_box').removeClass('chose');
            // 2. 处理子“子”菜单
            // “子”子菜单选中数量-1
            $(".node_item[data-id="+parseInt(son_id)+"]").attr('chose_son_num', (parseInt($(".node_item[data-id="+parseInt(son_id)+"]").attr('chose_son_num')) - 1));
            // 判断减数量后是否全未选中
            if(parseInt($(".node_item[data-id="+parseInt(son_id)+"]").attr('chose_son_num')) === 0){
                // 全未选
                $(".node_item[data-id="+parseInt(son_id)+"]").find('.select_box').removeClass('chose');
                $(".node_item[data-id="+parseInt(son_id)+"]").find('.select_box').removeClass('have');
                chose_ids.splice(chose_ids.indexOf(son_id), 1); // 取消选中“子”菜单
            }else { // 未全选
                $(".node_item[data-id="+parseInt(son_id)+"]").find('.select_box').removeClass('chose');
                $(".node_item[data-id="+parseInt(son_id)+"]").find('.select_box').removeClass('have');
                $(".node_item[data-id="+parseInt(son_id)+"]").find('.select_box').addClass('have');
            }
            // 3.处理主菜单
            if(parseInt($(".node_item[data-id="+parseInt(son_id)+"]").attr('chose_son_num')) === 0){
                let _chose_son_num = (parseInt($(".node_item[data-id="+parseInt(main_id)+"]").attr('chose_son_num'))) -1;
                $(".node_item[data-id="+parseInt(main_id)+"]").attr('chose_son_num', _chose_son_num);
            }
            // 主菜单选中数量-1
            $(".node_item[data-id="+parseInt(main_id)+"]").attr('chose_son_num', (parseInt($(".node_item[data-id="+parseInt(main_id)+"]").attr('chose_son_num')) - 1));
            // 判断减数量后是否全未选中
            if(parseInt($(".node_item[data-id="+parseInt(main_id)+"]").attr('chose_son_num')) === 0){
                // 全未选
                $(".node_item[data-id="+parseInt(main_id)+"]").find('.select_box').removeClass('chose');
                $(".node_item[data-id="+parseInt(main_id)+"]").find('.select_box').removeClass('have');
                chose_ids.splice(chose_ids.indexOf(main_id), 1); // 取消选中主菜单
            }else { // 未全选
                $(".node_item[data-id="+parseInt(main_id)+"]").find('.select_box').removeClass('chose');
                $(".node_item[data-id="+parseInt(main_id)+"]").find('.select_box').removeClass('have');
                $(".node_item[data-id="+parseInt(main_id)+"]").find('.select_box').addClass('have');
            }
        }
    }

    layui.use('form', function(){
        let form = layui.form;
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