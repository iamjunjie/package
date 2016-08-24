<?php
/**
 * 微信服务器通信的API
 *
 * @author wangjunjie <1252547929@qq.com>
 * @date 2015-03-07
 */
class WeiXinApi {

	//应用ID
	private $appID;

	//应用密钥
	private $appSecret;

	//公众号的全局唯一票据
	private $accessToken;

	//微信公众平台借口url
	private $urls = array(
		//公众号的全局唯一票据
		'get_access_token' => 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s',
		//微信服务器IP地址
		'get_server_ip'	   => 'https://api.weixin.qq.com/cgi-bin/getcallbackip?access_token=%s',
		//上传多媒体文件
		'upload_media_file' => 'http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token=%s&type=%s',
		//下载多媒体文件
		'get_media_file' => 'http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=%s&media_id=%s',
		//查询所有分组
		'get_group' => 'https://api.weixin.qq.com/cgi-bin/groups/get?access_token=%s',
		//创建分组
		'create_group' => 'https://api.weixin.qq.com/cgi-bin/groups/create?access_token=%s',
		//修改分组
		'update_group' => 'https://api.weixin.qq.com/cgi-bin/groups/update?access_token=%s',
		//查询用户所在分组
		'get_user_group_id' => 'https://api.weixin.qq.com/cgi-bin/groups/getid?access_token=%s',
		//移动用户分组
		'update_user_group_id' => 'https://api.weixin.qq.com/cgi-bin/groups/members/update?access_token=%s',
		//批量移动用户分组
		'update_users_group_id' => 'https://api.weixin.qq.com/cgi-bin/groups/members/batchupdate?access_token=%s',
		//设置 备注名
		'update_user_remark' => 'https://api.weixin.qq.com/cgi-bin/user/info/updateremark?access_token=%s',
		//获取用户基本信息
		'get_user_base_info' => 'https://api.weixin.qq.com/cgi-bin/user/info?access_token=%s&openid=%s&lang=%s',
		//获取用户列表
		'get_user' => 'https://api.weixin.qq.com/cgi-bin/user/get?access_token=%s&next_openid=%s',
		//获取菜单
		'get_menu' => 'https://api.weixin.qq.com/cgi-bin/menu/get?access_token=%s',
		//创建菜单
		'create_menu' => 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=%s',
		//删除菜单
		'del_menu' => 'https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=%s',
		//获取短链接
		'get_short_url' => 'https://api.weixin.qq.com/cgi-bin/shorturl?access_token=%s',
		//获取用户增减数据
		'get_user_summary' => 'https://api.weixin.qq.com/datacube/getusersummary?access_token=%s',
		//获取累计用户数据
		'get_user_cumulate' => 'https://api.weixin.qq.com/datacube/getusercumulate?access_token=%s',
		//获取图文群发每日数据
		'get_article_summary' => 'https://api.weixin.qq.com/datacube/getarticlesummary?access_token=%s',
		//获取图文群发总数据
		'get_article_total' => 'https://api.weixin.qq.com/datacube/getarticletotal?access_token=%s',
		//获取图文统计数据
		'get_user_read' => 'https://api.weixin.qq.com/datacube/getuserread?access_token=%s',
		//获取图文统计分时数据
		'get_user_read_hour' => 'https://api.weixin.qq.com/datacube/getuserreadhour?access_token=%s',
		//获取图文分享转发数据
		'get_user_share' => 'https://api.weixin.qq.com/datacube/getusershare?access_token=%s',
		//获取图文分享转发分时数据
		'get_user_share_hour' => 'https://api.weixin.qq.com/datacube/getusersharehour?access_token=%s',
		//获取jsapi_ticket
		'get_jsapi_ticket' => 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=%s&type=jsapi',
		'' => '',
	);

	/**
	 * 初始化
	 * 
	 * @param string $appID     应用ID
	 * @param string $appSecret 应用密钥
	 */
	public function __construct($appID, $appSecret){
		$this->appID = $appID;
		$this->appSecret = $appSecret;
		$this->accessToken = $this->getAccessToken();
	}

	/**
	 * 获取公众号的全局唯一票据
	 * 
	 * @return string
	 */
	public function getAccessToken(){
		$url  = sprintf($this->urls['get_access_token'], $this->appID, $this->appSecret);
		$file = dirname(__FILE__) . DIRECTORY_SEPARATOR . "{$this->appID}_{$this->appSecret}_weixin_access_token.json";
		if(file_exists($file)){
			$content = file_get_contents($file);
			$content = $this->jsonDecode($content);
			if(time() < $content['expires_in']){
				return $content['access_token'];
			}
		}
		$result = $this->curl($url);
		$result = $this->jsonDecode($result);
		$result['expires_in'] = $result['expires_in'] + time();
		file_put_contents($file, json_encode($result));
		return (isset($result['access_token']) ? $result['access_token'] : null);
	}

