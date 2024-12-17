<?php
/*!
 *  func_global.php
 *  06/28/2012		James		Created
*/
 
//
//	get_guid([int num]) - 获取15位编号
//
//	num: 产生 num 个编号，并保证没有重复
//
//  Format: Date + Time + 3 random number
//  e.g. 081117 + 203020 + 023 = '081117203020023'
//

//
// 递归数组搜索 - 搜索值，返回键
//
function array_search_recursive( $needle, $haystack, $strict=false, $path=array() ) {
    if( !is_array($haystack) ) return FALSE;
    foreach( $haystack as $key => $val ) {
        if( is_array($val) && $subPath = array_search_recursive($needle, $val, $strict, $path) ) {
            $path = array_merge($path, array($key), $subPath);
            return $path;
        } elseif( (!$strict && $val == $needle) || ($strict && $val === $needle) ) {
            $path[] = $key;
            return $path;
        }
    }
    return FALSE;
}

//
// 递归数组搜索 - 搜索键，返回键
//
function array_search_key_recursive( $needle, $haystack, $strict=false, $path=array() ) {
    if( !is_array($haystack) ) return FALSE;
    foreach( $haystack as $key => $val ) {
        if( is_array($val) && $subPath = array_search_key_recursive($needle, $val, $strict, $path) ) {
            $path = array_merge($path, array($key), $subPath);
            return $path;
        } elseif( (!$strict && $key == $needle) || ($strict && $key === $needle) ) {
            $path[] = $key;
            return $path;
        }
    }
    return FALSE;
}

//
// 递归遍历数组 - 搜索键，返回值
//
function array_recursive( $key, $array, $strict=false ) {
    if( !is_array($array) ) return FALSE;
    if ( $array[$key] !== NULL ) return $array[$key];
	else foreach ($array as $sub_array) {
		if ( $ret = array_recursive($key, $sub_array, $strict) ) return $ret;
	}
}

//
// 格式化显示选题分类
//
function FormatBookTags( $idBookTag ) {
	global $_BOOKTAGS;
	if (0 == $idBookTag) return;
	$sBookTag = array_recursive( $idBookTag, $_BOOKTAGS );
	list($t1, $dummy, $t2, $dummy, $t3) = array_search_key_recursive( $idBookTag, $_BOOKTAGS );
	return sprintf('<a href="/copyrights/list?t1=%d&amp;t2=%d&amp;t3=%d">%s</a>', $t1, $t2, $t3, $sBookTag);
}

//
// 生成 body id
//
function MakeBodyId( $req_uri ) {
	$req_uri = trim($req_uri, '/');
	$req_uri = str_replace('/', '-', $req_uri);
	if ( $_GET['do'] != '' ) $req_uri .= '-' . $_GET['do'];
	return $req_uri;
}

//
// 生成唯一编号
//
function GetOnlyId() {
	// 生成多个编号，返回数组
	if ( func_num_args() ) {
		$num = func_get_arg(0);
		$count = 0;
		$aId = array();
		while ($count < $num) {
			$aId[] = date('ymdHis') . mt_rand(100,999);
			$aId = array_flip(array_flip($aId));
			$count = count($aId);
		}
		shuffle($aId); // 重新生成连续的键名
		return $aId;
	}
	// 生成单个编号，返回字符串
	else return date('ymdHis') . mt_rand(100,999);
}

//
// 出错
//
function DieEx($message, $error_code = '500') {
	die("<meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\" />\n<h1>系统错误</h1>\n<hr /><p>{$message}</p>");
}

