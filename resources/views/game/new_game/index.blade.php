<!DOCTYPE html>
<html lang="en">
<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");

?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>My script</title>

    <link rel="stylesheet" href="{{asset('public/game/new/')}}/css/new/day.css">
    <link rel="stylesheet" href="{{asset('public/game/new/')}}/css/new/night.css">
    <link rel="stylesheet" href="{{asset('public/game/new/')}}/css/new/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.0.0/css/bootstrap.min.css"
        integrity="sha512-NZ19NrT58XPK5sXqXnnvtf9T5kLXSzGQlVZL9taZWeTBtXoN3xIfTdxbkQh6QSoJfJgpojRqMfhyqBAAEeiXcA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css"
        integrity="sha512-MV7K8+y+gLIBoVD59lQIYicR65iaqukzvf/nwasF0nqhPay5w/9lJmVM2hMDcnK1OnMGCdVK+iQrJ7lzPJQd1w=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="{{asset('public/game/new/')}}/js/main_{{$fortunesetting->pusher_id}}.js"></script> 
<style>
  /* Disable text selection and long-press actions */
  body {
    -webkit-user-select: none;      /* Chrome/Safari/Edge */
    -webkit-touch-callout: none;    /* Disable the iOS callout (copy/paste) */
    user-select: none;              /* Standard */
  }
</style>
</head>