	/**
	 * 获取公众号的全局唯一jsapi_ticket
	 * 
	 * @return string
	 */
	public function getJsApiTicket(){
		$url  = sprintf($this->urls['get_jsapi_ticket'], $this->accessToken);
		$file = dirname(__FILE__) . DIRECTORY_SEPARATOR . "{$this->appID}_weixin_jsapi_ticket.json";
		if(file_exists($file)){
			$content = file_get_contents($file);
			$content = $this->jsonDecode($content);
			if(time() < $content['expires_in']){
				return $content['ticket'];
			}
		}
		$result = $this->curl($url);
		$result = $this->jsonDecode($result);
		$result['expires_in'] = $result['expires_in'] + time();
		file_put_contents($file, json_encode($result));
		return (isset($result['ticket']) ? $result['ticket'] : null);
	}

	/**
	 * 获取微信服务器IP地址
	 * 
	 * @return array
	 */
	public function getServerIP(){
		$url = sprintf($this->urls['get_server_ip'], $this->accessToken);
		$result = $this->curl($url);
		$result = $this->jsonDecode($result);
		return (isset($result['ip_list']) ? $result['ip_list'] : null);
	}

	/**
	 * 上传媒体文件
	 * 
	 * 1、路径前加上@
	 * 2、路径必须是绝对路径
	 * 3、请注意，每个多媒体文件(media_id)会在上传、用户发送到微信服务器3天后自动删除，以节省服务器资源
	 * 
	 * @param  string $type     上传文件类型(image，voice，video，thumb)
	 * @param  string $filePath 文件路径
	 * @return string
	 */
	public function uploadMediaFile($type, $filePath){
		$url = sprintf($this->urls['upload_media_file'], $this->accessToken, $type);
		$result = $this->curl($url, array('media' => "@{$filePath}"));
		$result = $this->jsonDecode($result);
		return (isset($result['media_id']) ? $result['media_id'] : null);
	}

	/**
	 * 获取多媒体文件
	 * 
	 * @param  [type] $mediaID 媒体文件ID
	 * @return [type]          [description]
	 */
	public function getMediaFile($mediaID){
		$url = sprintf($this->urls['get_media_file'], $this->accessToken, $mediaID);
		return $this->curl($url);
	}

	/**
	 * 获取所有分组
	 * 
	 * @return array
	 */
	public function getGroup(){
		$url = sprintf($this->urls['get_group'], $this->accessToken);
		$result = $this->curl($url);
		$result = $this->jsonDecode($result);
		return (isset($result['groups']) ? $result['groups'] : null);
	}

	/**
	 * 创建分组
	 * 
	 * @param  string $name 名字
	 * @return int
	 */
	public function createGroup($name){
		$url = sprintf($this->urls['create_group'], $this->accessToken);
		$param = '{"group":{"name":"' . $name . '"}}';
		$result = $this->curl($url, $param);
		$result = $this->jsonDecode($result);
		return (isset($result['group']['id']) ? $result['group']['id'] : null);
	}

	/**
	 * 修改分组
	 * 
	 * @param  int    $id   ID
	 * @param  string $name 名称
	 * @return mixed
	 */
	public function updateGroup($id, $name){
		$url = sprintf($this->urls['update_group'], $this->accessToken);
		$param = '{"group":{"id":' . $id . ',"name":"' . $name . '"}}';
		$result = $this->curl($url, $param);
		$result = $this->jsonDecode($result);
		return ((isset($result['errmsg']) && ($result['errmsg']=='ok')) ? true : $result);
	}

	/**
	 * 通过用户的OpenID查询其所在的GroupID
	 * 
	 * @param  string $openID openID
	 * @return int
	 */
	public function getUserGroupID($openID){
		$url = sprintf($this->urls['get_user_group_id'], $this->accessToken);
		$param = '{"openid":"' . $openID . '"}';
		$result = $this->curl($url, $param);
		$result = $this->jsonDecode($result);
		return (isset($result['groupid']) ? $result['groupid'] : null);
	}

