<?php
// .-----------------------------------------------------------------------------------
// | WE TRY THE BEST WAY 杭州博也网络科技有限公司
// |-----------------------------------------------------------------------------------
// | Author: 贝贝 <hebiduhebi@163.com>
// | Copyright (c) 2013-2016, http://www.itboye.com. All Rights Reserved.
// |-----------------------------------------------------------------------------------

namespace Admin\Controller;

class WxshopProductController extends AdminController {
	
	/**
	 * 商品SKU 管理
	 */
	public function sku(){
		
		if(IS_GET){
			$productid = I('get.productid','');
			$id = I('get.id',0);
			
			$result = apiCall("Admin/Wxproduct/getInfo", array(array('product_id'=>$productid) ));
			
			if(!$result['status']){
				$this->error($result['info']);
			}
			
			if($result['info']['has_sku'] == 1){
				//多规格
				$skuinfo = $result['info']['sku_info'];
				
				$this->assign("skuinfo",$this->getSkuValue(json_decode($skuinfo,JSON_UNESCAPED_UNICODE)));
				
				$skulist = apiCall("Admin/WxproductSku/queryNoPaging", array(array('product_id'=>$productid)));
				if($skulist['status']){
					
					$this->assign("skuvalue",json_encode($skulist['info']));
				}
			}
			
			if(is_null($result['info'])){
				$this->error("警告：商品信息获取失败！");
			}
			
			$wxshopapi = new \Common\Api\WxShopApi($this -> appid, $this -> appsecret);
			
			$cate_id = $result['info']['cate_id'];
			
			$result = $wxshopapi -> getSKU($cate_id);
			
			if($result['status']){				
				$this->assign("skulist",$this->color2First($result['info']));
			}
			
			$this->assign("productid",$productid);
			$this->assign("id",$id);
			$this->assign("storeid",$result['info']['storeid']);
			$this->display();
		}else{
			
			$sku_list = I('post.sku_list','');
			$sku_info = I('post.sku_info','');
			$id = I('post.id',0);
			$productid = I('post.productid',0);
			
			$sku_info = json_decode(htmlspecialchars_decode($sku_info),JSON_UNESCAPED_UNICODE);
			$sku_list = json_decode(htmlspecialchars_decode($sku_list),JSON_UNESCAPED_UNICODE);
//			
//			dump($sku_list);			
//			dump($sku_info);
			
//			$sku_info = json_encode($sku_info);
			$result = apiCall("Admin/WxproductSku/addSkuList", array($productid,$sku_info,$sku_list));
			if(!$result['status']){
				$this->error($result['info']);
			}else{
				$this->success("保存成功！");
			}
		}
	}
	
	/**
	 * 
	 */
	private function getSkuValue($skuvalue){
		$valuelist = "";
		foreach($skuvalue as $value){
			foreach($value['vid'] as $vo){
				$valuelist = $valuelist.$vo.",";
			}
		}
		return $valuelist;
	}
	
	/**
	 * 商品详情页/新增
	 * @param $get.productid  商品ID
	 * @param $get.storeid 店铺ID
	 */
	public function detail(){
		if(IS_GET){
			$productid = I('get.productid','');
			$id = I('get.id',0);
			$storeid = I('get.storeid',0);
			if(empty($productid)){
				$this->error("缺少商品ID");
			}
			if(empty($storeid)){
				$this->error("缺少店铺ID");
			}
			$map['product_id'] = $productid;
			$result = apiCall("Admin/Wxproduct/getInfo",array($map));
			if($result['status']){
				$detail = $result['info']['detail'];
				
			}else{
				$this->error("商品信息获取失败！");
			}
			
			$this->assign("detail",json_decode(htmlspecialchars_decode($detail),JSON_UNESCAPED_UNICODE));
			$this->assign("productid",$productid);
			$this->assign("storeid",$storeid);
			$this->assign("id",$id);
			$this->display();
			
		}else{
			$detail = I("post.detail",'');
			$productid = I("post.productid",'');
				
			$product_detail = array();
			
			if(!empty($detail)){
				$detail_decode = json_decode(htmlspecialchars_decode($detail),JSON_UNESCAPED_UNICODE);				
			}else{
				$this->error("详情为空");
			}
			
			$map['product_id'] = $productid;
			$result = apiCall("Admin/Wxproduct/save",array($map,array('detail'=>$detail)));
			if($result['status']){
				$this->success("修改成功！");
			}else{
				$this->error($result['info']);
			}
		}
	}
	
