<?php

/*
Plugin Name: Wassup Keywords
Plugin URI: http://pingbackpro.com/wassup-keywords/
Description: Datamines your Wassup stats database for visitor search engine keywords and provides a ranked list. Checks Google ranking positions.
Version: 1.0.0
Author: Tony Hayes
Author URI: http://pingbackpro.com/
*/

add_action('admin_menu','add_wassup_keywords_menu');
if ($_REQUEST['wassupkeywords'] == 'checkgoogleresult') {add_action('admin_head','check_google_result');}
add_option('pbpref','');

function add_wassup_keywords_menu() {
		add_submenu_page('index.php', __('WassUp Keywords'), __('WassUp Keywords'), 8, 'wassup-keywords', 'wassup_keywords');
		add_submenu_page(WASSUPFOLDER, __('Wassup Keywords', 'wassup'), __('Wassup Keywords', 'wassup'), 8, 'wassup-keywords', 'wassup_keywords');
}

function check_google_result() {
	$vsearchterm = $_REQUEST['searchterm'];
	$vcheckpage = $_REQUEST['checkpage'];
	$vcheckrow = $_REQUEST['checkrow'];

	$vgoogleresults = get_google_rankings($vsearchterm,$vcheckpage);

	// print_r($vgoogleresults);

	$vi = 0; $vsiterankings = "";
	foreach ($vgoogleresults as $varesult) {
		if (stristr($vgoogleresults[$vi],$_SERVER['HTTP_HOST'])) {
			$vj = $vi + 1;
			if ($vsiterankings == "") {$vsiterankings = $vj;}
			else {$vsiterankings .= ",".$vj;}
		}
		$vi = $vi + 1;
	}
	echo "<font face=helvetica style='font-size:8pt;'>Page ".$vcheckpage." positions:<br>";
	echo "<div align='right'>".$vsiterankings."</div>";

	// echo "<script language='javascript' type='text/javascript'>";
	// echo " alert('".$vsiterankings."');";
	// echo " var grref = 'ranking".$vcheckrow."';";
	// echo " alert(grref);";
	// echo " if (parent.document.getElementById(grref)) {alert('Cant access parent frame WHY?');}";
	// echo " window.opener.getElementById(grref).value = '".$vsiterankings."';";
	// echo " alert('".$vsiterankings."');";
	// echo "</script>";
	wp_die(false);
}