<body>

    <link rel="stylesheet" href="{{asset('public/game/new/')}}/css\saven_win.css">
    <style>
        .text-danger {
            color: #ebc400!important;
        }
        .text-success {
            color: #0BB568!important;
        }
        .zoomeffectleft {
            font-weight: 700;
            position: absolute;
            margin-left: -1px;
            margin-top: 0px;
            font-size: 15px;
        }
        .zoomeffectright {
            font-weight: 700;
            position: absolute;
            margin-left: -81px;
            margin-top: 0px;
            font-size: 15px;
        }
        /* .topplayer {
            width: fit-content;
            border: 1px solid #b9bd88;
            padding: 1px 7px;
            text-align: center;
            border-radius: 10px;
            background: #898989;
            margin-bottom: 2px;
            box-shadow: inset 3px 2px 9px rgba(0, 0, 0, 0.445);
        } */
    </style>
    <link rel="stylesheet" href="">
    <input value="{{ $authkey }}" name="email" id="authkey" hidden>
    <input value="{{$authtoken }}" name="authtoken" id="authtoken" hidden>

    <!-- <script src="js\old\app.63f5c45e.js"></script> -->


    <audio id="background_audio" src="#"></audio>
    <audio id="click_audio" src="#"></audio>
    <audio id="coins_audio" src="#"></audio>


    <section id="saven_winner" class="gameconatiner">
        <div class="container gamemain" >
            <div class="header_r">
                
                <div class="header_left">


                </div>
            <div id="logs-container"></div>
                <div class="header_right">
                    <div style="position: relative" class="icons icons_header_right ">
                        <ul class="header_right_icons_ul d-flex" style="right: 0;z-index: 99;    flex-direction: column-reverse;">
                            <li class="icons_header_right_click_4"><img class="setng" src="{{URL::to('/')}}/public/game/new/image/users.png" style="width: 55px;" alt="">
                            </li>
                            <li class="icons_header_right_click_1"><img class="setng"
                                    src="{{URL::to('/')}}/public/game/new/image/setting.png" style="width: 55px;"
                                    alt=""></li>
                            <li class="icons_header_right_click_3"><img class="setng"
                                    src="{{URL::to('/')}}/public/game/new/image/help.png" style="width: 55px;" alt="">
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="row  topheadplayer" style="position: absolute;top: 20%; width:100%;">
                <div class="col-6 d-flex flex-column leftplayr" style="padding-left: 40px;">
                   <div class="topplayer">
                        <img class="" id="top_one" src="{{URL::to('/')}}/public//user.png" style="width: 30px;height: 30px;" alt="">
                        <input type="hidden" id="top_one_id">
                        <span id="top_one_result" class="zoomeffectleft "></span>
                   </div>
                   <div class="topplayer">
                        <img class="" id="top_two"src="{{URL::to('/')}}/public//user.png" style="width: 30px;height: 30px;" alt="">
                         <input type="hidden" id="top_two_id"> 
                        <span id="top_two_result" class="zoomeffectleft "></span>
                   </div>
                </div>
                <div class="col-6 d-flex flex-column align-items-end rightplayr"  style="padding-right: 40px;">
                    <div class="topplayer">
                        <img class="" id="top_three" src="{{URL::to('/')}}/public//user.png" style="width: 30px;height: 30px;" alt="">
                       <input type="hidden" id="top_three_id"> 
                        <span id="top_three_result" class="zoomeffectright"></span>
                    </div>
                    <div class="topplayer">
                        <img class="" id="top_four" src="{{URL::to('/')}}/public//user.png" style="width: 30px;height: 30px;"" alt="">
                        <input type="hidden" id="top_four_id">
                        <span id="top_four_result" class="zoomeffectright"></span>
                    </div>
                    
                </div>
            </div>


            <div class="body">
                <div class="body_middle">
                    <div style="z-index: 0" class="images">
                        <img style="z-index: 10;position: inherit;" src="{{asset('public/game/new/image')}}/wheel.png"
                            alt="Saven Winner">
                        <img style="z-index: -1" class="spinner_while"
                            src="{{asset('public/game/new/image')}}/while_in.png" alt="Saven Winner">

                        <div id="all_animation_foots">
                            <span style="--i:1">
                                <img src="{{asset('public/game/new/image')}}/watermelon.png" alt="Saven Winner">
                            </span>
                            <span style="--i:2">
                                <img src="{{asset('public/game/new/image')}}/apple.png" alt="Saven Winner">
                            </span>
                            <span style="--i:3">
                                <img src="{{asset('public/game/new/image')}}/lemon.png" alt="Saven Winner">
                            </span>
                            <span style="--i:4">
                                <img src="{{asset('public/game/new/image')}}/watermelon.png" alt="Saven Winner">
                            </span>
                            <span style="--i:5">
                                <img src="{{asset('public/game/new/image')}}/apple.png" alt="Saven Winner">
                            </span>
                            <span style="--i:6">
                                <img src="{{asset('public/game/new/image')}}/lemon.png" alt="Saven Winner">
                            </span>
                            <span style="--i:7">
                                <img src="{{asset('public/game/new/image')}}/watermelon.png" alt="Saven Winner">
                            </span>
                            <span style="--i:8">
                                <img src="{{asset('public/game/new/image')}}/apple.png" alt="Saven Winner">
                            </span>
                            <span style="--i:9">
                                <img src="{{asset('public/game/new/image')}}/lemon.png" alt="Saven Winner">
                            </span>
                        </div>
                    </div>
                </div>

                <div class="body_bottom">
                    <div style="display: none;" class="images ">
                        <img class="clock" src="{{asset('public/game/new/image')}}/clock.png" alt="Saven Winner">
                        <h2 class="header clock_time_count_down"></h2>
                    </div>
                </div>
            </div>

            <div class="footer">
                <div class="footer_top">
                    <button class="box_wrapper" disabled style="background-image:url(https://queenlive.site/public/game/new/image/appleboard.png);">
                        <input type="hidden" value="apple">
                        <div class="box_wrapper_header">
                            <h2 class="header">00</h2>
                        </div>
                        <div class="box_wrapper_body" id="box_wrapper_bet_1">
                            <span class="all_batting_img_here"></span>

                        </div>
                        <div class="box_wrapper_footer">
                            <h2 class="header" id="won_bet_apple">00</h2>
                        </div>
                    </button>

                    <button class="box_wrapper" disabled style="background-image:url(https://queenlive.site/public/game/new/image/lemonboard.png);">
                        <input type="hidden" value="saven_win">
                        <div class="box_wrapper_header">
                            <h2 class="header">00</h2>
                        </div>
                        <div class="box_wrapper_body" id="box_wrapper_bet_2">
                            <span class="all_batting_img_here"></span>

                        </div>
                        <div class="box_wrapper_footer">
                            <h2 class="header" id="won_bet_saven_win">00</h2>
                        </div>
                    </button>

                    <button class="box_wrapper" disabled >
                        <input type="hidden" value="watermelon">
                        <div class="box_wrapper_header">
                            <h2 class="header">00</h2>
                        </div>
                        <div class="box_wrapper_body" id="box_wrapper_bet_3">
                            <span class="all_batting_img_here"></span>

                        </div>
                        <div class="box_wrapper_footer">
                            <h2 class="header" id="won_bet_watermelon">00</h2>
                        </div>
                    </button>
                </div>

                <div class="footer_bottom">

                    <div class="footer_bottom_left">
                        <div class="footer_bottom_left_right">
                            <div class="footer_bottom_left_right_top d-none">
                                <input type="text" name="" id="speed" value="" readonly placeholder="{{Auth::user()->name}}">
                            </div>

                            <div class="footer_bottom_left_right_bottom">
                                <img src="{{asset('public/game/new/image')}}/bt.png" alt="Saven Winner">
                                <input style="color: white" type="text" id="total_amount" value="..." disabled>
                              
                            </div>
                        </div>
                    </div>

                    <div class="footer_bottom_right">
                        <div class="images active">
                            <input type="hidden" value="500" />
                            <img src="{{asset('public/game/new/image')}}/500.png" alt="Saven Winner">
                            <span id="btn_animation_wrapper">
                                <div class="animation">
                                    <span style="--i:1"><i class="fa-solid fa-play"></i></span>
                                    <span style="--i:2"><i class="fa-solid fa-play"></i></span>
                                    <span style="--i:3"><i class="fa-solid fa-play"></i></span>
                                    <span style="--i:4"><i class="fa-solid fa-play"></i></span>
                                    <span style="--i:5"><i class="fa-solid fa-play"></i></span>
                                </div>
                            </span>
                        </div>

                        <div class="images">
                            <input type="hidden" value="1000" />
                            <img src="{{asset('public/game/new/image')}}/1000.png" alt="Saven Winner">
                            <div id="btn_animation_wrapper">

                            </div>
                        </div>

                        <div class="images">
                            <input type="hidden" value="10000" />
                            <img src="{{asset('public/game/new/image')}}/10k.png" alt="Saven Winner">
                            <div id="btn_animation_wrapper">

                            </div>
                        </div>

                        <div class="images">
                            <input type="hidden" value="50000" />
                            <img src="{{asset('public/game/new/image')}}/50k.png" alt="Saven Winner">
                            <div id="btn_animation_wrapper">

                            </div>
                        </div>
                        <div class="images">
                            <input type="hidden" value="100000" />
                            <img src="{{asset('public/game/new/image')}}/100k.png" alt="Saven Winner">
                            <div id="btn_animation_wrapper">

                            </div>
                        </div>
                        <div class="icons_header_right_click_2">
                            <img src="{{asset('public/game/new/image')}}/ranking.png" class="rankingsdf" alt="Saven Winner">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="gameconatiner2">
    <div id="hidden_info_here" class="roatenoe Reminder_Here d-none" style="width: 60%;">
        <div style="height: 20vh" class="container">
            <div class="body">
                <p class="title text-dark">Insuffisant coins!</p>
            </div>
        </div>
    </div>
    
    <div id="hidden_info_here" class="roatenoe Server_Issue" style="display:none;width: 60%;">
        <div style="height: 20vh" class="container">
            <div class="body">
                <p class="title text-dark">Connecting Server....</p>
            </div>
        </div>
    </div>


    <div id="hidden_info_here" class="roatenoe Winner_Here d-none" style="width: 80%;">
        <div class="container">


            <div class="body">
                <p class="title"><span id='emoji'>Congratulations</span> to the following winner(S)</p>

                <div class="box_wrapper">

                    <div class="box_r">
                        <div class="images">
                            <img class="img1" src="{{URL::to('/')}}/public//user.png" alt="">
                        </div>
                        <p class="title username1">User Name</p>
                        <li>
                            <span class="info">Bet : </span>
                            <span class="info_r bet1">...</span>
                        </li>
                        <li>
                            <span class="info">Win : </span>
                            <span class="info_r betresult1">...</span>
                        </li>
                    </div>

                    <div class="box_r">
                        <div class="images">
                            <img class="img2" src="{{URL::to('/')}}/public//user.png" alt="">
                        </div>
                        <p class="title username2">User Name</p>
                        <li>
                            <span class="info">Bet : </span>
                            <span class="info_r bet2">...</span>
                        </li>
                        <li>
                            <span class="info">Win : </span>
                            <span class="info_r betresult2">...</span>
                        </li>
                    </div>

                    <div class="box_r">
                        <div class="images">
                            <img class="img2" src="{{URL::to('/')}}/public//user.png" alt="">
                        </div>
                        <p class="title username3">User Name</p>
                        <li>
                            <span class="info">Bet : </span>
                            <span class="info_r bet3">...</span>
                        </li>
                        <li>
                            <span class="info">Win : </span>
                            <span class="info_r betresult3">...</span>
                        </li>
                    </div>

                </div>

                <div class="my_wining_info">
                    <div class="right">
                        <div class="images">
                            <img src="{{URL::to('/')}}/public//user.png" alt="">
                        </div>
                        <p style="color: white" class="title username4">{{Auth::user()->name}}</p>
                    </div>
                    <div class="left">
                        <li>
                            <span class="info">Bet : </span>
                            <span class="info_r myBet">...</span>
                        </li>
                        <li>
                            <span class="info">Win : </span>
                            <span class="info_r myBetWin">...</span>
                        </li>
                    </div>
                </div>

            </div>
        </div>
    </div>


    <div id="hidden_info_here" class="roatenoe Settings_Here d-none" style="width: 60%;">
        <div class="container">
            <img src="{{URL::to('/')}}/public/game/new/image/close.png" class="close_bar" style="width: 30px;" alt="">
            <div style="width: 100%" class="body">
                <div class="music">
                    <p class="header">Music : </p>
                    <input type="checkbox" class="music_checkbox music_1_checkbox">
                </div>
                <div style="margin-top: 2.5rem" class="music">
                    <p class="header">Sound : </p>
                    <input type="checkbox" class="music_checkbox sound_checkbox">
                </div>
            </div>
        </div>
    </div>


    <div id="hidden_info_here" class="roatenoe Rules_here d-none" style="width: 60%;">
        <div class="container">
            <img src="{{URL::to('/')}}/public/game/new/image/close.png" class="close_bar" style="width: 30px;" alt="">

            <div style="width: 100%;align-items:start;height: 100%; overflow-y: scroll;justify-content:flex-start;" class="body">
               
            </div>
        </div>
    </div>


    <div id="hidden_info_here" class=" roatenoe reward_here d-none" style="width: 55%;">
        <div style="flex-direction: unset;flex-wrap: wrap;" class="container">
            <img src="{{URL::to('/')}}/public/game/new/image/close.png" class="close_bar" style="width: 30px;" alt="">
            <div class="topbodybar" style="width: 100%; align-items: start; padding: 2px;">
                <div class="row col-12" style="margin-left:  -2px;background:#0000008c;border-radius: 6px;"> 
                    <div class="col-4 text-center text-white" style=""><img src="https://queenlive.site/public/game/new/image/apple.png" alt="Saven Winner"></div> 
                    <div class="col-4 text-center text-white" style=""><img src="https://queenlive.site/public/game/new/image/lemon.png" alt="Saven Winner"></div> 
                    <div class="col-4 text-center text-white" style="">
                        <img src="https://queenlive.site/public/game/new/image/watermelon.png" alt="Saven Winner">
                    </div> 
                </div>
            </div>
            <div style="width: 100%;align-items:start;overflow-y: scroll; height: 90%;" class="body">
                
            </div>
        </div>
    </div>


    <div id="hidden_info_here" class=" roatenoe users_here d-none" style="width: 60%;">
        <div class="container">
            <img src="{{URL::to('/')}}/public/game/new/image/close.png" class="close_bar" style="width: 30px;" alt="">

            <div style="width: 100%;align-items:start;overflow-y: scroll; height: 100%;" class="body">

                <div class="users_box w-100" id="">

                </div>



            </div>
        </div>
    </div>

    <div id="hidden_info_here" class="roatenoe Reminder_Here This_is_notification d-none" style="width: 100%;">
        <div class="container" style=" border:1px solid gold;   border-radius: 0;border-left: none; border-right: none;background:linear-gradient(90deg, rgb(229 57 130 / 48%) 0%, rgb(77 16 57) 23%, rgba(89,19,63,1) 90%, rgb(229 57 130 / 23%) 100%);">
            <div class="body">
                <p class="title text-white"></p>
            </div>
        </div>
    </div>

    </div>
    <input type="hidden" id="tray_id" value="{{ time()}}">
    <script>
    $(document).ready(function() {
        checkMobileScreen();
    });
    $(document).ready(function() {
        function checkMobileScreen() {
          var width = $(window).width();
          var noticewidth = width -100;
         
          var height = $(window).height();
            if ($(window).height() > 345 && $(window).width() <= 450) { // Adjust the threshold as needed
            
                $('.gameconatiner').css('transform', 'rotate(90deg)');
                $('.roatenoe').css('transform', 'translate(-50%, -50%) rotate(90deg)');
                $('.gamemain').css('min-height', width);
                $('.gamemain').css('width', height);
                $('body').css('overflow', 'visible');
                $('.body_middle').css('margin-top', '-33%');
                $('.body_middle').css('zoom', '150%');
                $('.topheadplayer').css('top', '20%');
                $('.topplayer').css('zoom', '150%');
                $('.users_here').css('width', '100%');
                $('#hidden_info_here.users_here .container .body .users_box').css('grid-template-columns', 'repeat(5, 1fr)');
                $('#saven_winner .container .footer .footer_bottom .footer_bottom_right').css('gap', '40px');
                $('.topplayer').css('margin-bottom', '2px');
                $('.topplayer img').css('width', '20px');
                $('.topplayer img').css('height', '20px');
                $('.topheadplayer').css('width', height);
                $('.header_r').css('width', height);
                $('#saven_winner .container .header_r .icons_header_right .header_right_icons_ul').css('flex-direction', 'row-reverse');

                $('.topplayer p').css('font-size', '10px');
                $('#hidden_info_here.reward_here .container').css('height', noticewidth);
                $('#hidden_info_here.users_here .container').css('height', noticewidth);
                $('#hidden_info_here.Rules_here .container').css('height', noticewidth);
                // $('body').css('height', height);
            }
            else if ($(window).height() <= 345 && $(window).width() <= 1024) { // Adjust the threshold as needed
           
                $('.gameconatiner').css('transform', 'none');
                $('#saven_winner .container .body .body_bottom').css('zoom', '80%');
                $('.body_middle').css('margin-top', '-33%');
                $('.body_middle').css('zoom', '125%');
                $('.topheadplayer').css('top', '20%');
                $('.topplayer').css('margin-bottom', '2px');
                $('.topplayer img').css('width', '20px');
                $('.topplayer img').css('height', '20px');
                $('.topplayer p').css('font-size', '10px');
                 $('#hidden_info_here.reward_here .container').css('height', noticewidth);
                $('#hidden_info_here.users_here .container').css('height', noticewidth);
                $('#hidden_info_here.Rules_here .container').css('height', noticewidth);
            } else {
                
                $('.gameconatiner').css('transform', 'none');
                $('.body_middle').css('margin-top', '30%');
                $('.topheadplayer').css('top', '40%');
                $('.topplayer').css('margin-bottom', '10px');
                $('.topplayer img').css('width', '40px');
                $('.topplayer img').css('height', '40px');
                $('.topplayer p').css('font-size', '14px');
                $('#saven_winner .container .body .body_bottom .images h2.header').css('top', '250%');
            }
        }

        // Initial check
        checkMobileScreen();

        // Check on window resize
        $(window).resize(function() {
            checkMobileScreen();
        });
    });