	/**
	 * 首页/商品管理页面
	 */
	public function index() {
		$onshelf = I('onshelf', 0);
		$storeid = I('storeid', 0, "intval");
		if (empty($storeid)) {
			$this -> error("缺少店铺ID参数！");
		}
		//get.startdatetime
		//分页时带参数get参数
		$params = array('onshelf' => $onself);
		$name = I('post.name', '');

		$map = array();
		if (!empty($name)) {
			$map['name'] = array('like', '%' + $name + '%');
			$params['name'] = $name;
		}
		$map['onshelf'] = $onshelf;
		$map['storeid'] = $storeid;
		$page = array('curpage' => I('get.p', 0), 'size' => C('LIST_ROWS'));
		$order = " createtime desc ";
		//
		$result = apiCall('Admin/Wxproduct/query', array($map, $page, $order, $params));
		//
		if ($result['status']) {
			$this -> assign('name', $name);
			$this -> assign('onshelf', $onshelf);
			$this -> assign('storeid', $storeid);
			$this -> assign('show', $result['info']['show']);
			$this -> assign('list', $result['info']['list']);
			$this -> display();
		} else {
			LogRecord('INFO:' . $result['info'], '[FILE] ' . __FILE__ . ' [LINE] ' . __LINE__);
			$this -> error(L('UNKNOWN_ERR'));
		}
	}


	/**
	 * 商品上下架
	 * @param $success_url 删除成功后跳转
	 */
	public function shelf() {
		$wxshopapi = new \Common\Api\WxShopApi($this -> appid, $this -> appsecret);
		$status = I('get.on',0,'intval');
		$map = array('product_id' => I('get.productid', -1));
		
		$result = $wxshopapi -> productStatus($map['product_id'],$status);
		
		if ($result['status']) {
			$entity['onshelf'] = $status;
			$result = apiCall('Admin/Wxproduct/save', array($map,$entity));

			if ($result['status'] === false) {
				LogRecord('[INFO]' . $result['info'], '[FILE] ' . __FILE__ . ' [LINE] ' . __LINE__);
				$this -> error($result['info']);
			} else {
				$this -> success(L('RESULT_SUCCESS'));
			}

		} else {
			LogRecord('[INFO]' . $result['info'], '[FILE] ' . __FILE__ . ' [LINE] ' . __LINE__);
			$this -> error($result['info']);
		}
	}

	/**
	 * 单个删除
	 * @param $success_url 删除成功后跳转
	 */
	public function delete($success_url = false) {
		$wxshopapi = new \Common\Api\WxShopApi($this -> appid, $this -> appsecret);

		if ($success_url === false) {
			$success_url = U('Admin/WxshopProduct/index',array('storeid'=>$storeid));
		}
		$map = array('product_id' => I('productid', -1));
		
		$result = $wxshopapi -> productDel($map['product_id']);
		
//		dump($result);

		if ($result['status']) {

			$result = apiCall('Admin/Wxproduct/delete', array($map));

			if ($result['status'] === false) {
				LogRecord('[INFO]' . $result['info'], '[FILE] ' . __FILE__ . ' [LINE] ' . __LINE__);
				$this -> error($result['info']);
			} else {
				$this -> success(L('RESULT_SUCCESS'));
			}

		} else {
			LogRecord('[INFO]' . $result['info'], '[FILE] ' . __FILE__ . ' [LINE] ' . __LINE__);
			$this -> error($result['info']);
		}
	}
	