	/**
	 * 移动用户分组
	 * 
	 * @param  string $openID  openID
	 * @param  int    $groupID 分组ID
	 * @return mixed
	 */
	public function updateUserGroupID($openID, $groupID){
		$url = sprintf($this->urls['update_user_group_id'], $this->accessToken);
		$param = '{"openid":"' . $openID . '","to_groupid":' . $groupID . '}';
		$result = $this->curl($url, $param);
		$result = $this->jsonDecode($result);
		return ((isset($result['errmsg']) && ($result['errmsg']=='ok')) ? true : $result);
	}

	/**
	 * 批量移动用户分组
	 * 
	 * @param  array $openIDs openID数组
	 * @param  int   $groupID 分组ID
	 * @return mixed
	 */
	public function updateUsersGroupID($openIDs, $groupID){
		$url = sprintf($this->urls['update_users_group_id'], $this->accessToken);
		$openIDString = implode('","', $openIDs);
		$param = '{"openid_list":["' . $openIDString . '"],"to_groupid":' . $groupID . '}';
		$result = $this->curl($url, $param);
		$result = $this->jsonDecode($result);
		return ((isset($result['errmsg']) && ($result['errmsg']=='ok')) ? true : $result);
	}

	/**
	 * 设置 备注名
	 * 
	 * @param  string $openID openID
	 * @param  string $remark 备注名
	 * @return mixed
	 */
	public function updateUserRemark($openID, $remark){
		$url = sprintf($this->urls['update_user_remark'], $this->accessToken);
		$param = '{"openid":"' . $openID . '","remark":"' . $remark . '"}';
		$result = $this->curl($url, $param);
		$result = $this->jsonDecode($result);
		return ((isset($result['errmsg']) && ($result['errmsg']=='ok')) ? true : $result);
	}

	/**
	 * 获取用户基本信息
	 * 
	 * @param  string $openID openID
	 * @param  string $lang   语言版本(zh_CN，zh_TW，en)
	 * @return array
	 */
	public function getUserBaseInfo($openID, $lang = 'zh_CN'){
		$url = sprintf($this->urls['get_user_base_info'], $this->accessToken, $openID, $lang);
		$result = $this->curl($url);
		$result = $this->jsonDecode($result);
		return $result;
	}

	/**
	 * 获取用户列表
	 * 
	 * @param  string $nextOpenID 第一个拉取的OPENID，不填默认从头开始拉取
	 * @return array
	 */
	public function getUser($nextOpenID = ''){
		$url = sprintf($this->urls['get_user'], $this->accessToken, $nextOpenID);
		$result = $this->curl($url);
		$result = $this->jsonDecode($result);
		static $count = 0;
		static $users = array();
		if(isset($result['count'])){
			$count += $result['count'];
		}
		if(isset($result['data']['openid']) && !empty($result['data']['openid'])){
			foreach ($result['data']['openid'] as $key => $value) {
				$users[] = $this->getUserBaseInfo($value);
			}
		}
		if(isset($result['next_openid']) && !empty($result['next_openid'])){
			$this->getUser($result['next_openid']);
		}
		return array('total' => $count, 'users' => $users);
	}

	/**
	 * 获取菜单
	 * 
	 * @return array
	 */
	public function getMenu(){
		$url = sprintf($this->urls['get_menu'], $this->accessToken);
		$result = $this->curl($url);
		$result = $this->jsonDecode($result);
		return (isset($result['menu']['button']) ? $result['menu']['button'] : null);
	}

	/**
	 * 创建菜单
	 * 
	 * 1、最多包括3个一级菜单，每个菜单最多4个汉字
	 * 2、每个一级菜单最多包含5个二级菜单，二级菜单最多7个汉字，多出来的部分将会以“...”代替
	 * 3、请注意，创建自定义菜单后，由于微信客户端缓存，需要24小时微信客户端才会展现出来
	 *
	 * $menu = array(
	 *	 array(
	 *  	'type' => 'click', 
	 *  	'name' => '今日歌曲', 
	 *  	'key'  => 'V1001_TODAY_MUSIC',
	 *   ),
	 *   array(
	 *  	'name' => '子菜单', 
	 *  	'sub_button' => array(
	 *  		array('type' => 'view',             'name' => '我要点歌啊',   'url' => 'http://www.diange.com'),
	 *  		array('type' => 'scancode_waitmsg', 'name' => '扫码带提示',   'key' => 'rselfmenu_0_0'),
	 *  		array('type' => 'pic_sysphoto',     'name' => '系统拍照发图', 'key' => 'rselfmenu_1_0'),
	 *  		array('type' => 'location_select',  'name' => '发送位置',     'key' => 'rselfmenu_2_0'),
	 *  	),
	 *   ),
	 * );
	 * 
	 * @param  array $menu 菜单
	 * @return mixed
	 */
	public function createMenu($menu){
		$url = sprintf($this->urls['create_menu'], $this->accessToken);
		foreach ($menu as $menuKey => $menuValue) {
			//处理一级菜单数据
			foreach ($menuValue as $oneMenuKey => $oneMenuValue) {
				if(empty($oneMenuValue)){
					continue;
				}
				if($oneMenuKey != 'sub_button'){
					$menu[$menuKey][$oneMenuKey] = urlencode($oneMenuValue);
					continue;
				}
				//处理二级菜单
				foreach ($oneMenuValue as $subMenuKey => $subMenuValue) {
					foreach ($subMenuValue as $twoMenuKey => $twoMenuValue) {
						$menu[$menuKey][$oneMenuKey][$subMenuKey][$twoMenuKey] = urlencode($twoMenuValue);
					}
				}
			}
		}
		$param = array('button' => $menu);
		$param = urldecode(json_encode($param));
		$result = $this->curl($url, $param);
		$result = $this->jsonDecode($result);
		return ((isset($result['errmsg']) && ($result['errmsg']=='ok')) ? true : $result);
	}