</script>
<script>
// Clear all cookies
var cookies = document.cookie.split("; ");
for (var i = 0; i < cookies.length; i++) {
  var cookie = cookies[i];
  var eqPos = cookie.indexOf("=");
  var name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;
  document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/";
}
    $(document).ready(function(){
        css_tricks();
        $(document).click(function(event) {
          var target = $(event.target);
          var targetClass = target.attr('class'); // or target.prop('class')
          var rewardDiv = $('.reward_here');
          if (targetClass === "rankingsdf") {
              if($(".reward_here").css('display') == 'none') {
                    $('.reward_here').show();
              }else{
                  $('.reward_here').hide();
              }
          }
          else{
              $('.reward_here').hide();
          }
        });
    });
    $(window).resize(function(){
        css_tricks();
    });
    /*
    |--------------
    | CSS START 
    |--------------
    */
  const css_tricks = () => {
        let height  = window.innerHeight;
        if(height < 400){

        }else{

            
        }
    }
    /*
    |--------------
    | CSS END 
    |--------------
    */

</script>

<script type="text/javascript">


    var click_audio = document.getElementById('click_audio');
var coins_audio = document.getElementById('coins_audio');
var audio_bg = document.getElementById('background_audio');

coins_audio.volume=0;
click_audio.volume=0;
audio_bg.volume=0;
$(document).ready(function(){
   settimeout_here();
   get_users_amount();
   win_or_loss_calculation();

   input_online_or_oflline();
   get_fruits_results();
   
});
// sound 
var audio_bg = document.getElementById('background_audio');