	/**
	 * SKU列表
	 */
//	public function skulist() {
//
//		$wxshopapi = new \Common\Api\WxShopApi($this -> appid, $this -> appsecret);
//
//		if (IS_POST) {
//			$cate_id = I('cate_id', 1);
//
//			$result = $wxshopapi -> getSKU($cate_id);
//
//			if ($result['status']) {
//				$this -> success($result['info']);
//			} else {
//				$this -> error($result['info']);
//			}
//		}
//	}

	/**
	 * 商品预创建－选择类目
	 */
	public function precreate() {
		$wxshopapi = new \Common\Api\WxShopApi($this -> appid, $this -> appsecret);

		if (IS_POST) {
			//保存
		} else {

			$result = $wxshopapi -> category(1);
			$storeid = I('get.storeid', 0);

			if ($storeid == 0) {
				$this -> error("缺少商铺ID参数");
			}

			$this -> assign("storeid", $storeid);
			if ($result['status']) {
				$this -> assign("rootcate", $result['info']);
			}
			$this -> display();
		}
	}

	/**
	 * 添加商品
	 *
	 */
	public function create() {

		if (IS_POST) {

			$wxshopapi = new \Common\Api\WxShopApi($this -> appid, $this -> appsecret);
			
			$base_attr = $this -> getBaseAttr();
			$storeid = I('storeid', 0);

			if ($storeid == 0) {
				$this -> error("缺少商铺ID参数");
			}

			$attrext = array('isPostFree' => 1, 'isHasReceipt' => I('post.ishasreceipt', 0), 'isUnderGuaranty' => I('post.isunderguaranty', 0), 'isSupportReplace' => I('post.issupportreplace', 0), 'location' => array('country' => '中国', 'province' => '四川省', 'city' => '内江市', 'address' => '威远县'));
			$sku_list = $this -> getSKUList();

			$product = array('product_base' => $base_attr, 'attrext' => $attrext, 'sku_list' => $sku_list);

			//			echo (json_encode($product,JSON_UNESCAPED_UNICODE));
			
			$result = ($wxshopapi -> productCreate($product));
			$product_id = "";
			if ($result['status']) {
				$info = $result['info'];
				if ($info -> errcode == 0) {
					$product_id = $info -> product_id;
				} else {
					$this -> error($info -> errmsg);
				}
			} else {
				$this -> error($result['info']);
			}

			//TODO:添加到本地
			//			dump($product);
			//			dump($product_id);

			if (!empty($product_id)) {
				$result = $this -> addToProduct($storeid, $product_id, $product);
			}

			$this -> productToGroup($wxshopapi, $product_id);
			if ($result['status']) {
				$this -> success("操作成功!", U('Admin/WxshopProduct/index', array('storeid' => $storeid)));
			} else {
				$this -> error($result['info']);
			}

		} else {
			$catename = I('catename', '');
			$storeid = I('storeid', 0);
			$this -> assign("storeid", $storeid);
			$this -> assign("catename", $catename);
			$this -> assign("cates", I('cates', ''));
			$this -> display();
		}
	}

