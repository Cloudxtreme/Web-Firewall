<?php
/* Layer 7 DDoS protection script */
function l7prot(){
	$CFAK = "scrubbed"; // Cloudflare API Key
	$CFEMAIL= "scrubbed"; // Cloudflare Email
	if(empty($_SERVER['HTTP_CF_IPCOUNTRY'])){
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
		$f = array_merge($p,$f);
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
		if(!cfSend("ban", array("key"=>$_SERVER['REMOTE_ADDR']))){
			// iptables execution
		}
		header("HTTP/1.0 403 Forbidden");
		echo "You have been denied.";
		die();
	}
	function checkLegit(){
		echo "<script scr='https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js'>function getCookie(cname){var name=cname+'=';var ca=document.cookie.split(';');for(var i=0;i<ca.length;i++){var c=ca[i];while (c.charAt(0)==' ')c=c.substring(1);if(c.indexOf(name)==0)return c.substring(name.length,c.length);}return "";}$.get('?_wf='&getCookie('wf_ini'),function(d){$('#token').html(d);});$.get('?_wf_ACK='&$('#token').html(),function(r){location.reload();});</script><div id='tkn_hdr'>Token:</div><div id='token'>[Nothing]</div>";
	}
	function cleanExit(){
		goto eod;
	}
	eod:
	return true;
}
