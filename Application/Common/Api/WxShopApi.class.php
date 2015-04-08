<?php
// .-----------------------------------------------------------------------------------
// | WE TRY THE BEST WAY 杭州博也网络科技有限公司
// |-----------------------------------------------------------------------------------
// | Author: 贝贝 <hebiduhebi@163.com>
// | Copyright (c) 2013-2016, http://www.itboye.com. All Rights Reserved.
// |-----------------------------------------------------------------------------------

namespace Common\Api;

class WxShopApi extends WeixinApi{
	
	//==================分组管理
	/**
	 * 分组所属商品管理
	 * @param $group_id 		分组ID
	 * @param $product_list 产品列表	 [ 修改操作(0- 删除, 1- 增加)
								{
									"product_id": "pDF3iY-CgqlAL3k8Ilz-6sj0UYpk",
									"mod_action": 1 
								}, 
								{
									"product_id": "pDF3iY-RewlAL3k8Ilz-6sjsepp9",
									"mod_action": 0
								}, 
							]
	 * 
	 */
	public function groupModProduct($group_id,$product_list){
		
		$accessToken = $this->getAccessToken();
		$data = json_encode(array('group_id'=>$group_id,'product'=>$product_list),JSON_UNESCAPED_UNICODE);
		$url = "https://api.weixin.qq.com/merchant/group/productmod?access_token=$accessToken";
				
		$result = $this->curlPost($url,$data);
		
		if($result['status']){
			if ($result['msg'] -> errcode == 0) {
				return array('status' => true, 'info' => $result['msg']);
			} else {
				$index = $result['msg'] -> errcode;
				return array('status' => false, 'info' => $this -> errcode[$index]);
			}
		}else{
			return array('status' => false, 'info' => $result['msg']);
		}
	}
	
	/**
	 * 分组信息修改
	 * @param $group_id 分组ID
	 * @param $group_name 分组名称
	 */
	public function groupModify($group_id,$group_name){
		
		$accessToken = $this->getAccessToken();
		$data = json_encode(array('group_id'=>$group_id,'group_name'=>$group_name),JSON_UNESCAPED_UNICODE);
		$url = "https://api.weixin.qq.com/merchant/group/propertymod?access_token=$accessToken";
		
		$result = $this->curlPost($url,$data);
		
		if($result['status']){
			if ($result['msg'] -> errcode == 0) {
				return array('status' => true, 'info' => $result['msg']);
			} else {
				$index = $result['msg'] -> errcode;
				return array('status' => false, 'info' => $this -> errcode[$index]);
			}
		}else{
			return array('status' => false, 'info' => $result['msg']);
		}
		
	}
	
	/**
	 * 分组删除
	 * @param $group_id 
	 */
	public function groupDel($group_id){
		$accessToken = $this->getAccessToken();
		$url = "https://api.weixin.qq.com/merchant/group/del?access_token=$accessToken";
		$data = json_encode(array('group_id'=>$group_id));
		$result = $this->curlPost($url,$data);
		
		if($result['status']){
			if ($result['msg'] -> errcode == 0) {
				return array('status' => true, 'info' => $result['msg']);
			} else {
				$index = $result['msg'] -> errcode;
				return array('status' => false, 'info' => $this -> errcode[$index]);
			}
		}else{
			return array('status' => false, 'info' => $result['msg']);
		}
	}
	
	/**
	 * 分组增加
	 * @param $group_name 分组名称
	 * @param $product_list 商品ID数组
	 * @return array 
	 */
	public function groupAdd($group_name,$product_list){
		$accessToken = $this->getAccessToken();
		$data = json_encode(array('group_detail'=>array('group_name'=>$group_name,'product_list'=>$product_list)),JSON_UNESCAPED_UNICODE);
		$url = "https://api.weixin.qq.com/merchant/group/add?access_token=$accessToken";
		$result = $this->curlPost($url,$data);
		
		if($result['status']){
			if ($result['msg'] -> errcode == 0) {
				return array('status' => true, 'info' => $result['msg']->group_id);
			} else {
				$index = $result['msg'] -> errcode;
				return array('status' => false, 'info' => $this -> errcode[$index]);
			}
		}else{
			return array('status' => false, 'info' => $result['msg']);
		}
		
	}
	