	/**
	 * 修改商品
	 *	1 product_id表示要更新的商品的ID，其他字段说明请参考增加商品接口。
		2 从未上架的商品所有信息均可修改，否则商品的名称(name)、商品分类(category)、商品属性(property)这三个字段不可修改。
	 */
	public function edit() {

		if (IS_POST) {
			
			$wxshopapi = new \Common\Api\WxShopApi($this -> appid, $this -> appsecret);
			$productid = I('post.product_id','');
			if(empty($productid)){
				$this -> error("缺少商品ID参数");
			}
			$base_attr = $this -> getBaseAttr();
			$storeid = I('storeid', 0);

			if ($storeid == 0) {
				$this -> error("缺少商铺ID参数");
			}
			
			$attrext = array('isPostFree' => 1, 'isHasReceipt' => I('post.ishasreceipt', 0), 'isUnderGuaranty' => I('post.isunderguaranty', 0), 'isSupportReplace' => I('post.issupportreplace', 0), 'location' => array('country' => '中国', 'province' => '四川省', 'city' => '内江市', 'address' => '威远县'));
			$sku_list = $this -> getSKUList();

			$product = array('product_base' => $base_attr, 'attrext' => $attrext, 'sku_list' => $sku_list);

			//			echo (json_encode($product,JSON_UNESCAPED_UNICODE));

			$result = ($wxshopapi -> productMod($productid,$product));
			$product_id = "";
			if ($result['status']) {
				$info = $result['info'];
				if ($info -> errcode == 0) {
					$product_id = $info -> product_id;
				} else {
					$this -> error($info -> errmsg);
				}
			} else {
				$this -> error($result['info']);
			}

			//TODO:添加到本地
			//			dump($product);
			//			dump($product_id);

			
			$result = $this -> saveToProduct($productid, $product);
			
			$this -> productToGroup($wxshopapi, $product_id);
			if ($result['status']) {
				$this -> success("操作成功!", U('Admin/WxshopProduct/index', array('storeid' => $storeid)));
			} else {
				$this -> error($result['info']);
			}

		} else {
			$id = I('get.id',0);
			$result = apiCall("Admin/Wxproduct/getInfo", array(array('id'=>$id)));
			if($result['status']){				
				$product = $result['info'];				
//				$catename = I('catename', '');
				$storeid = I('storeid', 0);
				$this -> assign("storeid", $storeid);
				$this -> assign("catename", $catename);
				$this -> assign("cates", I('cates', ''));
				$this -> display();
			}else{
				$this->error($result['info']);
			}
		}
	}
	

	/**
	 * 指定分类的所有属性
	 */
	public function cateAllProp() {

		if (IS_AJAX) {
			$cate_id = I('cate_id', 1);
			$wxshopapi = new \Common\Api\WxShopApi($this -> appid, $this -> appsecret);
			$result = $wxshopapi -> cateAllProp($cate_id);
			if ($result['status']) {
				$this -> success($result['info']);
			} else {
				$this -> error($result['info']);
			}
		}
	}

	
	/**
	 * 同步商品信息到微信服务器
	 */
	public function sendToWxServer(){
		if(IS_AJAX){
			$id = I('get.id',0);
			$map['id']=$id;
			$result = apiCall("Admin/Wxproduct/getInfo", array($map));
			if($result['status']){
				
				$wxshopapi = new \Common\Api\WxShopApi($this -> appid, $this -> appsecret);
				$wxproduct = $this->toWeixinproduct($result['info']);
				$wxproduct['product_id'] = $result['info']['product_id'];
				
				$result = $wxshopapi->productMod($wxproduct);
//				var_dump(json_encode($wxproduct,JSON_UNESCAPED_UNICODE));
				if($result['status']){
					$this->success("操作成功！");
				}else{
					$this->error($result['info']);
				}
								
			}else{
				$this->error($result['info']);
			}
		}
	}

	
	//==========================私有方法
	/**
	 * 将产品信息保存到数据库
	 */
	private function addToProduct($storeid, $productid, $product) {

		$has_sku = I('post.has_sku', 0, 'intval');

		$entity = array(
					'storeid' => $storeid, 
					'wxaccountid' => getWxAccountID(), 
					'product_id' => $productid, 
					'name' => $product['product_base']['name'], 
					'main_img' => $product['product_base']['main_img'], 
					'img' => I('post.img', ''), 
					'buy_limit' => $product['product_base']['buy_limit'], 
					'cate_id' => $product['product_base']['category_id'][0], 
					'delivery_type' => -1, // 包邮
					'template_id' => '', 
					'express_id' => 0, 
					'express_price' => 0, 
					'attrext_ispostfree' => $product['attrext']['isPostFree'], 
					'attrext_ishasreceipt' => $product['attrext']['isHasReceipt'], 
					'attrext_isunderguaranty' => $product['attrext']['isUnderGuaranty'], 
					'attrext_issupportreplace' => $product['attrext']['isSupportReplace'], 
					'loc_country' => $product['attrext']['location']['country'], 
					'loc_province' => $product['attrext']['location']['province'], 
					'loc_city' => $product['attrext']['location']['city'], 
					'loc_address' => $product['attrext']['location']['address'], 
					'has_sku' => intval($has_sku), 
					'detail' => '', 
					'onself' => '0', 
					'status' => 1,
					'properties'=> I('post.property', ''),
					'sku_info'=>'',
				);
		if ($has_sku == 0) {
			$entity['ori_price'] = $product['sku_list'][0]['ori_price'];
			$entity['price'] = $product['sku_list'][0]['price'];
			$entity['quantity'] = $product['sku_list'][0]['quantity'];
			$entity['product_code'] = $product['sku_list'][0]['product_code'];
		}
		
		$result = apiCall("Admin/Wxproduct/add", array($entity));

		return $result;
	}