	/**
	 * 删除菜单
	 * 
	 * @return mixed
	 */
	public function delMenu(){
		$url = sprintf($this->urls['del_menu'], $this->accessToken);
		$result = $this->curl($url);
		$result = $this->jsonDecode($result);
		return ((isset($result['errmsg']) && ($result['errmsg']=='ok')) ? true : $result);
	}

	/**
	 * 获取短链接
	 * 
	 * @param  string $longUrl 长链接
	 * @return string
	 */
	public function getShortUrl($longUrl){
		$url = sprintf($this->urls['get_short_url'], $this->accessToken);
		$param = '{"action":"long2short","long_url":"' . $longUrl . '"}';
		$result = $this->curl($url, $param);
		$result = $this->jsonDecode($result);
		return (isset($result['short_url']) ? $result['short_url'] : null);
	}

	/**
	 * 获取用户增减数据
	 *
	 * 1、最大时间跨度是7天
	 * 2、最大时间跨度是指一次接口调用时最大可获取数据的时间范围
	 * 3、比如：2015-03-03至2015-03-09
	 * 
	 * @param  string $startTime 开始时间，年-月-日
	 * @param  string $endTime   结束时间，年-月-日
	 * @return mixed
	 */
	public function getUserSummary($startTime, $endTime){
		$url = sprintf($this->urls['get_user_summary'], $this->accessToken);
		$param = '{"begin_date": "' . $startTime . '","end_date": "' . $endTime . '"}';
		$result = $this->curl($url, $param);
		$result = $this->jsonDecode($result);
		return (isset($result['list']) ? $result['list'] : null);
	}

	/**
	 * 获取累计用户数据
	 *
	 * 1、最大时间跨度是7天
	 * 2、最大时间跨度是指一次接口调用时最大可获取数据的时间范围
	 * 3、比如：2015-03-03至2015-03-09
	 * 
	 * @param  string $startTime 开始时间，年-月-日
	 * @param  string $endTime   结束时间，年-月-日
	 * @return mixed
	 */
	public function getUserCumulate($startTime, $endTime){
		$url = sprintf($this->urls['get_user_cumulate'], $this->accessToken);
		$param = '{"begin_date": "' . $startTime . '","end_date": "' . $endTime . '"}';
		$result = $this->curl($url, $param);
		$result = $this->jsonDecode($result);
		return (isset($result['list']) ? $result['list'] : null);
	}

	/**
	 * 获取图文群发每日数据
	 *
	 * 1、最大时间跨度是1天
	 * 2、最大时间跨度是指一次接口调用时最大可获取数据的时间范围
	 * 3、比如：2015-03-09至2015-03-09
	 * 
	 * @param  string $startTime 开始时间，年-月-日
	 * @param  string $endTime   结束时间，年-月-日
	 * @return mixed
	 */
	public function getArticleSummary($startTime, $endTime){
		$url = sprintf($this->urls['get_article_summary'], $this->accessToken);
		$param = '{"begin_date": "' . $startTime . '","end_date": "' . $endTime . '"}';
		$result = $this->curl($url, $param);
		$result = $this->jsonDecode($result);
		return (isset($result['list']) ? $result['list'] : null);
	}

