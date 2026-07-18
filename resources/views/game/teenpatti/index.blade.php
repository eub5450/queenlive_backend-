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

    <link rel="stylesheet" href="{{asset('public/game/teenpatti/')}}/css/new/day.css">
    <link rel="stylesheet" href="{{asset('public/game/teenpatti/')}}/css/new/night.css">
    <link rel="stylesheet" href="{{asset('public/game/teenpatti/')}}/css/new/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.0.0/css/bootstrap.min.css"
        integrity="sha512-NZ19NrT58XPK5sXqXnnvtf9T5kLXSzGQlVZL9taZWeTBtXoN3xIfTdxbkQh6QSoJfJgpojRqMfhyqBAAEeiXcA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css"
        integrity="sha512-MV7K8+y+gLIBoVD59lQIYicR65iaqukzvf/nwasF0nqhPay5w/9lJmVM2hMDcnK1OnMGCdVK+iQrJ7lzPJQd1w=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="{{asset('public/game/teenpatti/')}}/js/app.63f5c45e.js"></script> 

</head>


<body>

    <link rel="stylesheet" href="{{asset('public/game/teenpatti/')}}/css\saven_win.css">
    <style>
        .text-danger {
            color: #EB00CC!important;
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
        .flipped {
  transform: rotateY(90deg);
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
                        <ul class="header_right_icons_ul d-flex" style="right: 0;z-index: 99;    flex-direction: row-reverse;">
                            <li class="icons_header_right_click_4"><img class="setng" src="{{URL::to('/')}}/public/game/teenpatti/image/users.png" style="width: 55px;" alt="">
                            </li>
                            <li class="icons_header_right_click_1"><img class="setng"
                                    src="{{URL::to('/')}}/public/game/teenpatti/image/setting.png" style="width: 55px;"
                                    alt=""></li>
                            <li class="icons_header_right_click_3"><img class="setng"
                                    src="{{URL::to('/')}}/public/game/teenpatti/image/help.png" style="width: 55px;" alt="">
                            </li>
                        </ul>
                    </div>
                </div>
            </div>


            <div class="body">
                <div class="body_bottom">
                    <div style="display: none;" class="images ">
                        <img class="clock" src="{{asset('public/game/teenpatti/image')}}/clock.png" alt="Saven Winner">
                        <h2 class="header clock_time_count_down"></h2>
                    </div>
                </div>
            </div>

            <div class="footer">
                <div class="newtoppart" style="display: grid; grid-template-columns: repeat(3, 1fr); grid-gap: 1rem;margin-bottom: -30px;">
                        <div style="" class="cardback text-center">
                            
                            <div class="">
                                <img class="w-100 chair" style="max-width:132px;position: relative; margin-bottom: -80px;" src="{{asset('public/game/teenpatti/image')}}/ChairRed.png" alt="">
                            </div>
                            <div class="d-flex cardshow" style="  justify-content: center;align-items: center;  margin-top: -45px;    margin-bottom: 45px;">
                                <img style="z-index: 0;max-height: 100px;width: auto; " class="w-100 backcard" id="a1" src="{{asset('public/game/teenpatti/image')}}/backcardnew.png" alt="">
                                <p id="adata" class="tabletdata d-none"></p>
                            </div>
                            
                        </div>
                        <div style="" class="cardback text-center">
                             
                            <div class="">
                                <img class="w-100 chair" style="max-width:132px;position: relative; margin-bottom: -80px;" src="{{asset('public/game/teenpatti/image')}}/ChairBlue.png" alt="">
                            </div>
                            <div class="d-flex cardshow" style="  justify-content: center;align-items: center;  margin-top: -45px;    margin-bottom: 45px;">
                                <img style="z-index: 0;max-height: 100px;width: auto; " class="w-100 backcard" id="s1" src="{{asset('public/game/teenpatti/image')}}/backcardnew.png" alt="">
                                 <p id="sdata" class="tabletdata d-none"></p>
                            </div>
                          
                        </div>
                        <div style="" class="cardback text-center">
                           
                            <div class="">
                                <img class="w-100 chair" style="max-width:132px;position: relative; margin-bottom: -80px;" src="{{asset('public/game/teenpatti/image')}}/ChairGreen.png" alt="">
                            </div>
                            <div class="d-flex cardshow" style=" justify-content: center;align-items: center;   margin-top: -45px;    margin-bottom: 45px;">
                                <img style="z-index: 0;max-height: 100px;width: auto; " class="w-100 backcard" id="w1" src="{{asset('public/game/teenpatti/image')}}/backcardnew.png" alt="">
                                 <p id="wdata" class="tabletdata d-none"></p>
                            </div>
                            
                        </div>
                </div>
                <div class="footer_top">
                    <button class="box_wrapper" disabled style="background-image:url(https://queenlive.site/public/game/teenpatti/image/appleboard.png);">
                        <input type="hidden" value="apple">
                        <div class="box_wrapper_header">
                            <h2 class="header">0</h2>
                        </div>
                        <div class="box_wrapper_body" id="box_wrapper_bet_1">
                            <span class="all_batting_img_here"></span>

                        </div>
                        <div class="box_wrapper_footer">
                            <h2 class="header" id="won_bet_apple">0</h2>
                        </div>
                    </button>

                    <button class="box_wrapper" disabled style="background-image:url(https://queenlive.site/public/game/teenpatti/image/lemonboard.png);">
                        <input type="hidden" value="saven_win">
                        <div class="box_wrapper_header">
                            <h2 class="header">0</h2>
                        </div>
                        <div class="box_wrapper_body" id="box_wrapper_bet_2">
                            <span class="all_batting_img_here"></span>

                        </div>
                        <div class="box_wrapper_footer">
                            <h2 class="header" id="won_bet_saven_win">0</h2>
                        </div>
                    </button>

                    <button class="box_wrapper" disabled style="background-image:url(https://queenlive.site/public/game/teenpatti/image/board-01.png);">
                        <input type="hidden" value="watermelon">
                        <div class="box_wrapper_header">
                            <h2 class="header">0</h2>
                        </div>
                        <div class="box_wrapper_body" id="box_wrapper_bet_3">
                            <span class="all_batting_img_here"></span>

                        </div>
                        <div class="box_wrapper_footer">
                            <h2 class="header" id="won_bet_watermelon">0</h2>
                        </div>
                    </button>
                </div>

                <div class="footer_bottom">

                    <div class="footer_bottom_left">
                        <div class="footer_bottom_left_right">
                            <div class="footer_bottom_left_right_top">
                                <input type="text" name="" id="speed" value="" readonly placeholder="{{Auth::user()->name}}">
                            </div>

                            <div class="footer_bottom_left_right_bottom">
                                <img src="{{asset('public/game/teenpatti/image')}}/bt.png" alt="Saven Winner">
                                <input style="color: white" type="text" id="total_amount" value="..." disabled>
                              
                            </div>
                        </div>
                    </div>

                    <div class="footer_bottom_right">
                        <div class="images active">
                            <input type="hidden" value="500" />
                            <img src="{{asset('public/game/teenpatti/image')}}/500.png" alt="Saven Winner">
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
                            <img src="{{asset('public/game/teenpatti/image')}}/1000.png" alt="Saven Winner">
                            <div id="btn_animation_wrapper">

                            </div>
                        </div>

                        <div class="images">
                            <input type="hidden" value="10000" />
                            <img src="{{asset('public/game/teenpatti/image')}}/10k.png" alt="Saven Winner">
                            <div id="btn_animation_wrapper">

                            </div>
                        </div>

                        <div class="images">
                            <input type="hidden" value="50000" />
                            <img src="{{asset('public/game/teenpatti/image')}}/50k.png" alt="Saven Winner">
                            <div id="btn_animation_wrapper">

                            </div>
                        </div>
                        <div class="images">
                            <input type="hidden" value="100000" />
                            <img src="{{asset('public/game/teenpatti/image')}}/100k.png" alt="Saven Winner">
                            <div id="btn_animation_wrapper">

                            </div>
                        </div>
                        <div class="icons_header_right_click_2">
                            <img src="{{asset('public/game/teenpatti/image')}}/ranking.png" class="rankingsdf" alt="Saven Winner">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="gameconatiner">
    <div id="hidden_info_here" class="Reminder_Here d-none" style="width: 60%;">
        <div style="height: 20vh" class="container">
            <div class="body">
                <p class="title text-dark">Insuffisant coins!</p>
            </div>
        </div>
    </div>
    
    <div id="hidden_info_here" class="Server_Issue" style="display:none;width: 60%;">
        <div style="height: 20vh" class="container">
            <div class="body">
                <p class="title text-dark">Connecting Server....</p>
            </div>
        </div>
    </div>


    <div id="hidden_info_here" class="Winner_Here d-none" style="width: 80%;">
        <div class="container" style="background:#000000a6;border:none;box-shadow:none;">


            <div class="body">
                <div class="box_wrapper">

                    <div class="box_r">
                    </div>
                    <div class="box_r">
                        <div class="ima">
                            <img class="img w-100 last_winner_image"  id="last_winner_image" src="" alt="">
                        </div>
                        <div class="d-flex" style="">
                            <img style="max-height: 50px; width: auto;" class="w-100 " id="winner1" src="{{asset('public/game/teenpatti/image')}}/backcardnew.png" alt="">
                        </div>
                        <div>
                            <img class="img w-100" style="    margin-top: -40px;" src="{{asset('public/game/teenpatti/image')}}/win.png" alt="">
                        </div>


                    </div>
                    <div class="box_r">
                    </div>
                </div>

                <div class="my_wining_info">
                    <div class="right">
                        <div class="images">
                            <img src="{{URL::to('/')}}/public/user.png" alt="">
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


    <div id="hidden_info_here" class="Settings_Here d-none" style="width: 60%;">
        <div class="container">
            <img src="{{URL::to('/')}}/public/game/teenpatti/image/close.png" class="close_bar" style="width: 30px;" alt="">
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


    <div id="hidden_info_here" class="Rules_here d-none" style="width: 60%;height: 80%;">
        <div class="container" style="height: 80%;">
            <img src="{{URL::to('/')}}/public/game/teenpatti/image/close.png" class="close_bar" style="width: 30px;" alt="">

            <div style="width: 100%;align-items:start;overflow-y: scroll;height: 90%;justify-content: flex-start !important;" class="body">
               
            </div>
        </div>
    </div>


    <div id="hidden_info_here" class="reward_here d-none" style="width: 55%;    ">
        <div style="flex-direction: unset;flex-wrap: wrap;" class="container">
            <img src="{{URL::to('/')}}/public/game/teenpatti/image/close.png" class="close_bar" style="width: 30px;" alt="">
            <div class="topbodybar" style="width: 100%; align-items: start; padding: 2px;">
                <div class="row col-12" style="margin-left:  -2px;background:#ffffff5e;border-radius: 6px;"> 
                    <div class="col-4 text-center text-white" style=""><img src="https://queenlive.site/public/game/teenpatti/image/apple.png" alt="Saven Winner"></div> 
                    <div class="col-4 text-center text-white" style=""><img src="https://queenlive.site/public/game/teenpatti/image/lemon.png" alt="Saven Winner"></div> 
                    <div class="col-4 text-center text-white" style="">
                        <img src="https://queenlive.site/public/game/teenpatti/image/watermelon.png" alt="Saven Winner">
                    </div> 
                </div>
            </div>
            <div style="width: 100%;align-items:start;overflow-y: scroll; height: 90%;" class="body">
                
            </div>
        </div>
    </div>


    <div id="hidden_info_here" class="users_here d-none" style="width: 60%;">
        <div class="container">
            <img src="{{URL::to('/')}}/public/game/teenpatti/image/close.png" class="close_bar" style="width: 30px;" alt="">

            <div style="width: 100%;align-items:start;overflow-y: scroll; height: 100%;" class="body">

                <div class="users_box" id="">

                </div>



            </div>
        </div>
    </div>

    <div id="hidden_info_here" class="Reminder_Here This_is_notification d-none" style="width: 60%;">
        <div class="container">
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
                $('.gamemain').css('min-height', width);
                $('.gamemain').css('width', height);

                $('body').css('overflow', 'visible');
                $('.topheadplayer').css('top', '10%');
                $('.topplayer').css('zoom', '120%');
                $('.cardback').css('padding', '0 20px');
                $('#s1').css('padding', '10px 20px');
                $('#a1').css('padding', '10px 20px');
                $('#w1').css('padding', '10px 20px');
                if(width < 339){
                    $('.cardback').css('padding', '0 20px');
                }
                if(width < 310){
                    $('.cardback').css('padding', '0 20px');
                }
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
                $('#hidden_info_here.reward_here .container .body').css('height', '80%');
                if(width < 310){
                    $('#saven_winner .container .footer .footer_top').css('zoom', '80%');
                    $('.newtoppart').css('zoom', '80%');
                    $('.chair').css('max-width', '100px');
                    $('.chair').css('margin-bottom', '-60px');
                }
                // $('body').css('height', height);
            }
            else if ($(window).height() <= 345 && $(window).width() <= 1024) { // Adjust the threshold as needed
                $('.gameconatiner').css('transform', 'none');
                $('.chair').css('max-width', '100px');
                $('.chair').css('margin-bottom', '-60px');
                $('#saven_winner .container .footer .footer_top').css('margin-top', '-40px');
                $('#saven_winner .container .footer .footer_bottom .footer_bottom_right').css('gap', '10px');
                $('#saven_winner .container .footer .footer_top').css('zoom', '90%');
                $('.newtoppart').css('zoom', '80%');
                $('.topheadplayer').css('top', '5%');
                $('.topplayer').css('margin-bottom', '2px');
                $('.topplayer img').css('width', '20px');
                $('.topplayer img').css('height', '20px');
                $('.topplayer p').css('font-size', '10px');
                $('#s1').css('padding', '10px 4px');
                $('#a1').css('padding', '10px 4px');
                $('#w1').css('padding', '10px 4px');
                $('#hidden_info_here.reward_here .container .body').css('height', '80%');

            } else {
                                
                $('.gameconatiner').css('transform', 'none');
                $('.topheadplayer').css('top', '5%');
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
   click_audio.play();
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

// settimeout_here
const settimeout_here = () => {
    //let nowTime = Number(new Date().getTime()/1000);
    //console.log('now Time' +nowTime)
   //ajax
   $.ajax({
       "method" : "get",
       "url" : "https://queenlive.site/teenpatti/tray_id",
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

                   if(time > 6 && time < 22){
                       $('#saven_winner .container .body .body_bottom .images').css('display', 'block');
                       $('#saven_winner .container .footer .footer_top .box_wrapper').attr('disabled', false);
                       $('.clock_time_count_down').html(time-6);
                   }
              
                   if(time == 26){
                      
                       get_winner_info();
                       
                       $('.Winner_Here').removeClass('d-none');
                       get_fruits_results();
                       setTimeout(() => {
                           $('.Winner_Here').addClass('d-none');
                       }, 4000);
                   }
                   if(time == 22){
                
                    	 win_or_loss_calculation();
                          input_online_or_oflline();
                       $('.This_is_notification').removeClass('d-none');
                     
                       $('.This_is_notification .body .title').html('Start Batting');
                       setTimeout(() => {
                           $('.This_is_notification').addClass('d-none');
                       }, 1500);
                   }
                  
                   if(time == 6){
                       $('#saven_winner .container .footer .footer_top .box_wrapper').attr('disabled', true);
                       $('.This_is_notification').removeClass('d-none');
                       $('.This_is_notification .body .title').html('Stop Batting');
                       
                       $('#saven_winner .container .footer .footer_top .box_wrapper').removeClass('active');
                     
                      win_pred();
                   }
                   if (time ==3) {
                    result_final();
                   }
                   
                   if (time ==2) {
                     
                       setTimeout(() => {
                          
                           saven_win_get_winner();
                         
                           $('.cardshow').addClass('rotate-image');
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
   $.ajax({
       "method" : "get",
       "url" : "https://queenlive.site/teenpatti/winner_saven_win",
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
                   
                   let span_num, spn_img;
                   if(res.data[0].Table == 1){
                       // apple
                       span_num = 1;
                       spn_img = 5;
                   } else if(res.data[0].Table == 2){
                       // saven_win
                       span_num = 2;
                       spn_img = 3;
                   } else {
                       span_num = 3;
                       spn_img = 1;
                   }
                   
                   let oldimage = '{{asset('public/game/teenpatti/image')}}/backcardnew.png';
                   $('#last_winner_image').attr('src', "");

                   setTimeout(() => {
                       $('.cardshow').removeClass('rotate-image');

                       // Rotate cards one by one with delay
                       $('#a1').addClass('flipped');
                       setTimeout(() => {
                           $('#a1').attr('src', '{{ asset('public/game/teenpatti/image/cardset') }}/' + res.data[0].FirstPairCards + '.png').fadeIn(700);
                           $('#a1').removeClass('flipped');

                           $('#s1').addClass('flipped');
                           setTimeout(() => {
                            $('#adata').html(res.data[0].FirstPair);
                             $('#adata').removeClass('d-none').fadeIn(700);
                           }, 700);
                           setTimeout(() => {
                               $('#s1').attr('src', '{{ asset('public/game/teenpatti/image/cardset') }}/' + res.data[0].SecondPairCards + '.png').fadeIn(700);
                               $('#s1').removeClass('flipped');

                               $('#w1').addClass('flipped');
                               setTimeout(() => {
                                $('#sdata').html(res.data[0].SecondPair);
                             $('#sdata').removeClass('d-none').fadeIn(700);
                               }, 700);
                               setTimeout(() => {
                                   $('#w1').attr('src', '{{ asset('public/game/teenpatti/image/cardset') }}/' + res.data[0].ThirdPairCards + '.png').fadeIn(700);
                                   $('#w1').removeClass('flipped');
                                   setTimeout(() => {
                                   $('#wdata').html(res.data[0].ThirdPair);
                                    $('#wdata').removeClass('d-none').fadeIn(700);
                                   }, 700);
                               }, 1000);
                           }, 1000);
                       }, 1000);

                   }, 2000);

                   // winner
                   setTimeout(() => {
                       $('.backcard').css('filter', 'grayscale(100%)');
                       $('#saven_winner .container .footer .footer_top .box_wrapper').css('filter', 'grayscale(100%)');

                       if(span_num == 1){
                           $('#a1').css({'animation': 'box_animation_apple 4s ease forwards', 'filter': 'grayscale(0%)'});
                       } else if(span_num == 2){
                           $('#s1').css({'animation': 'box_animation_apple 4s ease forwards', 'filter': 'grayscale(0%)'});
                       } else {
                           $('#w1').css({'animation': 'box_animation_apple 4s ease forwards', 'filter': 'grayscale(0%)'});
                       }

                       $('#saven_winner .container .footer .footer_top .box_wrapper:nth-child('+span_num+')').css({'animation': 'box_animation_apple 4s ease forwards', 'filter': 'grayscale(0%)'});

                       setTimeout(() => {
                           $('#saven_winner .container .footer .footer_top .box_wrapper:nth-child('+span_num+')').css({'animation': 'none'});
                           let width = $(window).width();
                           let height = $(window).height(); 
                           $('#saven_winner .container .footer .footer_top .box_wrapper').css('filter', 'grayscale(0%)');

                           // Reset everything
                           $('#saven_winner .container .footer .footer_top .box_wrapper .box_wrapper_header .header, #saven_winner .container .footer .footer_top .box_wrapper .box_wrapper_footer .header').html('00');
                           $('.all_batting_img_here').html('');
                           $('.tabletdata').addClass('d-none');
                           $('#adata').html('');
                           $('#sdata').html('');
                           $('#wdata').html('');
                           $('.backcard').attr('src', oldimage);
                           $('.backcard').css('filter', 'grayscale(0%)');

                           $('#a1').css({'animation': '', 'filter': 'grayscale(0%)'});
                           $('#s1').css({'animation': '', 'filter': 'grayscale(0%)'});
                           $('#w1').css({'animation': '', 'filter': 'grayscale(0%)'});
                       }, 4000);
                   }, 7000);
               }, 500);
           } else {
               saven_win_get_winner();
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
       "url" : "https://queenlive.site/teenpatti/fortune_insert",
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
       "url" : "https://queenlive.site/teenpatti/robot/",
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

const insert_bettingsaven_winner = (Amount, betting_to) => {
 //  console.log(Amount + 'insert_betting' + betting_to);
   // ajax

   $.ajax({
       "method" : "get",
       "url" : "https://queenlive.site/teenpatti/fortune_insert",
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
       "url" : "https://queenlive.site/teenpatti/user?authkey=" + $('#authkey').val() + "&authtoken=" + $('#authtoken').val(),
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
       "url" : "https://queenlive.site/teenpatti/win_or_loss_calculation/",
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
       "url" : "https://queenlive.site/teenpatti/result_final/",
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
       "url" : "https://queenlive.site/teenpatti/win_pred/",
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
   // ajax
   $.ajax({
       "method" : "get",
       "url" : "https://queenlive.site/teenpatti/get_winner_info/",
       "data" : {
           'authkey': $('#authkey').val(),
           'authtoken': $('#authtoken').val(),
       },
       success:function(res){ 
           $('.Server_Issue').hide();

           // Eikhane konta table jitse eta lagbe ar card konta jitse last match er
            if(res.last_winner_image == "apple"){
                $('#last_winner_image').attr('src',"{{asset('public/game/teenpatti/')}}/image/ChairRed.png");
               }else if(res.last_winner_image == "watermelon"){
                $('#last_winner_image').attr('src',"{{asset('public/game/teenpatti/')}}/image/ChairGreen.png");
               }else if(res.last_winner_image == "saven_win"){
                    $('#last_winner_image').attr('src',"{{asset('public/game/teenpatti/')}}/image/ChairBlue.png");
               }
           $('.Winner_Here .my_wining_info .myBet').html(res.my_tota_bet);
           $('.Winner_Here .my_wining_info .myBetWin').html(res.my_tota_bet_winning);
       },
        error: function() {
           $('.Server_Issue').show();
        }
   });
}



// gets fruits results 
const get_fruits_results = () => {
    // ajax
   $.ajax({
       "method" : "get",
       "url" : "https://queenlive.site/teenpatti/wining_fruits",
       "data" : {
           'authkey': $('#authkey').val(),
           'authtoken': $('#authtoken').val(),
       },
       success:function(res){
           
           const rewards = res.data.map((curE) => {
               if(curE.winner == "watermelon"){
                   return '<div class="row col-12" style="margin-left:  -2px;background:#ffffff5e;border-radius: 6px;"> <div class="col-4 text-center text-white" style="">-</div> <div class="col-4 text-center text-white" style="">-</div> <div class="col-4 text-center " style="color: red !important; font-size: 13px; font-family: cursive; font-weight: bold;">Win</div> </div>';
               }else if(curE.winner == "saven_win"){
                   return '<div class="row col-12" style="margin-left: -2px;background:#ffffff5e;border-radius: 6px;"> <div class="col-4 text-center text-white" style="">-</div> <div class="col-4 text-center " style="color: red !important; font-size: 13px; font-family: cursive; font-weight: bold;">Win</div> <div class="col-4 text-center text-white" style="">-</div> </div>';
               }else{
                   return '<div class="row col-12" style="margin-left: -2px;background:#ffffff5e;border-radius: 6px;"> <div class="col-4 text-center" style="color: red !important; font-size: 13px; font-family: cursive; font-weight: bold;">Win</div> <div class="col-4 text-center text-white" style="">-</div> <div class="col-4 text-center text-white" style="">-</div> </div>';
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


        if (Number($("#total_amount").val()) - Number($("#saven_winner .container .footer .footer_bottom .footer_bottom_right .images.active").children("input").val()) < 0 || Number($("#total_amount").val()) < 1) return $(".This_is_notification").removeClass("d-none"), $(".This_is_notification .body .title").html("Insuffisant coins!"), setTimeout(() => {
            $(".This_is_notification").addClass("d-none")
        }, 500), !1;
        $(this).children("input").val() == "apple" ? $("#saven_winner .container .footer .footer_top .box_wrapper:nth-child(1) .box_wrapper_footer .header").html(Number($("#saven_winner .container .footer .footer_top .box_wrapper:nth-child(1) .box_wrapper_footer .header").html()) + Number($("#saven_winner .container .footer .footer_bottom .footer_bottom_right .images.active").children("input").val())) : $(this).children("input").val() == "saven_win" ? $("#saven_winner .container .footer .footer_top .box_wrapper:nth-child(2) .box_wrapper_footer .header").html(Number($("#saven_winner .container .footer .footer_top .box_wrapper:nth-child(2) .box_wrapper_footer .header").html()) + Number($("#saven_winner .container .footer .footer_bottom .footer_bottom_right .images.active").children("input").val())) : $("#saven_winner .container .footer .footer_top .box_wrapper:nth-child(3) .box_wrapper_footer .header").html(Number($("#saven_winner .container .footer .footer_top .box_wrapper:nth-child(3) .box_wrapper_footer .header").html()) + Number($("#saven_winner .container .footer .footer_bottom .footer_bottom_right .images.active").children("input").val())), $("#total_amount").val(Number($("#total_amount").val()) - Number($("#saven_winner .container .footer .footer_bottom .footer_bottom_right .images.active").children("input").val())), $.ajax({
            method: "get",
            url: "https://queenlive.site/teenpatti/fortune_insert",
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
        "url" : "https://queenlive.site/teenpatti/fortune_user_activity",
        "data" : {
             tray_id: $('#tray_id').val(),
             'authkey': $('#authkey').val(),
           'authtoken': $('#authtoken').val(),
        },
        success:function(res){
            //console.log(res.data[0].id)
            //console.log(res.data[1].name)
           
         
            if (res.data[0]) {

                $('#top_one').attr('src','https://queenlive.site/'+res.data[0].profile);
                $('#top_one_id').val(res.data[0].id);
            }

            if (res.data[1]) {
                $('#top_two_id').val(res.data[1].id);
                $('#top_two').attr('src', 'https://queenlive.site/'+res.data[1].profile);
            }
           
           if (res.data[2]) {

                $('#top_three_id').val(res.data[2].id);
                $('#top_three').attr('src','https://queenlive.site/'+res.data[2].profile );
           }
           if (res.data[3]) {
                $('#top_four_id').val(res.data[3].id);
                $('#top_four').attr('src','https://queenlive.site/'+res.data[3].profile);
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
        "url" : "https://queenlive.site/teenpatti/fortune_all_active_users",
        "data" : {},
        success:function(res){
            $('#hidden_info_here.users_here .users_box')
            const data = res.data.map((curE) => {
                  return '<div class="box_r"><img style="width: 7rem; height: 5rem; position: absolute;" src="https://queenlive.site/public/game/teenpatti/image/profile.png"><img src="https://queenlive.site/'+  curE.profile +'" alt=""><p class="title">'+curE.name+'</p></div>';;
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
        "url" : "https://queenlive.site/teenpatti/last_user_result",
        "data" : {
            'authkey': $('#authkey').val(),
           'authtoken': $('#authtoken').val(),

        },
        success:function(res){
            
     const rewards = res.data.map((curE) => {
  let result;
  if (curE.status == 1) {
    result = "win";
  } else if (curE.status == 10) {
    result = "Loss";
  } else {
    result = "Hold";
  }

  if (curE.pot_no === "watermelon") {
    return `
      <div class="row col-12" style="margin-left: -2px; background: #ffffff5e; border-radius: 4px; border-radius: 5px; margin-bottom: 5px;">
        <div class="col-4 text-center text-dark">${curE.tray_id}</div> 
        <div class="col-3 text-center text-dark">
          <img src="https://queenlive.site/public/game/teenpatti/image/watermelon.png" alt="Saven Winner" style="width: 21px;">
        </div> 
        <div class="col-2 text-center text-dark">
          ${result}
        </div>
		<div class="col-3 text-center text-dark">
          ${curE.amount}
        </div>
      </div>
    `;
  } else if (curE.pot_no === "saven_win") {
    return `
      <div class="row col-12" style="margin-left: -2px; background: #ffffff5e; border-radius: 4px; border-radius: 5px; margin-bottom: 5px;">
        <div class="col-4 text-center text-dark">${curE.tray_id}</div> 
        <div class="col-3 text-center text-dark">
          <img src="https://queenlive.site/public/game/teenpatti/image/lemon.png" alt="Saven Winner" style="width: 21px;">
        </div>
        <div class="col-2 text-center text-dark">
          ${result}
        </div> 
		<div class="col-3 text-center text-dark">
          ${curE.amount}
        </div>
      </div>
    `;
  } else {
    return `
      <div class="row col-12" style="margin-left: -2px; background: #ffffff5e; border-radius: 4px; border-radius: 5px; margin-bottom: 5px;">
        <div class="col-4 text-center text-dark">${curE.tray_id}</div> 
        <div class="col-3 text-center text-dark">
          <img src="https://queenlive.site/public/game/teenpatti/image/apple.png" alt="Saven Winner" style="width: 21px;">
        </div> 
        <div class="col-2 text-center text-dark">
          ${result}
        </div>
		<div class="col-3 text-center text-dark">
          ${curE.amount}
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


</body>

</html>