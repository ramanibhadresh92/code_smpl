<?php
use frontend\assets\AppAsset; 
$baseUrl = AppAsset::register($this)->baseUrl;
?>
<div class="content-box bshadow">
    <div class="cbox-title">
        Page profile strength
    </div>
    <div class="cbox-desc">
        <div class="proprogress-holder">
            <div class="profile-progress">
                <span class="progress-span"></span>                         
            </div>                      
            <span class="progress-text">Strong</span>
        </div>                      
    </div>
</div>

<div class="content-box bshadow tbpagelikes">
    <div class="cbox-title">
        How often people viewed you
    </div>
    <div class="cbox-desc">
        <p><?=$cntrr?> Profile Views</p>
        <canvas id="myChart" width="280" height="171"></canvas>
    </div>
</div> 

<?php include('../views/layouts/recently_joined.php'); ?>

<div class="content-box bshadow greenbox">                  
    <div class="cbox-desc">
        <h6><img src="<?=$baseUrl?>/images/badge-icon.png"/>Get Verified</h6>
        <p>Verified members find more hosts.</p>
        <a href="javascript:void(0)">Learn More <i class="mdi mdi-menu-right"></i></a>
    </div>
</div>

<?php ('../views/layouts/weekphoto.php'); ?>

<?php include('../views/layouts/viewedprofile.php'); ?>

<?php include('../views/layouts/page_strength.php'); ?>

<?php include('../views/layouts/recently_joined.php'); ?>

<div class="content-box bshadow">
    <div class="cbox-desc">
        <div class="side-travad brand-travad">
            <div class="travad-maintitle">Best coffee in the world!</div>
            <div class="imgholder">
                <img src="<?=$baseUrl?>/images/brand-p.jpg">
            </div>
            <div class="descholder">                                
                <div class="travad-subtitle">We just get new starbucks coffee that is double in caffine that everybody is calling it a boost!</div>                                                             
                <a href="javascript:void(0)" class="btn btn-primary btn-sm adbtn">Explore</a>
            </div>
        </div>
    </div>                  
</div>

<div class="content-box bshadow">
    
    <div class="cbox-desc">
        <div class="side-travad travstore-travad">
            <div class="imgholder">
                <img src="<?=$baseUrl?>/images/tstore-p2.png">
            </div>
            <div class="descholder">                                
                <div class="travad-title">iPhone 6</div>
                <div class="travad-price">$1100.00</div>
                <div class="travad-info">Sponsered by <a href="javascript:void(0)">www.ebay.com</a></div>
                <a href="javascript:void(0)" class="btn btn-primary btn-sm adbtn">Shop Now</a>
            </div>
        </div>
    </div>
</div>
                
<div class="content-box bshadow">                   
    
    <div class="side-travad action-travad">                     
        <div class="travad-maintitle"><span class="iholder"><i class="mdi mdi-account-group"></i></span><h6>Heal Well</h6><span class="adtext">Sponsored</span></div>
        <div class="imgholder">
            <img src="<?=$baseUrl?>/images/groupad-actionvideo.jpg"/>
        </div>
        <div class="descholder">                                                            
            <div class="travad-title">Medical Research Methodolgy</div>
            <div class="travad-subtitle">Checkout the new video on our website exploring the latest techniques of medicine research</div>                                       
        <a href="javascript:void(0)" class="btn btn-primary btn-sm adbtn">Learn More</a>
        </div>
    </div>                      

</div>              

<div class="content-box bshadow">
    <div class="cbox-desc">
        <div class="side-travad page-travad">
            <div class="travad-maintitle"><img src="<?=$baseUrl?>/images/hyattprofile.jpg"><h6>Hyatt Hotel</h6><span>Sponsored</span></div>
            <div class="imgholder">
                <img src="<?=$baseUrl?>/images/pagead-endorse.jpg"/>                              
            </div>
            <div class="descholder">                                
                <div class="travad-title">Best facilites you ever found!</div>
                <div class="travad-subtitle">Endorse us for the best hospitality services we provide.</div>
                <div class="travad-info">78 people endorsed this page</div>
                <a href="javascript:void(0)" class="btn btn-primary btn-sm adbtn">Endorse</a>
            </div>
        </div>
    </div>
