<?php
//Powered by zQuery (http://www.zquery.com)

function get_pr($url){
	if(!$url || strpos($url,'.') === false){
		return 0;
	}
	$pr = pr_cache($url);
	if($pr === false){
		$pr = pr_update($url);
	}
	return $pr;
}

function pr_cache($url){
	$parsed = parse_url($url);
	if(file_exists('zquery.txt')){
		$prs = pr_explode('zquery.txt');
		if(is_array($prs[$url])){
			if(time() - $prs[$url]['timer'] <= 172800){//172800
				return $prs[$url]['pr'];
			}else{
				return false;
			}
		}else{
			return false;
		}
	}else{
		$fp = fopen('zquery.txt','w');
		fclose($fp);
		return false;
	}
}

function pr_update($url){
	$fp = @fsockopen('www.google.com', 80, $errno, $errstr, 10); 
	if (!$fp){
		return false;
	}else{
		$out = 'GET /search?client=navclient-auto&ch=6-100&features=Rank&q=info:' . urlencode($url) . " HTTP/1.1\r\n" ;
		$out .= "Host: www.google.com\r\n" ;
		$out .= "Connection: Close\r\n\r\n" ;
		fwrite($fp, $out);
		while (!feof($fp)) {
			$data = fgets($fp);
			if(strpos($data, "Rank_") !== false){
				$pagerank = trim(substr($data, $pos + 9));
			}
		}
		fclose($fp);
		
	}
	$pr = $pagerank ? $pagerank : 0;
	write_pr($pr,$url);
	return $pr;
}

function write_pr($pr,$url){
	$parsed = parse_url($url);
	$prs = pr_explode('zquery.txt');
	unset($prs[$url]);
	$prs[urlencode($url)] = array('pr' => $pr , 'timer' => time() ,'key' => $url );
	
	$fp = fopen('zquery.txt','w');
	foreach($prs as $item){
		fwrite($fp,$item['timer'].' '.urlencode($item['key']).' '.$item['pr']."\r\n");
	}
	fclose($fp);
}

function pr_explode($file){
	$lines = file($file);
	foreach($lines as $line){
		$line = str_replace("\r\n",'',trim($line));
		if(trim($line) != ''){
			$c = explode(' ',$line);
			$result[urldecode($c[1])] = array('pr' => $c[2] ? $c[2] : 0,'timer' => $c[0],'key' => urldecode($c[1]));
		}
	}
	if(is_array($result)){
		$result = array_filter($result);
	}
	return $result;
}




$url = $_GET['q'];
$pr = get_pr($url);

header('Content-Type: image/gif');
readfile('images/pr_'.$pr.'.gif');
?>