	/**
	 * 产品与分组进行关联
	 */
	private function productToGroup($wxshopapi, $productid) {

		$groups = I('groups', '');
		if ($groups) {
			foreach ($groups as $vo) {
				$product_list = array('product_id' => $productid, 'mod_action' => 1);
				$result = $wxshopapi -> groupModProduct($vo, array($product_list));

			}
		}

	}
	
	private function getBaseAttr() {

		$cates = I("post.cates", '');
		$cates = explode("_", $cates);
		if (count($cates) <= 1) {
			$this -> error("商品类目错误！");
		}

		//属性
		$property = I('post.property', '');
		$property = explode(";", $property);
		$properties = array();
		//		dump($property);
		foreach ($property as $vo) {
			$prop = explode(",", $vo);
			if (count($prop) == 2) {
				$properties[] = array('id' => $prop[0], 'vid' => $prop[1]);
			}
		}

		//SKU
		$sku_info = array();

		$category = $cates[count($cates) - 1];
		$main_img = I('post.main_img', '');
		if (I('post.isbuylimit', '0') == 1) {
			$buylimit = I('post.buylimit', 0);
		} else {
			$buylimit = 0;
		}
		$imglist = array();
		$img = explode(",", I('post.img', ''));
		//		dump($img);
		foreach ($img as $vo) {
			if ($vo) {
				$imglist[] = $vo;
			}
		}

		//
		return array('name' => I('post.product_name', ''), 
		'category_id' => array($category), 
		'img' => $imglist,
		 'main_img' => $main_img, 
		'datail' => array(), 
		'property' => $properties, 
		'sku_info' => $sku_info, 
		'buy_limit' => $buylimit);
	}

