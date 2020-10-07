<?php
			

				$connection = new mysqli('127.0.0.1','root','','hb');
				if ($connection->connect_errno) die($connection->connect_error);
		
		

			function destroySession()
			{
				$_SESSION=array();
				
				if(session_id() !="" || isset($_COOKIE[session_name()]))
					setcookie(session_name(), '', time()-2592000,'/');
					
				session_destroy();
			}
			
			function sanitizeString($var)
			{
				global $connection;
				$var = strip_tags($var);
				$var = htmlentities($var);
				$var = stripslashes($var);
				
				return $connection->real_escape_string($var);
			}

			function queryMysql($query)
			{
				global $connection;
				$result = $connection->query($query);
				if(!$result) die($connection->error);
				return $result;
			}

			function profileInfo($user)
			{
				global $connection;

				$result = queryMysql("SELECT * FROM hm_profile WHERE hm_id = $user ");
				if(!$result) die($connection->error);

				if($result->num_rows)
				{
					$row = $result->fetch_array(MYSQLI_ASSOC);
				}
				
				return $row;
			}
			
			function swapBox($swap_user)
			{
				global $connection;

				$swap_result = queryMysql("SELECT * FROM swap WHERE user_id = $swap_user");
				if(!$swap_result) die($connection->error);
				if($swap_result->num_rows)
				$swap_col = array();
				{
					$swap_col = $swap_result->fetch_array(MYSQLI_ASSOC);
				}
				return $swap_col;
			}
			
			function swapInfo($user_id){
				global $connection;

				$swap_info = queryMysql("SELECT first_name, last_name, profile_pic FROM hm_profile WHERE hm_id = '$user_id' ");
				if(!$swap_info) die($connection->error);
				if($swap_info->num_rows)
				$swap_info_col = array();
				{
					$swap_info_col = $swap_info->fetch_array(MYSQLI_ASSOC);
				}
				return $swap_info_col;
			}
			
			function timeAgo($date_from, $dateto=0)
			{
				// Defaults and assume if 0 is passed in that
				// its an error rather than the epoch
				
				if($date_from<=0) { return "A long time ago"; }
				if($dateto==0) { $dateto = time(); }
				
				// Calculate the difference in seconds betweeen
				// the two timestamps
				$datefrom = strtotime($date_from); 
				$difference = ($dateto + 1) - $datefrom;
				
				// If difference is less than 60 seconds,
				// seconds is a good interval of choice
				
				if($difference < 60)
				{
				$interval = "s";
				}
				
				// If difference is between 60 seconds and
				// 60 minutes, minutes is a good interval
				elseif($difference >= 60 && $difference<60*60)
				{
				$interval = "n";
				}
				
				// If difference is between 1 hour and 24 hours
				// hours is a good interval
				elseif($difference >= 60*60 && $difference<60*60*24)
				{
				$interval = "h";
				}
				
				// If difference is between 1 day and 7 days
				// days is a good interval
				elseif($difference >= 60*60*24 && $difference<60*60*24*7)
				{
				$interval = "d";
				}
				
				// If difference is between 1 week and 30 days
				// weeks is a good interval
				elseif($difference >= 60*60*24*7 && $difference <
				60*60*24*30)
				{
				$interval = "ww";
				}
				
				// If difference is between 30 days and 365 days
				// months is a good interval, again, the same thing
				// applies, if the 29th February happens to exist
				// between your 2 dates, the function will return
				// the 'incorrect' value for a day
				elseif($difference >= 60*60*24*30 && $difference <
				60*60*24*365)
				{
				$interval = "m";
				}
				
				// If difference is greater than or equal to 365
				// days, return year. This will be incorrect if
				// for example, you call the function on the 28th April
				// 2008 passing in 29th April 2007. It will return
				// 1 year ago when in actual fact (yawn!) not quite
				// a year has gone by
				elseif($difference >= 60*60*24*365)
				{
				$interval = "y";
				}
				
				// Based on the interval, determine the
				// number of units between the two dates
				// From this point on, you would be hard
				// pushed telling the difference between
				// this function and DateDiff. If the $datediff
				// returned is 1, be sure to return the singular
				// of the unit, e.g. 'day' rather 'days'
				
				switch($interval)
				{
				case "m":
				$months_difference = floor($difference / 60 / 60 / 24 /
				29);
				while (mktime(date("H", $datefrom), date("i", $datefrom),
				date("s", $datefrom), date("n", $datefrom)+($months_difference),
				date("j", $dateto), date("Y", $datefrom)) < $dateto)
				{
				$months_difference++;
				}
				$datediff = $months_difference;
				
				// We need this in here because it is possible
				// to have an 'm' interval and a months
				// difference of 12 because we are using 29 days
				// in a month
				
				if($datediff==12)
				{
				$datediff--;
				}
				
				$res = ($datediff==1) ? "$datediff mth. ago" : "$datediff
				mths. ago";
				break;
				
				case "y":
				$datediff = floor($difference / 60 / 60 / 24 / 365);
				$res = ($datediff==1) ? "$datediff yr. ago" : "$datediff
				yrs. ago";

				break;
				
				case "d":
				$datediff = floor($difference / 60 / 60 / 24);
				$res = ($datediff==1) ? "$datediff day ago" : "$datediff
				days ago";
				break;
				
				case "ww":
				$datediff = floor($difference / 60 / 60 / 24 / 7);
				$res = ($datediff==1) ? "$datediff wk. ago" : "$datediff
				wks. ago";
				break;
				
				case "h":
				$datediff = floor($difference / 60 / 60);
				$res = ($datediff==1) ? "$datediff hr. ago" : "$datediff
				hrs. ago";
				break;
				
				case "n":
				$datediff = floor($difference / 60);
				$res = ($datediff==1) ? "$datediff min. ago" :
				"$datediff mins. ago";
				break;
				
				case "s":
				$datediff = $difference;
				$res = ($datediff==1) ? "$datediff sec. ago" :
				"$datediff secs. ago";
				break;
				}
				return $res;
			}
			
			function msgSeen($sender1, $reciver1)
			{
				$sender_id1 = $sender1;
				$reciver_id1 = $reciver1;
				$now = date("Y-m-d H:i:s");
				queryMysql("UPDATE `chat` SET `seen`= 1,`seen_on`= '$now' WHERE sender_id = '$reciver_id1' AND reciver_id = '$sender_id1' AND seen = 0");
			}
			
			function friendList($user)
			{
				global $connection;
				$friends = array();
				
				$result = queryMysql("SELECT user_id_1, user_id_2 FROM hm_friend WHERE (`user_id_1` = $user OR `user_id_2` = $user) AND `status` = 1");
				if(!$result) die($connection->error);

				
				if($result->num_rows)
				{
					while($row = $result->fetch_array(MYSQLI_ASSOC))
					{
						if($row['user_id_1'] == $user){
							$friends[] = $row['user_id_2'];
						}
						else{
							$friends[] = $row['user_id_1'];
						}
					}
				} 
				
				return $friends;
			}
			
			function oldHmList($user)
			{
				global $connection;
				$oldHm = array();
				
				$result = queryMysql("SELECT user_id_1, user_id_2 FROM hm_friend WHERE (`user_id_1` = $user OR `user_id_2` = $user)");
				if(!$result) die($connection->error);

				
				if($result->num_rows)
				{
					while($row = $result->fetch_array(MYSQLI_ASSOC))
					{
						if($row['user_id_1'] == $user){
							$oldHm[] = $row['user_id_2'];
						}
						else{
							$oldHm[] = $row['user_id_1'];
						}
					}
				}
				return $oldHm;
			}
			
			function blockList($user)
			{
				global $connection;
				$blocks = array();
				
				$result = queryMysql("SELECT user_id_1, user_id_2 FROM hm_friend WHERE (`user_id_1` = $user OR `user_id_2` = $user) AND `status` = 3 AND `action_user_id` = $user");
				if(!$result) die($connection->error);

				
				if($result->num_rows)
				{
					while($row = $result->fetch_array(MYSQLI_ASSOC))
					{
						if($row['user_id_1'] == $user){
							$blocks[] = $row['user_id_2'];
						}
						else{
							$blocks[] = $row['user_id_1'];
						}
					}
				}
				return $blocks;
			}
			
			function showAim($hm_id)
			{
				$hm_info1 = profileInfo($hm_id);
				
				$user_aim =  $hm_info1['user_aim'];
				return $user_aim;
			}
			
			function is_connected()
			{
				$connected = @fsockopen('www.google.com',81);
				
				if($connected){
					return true;
				}else{
					return false;
				}
			}
			
			function linkify($value, $protocols = array('http', 'mail'), array $attributes = array())
			{
				// Link attributes
				$attr = '';
				foreach ($attributes as $key => $val) {
					$attr = ' ' . $key . '="' . htmlentities($val) . '"';
				}
				
				$links = array();
				// Extract existing links and tags
				$value = preg_replace_callback('~(<a .*?>.*?</a>|<.*?>)~i', function ($match) use (&$links) { return '<' . array_push($links, $match[1]) . '>'; }, $value);
				
				// Extract text links for each protocol
				foreach ((array)$protocols as $protocol) {
					switch ($protocol) {
						case 'http':
						case 'https':   $value = preg_replace_callback('~(?:(https?)://([^\s<]+)|(www\.[^\s<]+?\.[^\s<]+))(?<![\.,:])~i', function ($match) use ($protocol, &$links, $attr) { if ($match[1]) $protocol = $match[1]; $link = $match[2] ?: $match[3]; return '<' . array_push($links, "<a $attr href=\"$protocol://$link\" target='_blank'>$link</a>") . '>'; }, $value);						break;
						case 'mail':    $value = preg_replace_callback('~([^\s<]+?@[^\s<]+?\.[^\s<]+)(?<![\.,:])~', function ($match) use (&$links, $attr) { return '<' . array_push($links, "<a $attr href=\"mailto:{$match[1]}\" target='_blank'>{$match[1]}</a>") . '>'; }, $value);
						break;
						case 'twitter': $value = preg_replace_callback('~(?<!\w)[@#](\w++)~', function ($match) use (&$links, $attr) { return '<' . array_push($links, "<a $attr href=\"https://twitter.com/" . ($match[0][0] == '@' ? '' : 'search/%23') . $match[1]  . "\" target='_blank'>{$match[0]}</a>") . '>'; }, $value);
						break;
						default:        $value = preg_replace_callback('~' . preg_quote($protocol, '~') . '://([^\s<]+?)(?<![\.,:])~i', function ($match) use ($protocol, &$links, $attr) { return '<' . array_push($links, "<a $attr href=\"$protocol://{$match[1]}\" target='_blank'>{$match[1]}</a>") . '>'; }, $value);
						break;
					}
					
				}
				
				// Insert all link
				return preg_replace_callback('/<(\d+)>/', function ($match) use (&$links) { return $links[$match[1] - 1]; }, $value);
			}
			
			function urlData($value, $protocols = array('http', 'mail'), array $attributes = array())
			{
				// Link attributes
				$attr = '';
				foreach ($attributes as $key => $val) {
					$attr = ' ' . $key . '="' . htmlentities($val) . '"';
				}
				
				$links = array();
				// Extract existing links and tags
				$value = preg_replace_callback('~(<a .*?>.*?</a>|<.*?>)~i', function ($match) use (&$links) { return '<' . array_push($links, $match[1]) . '>'; }, $value);
				
				// Extract text links for each protocol
				foreach ((array)$protocols as $protocol) {
					switch ($protocol) {
						case 'http':
						case 'https':   $value = preg_replace_callback('~(?:(https?)://([^\s<]+)|(www\.[^\s<]+?\.[^\s<]+))(?<![\.,:])~i', function ($match) use ($protocol, &$links, $attr) { if ($match[1]) $protocol = $match[1]; $link = $match[2] ?: $match[3]; return '<' . array_push($links, "<a $attr href=\"$protocol://$link\" target='_blank'>$link</a>") . '>'; }, $value);						break;
						case 'mail':    $value = preg_replace_callback('~([^\s<]+?@[^\s<]+?\.[^\s<]+)(?<![\.,:])~', function ($match) use (&$links, $attr) { return '<' . array_push($links, "<a $attr href=\"mailto:{$match[1]}\" target='_blank'>{$match[1]}</a>") . '>'; }, $value);
						break;
						case 'twitter': $value = preg_replace_callback('~(?<!\w)[@#](\w++)~', function ($match) use (&$links, $attr) { return '<' . array_push($links, "<a $attr href=\"https://twitter.com/" . ($match[0][0] == '@' ? '' : 'search/%23') . $match[1]  . "\" target='_blank'>{$match[0]}</a>") . '>'; }, $value);
						break;
						default:        $value = preg_replace_callback('~' . preg_quote($protocol, '~') . '://([^\s<]+?)(?<![\.,:])~i', function ($match) use ($protocol, &$links, $attr) { return '<' . array_push($links, "<a $attr href=\"$protocol://{$match[1]}\" target='_blank'>{$match[1]}</a>") . '>'; }, $value);
						break;
					}
					
				}
				$urlData='';
				if(isset($links[0]))
				{ 
					$urlData = fetchUrlData($links[0]); 
				}
				
				return $urlData;
			}
			
			
			function check_url($value)
			{
				$value = trim($value);
				if (get_magic_quotes_gpc()) 
				{
				$value = stripslashes($value);
				}
				$value = strtr($value, array_flip(get_html_translation_table(HTML_ENTITIES)));
				$value = strip_tags($value);
				$value = htmlspecialchars($value);
				return $value;
			}	
			
			function file_get_contents_curl($url)
			{
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				
				$data = curl_exec($ch);
				$info = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
				
				//checking mime types
				if(strstr($info,'text/html')) {
				curl_close($ch);
				return $data;
				} else {
				return false;
				}
			}

			
			function fetchUrlData($url)
			{
				$url = trim($url);
				$url = check_url($url);
				
				//fetching url data via curl
				$html = file_get_contents_curl($url);
				
				if($html) {
					//parsing begins here:
					$doc = new DOMDocument();
					@$doc->loadHTML($html);
					$nodes = $doc->getElementsByTagName('title');
					
					//get and display what you need:
					$title = $nodes->item(0)->nodeValue;
					$metas = $doc->getElementsByTagName('meta');
					
					for ($i = 0; $i < $metas->length; $i++)
					{
						$meta = $metas->item($i);
						if($meta->getAttribute('name') == 'description')
							$description = $meta->getAttribute('content');
					}
					
					// fetch images
					$image_regex = '/<img[^>]+>/i';
					preg_match_all($image_regex, $html, $img, PREG_PATTERN_ORDER);
					?>
							<table>
								<tr>
									<td>
									
											<?php
											$k=1;
											foreach ($img as $images)
											{
												$i='';
												foreach ($images as $image)
												{
													$pattern = "/(<img\s+).*?src=((\".*?\")|(\'.*?\')|([^\s]*)).*?>/is";
													$replacement = "<img width='70px' height='70px' src=$2>";
													$image = preg_replace($pattern, $replacement, $image);
													if(!$i==$image){
														echo "<a href='". $url. "' target='_blank'>" . $image . '</a>';
														$i=$image;
													}
												}
											}
											?>
									
									</td>
									<td>
										<table>
											<tr>
												<td>
													<label class="title">
														<b><?php  echo isset($title) ? $title : ''; ?></b>
													</label><br />
													<label class="url">
														<a href="http://<?php  echo substr($url ,0,100); ?>" target="_blank"><?php  echo substr($url ,0,100); ?></a>
													</label>
												</td>
											</tr>
											<tr>
												<td>
													<label class="desc">
														<?php echo isset($description) ? $description : ''; ?>
													</label><br />
												</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
					<?php
				} 
			}
			
			function gethashtags($text)
			{
				//Match the hashtags
				preg_match_all('/(^|[^a-z0-9_])#([a-z0-9_]+)/i', $text, $matchedHashtags);
				$hashtag = '';
				// For each hashtag, strip all characters but alpha numeric
				if(!empty($matchedHashtags[0])) {
				  foreach($matchedHashtags[0] as $match) {
					  $hashtag .= preg_replace("/[^a-z0-9]+/i", "", $match).',';
				  }
				}
				//to remove last comma in a string
			return rtrim($hashtag, ',');
			}
			
			function showPost($user_id)
			{   
			
				$result = queryMysql("SELECT * FROM hm_post WHERE post_hm_id = '$user_id' ORDER BY post_time DESC ");
				if(!$result) die($connection->error);

				if($result->num_rows)
				{
					while($row = $result->fetch_array(MYSQLI_ASSOC))
					{
						$post_content_dis = $row['post_content'];
						$post_attach_dis = $row['post_attach'];
						$post_at = $row['loc_id'];
						$post_friend = $row['friend_tag_id'];
						$post_hash = $row['hash_tag_id'];
						$post_feel = $row['working_id'];
						$post_time = $row['post_time'];
						
						$result1 = queryMysql("SELECT first_name, last_name, profile_pic FROM hm_profile WHERE hm_id = '$user_id' ");
						if(!$result1) die($connection->error);
						
						if($result1->num_rows)
						{
							$user = $result1->fetch_array(MYSQLI_ASSOC);
							
					 		$user_first_name = sanitizeString($user['first_name']);
							$user_last_name = sanitizeString($user['last_name']);
							$user_profile_pic = sanitizeString($user['profile_pic']);
						}
?>						
							<div id="s_plat" style="margin-top:5px;">
								<img src="images/profile_pic/<?php echo $user_profile_pic; ?>" align="left" style="margin:4px; height:33px; width:33px;"/>
								<span id="plat_header"><b style="text-transform:capitalize;"><?php echo $user_first_name . " " . $user_last_name;?></b><br><small style="font-size:10px;">On <?php echo $post_time; ?></small></span><b style="float:right; margin:10px;">More</b>
						
								<table id="post_info" style="width:98%;">
									<tr>
										<td id="post_1">
											<table style="margin:5px;">
												<tr>
													<?php $urldata = urlData($post_content_dis);
														if(!isset($urldata)){ echo '<div style="margin:8px;"><td>'. $urldata . '</td><div>'; }
													?>
												</tr>
												<tr>
													<td>
														<pre id="post_cont_dis"><?php echo linkify($post_content_dis); ?></pre>
													</td>
												</tr>
												<tr>
												<?php if($post_attach_dis !== ""){ ?>
													<td id="post_img_dis">
														<img id="post_pic_dis" src="images/post_pic/<?php echo $post_attach_dis; ?>" style="margin:10px; width:50%; box-shadow:1px 2px 3px gray; border:1px solid #E8E8E8;"/>
													</td>
												<?php } ?>
												</tr>
												<tr>
													<td>
														<table>
															<tr>
																<td>
																	<input name="vote_post" value="Vote" type="button" style="cursor:pointer;"/>
																</td>
																<td>
																	<input name="comment_post" value="Comment" type="button" style="cursor:pointer;"/>
																</td>
																<td>
																	<input name="share_post" value="Share" type="button" style="cursor:pointer;"/>
																</td>
																<td></td><td></td>
																<td>
																	<small><a>21 Votes</a>  <a>9 Reviews</a> <a>4 Shares</a></small>
																</td>
															</tr>
														</table>
													</td>
												</tr>
											</table>
										</td>
									</tr>
								</table>
							</div>
<?php 	
					
					}
				}
			}
					

?>