//
// 重定向
//
function LocPage($sUrl = "")
{
	if ($sUrl != "")
	{
		die("<meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\" />
<meta http-equiv=\"refresh\" content=\"0;url={$sUrl}\" />");
	}
	else
	{
		die("<meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\" />
<script>setTimeout('document.location.href=\"javascript:history.go(-1)\"', 1)</script>");
	}
}

//
// 生成翻页链接
//
function MakePageIndex($nTotalRecords, $nCurrentPage, $nPageSize, $showInfo=true) {
	$sUrl = RemoveQueryParams($_SERVER['REQUEST_URI'], 'page');
	
	$nTotalPages = ceil($nTotalRecords / $nPageSize); // 总页数
	echo '<div id="page">';
        
        if($nTotalRecords > 0 && $showInfo == true) {
            $nTotalPages = ceil($nTotalRecords / $nPageSize); // 总页数
            echo "<span class='float-left'>共 {$nTotalRecords} 条&nbsp;&nbsp;{$nCurrentPage}/{$nTotalPages} 页</span>";
        }
	// 首页
	if ($nCurrentPage > 1)
		echo "<a href=\"{$sUrl}page=1\">« 首页</a>";
	
	// 上一页
	$nLastPage = $nCurrentPage - 1;
	if ($nLastPage > 0) echo "<a href=\"{$sUrl}page={$nLastPage}\">‹ 上一页</a>";

	// 中间页码
	$nListNum = 8;	// 页号个数
	$nPreList = 3;	// 当前页前的页号个数
	$nBeg = 1;
	$nEnd = $nBeg + $nListNum;
	if ($nEnd > $nTotalPages) $nEnd = $nTotalPages + 1;
	if ($nCurrentPage > $nPreList+1)
	{
		$nBeg = $nCurrentPage - $nPreList;
		$nEnd = $nBeg + $nListNum;
	}
	if ($nCurrentPage > $nTotalPages - $nListNum + $nPreList)
	{
		$nEnd = $nTotalPages + 1;
		$nBeg = $nEnd - $nListNum;
	}
	if ($nBeg < 1) $nBeg = 1;
	for ($i = $nBeg; $i < $nEnd; $i ++)
	{
		if ($i == $nCurrentPage) echo "<a class=\"selected\">{$nCurrentPage}</a>";
		else echo "<a href=\"{$sUrl}page={$i}\">{$i}</a>";
	}
	
	// 下一页
	$nNextPage = $nCurrentPage + 1;
	if ($nNextPage <= $nTotalPages) echo "<a href=\"{$sUrl}page={$nNextPage}\">下一页 ›</a>";
	
	// 末页
	if ($nCurrentPage < $nTotalPages) echo "<a href=\"{$sUrl}page={$nTotalPages}\">末页 »</a>";
	
	echo "</div>\n";
}

//
// 生成当前页信息
//
function MakePageInfo($nTotalRecords, $nCurrentPage, $nPageSize) {
	$nTotalPages = ceil($nTotalRecords / $nPageSize); // 总页数
	echo "共 {$nTotalRecords} 条&nbsp;&nbsp;{$nCurrentPage}/{$nTotalPages} 页";
}

//
// 删除 Query 参数
// string RemoveQueryParams( string $uri, string $param1 [, string $param2 [, string $... ]] )
//
function RemoveQueryParams($uri, $param1) {
	$func_num_args = func_num_args();
	$aParams = array();
	if ($func_num_args > 2) {
		for ($i=1; $i<$func_num_args; $i++) {
			$param = func_get_arg($i);
			if ($param) $aParams[] = $param;
		}
	} else {
		$aParams[] = func_get_arg(1);
	}
	$url = explode('?', $uri);
	$sUrl = $url[0] . '?';
	if ($url[1]) {
		$query = explode('&', $url[1]);
		foreach ($query as $key => $val) {
			$unset = FALSE;
			foreach ($aParams as $param) {
				if (empty($val) || strpos($val, "{$param}=") === 0) $unset = TRUE;
			}
			if ($unset) unset( $query[$key] );
		}
		$sUrl .= implode('&amp;', $query);
	}
	if ( count($query) > 0 ) $sUrl .= '&amp;';
	return $sUrl;
}

//
// 得到返回地址
//
function GetRequsetUrl()
{
	$pos = strpos($_SERVER['REQUEST_URI'], '?');
	if ($pos === false) return $_SERVER['REQUEST_URI'] . "?". $_SERVER['QUERY_STRING']; // 增加一个?号，避免直接跟随&号问题
	else return $_SERVER['REQUEST_URI'];
}

//
// MySQL 文本过滤
// * 需要事先连接 MySQL 数据库
//
function DbConform($input) {
	if ( !is_array($input) ) return mysql_real_escape_string($input);
	function DbConformArrayCallback(&$item, $key) { $item = DbConform($item); }
	array_walk($input, 'DbConformArrayCallback');
	return $input;
}

//
// 关键字高亮
// $string - 待处理文本
// $keyword - 关键字
//
function HighlightKeyword($string, $keyword) {
	$keywords = explode(' ', $keyword);
	foreach ($keywords as $word) {
		$string = str_replace($word, "<span class=\"HL\">{$word}</span>", $string);
	}
	return $string;
}

//
// 文本裁剪（varchar）
// length - 汉字数
//
function ClipVarchar($string, $maxlen = _DB_MAX_VARCHAR) {
	if (!$maxlen) $maxlen = 80;
	$string = trim($string);
	$string = preg_replace('/\n|\r/', '', $string);
	$strlen = GetStrLen($string);
	if ($strlen > $maxlen) $string = GetSubStr($string, 0, $maxlen) . '...';
	return $string;
}

//
// 文本裁剪（text）
// length - 汉字数
//
function ClipText($string, $maxlen = _DB_MAX_TEXT) {
	if (!$maxlen) $maxlen = 20000;
	$string = trim($string);
	$strlen = GetStrLen($string);
	if ($strlen > $maxlen) $string = GetSubStr($string, 0, $maxlen) . '...';
	return $string;
}

//
// 获取文本长度
//
function GetStrLen($string) {
	if ( function_exists('mb_strlen') ) {
		return mb_strlen($string);
	} else {
		return round(strlen($string) / 3);
	}
}

//
// 获取子字符串
//
function GetSubStr($string, $start, $length) {
	if ( function_exists('mb_substr') ) return mb_substr($string, $start, $length);
	$length = $length * 3;
	$strlen = $start + $length;

	$output = '';
	for ($i = 0; $i < $strlen; $i++) {
		if (ord(substr($string, $i, 1)) > 0xa0) {
			$output .= substr($string, $i, 3);
			$i += 2;
		} else {
			$output .= substr($string, $i, 1);
			$strlen -= 1;
		}
	}
	return  $output;
}

//
// 格式化日期字符串
//
function FormatDateTime($sDate, $sFlag = "YMD") {
	$sDate = str_replace("-", "", $sDate);
	$ret = $sDate;
	if (mb_strlen($sDate) >= 4)
	{
		if ($sFlag == "YMD")
			$ret = substr($sDate, 0, 4)."年".substr($sDate, 4, 2)."月".substr($sDate, 6, 2)."日";
		elseif ($sFlag == "ymd")
			$ret = substr($sDate, 0, 4).".".substr($sDate, 4, 2).".".substr($sDate, 6, 2);
		elseif ($sFlag == "YM")
			$ret = substr($sDate, 0, 4)."年".substr($sDate, 4, 2)."月";
		elseif ($sFlag == "ym")
			$ret = substr($sDate, 0, 4).".".substr($sDate, 4, 2);
		elseif ($sFlag == "MD")
			$ret = substr($sDate, 4, 2)."月".substr($sDate, 6, 2)."日";
		elseif ($sFlag == "md")
			$ret = substr($sDate, 4, 2).".".substr($sDate, 6, 2);
		elseif ($sFlag == "Y")
			$ret = substr($sDate, 0, 4)."年";
		elseif ($sFlag == "y")
			$ret = substr($sDate, 0, 4);
		elseif ($sFlag == "YMDH")
			$ret = substr($sDate, 0, 4)."年".substr($sDate, 4, 2)."月".substr($sDate, 6, 2)."日".substr($sDate, 9, 2)."时";
	}
	return $ret;
}

//
// 打印星级
//
function MakeStars($nStar) {
	if ($nStar == 0) {
		$star = '<span class="muted">（未评定）</span>';
	} else {
		$width = 8 * $nStar; // a star is 16px wide, 8px is a half star
		$star = sprintf('<span class="star" style="width:%dpx"></span>', $width);
	}
	return $star;
}

// 
// 样书消费
// sum(int)		消费金额
// bid			样书ID
//
function BookExpense($sum,$bid,$nStyle=6) {
	$ret = array();
	if($sum < 0){
		$ret['mid'] = 0;
		$ret['result'] = 0;
		$ret['msg'] = '请输入正确金额';
		return $ret;
	}
        
	$d_conn = new DB();
	$d_ret = $d_conn->query("SELECT `money` FROM rci_user WHERE sId='{$_SESSION['RCI']['USER']['UID']}'");
	$row = $d_ret->fetch_assoc();
	if($row['money'] >=  intval($sum)){
		$u_ret = $d_conn->query("UPDATE `rci_user` SET `money`=money-'{$sum}' WHERE (sId='{$_SESSION['RCI']['USER']['UID']}')");
                $meoeySid = 'CP'.GetOnlyId();
		if($u_ret){
			$time = time();
			$sId =$_SESSION['RCI']['USER']['UID'];
                        //nStyle 6 申请作品扣费   nType 2 扣费 nStatus 1 成功  content 记录当前操作人uid
			$m_ret = $d_conn->query("INSERT INTO `roa_money_log` (`id`,`sId`,`uid`, `nStyle`, `nType`, `money`, `nStatus`, `sOperator`, `content`, `dtInsert`) VALUES ('','$meoeySid','$sId', '$nStyle', '2', '$sum', '1', '$sId', '$bid', NOW())");
			if($m_ret){
				$ret['mid'] = $d_conn->d_conn->insert_id;
				$ret['result'] = 1;
				$ret['msg'] = '扣费成功';
			}
			return $ret;

		}else{
	 		$ret['mid'] = 0;
			$ret['result'] = 0;
			$ret['msg'] = '系统错误，请稍后再试';
			
			$time = time();
			$sId =$_SESSION['RCI']['USER']['UID'];
                        //nStyle 6 申请作品扣费   nType 2 扣费 nStatus 1 成功  content 记录当前操作人uid
			$m_ret = $d_conn->query("INSERT INTO `roa_money_log` (`id`,`sId`,`uid`, `nStyle`, `nType`, `money`, `nStatus`, `operator`, `content`, `dtInsert`) VALUES ('','$meoeySid','$sId', '$nStyle', '2', '$sum', '0', '$sId', '$bid', NOW())");
	
			return $ret;
		}
	
	}else{
		$ret['mid'] = 0;
		$ret['result'] = 0;
		$ret['msg'] = '余额不足,请充值';
		return $ret;
	}

}
/**
 * 小数格式化
 * @param type $amount
 * @param type $decimal_length
 * @return type
 */
function DecimalAmountDisplay($amount, $decimal_length=2) {
    if($amount == 0.00) {
        $amount = 0;
    }
    else{
        $amount = number_format($amount, $decimal_length);
    }
    return $amount;
}

function selectFunction($name='',$option=array(), $default_value='', $onchange='')
{

    if(empty($option))
        return false;

    $this_type[$default_value] = ' selected';
    
    $select_html = '';
    $select_html .= '<select name="'.$name.'" id="'.$name.'" onchange="'.$onchange.'">';
    foreach($option as $key=>$val) {
        $select_html .= '<option value="'.$key.'" '.$this_type[$key].'>'.$val.'</option>';
    }
    $select_html .= '</select>';
    
    return $select_html;
}

function checkboxFunction($name='',$option=array(), $default_value=array())
{
    if(empty($option))
        return false;
    $checkbox_html = '';
    foreach($option as $key=>$val) {
        if(in_array($key, $default_value)) {
            $this_type[$key] = ' checked';
        }
        $checkbox_html .= '<div class="inline-block"><input type="checkbox" id="'.$name.$key.'" name="'.$name.'[]" '.$this_type[$key].'  value="'.$key.'" /><label for="'.$name.$key.'">'.$val.'</label></div>';
    }

    
    return $checkbox_html;
}

function radioFunction($name='',$option=array(), $default_value='')
{
    if(empty($option))
        return false;
    $checkbox_html = '';
    foreach($option as $key=>$val) {
        if($key == $default_value) {
            $this_type[$key] = ' checked';
        }
        $checkbox_html .= '<input type="radio" id="'.$name.$key.'" name="'.$name.'" '.$this_type[$key].'  value="'.$key.'" /><label for="'.$name.$key.'">'.$val.'</label>';
    }

    
    return $checkbox_html;
}

//
// 生成随机字串
//
function fnRndStr($length = 4, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ') {
	$nCharsLength = strlen($chars);
	while ($length--) {
		$ret .= $chars{ mt_rand(0, $nCharsLength-1) };
	}
	return $ret;
}

//加密解密函数
function encrypt($string,$operation,$key=''){ 
    $key=md5($key); 
    $key_length=strlen($key); 
      $string=$operation=='D'?base64_decode($string):substr(md5($string.$key),0,8).$string; 
    $string_length=strlen($string); 
    $rndkey=$box=array(); 
    $result=''; 
    for($i=0;$i<=255;$i++){ 
           $rndkey[$i]=ord($key[$i%$key_length]); 
        $box[$i]=$i; 
    } 
    for($j=$i=0;$i<256;$i++){ 
        $j=($j+$box[$i]+$rndkey[$i])%256; 
        $tmp=$box[$i]; 
        $box[$i]=$box[$j]; 
        $box[$j]=$tmp; 
    } 
    for($a=$j=$i=0;$i<$string_length;$i++){ 
        $a=($a+1)%256; 
        $j=($j+$box[$a])%256; 
        $tmp=$box[$a]; 
        $box[$a]=$box[$j]; 
        $box[$j]=$tmp; 
        $result.=chr(ord($string[$i])^($box[($box[$a]+$box[$j])%256])); 
    } 
    if($operation=='D'){ 
        if(substr($result,0,8)==substr(md5(substr($result,8).$key),0,8)){ 
            return substr($result,8); 
        }else{ 
            return''; 
        } 
    }else{ 
        return str_replace('=','',base64_encode($result)); 
    } 
}

/**
 * 未认证用户使用功能被限制时显示提示内容
 */
function fnNoAuthenticationUserTips($source='') {
    global $USERINFO_DB_ACCOUNT_UNAUTHORIZED_SUBMITTED;
    echo '<style>.'.$source.'{color: #A00;font-weight: bold;}</style>';
    echo '<div class="no_authentication_user_tips">
            <h3 style="margin:0;" class="CN FL YH">提示</h3>
            <p>
               您尚未完成本网站的实名认证！<br />目前您只能受限制的享受本网站的部分作品浏览和功能，
               <br />若需更多作品信息和服务，请<span class="FW CN">实名认证</span>'.
            ($USERINFO_DB_ACCOUNT_UNAUTHORIZED_SUBMITTED == true ? '<span class="CM">(申请审核中)</span>' : '：').'
            </p>
           </div>';
}

//缓存判断条件函数
function cacheOperationalCondition($filenameCache){
    //读取缓存文件获取内容
    if(file_exists($filenameCache)) {
        $update_time = filemtime($filenameCache);
        $update_time = getdate($update_time);
        
        $now_time = getdate(time());
        
        if( ( $update_time['year'] === $now_time['year'] ) && ( $update_time['yday'] === $now_time['yday'] ) ) { 
            $tilesCache = true; //同一天
        } 
        else{
            $tilesCache = false; //不是同一天
        }
     }
     else {
        $tilesCache = false; //没有缓存文件
     }
    return $tilesCache;
}
//缓存读写函数
function cacheReadWrite($filenameCache, $content='') {
    //写缓存
    if(!empty($content)) {
        file_put_contents($filenameCache, $content); //写入到文件
    }
    //读缓存
    else {
        $content = file_get_contents($filenameCache);
    }
    return $content;
}

/** 
* 压缩html : 清除换行符,清除制表符,去掉注释标记 
* @param $string 
* @return 压缩后的$string 
* */ 
function compressHtml($string) { 
    $string = str_replace("\r\n", '', $string); //清除换行符 
    $string = str_replace("\n", '', $string); //清除换行符 
    $string = str_replace("\t", '', $string); //清除制表符 
    $pattern = array ( 
                    "/> *([^ ]*) *</", //去掉注释标记 
                    "/[\s]+/", 
                    "/<!--[^!]*-->/", 
                    "/\" /", 
                    "/ \"/", 
                    "'/\*[^*]*\*/'" 
                    ); 
    $replace = array ( 
                    ">\\1<", 
                    " ", 
                    "", 
                    "\"", 
                    "\"", 
                    "" 
                    ); 
    return preg_replace($pattern, $replace, $string); 
} 
/**
 * 检查当前访问系统平台
 * @return bool
 */
function fnIsMobile() {
    // 若是via信息含有wap则必定是移动设备,部分服务商会屏蔽该信息
    if (isset($_SERVER['HTTP_VIA']) && stristr($_SERVER['HTTP_VIA'], "wap")) {  //检查是否为wap代理
        return true;
    } 
    elseif (isset($_SERVER['HTTP_ACCEPT']) && strpos(strtoupper($_SERVER['HTTP_ACCEPT']), "VND.WAP.WML")) {   //检查浏览器是否接受WML
        return true;
    } 
    // 若是有HTTP_X_WAP_PROFILE则必定是移动设备
    elseif (isset($_SERVER['HTTP_X_WAP_PROFILE']) || isset($_SERVER['HTTP_PROFILE'])) {
        return true;
    } 
    //判断手机发送的客户端标志,兼容性有待提升。
    elseif (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/(blackberry|configuration\/cldc|hp |hp-|htc |htc_|htc-|iemobile|kindle|midp|mmp|motorola|mobile|nokia|opera mini|opera |Googlebot-Mobile|YahooSeeker\/M1A1-R2D2|android|iphone|ipad|ipod|mobi|palm|palmos|pocket|portalmmm|ppc;|smartphone|sonyericsson|sqh|spv|symbian|treo|up.browser|up.link|vodafone|windows ce|xda |xda_)/i', $_SERVER['HTTP_USER_AGENT'])) {
        return true;
    } 
    else {
        return false;
    }
}

//  判断是不是微信
function fnIsWeixin() {
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
        return true;
    } else {
        return false;
    }
}

