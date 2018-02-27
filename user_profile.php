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
        </header>
        <div class="page-title-container">
            <div class="container">
                <ul class="breadcrumbs pull-right">
                    <li class="active">@tonystark</li>
                </ul>
            </div>
        </div>
        <div class="block" style="background-color:#FFF;padding-top:30px;">
            <div style="background-color:#fff; text-align:center;">
                <figure>
                    <a title="" href="#"><img width="170" height="170" alt="" src="images/tonystark.jpg" style="border-radius:1000px;"></a>
                    &nbsp&nbsp&nbsp&nbsp
                    <a style="font-size:27px;">@tonystark &nbsp<img src="images/ver.png" width="17"></a>
                </figure><br>
                </div>
        </div>
        <div class="section white-bg" style="padding-top:0px;">
                <div class="container">
                    <div class="text-center description block">
                        <p style="font-weight:600; font-size:19px;">Tony Stark</p>
                        <p>Nunc cursus libero purus ac congue ar lorem cursus ut sed pulvinar massa idend porta nequetiam</p><br><hr>
                        <p style="font-size:19px;">7 Itineraries</p>
                    </div>
                    <div class="tour-packages row add-clearfix image-box">
                        <div class="col-sm-6 col-md-4" style="padding-right: 5px; padding-left: 5px; border-radius:8px;">
                            <article class="box animated" data-animation-type="fadeInLeft">
                                <figure>
                                    <a href="itinerary_user.php"><img src="images/iti_ny.jpg" alt=""></a>
                                    <figcaption>
                                        <span class="price"><img src="images/goldcoin.png" style="width:20px;"></span>
                                        <h2 class="caption-title">NY 4 days</h2>
                                    </figcaption>
                                </figure>
                            </article>
                        </div>
                        <div class="col-sm-6 col-md-4" style="padding-right: 5px; padding-left: 5px; border-radius:8px;">
                            <article class="box animated" data-animation-type="fadeInLeft">
                                <figure>
                                    <a href="itinerary_user.php"><img src="images/iti_rome.jpg" alt=""></a>
                                    <figcaption>
                                        <span class="price"><img src="images/goldcoin.png" style="width:20px;">&nbsp<img src="images/goldcoin.png" style="width:20px;"></span>
                                        <h2 class="caption-title">Rome!</h2>
                                    </figcaption>
                                </figure>
                            </article>
                        </div>
                        <div class="col-sm-6 col-md-4" style="padding-right: 5px; padding-left: 5px; border-radius:8px;">
                            <article class="box animated" data-animation-type="fadeInRight">
                                <!--<span class="discount"><span class="discount-text">10% Discount</span></span>-->
                                <figure>
                                    <a href="itinerary_user.php"><img src="images/iti_rome2.jpg" alt=""></a>
                                    <figcaption>
                                        <span class="price"><img src="images/silvercoin.png" style="width:20px;"></span>
                                        <h2 class="caption-title">Rome now!</h2>
                                    </figcaption>
                                </figure>
                            </article>
                        </div>
                        <div class="col-sm-6 col-md-4" style="padding-right: 5px; padding-left: 5px; border-radius:8px;">
                            <article class="box animated" data-animation-type="fadeInLeft">
                                <figure>
                                    <a href="itinerary_user.php"><img src="images/iti_tanah.jpg" alt=""></a>
                                    <figcaption>
                                        <span class="price"><img src="images/brocoin.png" style="width:20px;">&nbsp<img src="images/brocoin.png" style="width:20px;"></span>
                                        <h2 class="caption-title">Bali 3 days</h2>
                                    </figcaption>
                                </figure>
                            </article>
                        </div>
                        <div class="col-sm-6 col-md-4" style="padding-right: 5px; padding-left: 5px; border-radius:8px;">
                            <article class="box animated" data-animation-type="fadeInLeft">
                                <figure>
                                    <a href="itinerary_user.php"><img src="images/des_bali.jpg" alt=""></a>
                                    <figcaption>
                                        <span class="price"><img src="images/brocoin.png" style="width:20px;">&nbsp<img src="images/brocoin.png" style="width:20px;"></span>
                                        <h2 class="caption-title">Bali 2 days</h2>
                                    </figcaption>
                                </figure>
                            </article>
                        </div>
                        <div class="col-sm-6 col-md-4" style="padding-right: 5px; padding-left: 5px; border-radius:8px;">
                            <article class="box animated" data-animation-type="fadeInLeft">
                                <figure>
                                    <a href="itinerary_user.php"><img src="images/des_jap.jpg" alt=""></a>
                                    <figcaption>
                                        <span class="price"><img src="images/silvercoin.png" style="width:20px;">&nbsp<img src="images/silvercoin.png" style="width:20px;"></span>
                                        <h2 class="caption-title">Harakiri!</h2>
                                    </figcaption>
                                </figure>
                            </article>
                        </div>
                        <div class="col-sm-6 col-md-4" style="padding-right: 5px; padding-left: 5px; border-radius:8px;">
                            <article class="box animated" data-animation-type="fadeInLeft">
                                <figure>
                                    <a href="itinerary_user.php"><img src="images/des_hk.jpg" alt=""></a>
                                    <figcaption>
                                        <span class="price"><img src="images/goldcoin.png" style="width:20px;"></span>
                                        <h2 class="caption-title">Cingcongfan!</h2>
                                    </figcaption>
                                </figure>
                            </article>
                        </div>
                    </div>
                </div>
            </div><hr>
        <?php
            include "includes/footer.php";
        ?>
</body>
</html>