function wassup_keywords() {

	global $wpdb;
	$vaddlink = wk_get_link();
	echo '<div class="wrap"><table><tr><td align="left" style="vertical-align:top;"><h2>Wassup, Keywords?</h2></td><td width=80></td>
	<td align="center"><table cellspacing="7" style="background-color:#ffffff;border: 1px solid #dddddd;"><tr><td align="center"><font style="font-size:9pt;line-height:1.4em;"><a href="'.$vaddlink[0].'" target=_blank style="text-decoration:none;">'.$vaddlink[1].'</a></font></td></tr></table></td></tr></table>
	<div id="wassup-keywords" class="postbox "><div class="inside">';
	echo '<script language="javascript" type="text/javascript">
			function allkeywords() {location.href = "admin.php?page=wassup-keywords&wassupkeywords=allkeywords";}
			function topkeywords() {location.href = "admin.php?page=wassup-keywords&wassupkeywords=topkeywords";}
		  </script>';

	$vkeywordresults = $wpdb->get_results("SELECT timestamp, `urlrequested`, `search`, searchpage, searchengine FROM `wp_wassup` WHERE search != '' ORDER BY timestamp DESC");

	if ($_REQUEST['wassupkeywords'] == '') {
		echo "<center><h3>Control Panel</h3></center><br>";
	}

	echo "<br><center><table><tr><td><input type='button' onclick='allkeywords();' value='View All Visitor Keywords'></td>";
	echo "<td width=50></td><td><input type='button' onclick='topkeywords();' value='View Top Keyword Results'></td></tr></table><br>";

	if ($_REQUEST['wassupkeywords'] == 'allkeywords') {
		echo "<center><h3>All Visitor Keywords</h3></center><br><br><table><tr><td align='center'><b>Date</b></td><td width=10><td><b>Keyword</b></td><td align='center'><b>Search Engine</b></td><td align='center'><b>Page</b></td></tr><tr height=10><td> </td></tr>";
		$vi = 0;
		foreach ($vkeywordresults as $varesult) {
			if (!strstr($varesult->search,'#')) {
				$vdatestamp = date('d/m/y',$varesult->timestamp);
				echo "<tr><td>".$vdatestamp."</td><td></td>";
				echo "<td><font color=#0000ee><b>".$varesult->search."</b></font></td>";
				$vthissearchengine = str_replace('www.','',$varesult->searchengine);
				$vthissearchengine = str_replace('.com','',$vthissearchengine);
				echo "<td><font style='font-size:8pt;'>".$vthissearchengine."</font></td><td align='center'>";
				if ($varesult->searchpage == 1) {echo "<b><font color=#0000ee style='font-size:11pt;'>".$varesult->searchpage."</font></b>";}
				if ($varesult->searchpage == 2) {echo "<b><font color=#0000ee style='font-size:10pt;'>".$varesult->searchpage."</font></b>";}
				if (($varesult->searchpage < 6) && ($varesult->searchpage > 2)) {echo "<b><font color=#0000ee>".$varesult->searchpage."</font>";}
				if (($varesult->searchpage < 11) && ($varesult->searchpage > 5)) {echo "<font color=#0000ee>".$varesult->searchpage."</font>";}
				if ($varesult->searchpage > 10) {echo $varesult->searchpage;}
				echo "</td></tr><tr><td align='right'>-&gt;</td><td></td><td align='left'><font style='font-size:7pt;'><a href='".$varesult->urlrequested."' style='text-decoration:none;' target=_blank>".$varesult->urlrequested."</a></font></td></tr>";


			}
		}
		echo "</table><br><br>";
	}

	if ($_REQUEST['wassupkeywords'] == 'topkeywords') {

		$vi = 0;
		foreach ($vkeywordresults as $varesult) {
			if (!strstr($varesult->search,'#')) {
				if (isset($vkeywords[$varesult->search])) {
					$vkeywords[$varesult->search] = $vkeywords[$varesult->search] + 1;
				}
				else {$vkeywords[$varesult->search] = 1;}
				$vkeywordlist[$vi] = $varesult->search;
				$vi = $vi + 1;
			}
		}

		echo "<center><h3>Top Keyword Results</h3></center><br>";

		$vkeywordlist = array_unique($vkeywordlist);
		$vi = 0;
		foreach ($vkeywordlist as $vakeyword) {
			if ($vkeywords[$vakeyword] > 2) {
				$vtopkeywords[$vi]['keyword'] = $vakeyword;
				$vtopkeywords[$vi]['results'] = $vkeywords[$vakeyword];
			}
			$vi = $vi + 1;
		}

		$vtopkeywords = sort_subvals($vtopkeywords,'results');

		echo "<center><table><tr><td width=10></td><td width=350><b>Keywords</b></td><td width=7></td><td><b>Visitors</b></td><td width=7></td><td><b>Search Engines</b></td></tr><tr height=10><td> </td></tr>";
		$vi = 0;
		foreach ($vtopkeywords as $vwhatever) {
			echo "<tr><td></td><td style='vertical-align:top;' align='left'><center><font style='font-size:10pt;' color=#0000ee><b>".$vtopkeywords[$vi]['keyword']."</b></font></center><br><font style='font-size:8pt;'>";
			$vj = 0; $vprintstrings = array();
			foreach ($vkeywordresults as $varesult) {
				if ($vtopkeywords[$vi]['keyword'] == $varesult->search) {
					$vprintstring = "<a href='".$varesult->urlrequested."' target=_blank style='text-decoration:none;'>".$varesult->urlrequested."<br>";
					if (!in_array($vprintstring,$vprintstrings)) {
						echo $vprintstring;
						$vprintstrings[$vj] = $vprintstring;
						$vj = $vj + 1;
					}
				}
			}
			echo "</font></td><td></td><td style='vertical-align:top;'><font style='font-size:11pt;' color=#0000ee><b>".$vtopkeywords[$vi]['results']."</b></font></td>";
			echo "<td></td><td align='left><font style='font-size:8pt;'>";
			$vcheckgoogle = ""; $vj = 0; $vprintstrings = array();
			foreach ($vkeywordresults as $varesult) {
				if ($vtopkeywords[$vi]['keyword'] == $varesult->search) {
					$vthissearchengine = str_replace('www.','',$varesult->searchengine);
					$vthissearchengine = str_replace('.com','',$vthissearchengine);
					$vprintstring = $vthissearchengine." page ".$varesult->searchpage."<br>";
					if (!in_array($vprintstring,$vprintstrings)) {
						echo $vprintstring;
						if ($varesult->searchengine == "Google") {
							if ($vcheckgoogle == "") {$vcheckgoogle = $varesult->searchpage;}
							elseif ($varesult->searchpage < $vcheckgoogle) {$vcheckgoogle = $varesult->searchpage;}
						}
						$vprintstrings[$vj] = $vprintstring;
						$vj = $vj + 1;
					}
				}
			}
			echo "</font></td>";
			if ($vcheckgoogle != "") {
				$vsearchterm = $vtopkeywords[$vi]['keyword'];
				echo "<td><input type='button' onclick='checkgoogleranking(\"".$vsearchterm."\",\"".$vcheckgoogle."\",\"".$vi."\");' value='Check Google Rank' style='font-size:7pt;'><br>";
				echo "<iframe id='googlerow".$vi."' style='display:none;' src='javascript:void(0);' width=100 height=50 frameborder=0 scrolling=no></iframe></td>";
			}

			echo "<td width=10></td></tr><tr height=5><td> </td></tr>";
			$vi = $vi + 1;
		}
		echo "</table></center><br><br>";
		echo "<iframe id='checkingframe' name='checkingframe' width=500 height=200></iframe>";
		echo "<form name='checkgoogleresult' target='checkingframe'><input type='hidden' name='page' value='wassup-keywords'><input type='hidden' name='wassupkeywords' value='checkgoogleresult'>";
		echo "<input type='hidden' name='searchterm' id='searchterm' value=''><input type='hidden' name='checkpage' id='checkpage' value=''><input type='hidden' name='checkrow' id='checkrow' value=''>";
		echo "<script language='javscript' type='text/javascript'>";
		echo "function checkgoogleranking(searchterm,page,row) {";
		echo "  document.getElementById('searchterm').value = searchterm;";
		echo "  document.getElementById('checkpage').value = page;";
		echo "  document.getElementById('checkrow').value = row;";
		echo "  var googlerow = 'googlerow'+row;";
		echo "  document.checkgoogleresult.target = googlerow;";
		echo "  document.checkgoogleresult.submit();";
		echo "  document.getElementById(googlerow).style.display = '';";
		echo "}";
		echo "</script>";

		// print_r($vtopkeywords);
	}

	echo "</div></div></div>";
}