//数据库中存储的多选值进行转换显示数据
function dbStrAndArr2Str($sDbStr, $aConstArr=array(), $sShowSeparator=',') {
    $sReturn = '';
    if($sDbStr && $aConstArr) {
        $asDbStr = explode(',', $sDbStr);
        $aDbStr = array();
        foreach ($asDbStr as $value) {
            $aDbStr[] = $aConstArr[$value];
        }
        $sReturn = join($sShowSeparator, $aDbStr);
    }
    return $sReturn;
}

//系统信息拼接函数
function fnAddChglog($name,$arr){
    $sChglog = "\"{$name}\":{";
    foreach($arr as $key => $val){
        $sChglog.= "\"{$key}\":\"{$val}\",";
    }
    //去掉最后一个‘,’
    $sChglog = substr($sChglog,0,strlen($sChglog)-1);
    $sChglog.="},";
    return $sChglog;
}

//显示代理情况   指定区域情况
function  getAgentArea($pid){
    global $_SESSION;
    $d_conn = new DB();
    $data=array();
    //登陆 -  用户所在地/全球    未登陆 - 中国大陆/全球
    $area=(isset($_SESSION['RCI']['USER']['LOCATION_AREA'])) ? $_SESSION['RCI']['USER']['LOCATION_AREA'].",all" : "58,all";
    $string = "'" . str_replace(",", "','", $area) . "'";
    $sql = "SELECT * FROM roa_pdm_bookx_egent WHERE belongSid='{$pid}' AND sAgentArea IN ({$string}) AND nIsDel=0  ";
    $d_ret = $d_conn->query($sql);
    while($row_ag =$d_ret->fetch_assoc()){
        $data[$row_ag['sAgentArea']]=$row_ag;
    }
    return $data;
}