// Mute the audio initially
audio_bg.volume = 0;

// Add a click event listener to the document
document.addEventListener('click', function() {
 // Play the media element
 
});
/*
|---------------------
| All Animation Start
|---------------------
*/
// btn animation




$('#saven_winner .container .footer .footer_bottom .footer_bottom_right .images').click(function(){
   //click_audio.play();
   // active st
   $('#saven_winner .container .footer .footer_bottom .footer_bottom_right .images').removeClass('active');
   $(this).addClass('active');
   // annimation
   $('#saven_winner .container .footer .footer_bottom .footer_bottom_right .images #btn_animation_wrapper').html('');
   $(this).children('#btn_animation_wrapper').html('<div class="animation">' +
   '    <span style="--i:1"><i class="fa-solid fa-play"></i></span>' +
   '    <span style="--i:2"><i class="fa-solid fa-play"></i></span>' +
   '    <span style="--i:3"><i class="fa-solid fa-play"></i></span>' +
   '    <span style="--i:4"><i class="fa-solid fa-play"></i></span>' +
   '    <span style="--i:5"><i class="fa-solid fa-play"></i></span>' +
   '</div>' +
   '');
});

// responsive design
/*
|---------------------
| All Animation End
|---------------------
*/
$('input.music_1_checkbox').click(function(){
   if($('input.music_1_checkbox').is(':checked')){
       audio_bg.play();
       audio_bg.volume=1;
   }else{
       audio_bg.volume=0;
   }
   
});
$('input.sound_checkbox').click(function(){
   if($('input.sound_checkbox').is(':checked')){
       click_audio.volume=1;
       coins_audio.volume=1;
   }else{
       click_audio.volume=0;
       coins_audio.volume=0;
   }
   
});
/*
|---------------------
| Click & Run Event
|---------------------
*/
// $('.icons_header_right_click').click(function(){
//     $('.header_right_icons_ul').toggleClass('d-none');
// });

$('.icons_header_right_click_1').click(function(){
   // settings 
   $('.Settings_Here').removeClass('d-none');
});
$('.icons_header_right_click_2').click(function(){
   // settings 
   $('.reward_here').removeClass('d-none');
  
});

$('.icons_header_right_click_3').click(function(){
   // settings 
   $('.Rules_here').removeClass('d-none');
   get_last_my_result();
});

$('.icons_header_right_click_4').click(function(){
   // settings 
   $('.users_here').removeClass('d-none');
   input_online_or_oflline_get_users();
});

// close 
$('#hidden_info_here.Settings_Here .container .close_bar').click(function(){
   // settings 
   $('#hidden_info_here.Settings_Here').addClass('d-none');
});

$('#hidden_info_here.reward_here .container .close_bar').click(function(){
   // settings 
   $('#hidden_info_here.reward_here').addClass('d-none');
});

$('#hidden_info_here.Rules_here .container .close_bar').click(function(){
   // settings 
   $('#hidden_info_here.Rules_here').addClass('d-none');
});

$('#hidden_info_here.users_here .container .close_bar').click(function(){
   // settings 
   $('#hidden_info_here.users_here').addClass('d-none');
});
/*
|---------------------
| Click & Run Event
|---------------------
*/