function sort_subvals($va,$vsubkey) {
	foreach($va as $vk=>$vv) {
		$vb[$vk] = strtolower($vv[$vsubkey]);
	}
	arsort($vb);
	foreach($vb as $vkey=>$vval) {
		$vc[] = $va[$vkey];
	}
	return $vc;
}

function wk_get_link() {
	$vlinkurl = base64_decode('aHR0cDovL3BpbmdiYWNrcHJvLmNvbS9nZXRsaW5rLnBocA==');
	$vch = curl_init();
	curl_setopt($vch, CURLOPT_URL,$vlinkurl);
	curl_setopt($vch, CURLOPT_RETURNTRANSFER, 1);
	$vgetlink = curl_exec($vch);
	$vhttp_code = curl_getinfo($vch, CURLINFO_HTTP_CODE);
	curl_close ($vch);
	unset($vch);
	if ($vhttp_code == 200) {
		if (get_option('pbpref') != "") {
			$vpbpref = "pingbackpro.com/plugin/?".get_option('pbpref')."|||";
			$vlinkdata = str_replace("pingbackpro.com|||",$vpbpref,$vlinkdata);
		}
		$vlinkdata = explode("|||",$vgetlink);
		return $vlinkdata;
	}
	return false;
}

function wk_download_page($url) {
	$vch = curl_init();
	curl_setopt($vch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.1) Gecko/2008070208 Firefox/3.0.1");
	curl_setopt($vch, CURLOPT_URL,$url);
	curl_setopt($vch, CURLOPT_RETURNTRANSFER, 1);
	$urlcontents = curl_exec($vch);
	$http_code = curl_getinfo($vch, CURLINFO_HTTP_CODE);
	curl_close ($vch);
	unset($vch);
	if ($http_code == 200) {return $urlcontents;}
	elseif (($http_code == 301) || ($http_code == 302) || ($http_code == 307)) {
		$vch = curl_init();
		curl_setopt($vch, CURLOPT_URL,$url);
		curl_setopt($vch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($vch, CURLOPT_HEADER, 1);
		$header = curl_exec($vch);
		$http_code = curl_getinfo($vch, CURLINFO_HTTP_CODE);
		curl_close ($vch);
		unset($vch);
		$position = strpos($header,"Follow Location") + 15;
		if ($position > 15) {
			$chunks = str_split($header,$position);
			unset($chunks[0]);
			$header = implode("",$chunks);
		}
		$position = strpos($header,"Location: ") + 10;
		$chunks = str_split($header,$position);
		unset($chunks[0]);
		$header = implode("",$chunks);
		$position = strpos($header,"\r\n");
		if ($position == 0) {echo $header;}
		$newurl = str_split($header,$position);
		$url = $newurl[0];
		$vch = curl_init();
		curl_setopt($vch, CURLOPT_URL,$url);
		curl_setopt($vch, CURLOPT_RETURNTRANSFER, 1);
		$urlcontents = curl_exec($vch);
		$http_code = curl_getinfo($vch, CURLINFO_HTTP_CODE);
		curl_close ($vch);
		unset($vch);
		if ($http_code == 200) {return $urlcontents;}
		else {return false;}
	}
	else {$errors .= "<font style='font-size:8pt;'>Warning: ".$http_code." error code for URL: ".$url."</font><br>";
		return false;}
}

function get_google_rankings($vsearchterm,$vpage) {

		$vi = 0;
		$vlinkdata = array();
		$vsearchterm = str_replace(' ','+',$vsearchterm);
		$vUri = 'http://www.google.com/search?hl=en&num=10&q='.$vsearchterm;
		if ($vpage > 1) {$voffset = $vpage*10; $vUri .= '&start='.$voffset;}
		$vUri .= '&btnG=Search';

		// echo $vUri;

		$vnogoogleresults = 'did not match any documents.';
		$vbegingoogle = '<h3 class="r"><a href="http';
		$vendgoogle = '"';
		// '

		$vpagecontents = wk_download_page($vUri);
		// echo $vpagecontents;

		if (stristr($vpagecontents,$vnogoogleresults)) {return false;}
		else {
			if (stristr($vpagecontents,$vbegingoogle)) {
				while (stristr($vpagecontents,$vbegingoogle)) {
					$vposition = stripos($vpagecontents,$vbegingoogle) + strlen($vbegingoogle);
					$vpagechunks = str_split($vpagecontents,$vposition);
					$vpagechunks[0] = '';
					$vpagecontents = implode('',$vpagechunks);
					$vposition = stripos($vpagecontents,$vendgoogle);
					$vresults = str_split($vpagecontents,$vposition);
					$vresults[0] = "http".$vresults[0];
					$vresults[0] = strtolower($vresults[0]);
					if (strstr($vresults[0],"?")) {
						$vbloglinktemp = explode("?",$vresults[0]);
						$vresults[0] = $vbloglinktemp[0];
					}
					$vlinkdata[$vi] = $vresults[0];
					$vi = $vi + 1;
				}
			}
		}
		return $vlinkdata;
	}

?>