</div>

<div class="content-box bshadow">
    <div class="cbox-desc">
        <div class="side-travad actionlink-travad">
            <div class="travad-maintitle"><img src="<?=$baseUrl?>/images/adimg-flight.jpg"><h6>Jet Airways</h6><span>Sponsored</span></div>
            <div class="imgholder">
                <img src="<?=$baseUrl?>/images/admain-flight.jpg"/>                               
            </div>
            <div class="descholder">                                
                <div class="travad-title">Daily flight to London</div>
                <div class="travad-subtitle">Now we introduce a daily flight to London from the major cities of your country.</div>                                         
                <a href="javascript:void(0)" class="btn btn-primary adbtn">Book Now</a>
            </div>
        </div>
    </div>
</div>

<div class="content-box bshadow">
    <div class="cbox-desc">
        <div class="side-travad weblink-travad">
            <div class="travad-maintitle"><img src="<?=$baseUrl?>/images/adimg-food.jpg"><h6>Avida Food Hunt</h6><span>Sponsored</span></div>
            <div class="imgholder">
                <img src="<?=$baseUrl?>/images/admain-food.jpg"/>                             
            </div>
            <div class="descholder">                                
                <div class="travad-title">30% off on special pizza this weekend!</div>
                <div class="travad-subtitle">We bring you with flat 30% off on Avida special pizza this festive weekend.</div>                                                                      
                <a href="javascript:void(0)" class="adlink"><i class="mdi mdi-earth"></i><span>www.avidafoodhunt.com</span></a>
            </div>
        </div>
    </div>
</div>

<div class="content-box bshadow">
    <div class="cbox-desc">
        <div class="side-travad page-travad">
            <div class="travad-maintitle"><img src="<?=$baseUrl?>/images/hyattprofile.jpg"><h6>Hyatt Hotel</h6><span>Sponsored</span></div>
            <div class="imgholder">
                <img src="<?=$baseUrl?>/images/pagead.jpg"/>                              
            </div>
            <div class="descholder">                                
                <div class="travad-title">Get a luxurious experience with us!</div>
                <div class="travad-subtitle">We are here to provide you high class services with variety of amenities.</div>
                <div class="travad-info">345 people liked this</div>                                        
                <a href="javascript:void(0)" class="btn btn-primary btn-sm adbtn"><i class="zmdi zmdi-thumb-up"></i>Like</a>
            </div>
        </div>
    </div>
</div>

<script>
        var ctx = document.getElementById("myChart");

        var data = {
            labels: [<?=$valmonths?>],
            datasets: [
                {
                    fill: false,
                    lineTension: 0.1,
                    backgroundColor: "rgba(75,192,192,0.4)",
                    borderColor: "rgba(75,192,192,1)",
                    borderCapStyle: 'butt',
                    borderDash: [],
                    borderDashOffset: 0.0,
                    borderJoinStyle: 'miter',
                    pointBorderColor: "rgba(75,192,192,1)",
                    pointBackgroundColor: "#fff",
                    pointBorderWidth: 1,
                    pointHoverRadius: 5,
                    pointHoverBackgroundColor: "rgba(75,192,192,1)",
                    pointHoverBorderColor: "rgba(220,220,220,1)",
                    pointHoverBorderWidth: 2,
                    pointRadius: 4,
                    pointHitRadius: 10,
                    data: [<?=$provistit?>],
                    spanGaps: false,
                }
            ]
        };

        var myLineChart = Chart.Line(ctx, {
            data: data,
            options: {
                tooltips: {
                    enabled: true
                },
                legend: {
                    display: false
                },
                hover: {
                    display: false
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            stepSize: 10,
                            beginAtZero:true
                        }
                    }]
                }
            }
        });
        </script>

 