/*
|---------------------
| tseting here  st
|---------------------
*/
$('#saven_winner .container .footer .footer_bottom .footer_bottom_right .images').click(function(){

   $('#saven_winner .container .footer .footer_top .box_wrapper .box_wrapper_body img.coin_box3').css('animation', 'coin_box_2_anime 1s ease forwards');
   setTimeout(() => {
       var random_number = 1;
       $('#saven_winner .container .footer .footer_top .box_wrapper .box_wrapper_body img.coin_box3').css({'animation' : 'none', 'top' : ''+random_number+'%', 'left' : ''+random_number+'%'});
   }, 1000);

});
const settimeout_here = () => {
    //let nowTime = Number(new Date().getTime()/1000);
    //console.log('now Time' +nowTime)
   //ajax
   $.ajax({
       "method" : "get",
       "url" : "https://queenlive.site/betel/tray_id",
       "data" : {
           'tray_id' : $('#tray_id').val(),
           'authkey': $('#authkey').val(),
           'authtoken': $('#authtoken').val(),
       },crossDomain: true,
           headers: {
             'Access-Control-Allow-Origin': '*'
           },

       success:function(res){
          let st=res.st;
         let user_id={{ Auth::id() }};
           let data=res.data;
            let nowTime = res.currentTimeInSeconds;
             if(st === true){
               $('.Server_Issue').hide();
               var x = setInterval(() => {
                   
                   // start
                   nowTime++; 
                   let time = Number(data - nowTime).toFixed(0);
                   // some css before st
                   $('#saven_winner .container .body .body_bottom .images').css('display', 'none');
                   $('#saven_winner .container .footer .footer_top .box_wrapper').attr('disabled', true);
                  $('#tray_id').val(data);
                 //  console.log(time);

                   // time bottom 
                   if(time > 6 && time < 22){


                       $('#saven_winner .container .body .body_bottom .images').css('display', 'block');
                       $('#saven_winner .container .footer .footer_top .box_wrapper').attr('disabled', false);

                       $('.clock_time_count_down').html(time-6);
                   }
                   // if(time > 26){
                   //     win_or_loss_calculation();
                   // }
                   if(time == 26){
                      
                       get_winner_info();
                       
                      // $('.Winner_Here').removeClass('d-none');
                       get_fruits_results();
                       setTimeout(() => {
                          // $('.Winner_Here').addClass('d-none');
                       }, 4000);
                      // get_users_amount();
                     

                   }
                  if(time == 22){
                     
                      win_or_loss_calculation();
                       $('#top_one_result').hide(1000);
                       $('#top_two_result').hide(1000);
                       $('#top_three_result').hide(1000);
                       $('#top_four_result').hide(1000);
                        $('#top_two_result').removeClass('zoomeffect text-danger');
                        $('#top_two_result').removeClass('zoomeffect text-success');
                        $('#top_one_result').removeClass('zoomeffect text-danger');
                        $('#top_one_result').removeClass('zoomeffect text-success');
                        $('#top_three_result').removeClass('zoomeffect text-danger');
                        $('#top_three_result').removeClass('zoomeffect text-success');
                        $('#top_four_result').removeClass('zoomeffect text-danger');
                        $('#top_four_result').removeClass('zoomeffect text-success');
                        
                  }
                   if(time == 22){
                    
                    
                       $('#top_one_result').hide(1000);
                       $('#top_two_result').hide(1000);
                       $('#top_three_result').hide(1000);
                       $('#top_four_result').hide(1000);
                        $('#top_two_result').removeClass('zoomeffect text-danger');
                        $('#top_two_result').removeClass('zoomeffect text-success');
                        $('#top_one_result').removeClass('zoomeffect text-danger');
                        $('#top_one_result').removeClass('zoomeffect text-success');
                        $('#top_three_result').removeClass('zoomeffect text-danger');
                        $('#top_three_result').removeClass('zoomeffect text-success');
                        $('#top_four_result').removeClass('zoomeffect text-danger');
                        $('#top_four_result').removeClass('zoomeffect text-success');
                   
                    	 
                          input_online_or_oflline();
                       $('.This_is_notification').removeClass('d-none');
                     //  StartBetplayAudio();
                    
                       $('.This_is_notification .body .title').html('Start Batting');
                       setTimeout(() => {
                           $('.This_is_notification').addClass('d-none');
                       }, 1500);
                   }
                  
                   if (time==13) {
                   
                   robot();
                    
                   }
                   if(time == 7){
                    win_lock_id();
                    console.log('ID LOCK HIT')
                   }
                   if(time == 6){
                       //win_pred();
                       $('#saven_winner .container .footer .footer_top .box_wrapper').attr('disabled', true);
                       $('.This_is_notification').removeClass('d-none');
                     //  StopBetplayAudio();
                       $('.This_is_notification .body .title').html('Stop Batting');
                       $('#saven_winner .container .footer .footer_top .box_wrapper').removeClass('active');
                     
                      win_pred();
                      console.log('win_pred')
                   }
                   if (time ==3) {
                    result_final();
                   }
                   
                   if (time ==2) {

                       setTimeout(() => {
                          
                           saven_win_get_winner();
                           
                          
                           $('.This_is_notification').addClass('d-none');
                       }, 1000);
                   }
                   if(time < 0){
                       clearInterval(x);
                   }
               }, 1000);
           }else{
               settimeout_here();
               $('.Server_Issue').show();
           }
        },
        error: function() {
          // $('.Server_Issue').show();
        }
    })



}
// saven_win_get_winner_in
const saven_win_get_winner = () => {
   // ajax
   $.ajax({
       "method" : "get",
       "url" : "https://queenlive.site/betel/winner_saven_win",
       "data" : {
           'tray_id' : $('#tray_id').val(),
           'authkey': $('#authkey').val(),
           'authtoken': $('#authtoken').val(),
       },
       success:function(res){
           
           if(res.st === true){
               $('.Server_Issue').hide();
               setTimeout(() => {
                   // start Again
                   settimeout_here();

                   // st
                   $('#saven_winner .container .body').css('transform', 'translateY(0px)');
                   /*
                   |--------------
                   |spniner
                   |--------------
                   */
                   if(res.data == "apple"){
                       var span_num = 1;

                       var anim_while = "rotet_while_2 7s ease forwards";
                       var anim_while_f = "rotet_while_2_fruits 7s ease forwards";

                       var spn_img = 5;
                   }else if(res.data == "saven_win"){
                       var span_num = 2;

                       var anim_while = "saven_winner 7s ease forwards";
                       var anim_while_f = "saven_winner_fruits 7s ease forwards";

                       var spn_img = 3;
                   }else{
                       var span_num = 3;

                       var anim_while = "rotet_while_3 7s ease forwards";
                       var anim_while_f = "rotet_while_3_fruits 7s ease forwards";

                       var spn_img = 1;
                   }

                   $('#saven_winner .container .body .body_middle .images img.spinner_while').css('animation', anim_while);
                   $('#saven_winner .container .body .body_middle .images #all_animation_foots').css('animation', anim_while_f);

                   // winner
                   setTimeout(() => {
                       $('#saven_winner .container .footer .footer_top .box_wrapper').css('filter', 'grayscale(100%)');
                       $('#saven_winner .container .body .body_middle .images #all_animation_foots span img').css('filter', 'grayscale(100%)');
                       $('#saven_winner .container .body .body_middle .images img.spinner_while').css('filter', 'grayscale(100%)');
                       $('#saven_winner .container .body .body_middle .images #all_animation_foots span:nth-child('+span_num+')').css('filter', 'drop-shadow(0px 0px 1px black)');
                       $('#saven_winner .container .body .body_middle .images #all_animation_foots span:nth-child('+spn_img+') img').css({'animation' : 'box_animation_apple 4s ease forwards', 'filter' : 'drop-shadow(0px 0px 1px black)'});
                       $('#saven_winner .container .footer .footer_top .box_wrapper:nth-child('+span_num+')').css({'animation' : 'box_animation_apple 4s ease forwards', 'filter' : 'drop-shadow(0px 0px 1px black)'});
                       setTimeout(() => {
                           // css 
                           $('#saven_winner .container .body .body_middle .images img.spinner_while').css('animation', 'none');
                           $('#saven_winner .container .body .body_middle .images #all_animation_foots').css('animation', 'none');
                           $('#saven_winner .container .body .body_middle .images #all_animation_foots span img').css('filter', 'drop-shadow(0px 0px 1px black)');
                           $('#saven_winner .container .body .body_middle .images img.spinner_while').css('filter', 'drop-shadow(0px 0px 1px black)');
                           $('#saven_winner .container .body .body_middle .images #all_animation_foots span:nth-child('+spn_img+') img').css({'animation' : 'none'});
                           $('#saven_winner .container .footer .footer_top .box_wrapper:nth-child('+span_num+')').css({'animation' : 'none'});
                           var width = $(window).width();
                            var height = $(window).height(); 
                            
                            if ($(window).height() > 345 && $(window).width() <= 450) {
                                $('#saven_winner .container .body').css('transform', 'translateY(0px)');

                            }
                            else if ($(window).height() <= 345 && $(window).width() <= 1024) {
                                $('#saven_winner .container .body').css('transform', 'translateY(0px)');

                            }else{
                                $('#saven_winner .container .body').css('transform', 'translateY(0px)');

                            }
                           $('#saven_winner .container .footer .footer_top .box_wrapper').css('filter', 'drop-shadow(0px 0px 1px black)');
                           // all amount 
                           $('#saven_winner .container .footer .footer_top .box_wrapper .box_wrapper_header .header, #saven_winner .container .footer .footer_top .box_wrapper .box_wrapper_footer .header').html('00');
                           $('.all_batting_img_here').html('');
                       }, 5000);
                   }, 7000);
               }, 500);
           }else{

               saven_win_get_winner();
             //  $('.Server_Issue').show();
           }
       },
        error: function() {
          // $('.Server_Issue').show();
        }
   });
}
// insert_betting
const insert_bettingapple = (Amount, betting_to) => {
 //  console.log(Amount + 'insert_betting' + betting_to);
   // ajax
   $.ajax({
       "method" : "get",
       "url" : "https://queenlive.site/betel/fortune_insert",
       "data" : {
           'tray_id' : $('#tray_id').val(),
           'amount' : Amount,
           'pot_no' : betting_to,
           'authkey': $('#authkey').val(),
           'authtoken': $('#authtoken').val(),
       },
       success:function(res){
           $('.Server_Issue').hide();
       },
        error: function() {
           $('.Server_Issue').show();
        }
   });
}
const robot = () => {
  // win_or_loss_calculation();
   // ajax
   $.ajax({
       "method" : "get",
       "url" : "https://queenlive.site/betel/robot/",
       "data" : {
           'tray_id' : $('#tray_id').val(),
           'authkey': $('#authkey').val(),
           'authtoken': $('#authtoken').val(),
       },
       success:function(res){
           $('.Server_Issue').hide();
           if(res.st == true){
              // $('#total_amount').val(res.balance);
           }
           
       },
       error: function() {
           // Show error message
           //console.log('error');
           //$('.Server_Issue').show();
       }
   });
}
const win_lock_id = () => {
   // ajax
   //console.log($('#userID').val()+"win_or_loss_calculation");
   $.ajax({
       "method" : "get",
       "url" : "https://queenlive.site/betel/win_lock_id/",
       "data" : {
           'tray_id' : $('#tray_id').val(),
           'authkey': $('#authkey').val(),
           'authtoken': $('#authtoken').val(),
       },
       success:function(res){
           $('.Server_Issue').hide();
       },
        error: function() {
         //  $('.Server_Issue').show();
        }
   });
}
const insert_bettingsaven_winner = (Amount, betting_to) => {
 //  console.log(Amount + 'insert_betting' + betting_to);
   // ajax

   $.ajax({
       "method" : "get",
       "url" : "https://queenlive.site/betel/fortune_insert",
       "data" : {
           'tray_id' : $('#tray_id').val(),
           'amount' : Amount,
           'pot_no' : betting_to,
           'authkey': $('#authkey').val(),
           'authtoken': $('#authtoken').val(),
       },
       success:function(res){
           $('.Server_Issue').hide();
       },
        error: function() {
           $('.Server_Issue').show();
        }
   });
}
const insert_betting_watermellon = (Amount, betting_to) => {
 //  console.log(Amount + 'insert_betting' + betting_to);
   // ajax
   $.ajax({
       "method" : "get",
       "url" : "https://queenlive.site/betel/fortune_watermelon_insert",
       "data" : {
           'tray_id' : $('#tray_id').val(),
           'amount' : Amount,
           'pot_no' : betting_to,
           'authkey': $('#authkey').val(),
           'authtoken': $('#authtoken').val(),
       },
       success:function(res){
           $('.Server_Issue').hide();
       },
        error: function() {
           $('.Server_Issue').show();
        }
   });
}