	/**
	 * 获取图文群发总数据
	 *
	 * 1、最大时间跨度是1天
	 * 2、最大时间跨度是指一次接口调用时最大可获取数据的时间范围
	 * 3、比如：2015-03-09至2015-03-09
	 * 
	 * @param  string $startTime 开始时间，年-月-日
	 * @param  string $endTime   结束时间，年-月-日
	 * @return mixed
	 */
	public function getArticleTotal($startTime, $endTime){
		$url = sprintf($this->urls['get_article_total'], $this->accessToken);
		$param = '{"begin_date": "' . $startTime . '","end_date": "' . $endTime . '"}';
		$result = $this->curl($url, $param);
		$result = $this->jsonDecode($result);
		return (isset($result['list']) ? $result['list'] : null);
	}

	/**
	 * 获取图文统计数据
	 *
	 * 1、最大时间跨度是3天
	 * 2、最大时间跨度是指一次接口调用时最大可获取数据的时间范围
	 * 3、比如：2015-03-06至2015-03-09
	 * 
	 * @param  string $startTime 开始时间，年-月-日
	 * @param  string $endTime   结束时间，年-月-日
	 * @return mixed
	 */
	public function getUserRead($startTime, $endTime){
		$url = sprintf($this->urls['get_user_read'], $this->accessToken);
		$param = '{"begin_date": "' . $startTime . '","end_date": "' . $endTime . '"}';
		$result = $this->curl($url, $param);
		$result = $this->jsonDecode($result);
		return (isset($result['list']) ? $result['list'] : null);
	}

	/**
	 * 获取图文统计分时数据
	 *
	 * 1、最大时间跨度是1天
	 * 2、最大时间跨度是指一次接口调用时最大可获取数据的时间范围
	 * 3、比如：2015-03-09至2015-03-09
	 * 
	 * @param  string $startTime 开始时间，年-月-日
	 * @param  string $endTime   结束时间，年-月-日
	 * @return mixed
	 */
	public function getUserReadHour($startTime, $endTime){
		$url = sprintf($this->urls['get_user_read_hour'], $this->accessToken);
		$param = '{"begin_date": "' . $startTime . '","end_date": "' . $endTime . '"}';
		$result = $this->curl($url, $param);
		$result = $this->jsonDecode($result);
		return (isset($result['list']) ? $result['list'] : null);
	}

	/**
	 * 获取图文分享转发数据
	 *
	 * 1、最大时间跨度是7天
	 * 2、最大时间跨度是指一次接口调用时最大可获取数据的时间范围
	 * 3、比如：2015-03-03至2015-03-09
	 * 
	 * @param  string $startTime 开始时间，年-月-日
	 * @param  string $endTime   结束时间，年-月-日
	 * @return mixed
	 */
	public function getUserShare($startTime, $endTime){
		$url = sprintf($this->urls['get_user_share'], $this->accessToken);
		$param = '{"begin_date": "' . $startTime . '","end_date": "' . $endTime . '"}';
		$result = $this->curl($url, $param);
		$result = $this->jsonDecode($result);
		return (isset($result['list']) ? $result['list'] : null);
	}

	/**
	 * 获取图文分享转发分时数据
	 *
	 * 1、最大时间跨度是1天
	 * 2、最大时间跨度是指一次接口调用时最大可获取数据的时间范围
	 * 3、比如：2015-03-09至2015-03-09
	 * 
	 * @param  string $startTime 开始时间，年-月-日
	 * @param  string $endTime   结束时间，年-月-日
	 * @return mixed
	 */
	public function getUserShareHour($startTime, $endTime){
		$url = sprintf($this->urls['get_user_share_hour'], $this->accessToken);
		$param = '{"begin_date": "' . $startTime . '","end_date": "' . $endTime . '"}';
		$result = $this->curl($url, $param);
		$result = $this->jsonDecode($result);
		return (isset($result['list']) ? $result['list'] : null);
	}

	/**
	 * 把json字符串处理成数组
	 * 
	 * @param  string $data json字符串
	 * @return array
	 */
	public function jsonDecode($jsonString){
		$arr = array();
		if(!empty($jsonString)){
			$arr = json_decode($jsonString, true);
		}
		return $arr;
	}

	/**
	 * curl请求
	 * 
	 * @param  string $url  请求地址
	 * @param  array  $data 请求参数，可以不传
	 * @return mixed
	 */
	public function curl($url, $data = null){
		$ch = curl_init($url);
		//禁用后cURL将终止从服务端进行验证
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		//将获取的信息以文件流的形式返回，而不是直接输出
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		//启用时会将头文件的信息作为数据流输出
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		//设置post请求
		if(!empty($data)){
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}
		//执行请求
		$result = curl_exec($ch);
		//关闭
		curl_close($ch);
		return $result;
	}
}