<script>
    function redirect($target) {
        window.location.replace($target);
    }
</script>
<?php
	ob_start();
	include('./db.php');
	include('classes/login_class.php');

	if (Login::isLoggedIn()) {
		if (isset($_POST['submitPlan'])) {
			$destination 	= $_POST['destination'];
			$origin 		= $_POST['origin'];
			$fromDatex 		= $_POST['from-date'];
			$fromDate 		= date('Y-m-d', strtotime($fromDatex));
			$toDatex 		= $_POST['to-date'];
			$toDate 		= date('Y-m-d', strtotime($toDatex));
			$personCount 	= $_POST['person'];
			$budget 		= $_POST['budget']; 
			
			$arrive = date_create($fromDatex);
			$depart = date_create($toDatex);
			$dayDiff = date_diff($arrive, $depart);
			$dayCount = $dayDiff->format('%a') + 1;
			
			$personCount = explode(',', $personCount);
			
			$adult 	  = $personCount[0];
			$adult	  = explode(' adult', $adult);
			$adult	  = $adult[0];
			
			$children = $personCount[1];
			$children = explode(' children', $children);
			$children = $children[0];
			
			$destinationsx = str_replace(" ","+",$destination);
			
			$url = "http://maps.googleapis.com/maps/api/geocode/json?sensor=false&address=".$destinationsx;
			$lat_long = get_object_vars(json_decode(file_get_contents($url)));
			$lat = $lat_long['results'][0]->geometry->location->lat;
			$long = $lat_long['results'][0]->geometry->location->lng;
			
			$user_id = DB::query('SELECT user_id FROM login_tokens WHERE token=:token', array(':token'=>sha1($_COOKIE['FCID'])))[0]['user_id'];
			
			if (empty($_POST['from-date'])) {
				echo "<script>alert('Please pick your departure date...')</script>";
			} else if (empty($_POST['to-date'])) {
				echo "<script>alert('Please pick your arrival date...')</script>";
			} else if (empty($_POST['person'])) {
				echo "<script>alert('Please fill the person...')</script>";
			} elseif ($budget == '0') {
				echo "<script>alert('Please choose your estimate budget...')</script>";
			} else {
				DB::query('INSERT INTO t_master_itineraries VALUES (\'\', :destination, :day_arrive, :day_depart, :day_count, :adult, :user_id, :budget, :children)', array(':destination'=>$destination, ':day_arrive'=>$toDate, ':day_depart'=>$fromDate, ':day_count'=>$dayCount, ':adult'=>$adult, ':user_id'=>$user_id, ':budget'=>$budget, ':children'=>$children));
				DB::query('INSERT INTO t_master_destination VALUES (\'\', :destination, :lat, :long, :user_id)', array(':destination'=>$destination, ':lat'=>$lat, ':long'=>$long, ':user_id'=>$user_id));
				echo "<script type=text/javascript>alert('Success')</script>";
				echo "<script>redirect('itinerary_result.php?des=$destination&arrive=$toDate&depart=$fromDate&day=$dayCount&ppl=$personCount')</script>";
			}
		}
	} elseif (isset($_POST['submitPlan'])) {
		$des = $_POST['destination'];
		$pc = $_POST['person'];
		$fromDate = $_POST['from-date'];
		$toDate = $_POST['to-date'];

		$arrive = date_create($fromDate);
		$depart = date_create($toDate);
		$dayDiff = date_diff($arrive,$depart);
		$day = $dayDiff->format('%a') + 1;

		$dArrive = $arrive->format('d M Y');
		$dDepart = $depart->format('d M Y');
		
		echo "<script>redirect('itinerary_result.php?des=$des&arrive=$dArrive&depart=$dDepart&day=$day&ppl=$pc')</script>";
	}

	$slcopt = "<select class='form-group' style='margin-left:-3px;height:48.5px;' name='budget'>
				<option value=''>-- Choose Your Budget --</option>";
				
	$optx = DB::query('SELECT * FROM t_master_budget');
	
	// print_r($optx);
	if ($optx) {
		foreach ($optx as $rows) {
			$slcopt .= "<option value = '".$rows['id']."'>".$rows['budget']."</option>";
		}
	} else {
		
	}
	$slcopt .= "</select>";