// get user amount
const get_users_amount = () => {
  // win_or_loss_calculation();
   // ajax
   $.ajax({
       "method" : "get",
       "url" : "https://queenlive.site/betel/user?authkey=" + $('#authkey').val() + "&authtoken=" + $('#authtoken').val(),
       success:function(res){
           $('.Server_Issue').hide();
           if(res.st == true){
            $('#total_amount').val(res.balance);
           }else{
               $('.Server_Issue').show();
           }
           
       },
       error: function() {
           // Show error message
           //console.log('error');
           $('.Server_Issue').show();
       }
   });
}


// win_or_loss_calculation
const win_or_loss_calculation = () => {
   // ajax
   //console.log($('#userID').val()+"win_or_loss_calculation");
   $.ajax({
       "method" : "get",
       "url" : "https://queenlive.site/betel/win_or_loss_calculation/",
       "data" : {
           'tray_id' : $('#tray_id').val(),
           'authkey': $('#authkey').val(),
           'authtoken': $('#authtoken').val(),
       },
       success:function(res){
           $('.Server_Issue').hide();
           $('#total_amount').val(res.balance);
       },
        error: function() {
          // $('.Server_Issue').show();
        }
   });
}

const result_final = () => {
   // ajax
   //console.log($('#userID').val()+"win_or_loss_calculation");
   $.ajax({
       "method" : "get",
       "url" : "https://queenlive.site/betel/result_final/",
       "data" : {
           'tray_id' : $('#tray_id').val(),
           'authkey': $('#authkey').val(),
           'authtoken': $('#authtoken').val(),
       },
       success:function(res){
           $('.Server_Issue').hide();
       },
        error: function() {
         //  $('.Server_Issue').show();
        }
   });
}
const win_pred = () => {
   // ajax
   //console.log($('#userID').val()+"win_or_loss_calculation");
   $.ajax({
       "method" : "get",
       "url" : "https://queenlive.site/betel/win_pred/",
       "data" : {
           'tray_id' : $('#tray_id').val(),
           'authkey': $('#authkey').val(),
           'authtoken': $('#authtoken').val(),
       },
       success:function(res){
           $('.Server_Issue').hide();
       },
        error: function() {
         //  $('.Server_Issue').show();
        }
   });
}