//中文发布  根据发布方式显示作品封面
function   showCoverByPublishWay($way,$url,$url_cn,$url_alter,$type=0){
    $sUrlCover='';
    //按中文发布方式显示封面
    if($way==3){
        $sUrlCover= $url; //按原书封面发布
    }elseif($way==4){
        $sUrlCover= $url_cn;//按中文封面发布
    }else{
        $sUrlCover= $url_alter;//按封面暂无发布
    }

    //显示样式
    if($sUrlCover){
        if(substr($sUrlCover, 0, 8)=='/upload/'){
            $sUrlCover =$sUrlCover;
        }else{
            $sUrlCover ="/oa".substr(substr($sUrlCover, 1), 0, -4);
            // 1-原图片  2-标准图片  其他 -缩略图
            if($type==1)   $sUrlCover .=".jpg";
            elseif($type==2)    $sUrlCover .="_s.jpg";
            else  $sUrlCover .="_s_1.jpg"; 
        }
    }
    
    return $sUrlCover;
}

//展示关联作品
function showSeriesBooks($sProdIdSeries,$sProdId){
    global $_SESSION,$aEagentConst;

    $d_conn = new DB();
    $series=array();
    $s_whex=" WHERE A.nIsDel=0 AND A.nPublish=1 AND A.sProdIdSeries<>'' AND A.sProdIdSeries='{$sProdIdSeries}' AND A.sProdId<>'{$sProdId}' ";
    
    //用户访问限制设置
    $BOOKS_ACCESS_AUTHORITY = (isset($_SESSION['RCI']['AUTH']['BOOKS_ACCESS_AUTHORITY'])) ? $_SESSION['RCI']['AUTH']['BOOKS_ACCESS_AUTHORITY'] : '';
    //用户所在地区+全球
    $USER_ACCESS_AREA= (isset($_SESSION['RCI']['USER']['LOCATION_AREA'])) ? "'".$_SESSION['RCI']['USER']['LOCATION_AREA']."' , 'all' " : " 58,'all' ";
    if(!empty($BOOKS_ACCESS_AUTHORITY)) {
        $aAccessAuthority = explode(',', $BOOKS_ACCESS_AUTHORITY);
        if(!empty($aAccessAuthority) && !in_array('无限制', $aAccessAuthority)) {
            $string_aAccessAuthority=dbStrAndArr2Str($BOOKS_ACCESS_AUTHORITY, array_flip($aEagentConst));
            $string_aAccessAuthority = "'" . str_replace(",", "','", $string_aAccessAuthority) . "'";
            $s_whex .= " AND EXISTS (SELECT 1 FROM roa_pdm_bookx_egent E WHERE A.sProdId = E.belongSid AND E.sAgencies IN ({$string_aAccessAuthority}) AND E.sAgentArea IN ({$USER_ACCESS_AREA}) AND E.nIsDel=0) ";
        }
    }
    //屏蔽区域
    $nLocationArea = (isset($_SESSION['RCI']['USER']['LOCATION_AREA'])) ? $_SESSION['RCI']['USER']['LOCATION_AREA'] : 0;
    if($nLocationArea > 0) {
        $s_whex .= " AND (A.sSrejectionRegion NOT REGEXP '^{$nLocationArea}$' AND A.sSrejectionRegion NOT REGEXP '^{$nLocationArea},' AND A.sSrejectionRegion NOT REGEXP ',{$nLocationArea}$' AND A.sSrejectionRegion NOT REGEXP ',{$nLocationArea},') ";
    }
    //用户屏蔽企业设置
    $sUsrCorpType = $_SESSION['RCI']['USER']['TYPE'];
    $sUsrCorpId = $_SESSION['RCI']['USER']['CORPID'];
    if($sUsrCorpType && $sUsrCorpId) {
        $s_whex .= " AND NOT EXISTS (SELECT id FROM rci_user_limit_corp B WHERE B.sLimitCorpType=A.sClientType AND B.sLimitCorpId=A.belongCorpId AND B.sUsrCorpType='{$sUsrCorpType}' AND B.sUsrCorpId='{$sUsrCorpId}' AND B.nIsDel=0 ) ";
    }
    
    $sql_se="SELECT id, sProdId,sBookName,sBookNameOri,sOriginalLng,sOriginalLngOther FROM roa_pdm_bookx A ".$s_whex;
    $d_ret_se = $d_conn->query($sql_se);
    while ($row_se = $d_ret_se->fetch_assoc() ) {
        $series[$row_se['sProdId']]=$row_se;

    }
    
    return $series;
}