	private function getSKUList() {
		/**
		 * "sku_list": [
		 {
		 "sku_id": "1075741873:1079742386",
		 "price": 30,
		 "icon_url": "http://mmbiz.qpic.cn/mmbiz/4whpV1VZl28bJj62XgfHPibY3ORKicN1oJ4CcoIr4BMbfA8LqyyjzOZzqrOGz3f5KWq1QGP3fo6TOTSYD3TBQjuw/0",
		 "product_code": "testing",
		 "ori_price": 9000000,
		 "quantity": 800
		 },
		 ],
		 */
		$has_sku = I('post.has_sku', '0');

		if ($has_sku == "0") {
			$ori_price = I('post.ori_price', 0, 'intval');
			$price = I('post.price', 0, 'intval');
			//统一规格
			$sku = array('sku_id' => '', //暂时为统一规格
			'icon_url' => I('post.main_img', ''),
			 'ori_price' => $ori_price * 100, 'price' => $price * 100, 
			 'quantity' => I('post.quantity', 0, 'intval'), 
			 'product_code' => I('post.product_code', "")
			  );
		} else {
			//TODO:多规格
			//post.ori_price[]

		}
		return array($sku);
	}
	
	
	/**
	 * 将本地数据库存储的商品信息转换为微信商品信息
	 * @param $localproduct wxproduct表的一条信息
	 */
	private function toWeixinproduct($localproduct){
		$imglist = array();
		$img = explode(",", $localproduct['img']);
		//		dump($img);
		foreach ($img as $vo) {
			if ($vo) {
				$imglist[] = $vo;
			}
		}
		//处理SKU_INFO
		
		$sku_info = $localproduct['sku_info'];
		$sku_arr = explode(";", $sku_info);
		$sku_infolist = array();
				
		foreach ($sku_arr as $vo) {
			$sku = explode(":", $vo);
			if (count($sku) == 2) {
				$value_arr = array();
				$vallist = explode(",", $sku[1]);
				
				foreach ($vallist as $val) {
					array_push($value_arr,$val);
				}
				
				array_push($sku_infolist,array('id'=>$sku[0],'vid'=>$value_arr));
			}
		}
		
		//处理商品属性
		$property = $localproduct['properties'];
		$property = explode(";", $property);
		$properties = array();
		foreach ($property as $vo) {
			$prop = explode(",", $vo);
			if (count($prop) == 2) {
				$properties[] = array('id' => $prop[0], 'vid' => $prop[1]);
			}
		}
		
		//商品基本信息
		$base_attr = array(
			'name' => $localproduct['name'],
		 	'category_id' => array($localproduct['cate_id']), 
		 	'img' => $imglist, 
		 	'main_img' => $localproduct['main_img'], 
			'property' => $properties, 
			'sku_info' => $sku_infolist, 
			'buy_limit' => intval($localproduct['buy_limit']), 
			'detail'=>array(),
		);
		
		//商品详情
		if(!empty($localproduct['detail'])){
			$detail_arr = json_decode(htmlspecialchars_decode($localproduct['detail']),JSON_UNESCAPED_UNICODE);
			for($i=0;$i<count($detail_arr);$i++){
				if($detail_arr[$i]['type'] == 'text'){
					array_push($base_attr['detail'],array('text'=>$detail_arr[$i]['ct']));
				}else{
					array_push($base_attr['detail'],array('img'=>$detail_arr[$i]['ct']));
				}
			}
		}
		
		
		//属性
		$attrext = array(
			'isPostFree' => intval($localproduct['attrext_ispostfree']) , 
			'isHasReceipt' =>intval($localproduct['attrext_ishasreceipt']) , 
			'isUnderGuaranty' => intval($localproduct['attrext_isunderguaranty']) , 
			'isSupportReplace' => intval($localproduct['attrext_issupportreplace']) , 
			'location' => array(
				'country' => $localproduct['loc_country'], 
				'province' => $localproduct['loc_province'], 
				'city' => $localproduct['loc_city'], 
				'address' => $localproduct['loc_address'], 
			)			
		);
		
		$sku_list = array();
		
		if($localproduct['has_sku'] == 0){
			array_push($sku_list,array(			
			'sku_id'=>''	,	
			'ori_price'=>floatval($localproduct['ori_price'])+10,
			'price' => floatval($localproduct['price']),
			'quantity' => intval($localproduct['quantity']),
			'product_code' => $localproduct['product_code'],
			'icon_url'=>$localproduct['main_img']
			));
		}else{
			//TODO:多规格情况下处理
			//去wxproduct_sku 查询
			
		}
		
		return  array(
			'product_base' => $base_attr, 
			'attrext' => $attrext, 
			'sku_list' => $sku_list
		);
	}
	
	/**
	 * 将颜色SKU 放在最前面
	 */
	private function color2First($skulist){
		$colorIndex = 0;
		for($i=0;$i<count($skulist);$i++){
			if($skulist[$i]->name == "颜色"){
				$colorIndex = $i;
				break;
			}
		}
		
		if($colorIndex > 0){
			$temp = $skulist[0];
			$skulist[0] = $skulist[$colorIndex];
			$skulist[$colorIndex] = $temp;
		}
		return $skulist;
		
	}
}