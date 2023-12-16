<?php /*a:2:{s:75:"/home/gemxpbra/public_html/application/admin/view/term/member_team_log.html";i:1697722712;s:89:"/home/gemxpbra/public_html/application/admin/view/../../../public/static/common/base.html";i:1697722706;}*/ ?>
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
    

<!-- 		<link rel="stylesheet" href="https://unpkg.com/element-ui/lib/theme-chalk/index.css">
		先引入 Vue
		<script src="https://cdn.jsdelivr.net/npm/vue@2.6.12"></script>
		引入组件库
		<script src="https://unpkg.com/element-ui/lib/index.js"></script>
		<link rel="stylesheet" href="/../static/index.css"> -->
		<link rel="stylesheet" href="https://unpkg.com/element-ui/lib/theme-chalk/index.css">
		<script src="/../static/vue.js"></script>
		<script src="/../static/index.js"></script>

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

		<div id="treePage">
				<div class="layui-card-body ">
                <div class="layui-tab-content">
                 <div class="layui-form layui-col-space5">
                     <div class="layui-input-inline layui-show-xs-block">
                         <input type="text" id="tel" name="tel" placeholder="手机号码" value="<?php echo htmlentities($phone); ?>"autocomplete="off" class="layui-input" ref="searchInput">
                     </div>
                     <div class="layui-input-inline layui-show-xs-block">
                        <button class="layui-btn" @click.stop="LoadingInfo">
                             <i class="layui-icon">&#xe615;</i>
                         </button>
                     </div>
                 </div>
             </div>
            </div>
			<el-tree :data="data" :props="props" :load="loadNode" lazy ref="tree">
				<span class="custom-tree-node" slot-scope="{ node, data }"> 
					<span style="font-size:16px;">Id:{{ data.id }}--<span style="margin-right:16px">名称:{{data.user}}</span>(
					<span style="margin-right:16px">电话：<span>{{data.tel}}</span></span>
					<span style="margin-right:16px">有效团队：<span style="color:red;">{{data.yx_team}}</span></span>
					<span style="margin-right:16px">个人资产：<span style="color:orange;">{{data.personAssets}}</span></span>
					<span style="margin-right:16px">团队资产：<span style="color:green;">{{data.teamAssets}}</span></span>
					<span style="margin-right:16px">今日团队资产：<span style="color:blue;">{{data.teamAssetsToday}}</span></span>
					<span style="margin-right:16px">个人总收益：<span style="color:green;">{{data.allReward}}</span></span>
					<span style="margin-right:16px">团队总收益：<span>{{data.teamallReward}}</span></span>
					)</span>					
				 </span>
			</el-tree>
		</div>
		

</body>

<script>

new Vue({
	el: "#treePage",
	data() {
		return {
			props: {
				label:'text',
				children: 'zones',
				isLeaf: 'leaf'
			},
			data:[]
		}
	},
	methods: {
		/* let phone = <?php echo htmlentities($phone); ?>
		this.loadNode({tel:phone}); */
		LoadingInfo(){
			let input = this.$refs.searchInput.value;
			/* let phone = <?php echo htmlentities($phone); ?>;
			console.log(phone);
			if(input != '' && phone > 0){
				input = <?php echo htmlentities($phone); ?>
			} */
			this.loadNode({tel:input})
		},
		loadNode(node, resolve) {
        	let vm = this
			//console.log(node);
			let data = {};
			if(node.data){
				data.id = node.data.id;
			}
			if(node.tel){
				data.tel = node.tel
			}
			$.ajax({
				type : "post",
				url : "<?php echo url('term/memberteam'); ?>",
				async : false,
				data  : data,
				success : function(data) {
					//console.log(data)
					if(resolve){
						resolve(data);
					}else{
						vm.data =data
					}
					
		
				},
				error : function(){
					layer.msg("网络异常,请稍后刷新重试~",{icon:5,time:3000},function(){
						layer.close(index);
					});
				}
			})
			/*			console.log(node)
 			if (node.level === 0) {
				return resolve([{
					id:111,
					name: 'region'
				},{
					id:2,
					name:'tree2'
				}]);
			}
			if (node.level == 1) return resolve([{id:3,name:'tree3'}]);
			if (node.level > 2) return resolve([]);

			setTimeout(() => {
				const data = [{
					id:4,
					name: 'leaf',
					leaf: true
				}, {
					id:5,
					name: 'zone'
				}];

				resolve(data);
			}, 500); */
		}
	}
})


</script>

</html>