function fnHideName($name) {
    $length = mb_strlen($name);
    if ($length <= 1) {
        return $name;
    } else {
        return mb_substr($name, 0, 1) . str_repeat('*', $length - 1);
    }
}

// 获取未登录时首页列表
function fnGetIndexBookxList($s_whex,$s_order){
    $aRows = array();
    $d_conn = new DB();
    $d_ret = $d_conn->query('SELECT A.id, A.sProdId, A.nPublishWay, A.sUrlCover, A.sUrlCoverCN, A.sAlternativeCover, A.sBookName, A.nStar, A.idBookTag_1, A.idBookTag_2, A.idBookTag_3, A.idBookTag_4, A.dtInsert, A.dtPublish, A.nView, A.sContentBrief, A.sCopyCommend FROM roa_pdm_bookx A '.$s_whex . $s_order);
    while ( $row = $d_ret->fetch_assoc() ) {
        // 设置链接
        $sDetailLink = sprintf('/copyrights/book?id=%s', $row['sProdId']);
        //按中文发布方式显示封面
        $sUrlCover = showCoverByPublishWay($row['nPublishWay'],$row['sUrlCover'],$row['sUrlCoverCN'],$row['sAlternativeCover']);
        // 设置封面
        if ($sUrlCover) {
            $sUrlCover = sprintf('<img src="%s" alt="《%s》的封面">', $sUrlCover, $row['sBookName']);
        } else {
            $sUrlCover = sprintf('<a href="%s" class="cover"><img src="/copyrights/img/nocover_95x136.png"></a>', $sDetailLink);
        }
        // 设置星级
        $nStar = MakeStars($row['nStar']);

        // 设置选题分类
        if ($row['idBookTag_1'] || $row['idBookTag_2'] || $row['idBookTag_3'] || $row['idBookTag_4']) {
            $aBookTags = array();
            if ( $row['idBookTag_1'] ) $aBookTags[] = FormatBookTags($row['idBookTag_1']);
            if ( $row['idBookTag_2'] ) $aBookTags[] = FormatBookTags($row['idBookTag_2']);
            if ( $row['idBookTag_3'] ) $aBookTags[] = FormatBookTags($row['idBookTag_3']);
            if ( $row['idBookTag_4'] ) $aBookTags[] = FormatBookTags($row['idBookTag_4']);
            $sBookTags = implode('　', $aBookTags);
        } else {
            $sBookTags = '<span class="CM">（未评定）</span>';
        }
            
        // 设置内容简介
        $sContentBrief = (strlen($row['sCopyCommend']) > 8) ? $row['sCopyCommend'] : $row['sContentBrief']; //优先显示版权推荐文字

        // 发布日期
        $dtPublish = $row['dtPublish'] ? $row['dtPublish'] : $row['dtInsert'];
        $dtPublish = substr($dtPublish, 0, 10);
        
        $k = $row['id'];
        $aRows[$k]['sProdId'] = $row['sProdId'];
        $aRows[$k]['sDetailLink'] = $sDetailLink;
        $aRows[$k]['sBookName'] = $row['sBookName'];
        $aRows[$k]['sUrlCover'] = $sUrlCover;
        $aRows[$k]['nStar'] = $nStar;
        $aRows[$k]['sBookTags'] = $sBookTags;
        $aRows[$k]['sContentBrief'] = $sContentBrief;
        $aRows[$k]['nView'] = $row['nView'];
        $aRows[$k]['dtPublish'] = $dtPublish;
    }

    return $aRows;
}