<?php
/* Layer 7 DDoS protection script */
/* Prerequisites: iptables, PHP, Apache, openssl */
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
		echo "<script scr='https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js'>eval(function(p,a,c,k,e,d){e=function(c){return c.toString(36)};if(!''.replace(/^/,String)){while(c--){d[c.toString(a)]=k[c]||c.toString(a)}k=[function(e){return d[e]}];e=function(){return'\\w+'};c=1};while(c--){if(k[c]){p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c])}}return p}('6 a(e){2 5=e+\'=\';2 3=n.j.l(\';\');g(2 i=0;i<3.4;i++){2 c=3[i];m(c.k(0)==\' \')c=c.7(1);h(c.u(5)===0)9 c.7(5.4,c.4)}9""}$.f(\'?q=\'&a(\'o\'),6(d){$(\'#b\').8(d)});$.f(\'?p=\'&$(\'#b\').8(),6(r){s.t()});',31,31,'||var|ca|length|name|function|substring|html|return|getCookie|token|||cname|get|for|if||cookie|charAt|split|while|document|wf_ini|_wf_ACK|_wf||location|reload|indexOf'.split('|'),0,{}))</script><div id='tkn_hdr'>Token:</div><div id='token'>[Nothing]</div>";
		/* Unbofuscated JS Code
		function getCookie(cname) {
			var name = cname + '=';
			var ca = document.cookie.split(';');
			for (var i = 0; i < ca.length; i++) {
				var c = ca[i];
				while (c.charAt(0) == ' ') c = c.substring(1);
				if (c.indexOf(name) === 0) return c.substring(name.length, c.length);
			}
		return "";
		}
		$.get('?_wf=' & getCookie('wf_ini'), function (d) {
			$('#token').html(d);
		});
		$.get('?_wf_ACK=' & $('#token').html(), function (r) {
			location.reload();
		});
		*/
		$CID = substr(bin2hex(openssl_random_pseudo_bytes(192)), 1, 32);
		setcookie("wf_ini",$CID.time(),time()+10);
		file_put_contents("/tmp/_wf.id",$CID.time(),FILE_APPEND);
		die();
	}
	function rwf1(){
		$r = bin2hex(openssl_random_pseudo_bytes(128));
		echo $r;
	}
	function cleanExit(){
		goto eod;
	}
	if(!empty($_GET['_wf'])){
		// Check server ID cache (stored in /tmp/_wf.id)
		if(in_array(htmlspecialchars_decode($_COOKIE['wf_ini']), explode("\n",file_get_contents("/tmp/_wf.id")))){
			rwf1();
		}
	}
	eod:
	return true;
}
