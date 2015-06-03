<?php
/* Layer 7 DDoS protection script 


*/
function l7prot(){
	$CFAK = "scrubbed"; // Cloudflare API Key
	$CFEMAIL= "scrubbed"; // Cloudflare Email
	if(!empty(HTTP_CF_IPCOUNTRY)){
		$CLOUDFLARE= false;
	}else{
		$CLOUDFLARE= true;
	}
	function cfSend($a, $p = array()){
		global $CFAK, $CFEMAIL, $CLOUDFLARE;
		if(!$CLOUDFLARE){return false;}
		$url = 'https://www.cloudflare.com/api_json.html';
		$f = array(
			'a' => $a,
			'tkn' => $CFAK,
			'email' => $CFEMAIL,
		);
		array_merge($p,$f);
		foreach($f as $k=>$v) { $fs .= $k.'='.$v.'&'; }
		rtrim($fs, '&');
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_POST, count($f));
		curl_setopt($ch,CURLOPT_POSTFIELDS, $fs);
		$r = curl_exec($ch);
		curl_close($ch);
		return $r;
	}
	function kill($m=1){
		# if(fnmatch(0,cfSend("ip_lkup", array("ip"=>$_SERVER['REMOTE_ADDR'])))){}
		cfSend("ban", array("key"=>$_SERVER['REMOTE_ADDR']));
		header("HTTP/1.0 403 Forbidden");
		echo "You have been denied.";
		die();
	}
	function cleanExit(){
		goto eod;
		return true;
	}
	eod:
	return true;
}