// get_winner_info
const get_winner_info = () => {
  
    var topone = $('#top_one_id').val();
    var toptwo = $('#top_two_id').val();
    var topthree = $('#top_three_id').val();
    var topfour = $('#top_four_id').val();
   //console.log(toptwo);

   $.ajax({
       "method" : "get",
       "url" : "https://queenlive.site/betel/get_winner_info/",
       "data" : {
           'authkey': $('#authkey').val(),
           'authtoken': $('#authtoken').val(),
           'topone': topone,
           'toptwo': toptwo,
           'topthree': topthree,
           'topfour': topfour,
       },
       success:function(res){ 
         $('#top_one_result').show();
           $('.Server_Issue').hide();
       
       if (res.topone == null || res.top_one_bet==0|| res.topone<0) {
          $('#top_one_result').text("");
          $('#top_one_result').addClass('zoomeffect text-danger');
        } else {
          var sign = res.topone >= 0 ? "+" : "-";
          var textColor;

            if (res.topone >= 0) {
              textColor = 'text-success';
            } else {
              textColor = 'text-danger';
            }
          
          // Ensure the number is positive for display purposes
          
          var absoluteValue = Math.abs(res.topone);
          
          $('#top_one_result').text(sign + absoluteValue.toLocaleString());
          $('#top_one_result').show(1000);
          $('#top_one_result').addClass('zoomeffect ' + textColor);
        }
        if (res.toptwo == null || res.top_two_bet==0 || res.toptwo<0) {
          $('#top_two_result').text("");
          $('#top_two_result').addClass('zoomeffect text-danger');
        } else {
          var toptwosign = res.toptwo >= 0 ? "+" : "-";
           var toptwotextColor;
           // console.log(res.toptwo);
            if (res.toptwo >= 0) {
              toptwotextColor = 'text-success';
              console.log('success');
            } else {
              toptwotextColor = 'text-danger';
              // console.log('danger');
            }
         // console.log(toptwotextColor);
          // Ensure the number is positive for display purposes
          var toptwoabsoluteValue = Math.abs(res.toptwo);
          
          $('#top_two_result').text(toptwosign + toptwoabsoluteValue.toLocaleString());
          $('#top_two_result').show(1000);
          $('#top_two_result').addClass('zoomeffect ' + toptwotextColor);
        }
         if (res.topthree == null || res.top_three_bet==0|| res.topthree<0) {
          $('#top_three_result').text("");
          $('#top_three_result').addClass('zoomeffect text-danger');
        } else {
          var threesign = res.topthree >= 0 ? "+" : "-";
          var threetextColor;

            if (res.topthree >= 0) {
              threetextColor = 'text-success';
            } else {
              threetextColor = 'text-danger';
            }
          
          // Ensure the number is positive for display purposes
          var threeabsoluteValue = Math.abs(res.topthree);
          
          $('#top_three_result').text(threesign + threeabsoluteValue.toLocaleString());
          $('#top_three_result').show(1000);
          $('#top_three_result').addClass('zoomeffect ' + threetextColor);
        }
        if (res.topfour == null || res.top_four_bet==0 || res.topfour<0) {
          $('#top_four_result').text("");
          $('#top_four_result').addClass('zoomeffect text-danger');
        } else {
          var topfoursign = res.topfour >= 0 ? "+" : "-";
          var topfourtextColor;

            if (res.topfour >= 0) {
              topfourtextColor = 'text-success';
            } else {
              topfourtextColor = 'text-danger';
            }
          
          // Ensure the number is positive for display purposes
          var topfourabsoluteValue = Math.abs(res.topfour);
          
          $('#top_four_result').text(topfoursign +  topfourabsoluteValue.toLocaleString());
          $('#top_four_result').show(1000);
          $('#top_four_result').addClass('zoomeffect ' + topfourtextColor);
        }
       },
        error: function() {
         //  $('.Server_Issue').show();
        }
   });
}


// gets fruits results 
const get_fruits_results = () => {
    // ajax
   $.ajax({
       "method" : "get",
       "url" : "https://queenlive.site/betel/wining_fruits",
       "data" : {
           'authkey': $('#authkey').val(),
           'authtoken': $('#authtoken').val(),
       },
       success:function(res){
           
           const rewards = res.data.map((curE) => {
               if(curE.winner == "watermelon"){
                   return '<div class="row col-12 shadow" style="margin-left:  -2px;background:#0000008c;border-radius: 6px;"> <div class="col-4 text-center text-white" style="">-</div> <div class="col-4 text-center text-white" style="">-</div> <div class="col-4 text-center " style="color: #ffce00 !important; font-size: 13px; font-family: cursive; font-weight: bold;">Win</div> </div>';
               }else if(curE.winner == "saven_win"){
                   return '<div class="row col-12 shadow" style="margin-left: -2px;background:#0000008c;border-radius: 6px;"> <div class="col-4 text-center text-white" style="">-</div> <div class="col-4 text-center " style="color: #ffce00 !important; font-size: 13px; font-family: cursive; font-weight: bold;">Win</div> <div class="col-4 text-center text-white" style="">-</div> </div>';
               }else{
                   return '<div class="row col-12 shadow" style="margin-left: -2px;background:#0000008c;border-radius: 6px;"> <div class="col-4 text-center" style="color: #ffce00 !important; font-size: 13px; font-family: cursive; font-weight: bold;">Win</div> <div class="col-4 text-center text-white" style="">-</div> <div class="col-4 text-center text-white" style="">-</div> </div>';
               }
           });
           $('.reward_here .body').html(rewards);
           $('.apple_percentage').html(res.apple_parcentage+"%");
           $('.77win_percentage').html(res.lamon_parcentage+"%");
           $('.watermelon_percentage').html(res.watermellon_parcentage+"%");
         
       }
   });
}

