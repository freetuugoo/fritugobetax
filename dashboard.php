<?php
ob_start();
include_once('./db.php');
include_once('./classes/login_class.php');
$user_id = DB::query('SELECT user_id FROM login_tokens WHERE token=:token', array(':token'=>sha1($_COOKIE['FCID'])))[0]['user_id'];

$allDes = DB::query('SELECT destination FROM t_master_itineraries WHERE user_id=:user_id', array(':user_id'=>$user_id));
$desArray = array();
array_push($desArray, $allDes);
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
        body {
            font-family: "Myriad Pro", "Gill Sans", "Gill Sans MT", Calibri, sans-serif;
        }
    </style>
</head>
<body>
    
    <div id="page-wrapper">
        <header id="header" class="navbar-static-top">
            <?php
                include "includes/menu-loggedin.php";
            ?>
            
            <?php
                include "includes/menu_mobile.php";
            ?>
            <?php
                include "includes/signup.php";
            ?>
            <?php
                include "includes/login.php";
            ?>
            <?php
                include "includes/edit_profile.php";
            ?>
            <?php
                include "includes/edit_settings.php";
            ?>
            <?php
                include "includes/bookings.php";
            ?>
        </header>
        <div class="page-title-container">
            <div class="container">
                <ul class="breadcrumbs pull-right">
                    <li class="active">My Account</li>
                </ul>
            </div>
        </div>
        <div class="block" style="background-color:#FFF;padding-top:30px;">
            <div style="background-color:#fff; text-align:center;">
                <figure>
                    <a title="" href="#"><img width="170" height="170" alt="" src="images/jonsnow.jpg" style="border-radius:1000px;"></a>
                    &nbsp&nbsp&nbsp&nbsp
                    <a style="font-size:27px; color:#343434;">
                        <?php
                            if (Login::isLoggedIn()) {
                                $username = DB::query(
                                    'SELECT u.username
                                    FROM users u
                                    JOIN login_tokens lt ON lt.user_id = u.id
                                    WHERE lt.token = :token', array(':token'=>sha1($_COOKIE['FCID']))
                                    )[0]['username'];
                                echo '@'.$username;
                            }
                        ?>
                    </a>
                </figure><br>
                <a data-toggle="tab" href="#edit_profile" class="soap-popupbox" style="font-size:14px;"><i class="soap-icon-user circle"></i>&nbsp&nbspedit profile</a>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp
                <a data-toggle="tab" href="#edit_settings" class="soap-popupbox"  style="font-size:14px;"><i class="soap-icon-settings circle"></i>&nbsp&nbspsettings</a>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp
                <a data-toggle="tab" href="#bookings" class="soap-popupbox"  style="font-size:14px;"><i class="soap-icon-card circle"></i>&nbsp&nbspmy bookings</a>
            </div>
        </div>
        <div class="section white-bg" style="padding-top:0px;">
                <div class="container">
                    <div class="text-center description block">
                        <p style="font-weight:600; font-size:19px;">
                        <?php
                        if (Login::isLoggedIn()) {
                            $id = DB::query('SELECT id FROM users WHERE username=:username', array(':username'=>$username))[0]['id'];
                            
                            if (!DB::query('SELECT fullname FROM users WHERE id=:id', array(':id'=>$id))) {
                                echo "Please update your profile";
                            } else {
                                $fullname = DB::query(
                                    'SELECT u.fullname
                                    FROM users u
                                    JOIN login_tokens lt ON lt.user_id = u.id
                                    WHERE lt.token = :token', array(':token'=>sha1($_COOKIE['FCID']))
                                    )[0]['fullname'];
                                echo $fullname;
                            }
                        }
                        ?>
                        </p>
                        <p>
                            <?php
                                if (Login::isLoggedIn()) {
                                    $biodata = DB::query(
                                        'SELECT u.biodata
                                        FROM users u
                                        JOIN login_tokens lt ON lt.user_id = u.id
                                        WHERE lt.token = :token', array(':token'=>sha1($_COOKIE['FCID']))
                                        )[0]['biodata'];
                                    echo $biodata;
                                } 
                            ?>
                        </p><br><hr>
                        <p style="font-size:19px;">
                        <?php
                            if (Login::isLoggedIn()) {
                                $itineraryCount = DB::query(
                                    'SELECT COUNT(i.user_id)
                                    FROM t_master_itineraries i
                                    JOIN users u ON u.id = i.user_id
                                    WHERE u.username = :username', array(':username'=>$username)
                                    )[0]['COUNT(i.user_id)'];
                                if ($itineraryCount == 1) {
                                    echo $itineraryCount . " itinerary";
                                } else {
                                    echo $itineraryCount . " itineraries";
                                }
                            }
                        ?>
                        </p>
                    </div>
                    <div class="tour-packages row add-clearfix image-box">
                        <?php
                        if($itineraryCount == 0) {
                            echo "<div style='text-align:center;'>you dont have any itinerary right now, why not create one</div>";
                        } else {
                            foreach($desArray as $key => $innerKey){
                                foreach($innerKey as $desName){
                                    $name = $desName['destination'];
                                    echo "<div class='col-sm-6 col-md-4' style='padding-right: 5px; padding-left: 5px; border-radius:8px;'>
                                    <article class='box animated' data-animation-type='fadeInLeft'>
                                        <figure>
                                            <a href='itinerary_detail.php?des=$name'><img src='images/iti_ny.jpg' alt=''></a>
                                            <figcaption>
                                                <span class='price'><img src='images/goldcoin.png' style='width:20px;'></span>
                                                <h2 class='caption-title'>
                                                    $name
                                                </h2>
                                            </figcaption>
                                        </figure>
                                    </article>
                                </div>";
                                }
                            }
                        }
                        ?>
                        
                    </div>
                </div>
            </div><hr>
        <?php
            include "includes/footer.php";
        ?>
</body>
</html>

