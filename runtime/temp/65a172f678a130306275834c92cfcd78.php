<?php /*a:1:{s:67:"/home/gemxpbra/public_html/application/admin/view/index/indexs.html";i:1697722712;}*/ ?>
<!doctype html>
<html class="x-admin-sm">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlentities($log_title); ?></title>
    <meta name="renderer" content="webkit|ie-comp|ie-stand">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width,user-scalable=yes, minimum-scale=0.4, initial-scale=0.8,target-densitydpi=low-dpi" />
    <meta http-equiv="Cache-Control" content="no-siteapp" />
    <link href="/../static/css/font.css" rel="stylesheet">
    <link href="/../static/css/xadmin.css" rel="stylesheet">
    <script type="text/javascript" src="/../static/js/jquery.min.js"></script>
    <script src="/../static/lib/layui/layui.js" charset="utf-8"></script>
    <script src="/../static/js/xadmin.js" type="text/javascript"></script>
    <!--[if lt IE 9]>
    <script src="/../static/js/html5.min.js"></script>
    <script src="/../static/js/respond.min.js"></script>
    <![endif]-->
    <style>
        .module_menus_box {
            display: none;
        }
		.left-nav{
            background: #20222a !important;
        }
        .left-nav a{
            color: rgba(255, 255, 255, 1) !important;
        }
    </style>
    <script>
        var is_remember = false; // 关闭刷新记忆tab功能
    </script>
</head>
<body class="index">
<div class="container">
    <div class="logo">
        <a href="<?php echo url('index/index'); ?>"><?php echo htmlentities($log_title); ?></a></div><!-- /admin/index/index -->
    <div class="left_open">
        <a><i title="展开左侧栏" class="iconfont">&#xe699;</i></a>
    </div>
    <ul class="layui-nav left fast-add" lay-filter="">
        <?php if(is_array($menus) || $menus instanceof \think\Collection || $menus instanceof \think\Paginator): $i = 0; $__LIST__ = $menus;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$module_item): $mod = ($i % 2 );++$i;?>
        <li class="layui-nav-item" id="module_<?php echo htmlentities($module_item['module']['id']); ?>" data-module_id="<?php echo htmlentities($module_item['module']['id']); ?>">
            <a onclick="changeModule('<?php echo htmlentities($module_item['module']['id']); ?>')"><?php echo htmlentities($module_item['module']['name']); ?></a>
        </li>
        <?php endforeach; endif; else: echo "" ;endif; ?>
    </ul>
    <ul class="layui-nav right" lay-filter="">
        <li class="layui-nav-item">
            <a href="javascript:;"><?php echo htmlentities($username); ?></a>
            <dl class="layui-nav-child">
                <dd><a onclick="xadmin.open('个人信息' ,'<?php echo url('user/info'); ?>', 600, 500)">个人信息</a></dd><!-- /user/info -->
                <dd><a href="<?php echo url('user/logout'); ?>">退出</a></dd> <!-- /user/logout -->
            </dl>
        </li>
    </ul>
</div>
<div class="left-nav">
    <div id="side-nav">
        <ul id="nav">
            <?php if(is_array($menus) || $menus instanceof \think\Collection || $menus instanceof \think\Paginator): $index = 0; $__LIST__ = $menus;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$module): $mod = ($index % 2 );++$index;?>
            <div class="module_menus_box" id="module_menus_box_<?php echo htmlentities($index); ?>" data-module_id="<?php echo htmlentities($index); ?>">
                <?php if(!empty($module['menus'])): if(is_array($module['menus']) || $module['menus'] instanceof \think\Collection || $module['menus'] instanceof \think\Paginator): $i = 0; $__LIST__ = $module['menus'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$menu_main): $mod = ($i % 2 );++$i;?>
                <li>
                    <?php if($menu_main['menu']['type'] == 1): ?>
                    <a href="javascript:;">
                        <i class="iconfont left-nav-li" lay-tips="<?php echo htmlentities($menu_main['menu']['name']); ?>"><?php echo $menu_main['menu']['icon'];?></i>
                        <cite><?php echo htmlentities($menu_main['menu']['name']); ?></cite>
                        <i class="iconfont nav_right">&#xe697;</i>
                    </a>
                    <ul class="sub-menu">
                        <?php if(!empty($menu_main['menus'])): if(is_array($menu_main['menus']) || $menu_main['menus'] instanceof \think\Collection || $menu_main['menus'] instanceof \think\Paginator): $i = 0; $__LIST__ = $menu_main['menus'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$menu_son): $mod = ($i % 2 );++$i;?>
                        <li>
                            <a onclick="xadmin.add_tab('<?php echo htmlentities($menu_son['name']); ?>', '/witkey2022.php<?php echo htmlentities($menu_son['action']); ?>')">
                                <i class="iconfont">&#xe6a7;</i>
                                <cite><?php echo htmlentities($menu_son['name']); ?></cite></a>
                        </li>
                        <?php endforeach; endif; else: echo "" ;endif; ?>
                        <?php endif; ?>
                    </ul>
                    <?php else: ?>
                    <a onclick="xadmin.add_tab('<?php echo htmlentities($menu_main['menu']['name']); ?>', '<?php echo htmlentities($menu_main['menu']['action']); ?>')">
                        <i class="iconfont"><?php echo $menu_main['menu']['icon'];?></i>
                        <cite><?php echo htmlentities($menu_main['menu']['name']); ?></cite>
                    </a>
                    <?php endif; ?>
                </li>
                <?php endforeach; endif; else: echo "" ;endif; ?>
                <?php endif; ?>
            </div>
            <?php endforeach; endif; else: echo "" ;endif; ?>
        </ul>
    </div>
</div>
<div class="page-content">
    <div class="layui-tab tab" lay-filter="xbs_tab" lay-allowclose="false">
        <ul class="layui-tab-title">
            <li class="home"><i class="layui-icon">&#xe68e;</i>欢迎</li>
        </ul>
        <div class="layui-unselect layui-form-select layui-form-selected" id="tab_right">
            <dl>
                <dd data-type="this">关闭当前</dd>
                <dd data-type="other">关闭其它</dd>
                <dd data-type="all">关闭全部</dd>
            </dl>
        </div>
        <div class="layui-tab-content">
            <div class="layui-tab-item layui-show">
                <!-- <iframe src='/witkey2022.php/index/welcome' frameborder="0" scrolling="yes" class="x-iframe"></iframe> -->
                <iframe src="<?php echo url('index/welcome'); ?>" frameborder="0" scrolling="yes" class="x-iframe"></iframe>
            </div>
        </div>
        <div id="tab_show"></div>
    </div>
</div>
<div class="page-content-bg"></div>
<style id="theme_style"></style>
</body>
<script>
    var active_module = '1'; // 当前模块
    $("#module_"+active_module).addClass('layui-this'); // 初始化系统模块
    $("#module_menus_box_"+active_module).show(500); // 初始化系统模块菜单

    // 切换模块
    function changeModule(module_id) {
        if(module_id != active_module){
            active_module = module_id; // 切换模块
            $(".module_menus_box").hide(); // 先隐藏所有菜单
            $("#module_menus_box_"+active_module).show(500); // 显示当前模块菜单
        }
    }
</script>
</html>