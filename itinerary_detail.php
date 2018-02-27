<?php
date_default_timezone_get('Asia/Jakarta');
include_once('./db.php');
include_once('./classes/login_class.php');
$url = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$des = $_GET['des'];

$user_id = DB::query('SELECT user_id FROM login_tokens WHERE token=:token', array(':token'=>sha1($_COOKIE['FCID'])))[0]['user_id'];

$id = DB::query('SELECT id FROM t_master_itineraries WHERE destination=:destination order by id desc', array(':destination'=>$des))[0]['id'];
if(!DB::query('SELECT destination FROM t_master_itineraries WHERE destination=:destination AND user_id=:user_id', array(':destination'=>$des, ':user_id'=>$user_id))) {
    $dArrive = $_GET['arrive'];
    $dDepart = $_GET['depart'];
    $day = $_GET['day'];
    $ppl = $_GET['ppl'];

    DB::query('INSERT INTO t_master_itineraries VALUES (\'\', :destination, :day_arrive, :day_depart, :day_count, :person_count, :user_id)', array(':destination'=>$des, ':day_arrive'=>$dArrive, ':day_depart'=>$dDepart, ':day_count'=>$day, ':person_count'=>$ppl, ':user_id'=>$user_id));
    echo "<script type=text/javascript>alert('Success')</script>";
    echo "<script>redirect('$url')</script>";
} else {
	$lat 		 = DB::query('SELECT * FROM t_master_destination WHERE user_id=:user_id ORDER BY id DESC', array(':user_id'=>$user_id))[0]['lat'];
	$long 		 = DB::query('SELECT * FROM t_master_destination WHERE user_id=:user_id ORDER BY id DESC', array(':user_id'=>$user_id))[0]['long'];
	$destination = DB::query('SELECT * FROM t_master_destination WHERE user_id=:user_id ORDER BY id DESC', array(':user_id'=>$user_id))[0]['location_name'];
	$destination = str_replace(" ", "+", $destination);
	$daycount 	 = DB::query('SELECT day_count FROM t_master_itineraries WHERE id=:id', array(':id'=>$id))[0]['day_count'];
	$dayarrive 	 = DB::query('SELECT day_arrive FROM t_master_itineraries WHERE id=:id', array(':id'=>$id))[0]['day_arrive'];
	$daydepart 	 = DB::query('SELECT day_depart FROM t_master_itineraries WHERE id=:id', array(':id'=>$id))[0]['day_depart'];
	$daystep = "";
	$plandate = "";
	if ($daycount == '1') {
		$daystep .= "<a href='#' style='background-color:#0095da;color:#fff;padding:7px 14px 7px 14px;border-radius:30px;font-size:12px;font-weight:700;'>Day ".$dayCount."</a>
					&nbsp";
		$day = date('l', strtotime($daydepart));
		$date = date('d', strtotime($daydepart));
		$plandate .= "<button class='dropbtn' style='height:40px; width:270px; text-align:left;'>
							Day ".$daycount.", ".$day.", ".$date."
							<span class='fa fa-caret-down fa-lg' style='font-size:14px; position:absolute;right:0px;top:16px;'>
								&nbsp&nbsp&nbsp&nbsp&nbsp
							</span>
							&nbsp&nbsp
						</button>";
	} else {
		$date = date('d', strtotime($daydepart));
		$month = date('M', strtotime($daydepart));
		$formatbulan = date('m', strtotime($daydepart));
		$year = date('Y', strtotime($daydepart));
		$monthList = array(
			'Jan' => 'January',
			'Feb' => 'February',
			'Mar' => 'March',
			'Apr' => 'April',
			'May' => 'May',
			'Jun' => 'June',
			'Jul' => 'July',
			'Aug' => 'August',
			'Sep' => 'September',
			'Oct' => 'October',
			'Nov' => 'November',
			'Dec' => 'December'
		);
		for ($i=1;$i<=$daycount;$i++) {
			if ($i == '1') {
				$daystep .= "<a href='#' style='background-color:#0095da;color:#fff;padding:7px 14px 7px 14px;border-radius:30px;font-size:12px;font-weight:700;'>Day ".$i."</a>
							 &nbsp";
				$date = $date;
				$dt = $year."-".$formatbulan."-".$date;
				$day = date('l', strtotime($dt));
				$plandate .= "<button class='dropbtn' style='height:40px; width:270px; text-align:left;'>
								Day ".$i.", ".$day.", ".$date." ".$monthList[$month]." ".$year."
								<span class='fa fa-caret-down fa-lg' style='font-size:14px; position:absolute;right:0px;top:16px;'>
									&nbsp&nbsp&nbsp&nbsp&nbsp
								</span>
								&nbsp&nbsp
							  </button>";
			} else {
				$daystep .= "<a href='#' style='background-color:#0095da;color:#fff;padding:7px 14px 7px 14px;border-radius:30px;font-size:12px;font-weight:700;'>Day ".$i."</a>
							 &nbsp";
				$date = $date+1;
				$dt = $year."-".$formatbulan."-".$date;
				$day = date('l', strtotime($dt));
				$plandate .= "<div class='dropdown-content' style='text-align:left;'>
								<a href='#'>Day ".$i.", ".$day.", ".$date." ".$monthList[$month]." ".$year."</a>
							  <div>";
			}
		}
	}
	
	$GetAttractionAPI = "https://api.foursquare.com/v2/venues/explore?near=".$destination."&radius=100000&sortByDistance=1&oauth_token=DU4T4OYVI03J1EA5YBMYO5CJA4BFXU4XZWIP2VW0YBWKY5OK&v=20180206";
	$responseAttraction = json_decode(file_get_contents($GetAttractionAPI));
	$responseStatus = $responseAttraction->meta->code;
	$loc = "";
	$locdetail = "";
	
	if ($responseStatus == "200") {
		$groups = $responseAttraction->response->groups;
		foreach ($groups as $group) {
			$items = $group->items;
			foreach ($items as $item) {
				$placeid		= $item->venue->id;
				$name 			= $item->venue->name;
				if (isset($item->venue->location->formattedAddress[0])) {
					$address = $item->venue->location->formattedAddress[0];
				} else {
					$address = "-";
				}
				$lat 			= $item->venue->location->lat;
				$lng 			= $item->venue->location->lng;
				$cc 			= $item->venue->location->cc;
				if (isset($item->venue->location->city)) {
					$city = $item->venue->location->city;
				} else {
					$city = "-";
				}
				if (isset($item->venue->location->state)) {
					$state = $item->venue->location->state;
				} else {
					$state = "-";
				}
				$country 		= $item->venue->location->country;
				$categories 	= $item->venue->categories[0]->name;
				$prefix			= $item->venue->categories[0]->icon->prefix;
				$suffix			= $item->venue->categories[0]->icon->suffix;
				if (isset($item->venue->hours->isOpen)) {
					$isOpen = $item->venue->hours->isOpen;
				} else {
					$isOpen = "-";
				}
				
				// DB::query('
					// INSERT INTO itenerariesdetail VALUES (\'\', :itenearies_id, :itenearies_name, :itenearies_lat, :itenearies_long, 
					// :itenearies_address, :itenearies_cc, :itenearies_city, :itenearies_state, :itenearies_country, 
					// :itenearies_categories, :itenearies_prefix, :itenearies_suffix, :itenearies_isopen, :itenearies_placeid)', 
					// array(':itenearies_id'=>$id, ':itenearies_name'=>$name, ':itenearies_lat'=>$lat, ':itenearies_long'=>$lng, 
					// ':itenearies_address'=>$address, ':itenearies_cc'=>$cc, ':itenearies_city'=>$city, ':itenearies_state'=>$state, 
					// ':itenearies_country'=>$country, ':itenearies_categories'=>$categories, ':itenearies_prefix'=>$prefix, 
					// ':itenearies_suffix'=>$suffix, ':itenearies_isopen'=>$isOpen, ':itenearies_placeid'=>$placeid));
			}
		}
	} else {
		echo "Gagal";
	}
	$total = DB::query('SELECT * FROM itenerariesdetail WHERE itenearies_id=:itenearies_id limit 6', array(':itenearies_id'=>$id));
echo $total;
	for ($i=0;$i<$total;$i++) {
		if ($i == '1' || $i == '4') {
			$restaurantname = DB::query('SELECT itenearies_name FROM itenerariesdetail WHERE itenearies_id=:itenearies_id and itenearies_categories like "%Restaurant%"', array(':itenearies_id'=>$id))[0]['itenearies_name'];
			$restcategories = DB::query('SELECT itenearies_categories FROM itenerariesdetail WHERE itenearies_id=:itenearies_id and itenearies_categories like "%Restaurant%"', array(':itenearies_id'=>$id))[0]['itenearies_categories'];
			$restaddress = DB::query('SELECT itenearies_address FROM itenerariesdetail WHERE itenearies_id=:itenearies_id and itenearies_categories like "%Restaurant%" order by id', array(':itenearies_id'=>$id))[$i]['itenearies_address'];
			$restcity = DB::query('SELECT itenearies_city FROM itenerariesdetail WHERE itenearies_id=:itenearies_id and itenearies_categories like "%Restaurant%" order by id', array(':itenearies_id'=>$id))[$i]['itenearies_city'];
			$reststate = DB::query('SELECT itenearies_state FROM itenerariesdetail WHERE itenearies_id=:itenearies_id and itenearies_categories like "%Restaurant%" order by id', array(':itenearies_id'=>$id))[$i]['itenearies_state'];
			$restcountry = DB::query('SELECT itenearies_country FROM itenerariesdetail WHERE itenearies_id=:itenearies_id and itenearies_categories like "%Restaurant%" order by id', array(':itenearies_id'=>$id))[$i]['itenearies_country'];
			$restisopen = DB::query('SELECT itenearies_isopen FROM itenerariesdetail WHERE itenearies_id=:itenearies_id and itenearies_categories like "%Restaurant%"', array(':itenearies_id'=>$id))[0]['itenearies_isopen'];
			if ($restisopen == '1') {
				$restisopen = "Open Now";
			} else {
				$restisopen = "Closed";
			}
			
			$distance = "<tr>
							<td style='padding-top:0px; padding-left:20px;'>
								<div style='border-left: 2px solid #636363; height:30px; width:270px; margin-left:25px;'>
									<div style='padding: 8px 10px 10px 10px; text-align: left;'>
										<a style='font-size:10px; color:#636363; font-weight: 500;'>
											<img src='images/walking.png' style='width:4%;'>
											&nbsp 10 minutes
										</a>
									</div>
								</div>
							</td>
						 </tr>";
			
			$loc .= " <tr>
						<td style='padding-top:0px'>
							<div style='background-color:#fff; border-radius:30px; height:36px; width:160px; margin-left:10px;'>

								<div style='padding: 0px 10px 10px 0px; text-align: left;'>
								   <img src='images/piccolobuco_sq.jpg' style='width:36px; border-radius:1000px;'>&nbsp&nbsp&nbsp&nbsp
									<a style='font-size:13px; color:#636363; font-weight: 500; padding-top:10px;' class='soap-icon-breakfast'>&nbsp&nbspPiccolo Buco</a>
								</div>
							</div>
						</td>
					  </tr>
					 ".$distance."";
					 
			$locdetail .= "<div style='border-style: solid; border:0px solid #fff; border-color:#222; background-color: #fff; padding: 0px 0px 0px 10px;  border-radius:8px;'>
						<table style='text-align:center; border:0px solid #000; width:100%;'>
							<tr>
								<th rowspan='4' width='300px'>
									<a href='detail_attraction.php'>
										<img src='images/vaticangarden.jpg' width='300px;'>
									</a>
								</th>
								<th colspan='2' style='color:#343434; font-size:16px; font-weight:500; padding: 10px 10px 10px 20px; text-align:left;' class='soap-icon-departure'>
									<a href='detail_attraction.php'>".$restaurantname."</a><br>
									<img src='images/stars.png' style='width:10%;'>
								</th>
								<th rowspan='3' style='background-color:#fafafa; width:220px;vertical-align: text-top; font-weight:700; padding: 10px 10px 10px 10px; border-top-right-radius:8px; border-bottom-right-radius:8px;'>
									Category:<br>
									<a style='font-weight:500;'>
									".$restcategories."
									</a>
									<br><br><br>
									Opening hours:<br>
									<a style='font-weight:500;'>".$restisopen."</a>
								</th>
							</tr>
							<tr>
								<td colspan='2' style='color:#636363; font-size:13px; font-weight:400; padding: 10px 10px 10px 20px; text-align:left;'>
									Vatican City is a walled sovereign city-state within Rome it covers 110 acres it is the headquarters of the Catholic Church and the home of the Pope. The Vatican Gardens, as the name suggests, are gardens within this walled enclave. The gardens are located in the south and northeastern areas of the city and cover about 50 acres, almost half of the Vatican ...
									<br>
									<a href='#' style='color:#625f93; font-size:12px;'><i>view detail</i></a>
								</td>
							</tr>
							<tr>
								<td colspan='2' style='color:#636363; font-size:13px; font-weight:400; padding: 10px 10px 10px 20px; text-align:left;'>
									".$restaddress.", ".$restcity.", ".$reststate.", ".$restcountry."
								</td>
							</tr>
							<tr>
								<td style='color:#636363; font-size:13px; font-weight:400; padding: 10px 10px 10px 20px; text-align:left;'>
									<a href='#' style='color:#625f93; font-size:12px;'>
										&nbsp&nbsp show direction
									</a>
									&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp
									<span onclick='nearbyrestaurant()' data-target='#nearby-restaurant' class='soap-popupbox soap-icon-breakfast' style='cursor:pointer;background-color: #0095da; padding: 7px 14px 7px 14px; border-radius:30px; font-size:12px; font-weight:400; color:#fff;'>
										&nbsp find nearby restaurant
									</span>
								</td>
								<td style='color:#636363; font-size:13px; font-weight:400;'>
									<a href='#' style='margin-right:10px;'>remove</a>
									<a href='#change-attraction' class='soap-popupbox' >change</a>
								</td>
							</tr>
						</table>
					   </div>
					   <!--
					   <div style='border-style: solid; border:0px solid #fff; border-color:#222; padding: 0px 0px 0px 40px; margin-left:50px;'>
						<table style='text-align:center; border-left:2.5px solid #636363;'>
							<tr>
								<td style='padding-left:10px; height:80px;'>
									<a href='#'><img src='images/walking.png' style='padding-right:10px; padding-bottom:10px;'>0.9 km, 13 minutes walking</a><br>
									<a href='#'><img src='images/car.png' style='padding-right:10px; width:16%;'>0.9 km, 8 minutes by car</a>
								</td>
							</tr>
						</table>
					   </div>
					   -->
					   <script src='https://code.jquery.com/jquery-2.2.4.js' integrity='sha256-iT6Q9iMJYuQiMWNd9lDyBUStIq/8PuOW33aOqmvFpqI=' crossorigin='anonymous'></script>
					   <script>
							function nearbyrestaurant(lat,long) {
								$.ajax({
									type: 'POST',
									url: 'includes/find-nearby-restaurant.php',
									data: {lat:lat,long:long},
									cache: false,
									success: function(data) {
										$('#nearby-restaurant').html(data);
									},
									error: function(err) {
										alert(err);
									}
								});
							}
					   </script>";
			
		} else {
			$venuename 	 = DB::query('SELECT itenearies_name FROM itenerariesdetail WHERE itenearies_id=:itenearies_id and itenearies_categories not like "%Restaurant%" order by id', array(':itenearies_id'=>$id))[$i]['itenearies_name'];
			$categoriesx = DB::query('SELECT itenearies_categories FROM itenerariesdetail WHERE itenearies_id=:itenearies_id and itenearies_categories not like "%Restaurant%" order by id', array(':itenearies_id'=>$id))[$i]['itenearies_categories'];
			$addressx = DB::query('SELECT itenearies_address FROM itenerariesdetail WHERE itenearies_id=:itenearies_id and itenearies_categories not like "%Restaurant%" order by id', array(':itenearies_id'=>$id))[$i]['itenearies_address'];
			$cityx = DB::query('SELECT itenearies_city FROM itenerariesdetail WHERE itenearies_id=:itenearies_id and itenearies_categories not like "%Restaurant%" order by id', array(':itenearies_id'=>$id))[$i]['itenearies_city'];
			$statex = DB::query('SELECT itenearies_state FROM itenerariesdetail WHERE itenearies_id=:itenearies_id and itenearies_categories not like "%Restaurant%" order by id', array(':itenearies_id'=>$id))[$i]['itenearies_state'];
			$countryx = DB::query('SELECT itenearies_country FROM itenerariesdetail WHERE itenearies_id=:itenearies_id and itenearies_categories not like "%Restaurant%" order by id', array(':itenearies_id'=>$id))[$i]['itenearies_country'];
			$isopenx = DB::query('SELECT itenearies_isopen FROM itenerariesdetail WHERE itenearies_id=:itenearies_id and itenearies_categories not like "%Restaurant%" order by id', array(':itenearies_id'=>$id))[$i]['itenearies_isopen'];
			if ($isopenx == '1') {
				$isopenx = "Open Now";
			} else {
				$isopenx = "Closed";
			}
			
			
			$distance = "<tr>
							<td style='padding-top:0px; padding-left:20px;'>
								<div style='border-left: 2px solid #636363; height:30px; width:270px; margin-left:25px;'>
									<div style='padding: 8px 10px 10px 10px; text-align: left;'>
										<a style='font-size:10px; color:#636363; font-weight: 500;'>
											<img src='images/walking.png' style='width:4%;'>
											&nbsp 10 minutes
										</a>
									</div>
								</div>
							</td>
						 </tr>";
			$loc .= "<tr>
						<td style='padding-top:10px'>
							<div style='background-color:#fff; border-radius:4px; height:50px; width:270px;'>
								<div style='padding: 11px 10px 50px 10px; text-align: left;'>
									<a href='detail_attraction.php'>
									
									</a>
									&nbsp&nbsp
									<a href='detail_attraction.php' style='font-size:; color:#636363; font-weight: 500; padding-top:50px;' class='soap-icon-departure'>
										&nbsp ".$venuename."
									</a>
									<br>
								</div>
							</div>
						</td>
					 </tr>
					 ".$distance."";
					 
			$locdetail .= "<div style='border-style: solid; border:0px solid #fff; border-color:#222; background-color: #fff; padding: 0px 0px 0px 10px;  border-radius:8px;'>
						<table style='text-align:center; border:0px solid #000; width:100%;'>
							<tr>
								<th rowspan='4' width='300px'>
									<a href='detail_attraction.php'>
										<img src='images/vaticangarden.jpg' width='300px;'>
									</a>
								</th>
								<th colspan='2' style='color:#343434; font-size:16px; font-weight:500; padding: 10px 10px 10px 20px; text-align:left;' class='soap-icon-departure'>
									<a href='detail_attraction.php'>".$venuename."</a><br>
									<img src='images/stars.png' style='width:10%;'>
								</th>
								<th rowspan='3' style='background-color:#fafafa; width:220px;vertical-align: text-top; font-weight:700; padding: 10px 10px 10px 10px; border-top-right-radius:8px; border-bottom-right-radius:8px;'>
									Category:<br>
									<a style='font-weight:500;'>
									".$categoriesx."
									</a>
									<br><br><br>
									Opening hours:<br>
									<a style='font-weight:500;'>".$isopenx."</a>
								</th>
							</tr>
							<tr>
								<td colspan='2' style='color:#636363; font-size:13px; font-weight:400; padding: 10px 10px 10px 20px; text-align:left;'>
									Vatican City is a walled sovereign city-state within Rome it covers 110 acres it is the headquarters of the Catholic Church and the home of the Pope. The Vatican Gardens, as the name suggests, are gardens within this walled enclave. The gardens are located in the south and northeastern areas of the city and cover about 50 acres, almost half of the Vatican ...
									<br>
									<a href='#' style='color:#625f93; font-size:12px;'><i>view detail</i></a>
								</td>
							</tr>
							<tr>
								<td colspan='2' style='color:#636363; font-size:13px; font-weight:400; padding: 10px 10px 10px 20px; text-align:left;'>
									".$addressx.", ".$cityx.", ".$statex.", ".$countryx."
								</td>
							</tr>
							<tr>
								<td style='color:#636363; font-size:13px; font-weight:400; padding: 10px 10px 10px 20px; text-align:left;'>
									<a href='#' style='color:#625f93; font-size:12px;'>
										&nbsp&nbsp show direction
									</a>
									&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp
									<span onclick='nearbyrestaurant()' data-target='#nearby-restaurant' class='soap-popupbox soap-icon-breakfast' style='cursor:pointer;background-color: #0095da; padding: 7px 14px 7px 14px; border-radius:30px; font-size:12px; font-weight:400; color:#fff;'>
										&nbsp find nearby restaurant
									</span>
								</td>
								<td style='color:#636363; font-size:13px; font-weight:400;'>
									<a href='#' style='margin-right:10px;'>remove</a>
									<a href='#change-attraction' class='soap-popupbox' >change</a>
								</td>
							</tr>
						</table>
					   </div>
					   <!--
					   <div style='border-style: solid; border:0px solid #fff; border-color:#222; padding: 0px 0px 0px 40px; margin-left:50px;'>
						<table style='text-align:center; border-left:2.5px solid #636363;'>
							<tr>
								<td style='padding-left:10px; height:80px;'>
									<a href='#'><img src='images/walking.png' style='padding-right:10px; padding-bottom:10px;'>0.9 km, 13 minutes walking</a><br>
									<a href='#'><img src='images/car.png' style='padding-right:10px; width:16%;'>0.9 km, 8 minutes by car</a>
								</td>
							</tr>
						</table>
					   </div>
					   -->
					   <script src='https://code.jquery.com/jquery-2.2.4.js' integrity='sha256-iT6Q9iMJYuQiMWNd9lDyBUStIq/8PuOW33aOqmvFpqI=' crossorigin='anonymous'></script>
					   <script>
							function nearbyrestaurant(lat,long) {
								$.ajax({
									type: 'POST',
									url: 'includes/find-nearby-restaurant.php',
									data: {lat:lat,long:long},
									cache: false,
									success: function(data) {
										$('#nearby-restaurant').html(data);
									},
									error: function(err) {
										alert(err);
									}
								});
							}
					   </script>";
		}
	}
	
	// if ($AllDetail) {
		// foreach ($AllDetail as $row) {
			// $id = $row['id'];
			// $itenearies_id 			= $row['itenearies_id'];
			// $itenearies_name		= $row['itenearies_name'];
			// $itenearies_lat 		= $row['itenearies_lat'];
			// $itenearies_long 		= $row['itenearies_long'];
			// $itenearies_address 	= $row['itenearies_address'];
			// $itenearies_cc 			= $row['itenearies_cc'];
			// $itenearies_city 		= $row['itenearies_city'];
			// $itenearies_state 		= $row['itenearies_state'];
			// $itenearies_country 	= $row['itenearies_country'];
			// $itenearies_categories 	= $row['itenearies_categories'];
			// $itenearies_prefix 		= $row['itenearies_prefix'];
			// $itenearies_suffix 		= $row['itenearies_suffix'];
			// $itenearies_isopen 		= $row['itenearies_isopen'];
			// $itenearies_placeid 	= $row['itenearies_placeid'];
			
			// $name_length	= strlen($itenearies_name);
				
			// if ($name_length <= '23') {
				// $fontsize = '14px';
			// } else {
				// $fontsize = '11px';
			// }
			
			// if ($itenearies_isopen == "1") {
				// $itenearies_isopen = "Open Now";
			// } else {
				// $itenearies_isopen = "Closed";
			// }
			
			// $loc .= "<tr>
						// <td style='padding-top:10px'>
							// <div style='background-color:#fff; border-radius:4px; height:50px; width:270px;'>
								// <div style='padding: 11px 10px 50px 10px; text-align: left;'>
									// <a href='detail_attraction.php'>
										// <img src='".$itenearies_prefix.$itenearies_prefix."' style='width:23px; border-radius:1000px;'>
									// </a>
									// &nbsp&nbsp
									// <a href='detail_attraction.php' style='font-size:".$fontsize."; color:#636363; font-weight: 500; padding-top:50px;' class='soap-icon-departure'>
										// &nbsp".$itenearies_name."
									// </a>
									// <br>
								// </div>
							// </div>
						// </td>
					 // </tr>
					 // <!--
					 // <tr>
						// <td style='padding-top:0px; padding-left:20px;'>
							// <div style='border-left: 2px solid #636363; height:30px; width:270px; margin-left:25px;'>
								// <div style='padding: 8px 10px 10px 10px; text-align: left;'>
									// <a style='font-size:10px; color:#636363; font-weight: 500;'>
										// <img src='images/walking.png' style='width:4%;'>
										// &nbsp 10 minutes
									// </a>
								// </div>
							// </div>
						// </td>
					 // </tr>
					 // -->";
				
		// $locdetail .= "<div style='border-style: solid; border:0px solid #fff; border-color:#222; background-color: #fff; padding: 0px 0px 0px 10px;  border-radius:8px;'>
						// <table style='text-align:center; border:0px solid #000; width:100%;'>
							// <tr>
								// <th rowspan='4' width='300px'>
									// <a href='detail_attraction.php'>
										// <img src='images/vaticangarden.jpg' width='300px;'>
									// </a>
								// </th>
								// <th colspan='2' style='color:#343434; font-size:16px; font-weight:500; padding: 10px 10px 10px 20px; text-align:left;' class='soap-icon-departure'>
									// <a href='detail_attraction.php'>".$itenearies_name."</a><br>
									// <img src='images/stars.png' style='width:10%;'>
								// </th>
								// <th rowspan='3' style='background-color:#fafafa; width:220px;vertical-align: text-top; font-weight:700; padding: 10px 10px 10px 10px; border-top-right-radius:8px; border-bottom-right-radius:8px;'>
									// Category:<br>
									// <a style='font-weight:500;'>
									// ".$itenearies_categories."
									// </a>
									// <br><br><br>
									// Opening hours:<br>
									// <a style='font-weight:500;'>".$itenearies_isopen."</a>
								// </th>
							// </tr>
							// <tr>
								// <td colspan='2' style='color:#636363; font-size:13px; font-weight:400; padding: 10px 10px 10px 20px; text-align:left;'>
									// Vatican City is a walled sovereign city-state within Rome it covers 110 acres it is the headquarters of the Catholic Church and the home of the Pope. The Vatican Gardens, as the name suggests, are gardens within this walled enclave. The gardens are located in the south and northeastern areas of the city and cover about 50 acres, almost half of the Vatican ...
									// <br>
									// <a href='#' style='color:#625f93; font-size:12px;'><i>view detail</i></a>
								// </td>
							// </tr>
							// <tr>
								// <td colspan='2' style='color:#636363; font-size:13px; font-weight:400; padding: 10px 10px 10px 20px; text-align:left;'>
									// ".$itenearies_address.", ".$itenearies_city.", ".$itenearies_state.", ".$itenearies_country."
								// </td>
							// </tr>
							// <tr>
								// <td style='color:#636363; font-size:13px; font-weight:400; padding: 10px 10px 10px 20px; text-align:left;'>
									// <a href='#' style='color:#625f93; font-size:12px;'>
										// &nbsp&nbsp show direction
									// </a>
									// &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp
									// <span onclick='nearbyrestaurant(\"$itenearies_lat\",\"$itenearies_long\")' data-target='#nearby-restaurant' class='soap-popupbox soap-icon-breakfast' style='cursor:pointer;background-color: #0095da; padding: 7px 14px 7px 14px; border-radius:30px; font-size:12px; font-weight:400; color:#fff;'>
										// &nbsp find nearby restaurant
									// </span>
								// </td>
								// <td style='color:#636363; font-size:13px; font-weight:400;'>
									// <a href='#' style='margin-right:10px;'>remove</a>
									// <a href='#change-attraction' class='soap-popupbox' >change</a>
								// </td>
							// </tr>
						// </table>
					   // </div>
					   // <!--
					   // <div style='border-style: solid; border:0px solid #fff; border-color:#222; padding: 0px 0px 0px 40px; margin-left:50px;'>
						// <table style='text-align:center; border-left:2.5px solid #636363;'>
							// <tr>
								// <td style='padding-left:10px; height:80px;'>
									// <a href='#'><img src='images/walking.png' style='padding-right:10px; padding-bottom:10px;'>0.9 km, 13 minutes walking</a><br>
									// <a href='#'><img src='images/car.png' style='padding-right:10px; width:16%;'>0.9 km, 8 minutes by car</a>
								// </td>
							// </tr>
						// </table>
					   // </div>
					   // -->
					   // <script src='https://code.jquery.com/jquery-2.2.4.js' integrity='sha256-iT6Q9iMJYuQiMWNd9lDyBUStIq/8PuOW33aOqmvFpqI=' crossorigin='anonymous'></script>
					   // <script>
							// function nearbyrestaurant(lat,long) {
								// $.ajax({
									// type: 'POST',
									// url: 'includes/find-nearby-restaurant.php',
									// data: {lat:lat,long:long},
									// cache: false,
									// success: function(data) {
										// $('#nearby-restaurant').html(data);
									// },
									// error: function(err) {
										// alert(err);
									// }
								// });
							// }
					   // </script>";
		// }
	// } 
	// End of Get Nearby Search Place From Destination With Google Maps API//
	
    // echo "<script type=text/javascript>alert('Cannot input same destination from your itineraries')</script>";
}
?>