$(document).ready(function() {

    $("#saven_winner .container .footer .footer_top .box_wrapper").click(function() {
        if (isNaN(Number($("#total_amount").val()))) {
            return $(".This_is_notification").removeClass("d-none"), $(".This_is_notification .body .title").html("Please Refrash Your Game!"), setTimeout(() => {
            $(".This_is_notification").addClass("d-none")
        }, 500), !1;
        }

        if ($(this).addClass("active"), $("#saven_winner .container .footer .footer_top .box_wrapper:nth-child(1)").hasClass("active") && $("#saven_winner .container .footer .footer_top .box_wrapper:nth-child(2)").hasClass("active") && $("#saven_winner .container .footer .footer_top .box_wrapper:nth-child(3)").hasClass("active")) return $(".This_is_notification").removeClass("d-none"), $(".This_is_notification .body .title").html("You can't select 3 bord at atime"), setTimeout(() => {
            $(this).removeClass("active"), $(".This_is_notification").addClass("d-none")
        }, 500), !1;
        if (Number($("#total_amount").val()) - Number($("#saven_winner .container .footer .footer_bottom .footer_bottom_right .images.active").children("input").val()) < 0 || Number($("#total_amount").val()) < 1) return $(".This_is_notification").removeClass("d-none"), $(".This_is_notification .body .title").html("Insuffisant coins!"), setTimeout(() => {
            $(".This_is_notification").addClass("d-none")
        }, 500), !1;
        $(this).children("input").val() == "apple" ? $("#saven_winner .container .footer .footer_top .box_wrapper:nth-child(1) .box_wrapper_footer .header").html(Number($("#saven_winner .container .footer .footer_top .box_wrapper:nth-child(1) .box_wrapper_footer .header").html()) + Number($("#saven_winner .container .footer .footer_bottom .footer_bottom_right .images.active").children("input").val())) : $(this).children("input").val() == "saven_win" ? $("#saven_winner .container .footer .footer_top .box_wrapper:nth-child(2) .box_wrapper_footer .header").html(Number($("#saven_winner .container .footer .footer_top .box_wrapper:nth-child(2) .box_wrapper_footer .header").html()) + Number($("#saven_winner .container .footer .footer_bottom .footer_bottom_right .images.active").children("input").val())) : $("#saven_winner .container .footer .footer_top .box_wrapper:nth-child(3) .box_wrapper_footer .header").html(Number($("#saven_winner .container .footer .footer_top .box_wrapper:nth-child(3) .box_wrapper_footer .header").html()) + Number($("#saven_winner .container .footer .footer_bottom .footer_bottom_right .images.active").children("input").val())), $("#total_amount").val(Number($("#total_amount").val()) - Number($("#saven_winner .container .footer .footer_bottom .footer_bottom_right .images.active").children("input").val())), $.ajax({
            method: "get",
            url: "https://queenlive.site/betel/fortune_insert",
            data: {
                'authkey': $('#authkey').val(),
           'authtoken': $('#authtoken').val(),
                tray_id: $('#tray_id').val(),
                bord_name: $(this).children("input").val(),
                amount: $("#saven_winner .container .footer .footer_bottom .footer_bottom_right .images.active").children("input").val()
            },
            success: function(i) {
                // $("#total_amount").val(i.balance);
                // $("#won_bet_apple").val(i.apple);
                // $("#won_bet_watermelon").val(i.watermelon);
                // $("#won_bet_saven_win").val(i.lemon);

            }
        })
    })
});
//input_online_or_oflline
const input_online_or_oflline = () => {
    // ajax
    $.ajax({
        "method" : "get",
        "url" : "https://queenlive.site/betel/fortune_user_activity",
        "data" : {
             tray_id: $('#tray_id').val(),
             'authkey': $('#authkey').val(),
           'authtoken': $('#authtoken').val(),
        },
        success:function(res){
            //console.log(res.data[0].id)
            //console.log(res.data[1].name)
           
         
            if (res.data[0]) {

                $('#top_one').attr('src',res.data[0].profile);
                $('#top_one_id').val(res.data[0].id);
            }

            if (res.data[1]) {
                $('#top_two_id').val(res.data[1].id);
                $('#top_two').attr('src',res.data[1].profile);
            }
           
           if (res.data[2]) {

                $('#top_three_id').val(res.data[2].id);
                $('#top_three').attr('src',res.data[2].profile );
           }
           if (res.data[3]) {
                $('#top_four_id').val(res.data[3].id);
                $('#top_four').attr('src',res.data[3].profile);
           }
           
           
        }
    });
}
// input_online_or_oflline_get_users
const input_online_or_oflline_get_users = () => {
    $('.users_here .users_box').html('<h2 class="title">Loadding...</h2>');
    // ajax
    $.ajax({
        "method" : "get",
        "url" : "https://queenlive.site/betel/fortune_all_active_users",
        "data" : {},
        success:function(res){
            $('#hidden_info_here.users_here .users_box')
            const data = res.data.map((curE) => {
                  return '<div class="box_r"><img src="'+curE.profile +'" alt=""><p class="title">'+curE.name+'</p></div>';;
            });
      
            



            $('.users_here .users_box').html(data);
        }
    });
}
// user Last Result
const get_last_my_result = () => {
    $('.users_here .users_box').html('<h2 class="title">Loadding...</h2>');
    // ajax
    $.ajax({
        "method" : "get",
        "url" : "https://queenlive.site/betel/last_user_result",
        "data" : {
            'authkey': $('#authkey').val(),
           'authtoken': $('#authtoken').val(),

        },
        success:function(res){
            
     const rewards = res.data.map((curE) => {
  let result;
  let win_amount;
  if (curE.status == 1) {
    result = "win";
    win_amount = curE.win_balance;
    
  } else if (curE.status == 10) {
    result = "Loss";
    win_amount = '-';
  } else {
    result = "Hold";
    win_amount = '-';
  }

  if (curE.pot_no === "watermelon") {
    return `
      <div class="row col-12" style="margin-left: -2px; background: #ffffff5e; border-radius: 4px; border-radius: 5px; margin-bottom: 5px;">
        <div class="col-4 text-center text-dark">${curE.tray_id}</div> 
        <div class="col-2 text-center text-dark">
          <img src="https://queenlive.site/public/game/new/image/watermelon.png" alt="Saven Winner" style="width: 21px;">
        </div> 
        <div class="col-2 text-center text-dark">
          ${result}
        </div>
		<div class="col-2 text-center text-dark">
          ${curE.amount}
        </div>	<div class="col-2 text-center text-dark">
          ${win_amount}
        </div>
      </div>
    `;
  } else if (curE.pot_no === "saven_win") {
    return `
      <div class="row col-12" style="margin-left: -2px; background: #ffffff5e; border-radius: 4px; border-radius: 5px; margin-bottom: 5px;">
        <div class="col-4 text-center text-dark">${curE.tray_id}</div> 
        <div class="col-2 text-center text-dark">
          <img src="https://queenlive.site/public/game/new/image/lemon.png" alt="Saven Winner" style="width: 21px;">
        </div>
        <div class="col-2 text-center text-dark">
          ${result}
        </div> 
		<div class="col-2 text-center text-dark">
          ${curE.amount}
        </div>
        <div class="col-2 text-center text-dark">
          ${win_amount}
        </div>
      </div>
    `;
  } else {
    return `
      <div class="row col-12" style="margin-left: -2px; background: #ffffff5e; border-radius: 4px; border-radius: 5px; margin-bottom: 5px;">
        <div class="col-4 text-center text-dark">${curE.tray_id}</div> 
        <div class="col-2 text-center text-dark">
          <img src="https://queenlive.site/public/game/new/image/apple.png" alt="Saven Winner" style="width: 21px;">
        </div> 
        <div class="col-2 text-center text-dark">
          ${result}
        </div>
		<div class="col-2 text-center text-dark">
          ${curE.amount}
        </div>
        <div class="col-2 text-center text-dark">
          ${win_amount}
        </div>
      </div>
    `;
  }
});



      
        

            $('.Rules_here .body').html(rewards);
        }
    });
}


// Remove all cache from localStorage
localStorage.clear();

// Remove all cache from sessionStorage
sessionStorage.clear();

</script>
<script>
  document.addEventListener("contextmenu", (e) => e.preventDefault());
  document.onkeydown = function (e) {
    if (e.keyCode == 123 || // F12
        (e.ctrlKey && e.shiftKey && (e.keyCode == 73 || e.keyCode == 74)) || // Ctrl+Shift+I/J
        (e.ctrlKey && e.keyCode == 85)) { // Ctrl+U
      return false;
    }
  };
  document.addEventListener('touchstart', function(e) {
    if (e.touches.length > 1) {
      e.preventDefault();
    }
  }, { passive: false });
</script>


</body>

</html>