	/**
	 * 根据分组ID获取分组信息
	 * @return {
	"errcode": 0,
	"errmsg": "success",
	"group_detail": {
		"group_id": 200077549,
		"group_name": "最新上架",
		"product_list": [
		  "pDF3iYzZoY-Budrzt8O6IxrwIJAA",
		  "pDF3iY3pnWSGJcO2MpS2Nxy3HWx8",
		  "pDF3iY33jNt0Dj3M3UqiGlUxGrio"
		]
	}
}
	 */
	public function groupGetByID($groupid){
		$accessToken = $this->getAccessToken();
		$data = json_encode(array('group_id'=>$groupid));
		$url = "https://api.weixin.qq.com/merchant/group/getbyid?access_token=$accessToken";
		$result = $this->curlPost($url,$data);
//		dump($result);
		if($result['status']){
			if ($result['msg'] -> errcode == 0) {
				return array('status' => true, 'info' => $result['msg']->group_detail);
			} else {
				$index = $result['msg'] -> errcode;
				return array('status' => false, 'info' => $this -> errcode[$index]);
			}
		}else{
			return array('status' => false, 'info' => $result['msg']);
		}
	}
	/**
	 * 获取所有的分组信息
	 * @return array(2) {
				  ["status"] => bool(true)
				  ["info"] => array(1) {
				    [0] => array(3) {
				      ["group_id"] => int(200781536)
				      ["group_name"] => string(4) "Test"
				      ["product_list"] => array(0) {
	 * 						
				      }
				    }
				  }
				}
	 * 
	 */
	public function groupGetAll(){
		$accessToken = $this->getAccessToken();
		$url = "https://api.weixin.qq.com/merchant/group/getall?access_token=$accessToken";
		$result = $this->curlGet($url);
		$result = json_decode($result,JSON_UNESCAPED_UNICODE);
		if(isset($result['errcode'])){
			if($result['errcode'] == 0){
				return array('status'=>true,'info'=>$result['groups_detail']);
			}else{
				$index = $result['errcode'];
				return array('status' => false, 'info' => $this -> errcode[$index]);
			}
		}else{
			return array('status'=>false,'info'=>'');
		}
	}
	
	
	//==================商品管理
	
	
	
	/**
	 * 获取商品SKU
	 */
	public function getSKU($cate_id=1){
		$accessToken = $this->getAccessToken();
		$data = json_encode(array('cate_id'=>intval($cate_id)));
		$url = "https://api.weixin.qq.com/merchant/category/getsku?access_token=$accessToken";
		$result = $this->curlPost($url,$data);
//		dump($data);
		if($result['status']){
			if ($result['msg'] -> errcode == 0) {
				return array('status' => true, 'info' => $result['msg']->sku_table);
			} else {
				$index = $result['msg'] -> errcode;
				return array('status' => false, 'info' => $this -> errcode[$index]);
			}
		}else{
			return array('status' => false, 'info' => $result['msg']);
		}
		
	}
	
	/**
	 * 获取分类信息
	 * @param $cate_id 分类ID
	 */
	public function category($cate_id=1){
		$accessToken = $this->getAccessToken();
		$data = json_encode(array('cate_id'=>$cate_id));
		$url = "https://api.weixin.qq.com/merchant/category/getsub?access_token=$accessToken";
		$result = $this->curlPost($url,$data);
		if($result['status']){
			if ($result['msg'] -> errcode == 0) {
				return array('status' => true, 'info' => $result['msg']->cate_list);
			} else {
				$index = $result['msg'] -> errcode;

				return array('status' => false, 'info' => $this -> errcode[$index]);
			}
		}else{
			return array('status' => false, 'info' => $result['msg']);
		}
		
	}
	
	//==================功能接口－ 上传图片
	/**
	 * 功能接口－ 上传图片
	 * @param $filename 上传的文件名
	 */
	public function uploadImg($filename,$relativefilename){
		$accessToken = $this->getAccessToken();
		$imgdata = file_get_contents(realpath($relativefilename));
		$url = "https://api.weixin.qq.com/merchant/common/upload_img?access_token=$accessToken&filename=$filename";
		$result = $this->curlPost($url,$imgdata);
		if($result['status']){
			if ($result['msg'] -> errcode == 0) {
				return array('status' => true, 'info' => $result['msg']->image_url);
			} else {
				$index = $result['msg'] -> errcode;

				return array('status' => false, 'info' => $this -> errcode[$index]);
			}
		}else{
			return array('status' => false, 'info' => $result['msg']);
		}
		
	}
	
}