<!DOCTYPE html>
<!--[if IE 8]>          <html class="ie ie8"> <![endif]-->
<!--[if IE 9]>          <html class="ie ie9"> <![endif]-->
<!--[if gt IE 9]><!-->  <html> <!--<![endif]-->
<head>
    <!-- Page Title -->
    <title>fritugo - get unlimited, create your own dream itinerary for your future trip</title>

    <?php
        include "includes/common.php";
    ?>

    <style>
.dropbtn {
    background-color: #0095da;
    color: white;
    font-size: 14px;
    border: none;
    cursor: pointer;
    border-radius:4px;
}

.dropdown {
    position: relative;
    display: inline-block;
}

.dropdown-content {
    display: none;
    position: absolute;
    background-color: #f9f9f9;
    min-width: 270px;
    box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
    border-radius:4px;
    z-index: 1;
}

.dropdown-content a {
    color: black;
    padding: 12px 16px;
    text-decoration: none;
    display: block;
}

.dropdown-content a:hover {background-color: #f1f1f1}

.dropdown:hover .dropdown-content {
    display: block;
}

.dropdown:hover .dropbtn {
    background-color: #0095da;
}
</style>

</head>
	<body>
       <div id="page-wrapper">
			<section id="content" style="min-height:700px;">
				<header id="header" class="navbar-static-top">
					<?php include "includes/menu-loggedin.php"; ?>
					<?php include "includes/menu_mobile.php"; ?>
					<?php include "includes/signup.php"; ?>
					<?php include "includes/login.php"; ?>
					<?php include "includes/share_itinerary.php"; ?>
					<?php include "includes/find-nearby-restaurant.php"; ?>
					<?php include "includes/success-save.php"; ?>
					<?php include "includes/change-attraction.php"; ?>
				</header>
				<div style="background-color:#343434; width:100%; height:70px; position:relative; z-index:99;">
					<div class="container">
						<div style="border-style: solid; border:1px; border-color:#222; padding: 30px 20px 30px 30px; text-align:center; ">
							<h1 style="color:#fff; font-size:20px; margin-top:-5px; font-weight:700;">Your personal travel guide</h1>
						</div>                    
					</div>
				</div>
				<div style="background-color:#fff; width:100%; height:370px; position:relative; z-index:99;">
					<div class="container">
						<div style="border-style: solid; border:1px; border-color:#222; padding: 30px 70px 30px 60px; ">
							<table style="text-align:center; border:0px solid #000; width:100%; margin-top:-15px;">
								<tr>
									<th rowspan="3" width="350px;">
										<img src="images/result_iti_pic.jpg" style="padding-right:20px; width:100%;">
										<a href="#" style="font-size:13px; font-weight:400; margin-top:10px;"><br>
											<img src="images/changeimg.png" style="width:4%;">&nbsp change cover image
										</a>
										<br><br>
										<div style="border-style: solid; border:1px; border-color:#222; text-align:left; ">
											<a href="#" style="border:1px solid #d8d8d8; padding: 7px 14px 7px 14px; border-radius:30px; font-size:12px; font-weight:400;">museum &nbsp&nbsp x</a>
											&nbsp
											<a href="#" style="border:1px solid #d8d8d8; padding: 7px 14px 7px 14px; border-radius:30px; font-size:12px; font-weight:400;">sightseeing &nbsp&nbsp x</a>
											&nbsp
											<a href="#" style="border:1px solid #d8d8d8; padding: 7px 14px 7px 14px; border-radius:30px; font-size:12px; font-weight:400;">couple &nbsp&nbsp x</a>
											&nbsp
											<a href="#" href="#"><img src="images/add.png" style="width:13px;"></a>
										</div>
									</th>
									<th colspan="2" width="1000px" style="color:#343434; font-size:16px; font-weight:700; padding: 10px 10px 10px 0px; text-align:left;">
										<?php
											if (Login::isLoggedIn()) {
												$destination = DB::query(
													'SELECT destination
													FROM t_master_itineraries
													WHERE destination=:destination', array(':destination'=>$des)
												)[0]['destination'];

												$dayCount = DB::query(
													'SELECT day_count
													FROM t_master_itineraries
													WHERE id=:id', array(':id'=>$id)
												)[0]['day_count'];

												if ($dayCount == '1') {
													echo "Trip to " . $destination . " in " . $dayCount . " day";
												} else {
													echo "Trip to " . $destination . " in " . $dayCount . " days";
												}
											}
										?> 
										&nbsp
										<a href="#"><img src="images/pencil.png" style="padding-right:20px; width:12%;"></a><br>
										<p style="font-size:11px; font-weight:400;"><i>by</i>&nbsp&nbsp
											<a href="dashboard.php" style="color:#0095da;">
												<i>
													<?php
														if (Login::isLoggedIn()) {
															$username = DB::query(
																'SELECT u.username
																FROM users u
																JOIN login_tokens lt ON lt.user_id = u.id
																WHERE lt.token = :token', array(':token'=>sha1($_COOKIE['FCID']))
																)[0]['username'];
															echo $username;
														}
													?>
												</i>
											</a>
										</p>
									</th>
									<th colspan="2" style="color:#4e4e4e; font-size:18px; font-weight:400; padding: 10px 10px 10px 10px; text-align:right;">
										<a href="#" style="font-size:13px;">
											<?php
												if (Login::isLoggedIn()) {
													$arrive = DB::query(
														'SELECT day_arrive
														FROM t_master_itineraries
														WHERE id=:id', array(':id'=>$id)
													)[0]['day_arrive'];
													$dayArrive = date_create($arrive);

													$depart = DB::query(
														'SELECT day_depart
														FROM t_master_itineraries
														WHERE id=:id', array(':id'=>$id)
													)[0]['day_depart'];
													$dayDepart = date_create($depart);
													echo date_format($dayDepart, 'd M') . " - " . date_format($dayArrive, 'd M Y');
												}
											?>
											&nbsp
											<img src="images/pencil.png" style="width:3%;">
										</a>
										<br>
										<a href="#" style="font-size:13px;">
											<?php
												if (Login::isLoggedIn()) {
													$adult = DB::query(
														'SELECT adult
														FROM t_master_itineraries
														WHERE id=:id', array(':id'=>$id)
													)[0]['adult'];
													
													$children = DB::query(
														'SELECT children
														FROM t_master_itineraries
														WHERE id=:id', array(':id'=>$id)
													)[0]['children'];
													
													$personCount = $adult+$children;

													if ($personCount == 1) {
														echo $personCount . " person";
													} else {
														echo $personCount . " people";
													}
												}
											?>
											&nbsp
											<img src="images/pencil.png" style="width:3%;">
										</a>
									</th>
								</tr>
								<tr>
									<td colspan="4" style="color:#636363; font-size:14px; padding: 10px 10px 10px 0px; text-align:justify; height:150px;">
										This pre-planned 3 days Rome itinerary will allow you to explore Rome's must-sees, landmarks, museums and attractions. See how you can make the best out of a 3 days stay in Rome as you make your way through the Sistine Chapel with its world famous ceiling fresco, the Vatican Museums, the Colosseum and all other Rome's attractions - religious and imperial. 
										<br><br>
										If you are planning a 3 days visit to Rome and are interested to visit its top attractions then this plan may well suit your needs.
										&nbsp
										<a href="#">
											<img src="images/pencil.png" style="width:1.7%;">
										</a>
									</td>
								</tr>
								<tr style="margin-top:-10px;">
									<td width="150px"></td>
									<td style="color:#343434; font-size:13px; font-weight:500; padding: 10px 10px 0px 10px; text-align:right;"></td>
									<td style="color:#343434; font-size:13px; font-weight:500; padding: 10px 10px 0px 10px; text-align:right;"></td>
									<td style="color:#636363; font-size:13px; font-weight:500; padding: 10px 10px 0px 10px; text-align:right;">
										<a href="#"><img src="images/goldcoin.png" style="width:32px;">&nbsp&nbsp
										<a href="policies.php" target="_blank" style="font-size:8px;"><i>what's this?</i></a><br><br>
										<a href="#success-save" class="soap-popupbox" style="border-radius:30px; background-color:#0095da; font-size:13px; padding:10px 30px 10px 30px; color:#fff; font-weight:600;">save</a>&nbsp&nbsp&nbsp
										<a href="#" class="soap-popupbox"><img src="images/download_icon.png" style="width:6%; padding-right:5px;"></a>&nbsp&nbsp&nbsp
										<a href="#" class="soap-popupbox"><img src="images/print_icon.png" style="width:6%; padding-right:5px;"></a>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp
										<a href="#" class="soap-icon-facebook circle" style="border: 1px solid #3b5998; background-color:#3b5998; color:#fff; "></a>&nbsp&nbsp&nbsp
										<a href="#" class="soap-icon-twitter circle" style="border: 1px solid #00aced; background-color:#00aced; color:#fff;"></a>&nbsp&nbsp&nbsp
										<a href="#" class="soap-icon-googleplus circle" style="border: 1px solid #d34836; background-color:#d34836; color:#fff;"></a>&nbsp&nbsp&nbsp
										<a href="#" class="soap-icon-pinterest circle" style="border: 1px solid #cb2027; background-color:#cb2027; color:#fff;"></a>&nbsp&nbsp&nbsp
										<a href="#" class="soap-icon-tumblr circle" style="border: 1px solid #35465c; background-color:#35465c; color:#fff;"></a>
									</td>
								</tr>
							</table>
						</div>                    
					</div>
				</div>
				<div style="background-color:#f0f0f0; width:100%; position:relative; z-index:99; margin-top:0px;">
					<div class="container">
						<div style="border-style: solid; border:1px; border-color:#222; padding: 30px 70px 30px 60px;">
							<table  style="text-align:center; border:0px solid #000; width:100%;">
								<tr>
									<td>
										<div class="dropdown">
											<?php echo $plandate;?>
										</div>
									</td>
									<th style="padding-left:10px;">
										<div style="background-color:#0095da; border-radius:4px; height:40px; width:750px;">
											<div style="padding: 10px 10px 10px 10px;">
												<a style="font-size:14px; color:#fff; font-weight: 500; padding-top:10px;">
													<?php
														if ($dayCount == '1') {
															$ket = "Day";
														} else {
															$ket = "Days";
														}
													?>
													Total Trip : <?php echo $dayCount." ".$ket;?>
												</a>
											</div>
										</div>
									</th>
								</tr>
							</table>
							<div style="height:400px;width:295px;overflow:auto;color:white;scrollbar-base-color:gold;"><br>
								<tr>
									<th></th>
									<th rowspan="5"></th>
								</tr>
								<?php echo $loc;?>
							</div>
							<div style="position:relative; margin-top:-400px; margin-left:285px;height:400px;width:280px;color:black;"><br>	
								<tr><th><img src="images/maps.jpg" style="width:748px;"></th></tr>
							</div>
							<br>
							<div style="position:relative;"><br>
								<tr>
									<th>
										<a href="#"><img src="images/add.png">&nbsp add attraction</a>&nbsp&nbsp&nbsp&nbsp&nbsp
										<a href="#"><img src="images/add.png">&nbsp add restaurant</a>
										&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp
										<a href="#" style="background-color: #f5b418; padding: 7px 14px 7px 14px; border-radius:30px; font-size:12px; font-weight:400; color:#fff;"><img src="images/carrental.png" style="width:1.5%;">&nbsp find rental car</a>
										&nbsp&nbsp&nbsp&nbsp&nbsp
										<a href="#" style="background-color: #5fdd5f; padding: 7px 14px 7px 14px; border-radius:30px; font-size:12px; font-weight:400; color:#fff;"><img src="images/event.png" style="width:1.5%;">&nbsp find event</a>
									</th>
								</tr>
							</div>
						</div>
					</div>
					<div style="width:100%; position:relative; z-index:99;">
						<div class="container">
							<div style="border-style: solid; border:1px; border-color:#222; padding: 10px 70px 30px 60px; ">
								<div style="border-style: solid; border:1px; border-color:#222; text-align:left; ">
									<?php echo $daystep;?>
									<a href="#"><img src="images/add.png" style="width:13px;"> Add day</a>
								</div>
							</div>   
							<div class="container">
								<?php echo $locdetail;?>
								<br>
								<div style="border-style: solid; border:1px; border-color:#222; padding: 10px 70px 30px 60px; ">
									<div style="border-style: solid; border:1px; border-color:#222; text-align:left; ">
										<a href="#" style="background-color:#0095da; color:#fff; padding: 7px 14px 7px 14px; border-radius:30px; font-size:12px; font-weight:700;">Day 1</a>
										&nbsp
										<a href="#" style="border:1px solid #d8d8d8; padding: 7px 14px 7px 14px; border-radius:30px; font-size:12px; font-weight:700;">Day 2</a>
										&nbsp
										<a href="#" style="border:1px solid #d8d8d8; padding: 7px 14px 7px 14px; border-radius:30px; font-size:12px; font-weight:700;">Day 3</a>
										&nbsp
										<a href="#"><img src="images/add.png" style="width:13px;"> Add day</a>
									</div>
								</div>             
							</div>
							<hr style="border-color:#d3d3d3;">
						</div>
						<hr>
						<div class="container">
							<img src="images/flighacco.jpg" style="width:100%; margin-top:-40px;">             
						</div>
						<div style="background-color:#f0f0f0; width:100%; position:relative; z-index:99; margin-top:10px;">
							<?php
								include "includes/popular_nearby_attractions.php";
							?>
						</div>
						<br><br><br>
					</div>
				</div>
			</section>
		</div>
        <?php
            include "includes/footer.php";
        ?>
	</body>
</html>

