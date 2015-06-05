<?php
/* Layer 7 HTTP GET DDoS protection script */
/* Prerequisites: iptables, PHP, Apache, openssl */
	$CFAK = "scrubbed"; // Cloudflare API Key
	$CFEMAIL= "scrubbed"; // Cloudflare Email
	$TTL = 3600; //Time in seconds for channelge TTL 1 hour is default
	if(empty($_SERVER['HTTP_CF_IPCOUNTRY'])){
		$CLOUDFLARE= false;
	}else{
		$CLOUDFLARE= true;
	}
	function ban($ip){
		global $CFAK, $CFEMAIL, $CLOUDFLARE;
		if(!$CLOUDFLARE){return false;}
		$url = 'https://www.cloudflare.com/api_json.html';
		$f = array(
			'a' => "ban",
			'tkn' => $CFAK,
			'email' => $CFEMAIL,
			"key" => $ip
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
		ban($_SERVER['REMOTE_ADDR']);
		header("HTTP/1.0 403 Forbidden");
		echo "You have been denied.";
		die();
	}
	function checkLegit(){
		echo '<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script><script>
				function getCookie(cname) {
			var name = cname + "=";
			var ca = document.cookie.split(";");
			for (var i = 0; i < ca.length; i++) {
				var c = ca[i];
				while (c.charAt(0) == " ") c = c.substring(1);
				if (c.indexOf(name) === 0) return c.substring(name.length, c.length);
			}
			return "";
		}
		$.get("'.$_SERVER['PHP_SELF'].'?_wf=" & getCookie("wf_ini"), function (d) {
			$("#token").html(d);
			$.get("'.$_SERVER['PHP_SELF'].'?_wf_ACK=" & $("#token").html(), function (r) {
			if(r == "Ok"){
				alert("Press Ok to continue...");
				location.reload();
			}else{
				$("#token").html("Failed bot challenge......");
            }
			});
		});
		</script><div id="tkn_hdr">Token:</div><div id="token">[Nothing]</div>';
		$CID = substr(bin2hex(openssl_random_pseudo_bytes(192)), 1, 32);
		setcookie("wf_ini",$CID.time(),time()+10);
		file_put_contents("/tmp/_wf.id",$_SERVER['REMOTE_ADDR']."&".$CID.time()+10 ."\n",FILE_APPEND);
		die();
	}
	function verCookie(){
		$va = explode(":",explode("\n",file_get_contents("/tmp/_wf.wl")));
		if(in_array($_SERVER['REMOTE_ADDR'].":".htmlspecialchars_decode($_COOKIE['wf_twl']),$va[0].":".$va[1])){
			return true;
		}elseif(in_array(htmlspecialchars_decode($_COOKIE['wf_twl']),explode(":",explode("\n",file_get_contents("/tmp/_wf.wl")))[1])){
			setcookie("wf_twl",'',1);
			echo "Different-origin violation. This incidence has been forgiven.";
			die();
		}else{
			setcookie("wf_twl",'',1);
			ban($_SERVER['REMOTE_ADDR']);
			die();
		}
	}
	function setwfCookie(){
		$CID = substr(bin2hex(openssl_random_pseudo_bytes(192)), 1, 32);
		setcookie("wf_twl",$CID,$time+$TTL);
		file_put_contents("/tmp/_wf.wl",$_SERVER['REMOTE_ADDR'].":".$CID.":".time());
	}
	setwfCookie();
	echo var_dump(verCookie());