?>


<!DOCTYPE html>
<html>
	<head>
		<!-- <meta http-equiv="refresh" content="0; url=<?php echo $page ?>"> -->
		<!-- Page Title -->
		<title>fritugo - get unlimited, create your own dream itinerary for your future trip</title>
		<?php include "includes/common.php";?>
		<style>
			/*custom font*/

			@import url(http://fonts.googleapis.com/css?family=Montserrat);
			/*basic reset*/
			* {
				margin: 0;
				padding: 0;
			}
			
			html {
				height: 100%;
			}
			
			body {
				font-family: "Myriad Pro", "Gill Sans", "Gill Sans MT", Calibri, sans-serif;
			}
			
			/*form styles*/
			#msform {
				width: 1050px;
				text-align: left;
				position: relative;
			}
			
			#msform fieldset {
				border-radius: 30px;
				box-sizing: border-box;
				width: 100%;
				position: absolute;
				/*stacking fieldsets above each other*/
				border-radius:30px;
			}
			
			/*Hide all except first fieldset*/
			#msform fieldset:not(:first-of-type) {
				display: none;
			}
			
			/*inputs*/
			#msform input, #msform textarea {
				padding: 15px;
				border: 0px solid #ccc;
				width: 100%;
				box-sizing: border-box;
				font-family: montserrat;
				color: #2C3E50;
				font-size: 13px;
			}
			
			/*buttons*/
			#msform .action-button {
				width: 100px;
				background: #27AE60;
				font-weight: bold;
				color: white;
				border: 0 none;
				border-radius: 1px;
				cursor: pointer;
			}
			
			#msform .action-button:hover, #msform .action-button:focus {
				box-shadow: 0 0 0 0px white, 0 0 0 0px #27AE60;
			}
			
			/*headings*/
			.fs-title {
				font-size: 15px;
				text-transform: uppercase;
				color: #2C3E50;
				margin-bottom: 10px;
			}
			
			.fs-subtitle {
				font-weight: normal;
				font-size: 13px;
				color: #666;
				margin-bottom: 20px;
			}
			
			/*progressbar*/
			#progressbar {
				margin-bottom: 30px;
				overflow: hidden;
				/*CSS counters to number the steps*/
				counter-reset: step;
			}
			
			#progressbar li {
				list-style-type: none;
				color: white;
				font-size: 9px;
				width: 33.33%;
				float: left;
				position: relative;
			}
			
			#progressbar li:before {
				content: counter(step);
				counter-increment: step;
				width: 20px;
				line-height: 20px;
				display: block;
				font-size: 10px;
				color: #333;
				background: white;
				border-radius: 3px;
			}
			
			/*progressbar connectors*/
			#progressbar li:after {
				content: '';
				width: 100%;
				height: 2px;
				background: white;
				position: absolute;
				left: -50%;
				top: 9px;
				z-index: -1; /*put it behind the numbers*/
			}
			
			#progressbar li:first-child:after {
				/*connector not needed before the first step*/
				content: none;
			}
			
			/*marking active/completed steps green*/
			/*The number of the step and the connector before it = green*/
			#progressbar li.active:before, #progressbar li.active:after {
				background: #27AE60;
				color: white;
			}

			.fullscreen-bg {
				top: 0;
				right: 0;
				bottom: 0;
				left: 0;
				overflow: hidden;
				z-index: -100;
			}

			.fullscreen-bg__video {
				top: 0;
				left: 0;
				width: 100%;
				height: 100%;
			}
			
			.dropdown-menu {
				top: 30px;
				width: 215px;
				margin-left: -5px;
			}
		</style>
	</head>
	<body>
		<div id="page-wrapper">
			<section id="content" class="slideshow-bg">
				<header id="header" class="navbar-static-top">
				<?php
					if(Login::isLoggedIn()) {
						include "includes/menublank-loggedin.php";
					} else {
						include "includes/menu.php";
					}
				?>
				<?php include "includes/menu_mobile.php";?>
				<?php include "includes/signup.php";?>
				<?php include "includes/login.php";?>
			</header>
			<?php include "includes/slider.php";?>
			<div class="container">
				<div id="main">
					<h1 class="page-title">
						<img width="20%" src="images/logowhite.png">
					</h1>
					<h1 class="page-title" style="font-size:32px;">
						fritugo is the best place to make and discover itineraries
					</h1>
					<h2 style="text-align:center; color:#fff; font-size:15px; margin-top:-15px;">
						get unlimited, create your own dream itinerary for your future trip
					</h2>
					<div class="search-box-wrapper style2">
						<br>
						<div class="search-tab-content">
							<div class="tab-pane fade active in" id="hotels-tab">
								<form id="msform" action="index.php" method="post" style="positition:abosolute;">
									<fieldset>
										<input style="width:79%; border-top-left-radius:30px; border-bottom-left-radius:30px" type="text" name="destination" id="location-input" placeholder="Your destination" />
										<input style="width:19%; border-top-right-radius:30px; border-bottom-right-radius:30px; margin-left:-3px;background-color:#ffb400; color:#fff;" type="button" name="next" class="next" value="Next" />
										<br>
										<div class="row">
											<div class="col-md-6" id="alertmsg"></div>
										</div>
									</fieldset>
									
									<fieldset>
										<input style="width:6%; border-top-left-radius:30px; border-bottom-left-radius:30px; background-color:#ffb400; color:#fff;" type="button" name="previous" class="previous" value="Back" />
										<a class="">
											<input style="width:26.5%; margin-left:-3px;" type="text" name="origin" id="originlocation" placeholder="You're from" />
										</a>
										<a class="datepicker-wrap form-group" >
											<input style="width:10%; margin-left:-3px;" type="text" name="from-date" value="" />
										</a>
										<a class="datepicker-wrap form-group" >
											<input style="width:10%; margin-left:-2px;" type="text" name="to-date" value="" />
										</a>
										<span class="dropdown">
											<a class="dropdown-toggle form-group" data-toggle="dropdown">
												<input style="width:15%; margin-left:-3px;" type="text" id="people" name="person"/>
												<span class="caret" style="position:absolute; left:140px; top:8px; cursor:pointer">
											</a>
											<ul class="dropdown-menu">
												<li style="margin-bottom: 10px; margin-top: 10px;"> &nbsp <span id="adultCount">0</span> Adult <span style="cursor:pointer; margin-left:100px; border:2px solid #0095DA; border-radius:30px; padding:0px 5px;" onclick="clicks++;updateClickCount()"><strong id="aPlus" style="color:#0095DA">+</strong></span> <span style="cursor:pointer; border:2px solid #0095DA; border-radius:30px; padding:0px 6px;" onclick="personSelect()";><strong id="aMin" style="color:#0095DA; margin-top:-2px;">-</strong></span></li>
												<li style="margin-bottom: 10px;"> &nbsp <span id="childCount">0</span> Child <span style="cursor:pointer; margin-left:100px; border:2px solid #0095DA; border-radius:30px; padding:0px 5px;" onclick="clickss++;updateClickCount()"><strong id="cPlus" style="color:#0095DA">+</strong></span> <span style="cursor:pointer; border:2px solid #0095DA; border-radius:30px; padding:0px 6px;" onclick="childSelect()";><strong id="cMin" style="color:#0095DA; margin-top:-2px;">-</strong></span></li>
												<a href="#" style="float:right; margin-right:10px;">close</a>
											</ul>
										</span>
										<a class="form-group" style="text-decoration:none;color:#888;">
											<?php echo $slcopt;?>
										</a>
										<a class="form-group">
											<input style="border-top-right-radius:30px; border-bottom-right-radius:30px; width:10.5%; margin-left:-3px; background-color:#ffb400; color:#fff;" type="submit" onclick="redirect($target)" name="submitPlan" class="action-button" value="Plan my trip" />
										</a>
									</fieldset>
								</form>
							</div>
						</div>
					</div>
					<br><br><br><br>
				</div>
			</div>
			<?php include "includes/tags.php";?>
			<div style="background-color:#f0f0f0; width:100%; position:relative; z-index:99;">
				<div class="container"></div>
				<br><br>
				<?php include "includes/popular_itineraries.php";?>
				<?php include "includes/popular_destinations.php";?>
			</div>
		</section>
		<?php include "includes/footer.php";?>
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>		
		<script>
			function placeSearch(){
				var input = document.getElementById('location-input');
				var inputx = document.getElementById('originlocation');
				var autoComplete = new google.maps.places.Autocomplete(input);
				var autoComplete = new google.maps.places.Autocomplete(inputx);
			}
		</script>
		<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDZTzuxSItuhzQgG85ebwVWfpuS5HTCgGw&libraries=places&callback=placeSearch"></script>
		<script src="js/jquery.easing.min.js" type="text/javascript"></script> 
		<script>
			
			function personSelect() {
				var counter = document.getElementById('people').value;
				document.getElementById('aPlus').addEventListener('click', function(){
					document.getElementById('people').value = parseInt(counter) + parseInt(1) + " adult";
				});
				document.getElementById('aMin').addEventListener('click', function(){
					document.getElementById('people').value = parseInt(counter) - parseInt(1) + " adult";
				});
			}
			
			var clicks = 0;
			var clickss = 0;
			function updateClickCount() {
				var x = document.getElementById('adultCount').innerHTML = clicks;
				var xx = document.getElementById('childCount').innerHTML = clickss;
				if (x > 1 || xx > 1) {
					var ad = " adults";
					var ch = " children";
					document.getElementById('people').value = x+ad+", "+xx+ch;
				} else {
					var ad = " adult";
					var ch = " children";
					document.getElementById('people').value = x+ad+", "+xx+ch;
				}
			}
			
			function selectTag() {
				 $('#location-input').val($('#tags').text());
			}
			
			$(function() {
				var current_fs, next_fs, previous_fs;
				var left, opacity, scale;
				var animating;

				$(".next").click(function(){
					var locationinput = $("#location-input").val();
					if (locationinput == "") {
						alert("Please choose your destination first...");
						return;
					} else {
						if(animating) return false;
						animating = true;
						current_fs = $(this).parent();
						next_fs = $(this).parent().next();
						$("#progressbar li").eq($("fieldset").index(next_fs)).addClass("active");
						next_fs.show(); 
						current_fs.animate({opacity: 0}, {
							step: function(now, mx) {
								scale = 1 - (1 - now) * 0.2;
								left = (now * 50)+"%";
								opacity = 1 - now;
								current_fs.css({'transform': 'scale('+scale+')'});
								next_fs.css({'left': left, 'opacity': opacity});
							}, 
							duration: 800, 
							complete: function(){
								current_fs.hide();
								animating = false;
							}, 
							easing: 'easeInOutBack'
						});
					}
				});

				$(".previous").click(function(){
					if(animating) return false;
					animating = true;
					
					current_fs = $(this).parent();
					previous_fs = $(this).parent().prev();
					
					previous_fs.show(); 
					current_fs.animate({opacity: 0}, {
						step: function(now, mx) {
							scale = 0.8 + (1 - now) * 0.2;
							left = ((1-now) * 50)+"%";
							opacity = 1 - now;
							current_fs.css({'left': left});
							previous_fs.css({'transform': 'scale('+scale+')', 'opacity': opacity});
						}, 
						duration: 800, 
						complete: function(){
							current_fs.hide();
							animating = false;
						}, 
						easing: 'easeInOutBack'
					});
				});

				$(".submit").click(function(){
					return false;
				})
			});
		</script>
		<script>
			navigator.permissions.query({name:'geolocation'})
			.then(function(permissionStatus) {  
				console.log('geolocation permission state is ', permissionStatus.state);
				permissionStatus.onchange = function() {  
					console.log('geolocation permission state has changed to ', this.state);
				};
			});
		</script>
	</body>
</html>

