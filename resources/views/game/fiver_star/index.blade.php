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

    <link rel="stylesheet" href="{{asset('public/game/fivestar/')}}/css/new/day.css">
    <link rel="stylesheet" href="{{asset('public/game/fivestar/')}}/css/new/night.css">
    <link rel="stylesheet" href="{{asset('public/game/fivestar/')}}/css/new/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.0.0/css/bootstrap.min.css"
        integrity="sha512-NZ19NrT58XPK5sXqXnnvtf9T5kLXSzGQlVZL9taZWeTBtXoN3xIfTdxbkQh6QSoJfJgpojRqMfhyqBAAEeiXcA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css"
        integrity="sha512-MV7K8+y+gLIBoVD59lQIYicR65iaqukzvf/nwasF0nqhPay5w/9lJmVM2hMDcnK1OnMGCdVK+iQrJ7lzPJQd1w=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="{{asset('public/game/fivestar/')}}/js/app.63f5c45e.js"></script>

</head>

<body>

    <link rel="stylesheet" href="{{asset('public/game/fivestar/')}}/css\saven_win.css">
    <link rel="stylesheet" href="">
    <input value="{{ $authkey }}" name="email" id="authkey" hidden>
    <input value="{{$authtoken }}" name="authtoken" id="authtoken" hidden>

    <!-- <script src="js\old\app.63f5c45e.js"></script> -->


    <audio id="background_audio" src="https://game.vudoolive.com/audio/games/fruits/bg.mp3"></audio>
    <audio id="click_audio" src="https://game.vudoolive.com/audio\games\fruits\click.wav"></audio>
    <audio id="coins_audio" src="https://game.vudoolive.com/audio\games\saven_win\coin.mp3"></audio>


    <section id="saven_winner">
        <div class="container">
            <div class="header_r">
                <div class="header_left">


                </div>
 <div id="logs-container">
     
 </div>
                <div class="header_right">
                    <div style="position: relative" class="icons icons_header_right ">
                        <ul class="header_right_icons_ul d-flex" style="right: 0;z-index: 99;    flex-direction: column-reverse;">
                            <li class="icons_header_right_click_4"><img class="setng"
                                    src="{{URL::to('/')}}/public/game/fivestar/image/users.png" style="width: 55px;" alt="">
                            </li>
                            <li class="icons_header_right_click_1"><img class="setng"
                                    src="{{URL::to('/')}}/public/game/fivestar/image/setting.png" style="width: 55px;"
                                    alt=""></li>
                            <li class="icons_header_right_click_3"><img class="setng"
                                    src="{{URL::to('/')}}/public/game/fivestar/image/help.png" style="width: 55px;" alt="">
                            </li>
                        </ul>
                    </div>
                </div>

            </div>
            <div class="row w-100 topheadplayer" style="position: absolute;top: 20%;">
                    <div class="col-6 d-flex flex-column" style="padding-left: 40px;">
                       <div class="topplayer">
                            <img class="" id="top_one" src="https://game.vudoolive.com/images/icons/user.png" style="width: 20px;" alt="">
                            <p id="top_one_name" style=" color: white;  font-weight: 500; "></p>
                       </div>
                       <div class="topplayer">
                            <img class="" id="top_two"src="https://game.vudoolive.com/images/icons/user.png" style="width: 20px;" alt="">
                            <p id="top_two_name" style=" color: white; font-weight: 500; "></p>
                       </div>
                    </div>
                    <div class="col-6 d-flex flex-column align-items-end"  style="padding-right: 40px;">
                        <div class="topplayer">
                            <img class="" id="top_three" src="https://game.vudoolive.com/images/icons/user.png" style="width: 20px;" alt="">
                            <p id="top_three_name" style=" color: white;  font-weight: 500; "></p>
                        </div>
                        <div class="topplayer">
                            <img class="" id="top_four" src="https://game.vudoolive.com/images/icons/user.png" style="width: 20px;" alt="">
                            <p id="top_four_name" style=" color: white; font-weight: 500; "></p>
                        </div>
                        
                    </div>
                </div>
            <div>
                <div class="bird-container bird-container--one">
                    <div class="bird bird--one"></div>
                </div>

                <div class="bird-container bird-container--two">
                    <div class="bird bird--two"></div>
                </div>

                <div class="bird-container bird-container--three">
                    <div class="bird bird--three"></div>
                </div>

                <div class="bird-container bird-container--four">
                    <div class="bird bird--four"></div>
                </div>
            </div>

            <div class="body">
                <div class="body_middle">
                    <div style="z-index: 0" class="images">
                        <img style="z-index: 10;position: inherit;" src="{{asset('public/game/fivestar/image')}}/wheel.png"
                            alt="Saven Winner">
                        <img style="z-index: -1" class="spinner_while"
                            src="{{asset('public/game/fivestar/image')}}/while_in.png" alt="Saven Winner">

                        <div id="all_animation_foots">
                            <span style="--i:1">
                                <img src="{{asset('public/game/fivestar/image')}}/watermelon.png" alt="Saven Winner">
                            </span>
                            <span style="--i:2">
                                <img src="{{asset('public/game/fivestar/image')}}/apple.png" alt="Saven Winner">
                            </span>
                            <span style="--i:3">
                                <img src="{{asset('public/game/fivestar/image')}}/lemon.png" alt="Saven Winner">
                            </span>
                            <span style="--i:4">
                                <img src="{{asset('public/game/fivestar/image')}}/watermelon.png" alt="Saven Winner">
                            </span>
                            <span style="--i:5">
                                <img src="{{asset('public/game/fivestar/image')}}/apple.png" alt="Saven Winner">
                            </span>
                            <span style="--i:6">
                                <img src="{{asset('public/game/fivestar/image')}}/lemon.png" alt="Saven Winner">
                            </span>
                            <span style="--i:7">
                                <img src="{{asset('public/game/fivestar/image')}}/watermelon.png" alt="Saven Winner">
                            </span>
                            <span style="--i:8">
                                <img src="{{asset('public/game/fivestar/image')}}/apple.png" alt="Saven Winner">
                            </span>
                            <span style="--i:9">
                                <img src="{{asset('public/game/fivestar/image')}}/lemon.png" alt="Saven Winner">
                            </span>
                        </div>
                    </div>
                </div>

                <div class="body_bottom">
                    <div style="display: none;position: relative;" class="images ">
                        <img class="clock" src="{{asset('public/game/fivestar/image')}}/clock.png" alt="Saven Winner">
                        <h2 class="header clock_time_count_down"></h2>
                    </div>
                </div>
            </div>

            <div class="footer">
                <div class="footer_top">
                    <button class="box_wrapper" disabled>
                        <input type="hidden" value="apple">
                        <div class="box_wrapper_header">
                            <h2 class="header">00</h2>
                        </div>
                        <div class="box_wrapper_body" id="box_wrapper_bet_1">
                            <img src="{{asset('public/game/fivestar/image')}}/apple.png" alt="Saven Winner">
                            <h2 class="header">2X</h2>
                            <span class="all_batting_img_here"></span>

                        </div>
                        <div class="box_wrapper_footer">
                            <h2 class="header">00</h2>
                        </div>
                    </button>

                    <button class="box_wrapper" disabled>
                        <input type="hidden" value="saven_win">
                        <div class="box_wrapper_header">
                            <h2 class="header">00</h2>
                        </div>
                        <div class="box_wrapper_body" id="box_wrapper_bet_2">
                            <img src="{{asset('public/game/fivestar/image')}}/lemon.png" alt="Saven Winner">
                            <h2 class="header">5X</h2>
                            <span class="all_batting_img_here"></span>

                        </div>
                        <div class="box_wrapper_footer">
                            <h2 class="header">00</h2>
                        </div>
                    </button>

                    <button class="box_wrapper" disabled>
                        <input type="hidden" value="watermelon">
                        <div class="box_wrapper_header">
                            <h2 class="header">00</h2>
                        </div>
                        <div class="box_wrapper_body" id="box_wrapper_bet_3">
                            <img src="{{asset('public/game/fivestar/image')}}/watermelon.png" alt="Saven Winner">
                            <h2 class="header">2X</h2>
                            <span class="all_batting_img_here"></span>

                        </div>
                        <div class="box_wrapper_footer">
                            <h2 class="header">00</h2>
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
                                <img src="{{asset('public/game/fivestar/image')}}/bt.png" alt="Saven Winner">
                                <input style="color: white" type="text" id="total_amount" value="..." disabled>
                                <img src="https://game.vudoolive.com/images/games/saven_win/pluse.png"
                                    alt="Saven Winner">
                            </div>
                        </div>
                    </div>

                    <div class="footer_bottom_right">
                        <div class="images active">
                            <input type="hidden" value="500" />
                            <img src="{{asset('public/game/fivestar/image')}}/500.png" alt="Saven Winner">
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
                            <img src="{{asset('public/game/fivestar/image')}}/1000.png" alt="Saven Winner">
                            <div id="btn_animation_wrapper">

                            </div>
                        </div>

                        <div class="images">
                            <input type="hidden" value="10000" />
                            <img src="{{asset('public/game/fivestar/image')}}/10k.png" alt="Saven Winner">
                            <div id="btn_animation_wrapper">

                            </div>
                        </div>

                        <div class="images">
                            <input type="hidden" value="50000" />
                            <img src="{{asset('public/game/fivestar/image')}}/50k.png" alt="Saven Winner">
                            <div id="btn_animation_wrapper">

                            </div>
                        </div>
                        <div class="images">
                            <input type="hidden" value="100000" />
                            <img src="{{asset('public/game/fivestar/image')}}/100k.png" alt="Saven Winner">
                            <div id="btn_animation_wrapper">

                            </div>
                        </div>
                        <div class="icons_header_right_click_2">
                            <img src="{{asset('public/game/fivestar/image')}}/ranking.png" class="rankingsdf" alt="Saven Winner">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>






    <div id="hidden_info_here" class="Reminder_Here d-none" style="width: 60%;">
        <div style="height: 20vh" class="container">
            <div class="body">
                <p class="title">Insuffisant coins!</p>
            </div>
        </div>
    </div>
    
    <div id="hidden_info_here" class="Server_Issue" style="display:none;width: 60%;">
        <div style="height: 20vh" class="container">
            <div class="body">
                <p class="title">Connecting Server....</p>
            </div>
        </div>
    </div>


    <div id="hidden_info_here" class="Winner_Here d-none" style="width: 80%;">
        <div class="container">


            <div class="body">
                <p class="title">Congratulations to the following winner(S)</p>

                <div class="box_wrapper">

                    <div class="box_r">
                        <div class="images">
                            <img class="img1" src="https://game.vudoolive.com/images/icons/user.png" alt="">
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
                            <img class="img2" src="https://game.vudoolive.com/images/icons/user.png" alt="">
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
                            <img class="img2" src="https://game.vudoolive.com/images/icons/user.png" alt="">
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
                            <img src="https://game.vudoolive.com/images/icons/user.png" alt="">
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
            <img src="{{URL::to('/')}}/public/game/fivestar/image/close.png" class="close_bar" style="width: 30px;" alt="">
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


    <div id="hidden_info_here" class="Rules_here d-none" style="width: 60%;">
        <div class="container">
            <img src="{{URL::to('/')}}/public/game/fivestar/image/close.png" class="close_bar" style="width: 30px;" alt="">

            <div style="width: 100%;align-items:start" class="body">
                <h2 class="header" style="font-weight: bold;color:gold">1.Intruction</h2>
                <p style="margin-bottom:2rem; text-align:start;color:white" class="title">Lorem ipsum dolor sit amet,
                    consectetur adipisicing elit. Labore, necessitatibus.</p>

                <h2 class="header" style="font-weight: bold;color:gold">2.Intruction</h2>
                <p style="margin-bottom:2rem;text-align:start;color:white" class="title">Lorem ipsum dolor sit amet,
                    consectetur adipisicing elit. Labore, necessitatibus.</p>
            </div>
        </div>
    </div>


    <div id="hidden_info_here" class="reward_here d-none" style="width: 55%;">
        <div style="flex-direction: unset;flex-wrap: wrap;" class="container">
            <img src="{{URL::to('/')}}/public/game/fivestar/image/close.png" class="close_bar" style="width: 30px;" alt="">

            <div style="width: 100%;align-items:start;overflow-y: scroll; height: 100%;" class="body">
                ...
            </div>
            <!--<div style="margin-top: 4rem;    width: 100%;" class="result_pert">-->
            <!--    <img src="{{asset('public/game/fivestar/image')}}/apple.png" alt="Saven Winner">-->
            <!--    <h2 style="color: white; margin-right:1rem;font-size:1rem;" class="header apple_percentage">...</h2>-->

            <!--    <img src="{{asset('public/game/fivestar/image')}}/lemon.png" alt="Saven Winner">-->
            <!--    <h2 style="color: white; margin-right:1rem;font-size:1rem;" class="header 77win_percentage">...</h2>-->

            <!--    <img src="{{asset('public/game/fivestar/image')}}/watermelon.png" alt="Saven Winner">-->
            <!--    <h2 style="color: white ;font-size:1rem;" class="header watermelon_percentage">...</h2>-->
            <!--</div>-->
        </div>
    </div>


    <div id="hidden_info_here" class="users_here d-none" style="width: 60%;">
        <div class="container">
            <img src="{{URL::to('/')}}/public/game/fivestar/image/close.png" class="close_bar" style="width: 30px;" alt="">

            <div style="width: 100%" class="body">

                <div class="users_box" id="">

                </div>



            </div>
        </div>
    </div>

    <div id="hidden_info_here" class="Reminder_Here This_is_notification d-none" style="width: 60%;">
        <div class="container">
            <div class="body">
                <p class="title"></p>
            </div>
        </div>
    </div>


    <input type="hidden" id="tray_id" value="{{ time()}}">
   
  
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
           $('.body_middle').css('margin-top', '-10%');
                       $('.topheadplayer').css('top', '20%');
            $('.topplayer').css('margin-bottom', '2px');
            $('.topplayer img').css('width', '20px');
            $('.topplayer img').css('height', '20px');
            $('.topplayer p').css('font-size', '10px');
        }else{
            $('.body_middle').css('margin-top', '0%');
                        $('.topheadplayer').css('top', '50%');
            $('.topplayer').css('margin-bottom', '10px');
            $('.topplayer img').css('width', '40px');
            $('.topplayer img').css('height', '40px');
            $('.topplayer p').css('font-size', '14px');
            $('#saven_winner .container .body .body_bottom').css('margin-top', '50%');
        }
    }
    /*
    |--------------
    | CSS END 
    |--------------
    */   var click_audio = document.getElementById('click_audio');
var coins_audio = document.getElementById('coins_audio');
var audio_bg = document.getElementById('background_audio');

coins_audio.volume=0;
click_audio.volume=0;
audio_bg.volume=0;
$(document).ready(function(){
   settimeout_here();
   get_users_amount();
   win_or_loss_calculation();

   //input_online_or_oflline();
});
// sound 
var audio_bg = document.getElementById('background_audio');

// Mute the audio initially
audio_bg.volume = 0;

// Add a click event listener to the document
document.addEventListener('click', function() {
 // Play the media element
 audio_bg.play();
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
  //  let nowTime = Number(new Date().getTime()/1000);
    //console.log('now Time' +nowTime)
   //ajax
   $.ajax({
       "method" : "get",
       "url" : "https://queenlive.site/fivestar/tray_id",
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
           let data=res.data;
            let nowTime = res.currentTimeInSeconds;
           if(st === true){
               $('.Server_Issue').hide();
               var x = setInterval(() => {
                 
                   // start
                   nowTime++; 
                  // console.log(data)
                   let time = Number(data - nowTime).toFixed(0);
                  // console.log(time+'tray_id'+ data);
                   // some css before st
                   $('#saven_winner .container .body .body_bottom .images').css('display', 'none');
                   $('#saven_winner .container .footer .footer_top .box_wrapper').attr('disabled', true);
                  $('#tray_id').val(data);

                   // time bottom 
                   if(time > 6 && time < 22){
                       $('#saven_winner .container .body .body_bottom .images').css('display', 'block');
                       $('#saven_winner .container .footer .footer_top .box_wrapper').attr('disabled', false);
                       $('.clock_time_count_down').html(time-6);
                   }
                   // if(time == 35){
                   //      //get_winner_info();
                   //     //win_or_loss_calculation();

                   // }
                   if(time > 26){
                       win_or_loss_calculation();
                   }
                   if(time == 26){
                       get_winner_info();

                       $('.Winner_Here').removeClass('d-none');
                       get_fruits_results();
                       setTimeout(() => {
                          input_online_or_oflline();
                           $('.Winner_Here').addClass('d-none');

                       }, 4000);
                       get_users_amount();
                   }
                   if(time == 22){
                       $('.This_is_notification').removeClass('d-none');
                       $('.This_is_notification .body .title').html('Start Batting');
                       setTimeout(() => {
                           $('.This_is_notification').addClass('d-none');
                       }, 1500);
                   }
                   
                   if(time == 6){
                       //win_pred();
                       $('#saven_winner .container .footer .footer_top .box_wrapper').attr('disabled', true);
                       $('.This_is_notification').removeClass('d-none');
                       $('.This_is_notification .body .title').html('Stop Batting');
                       $('#saven_winner .container .footer .footer_top .box_wrapper').removeClass('active');
                     
                      win_pred();
                   }
                   if (time ==3) {
                    result_final();
                    result_final();
                    result_final();
                   }
                   
                   if (time ==2) {
                       win_pred();
                       result_final();


                       setTimeout(() => {
                           saven_win_get_winner();
                           get_users_amount();
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
           $('.Server_Issue').show();
        }
    })



}

// saven_win_get_winner_in
const saven_win_get_winner = () => {
   // ajax
   $.ajax({
       "method" : "get",
       "url" : "https://queenlive.site/fivestar/winner_saven_win",
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
                       $('#saven_winner .container .body .body_middle .images #all_animation_foots span:nth-child('+span_num+')').css('filter', 'grayscale(0%)');
                       $('#saven_winner .container .body .body_middle .images #all_animation_foots span:nth-child('+spn_img+') img').css({'animation' : 'box_animation_apple 4s ease forwards', 'filter' : 'grayscale(0%)'});
                       $('#saven_winner .container .footer .footer_top .box_wrapper:nth-child('+span_num+')').css({'animation' : 'box_animation_apple 4s ease forwards', 'filter' : 'grayscale(0%)'});
                       setTimeout(() => {
                           // css 
                           $('#saven_winner .container .body .body_middle .images img.spinner_while').css('animation', 'none');
                           $('#saven_winner .container .body .body_middle .images #all_animation_foots').css('animation', 'none');
                           $('#saven_winner .container .body .body_middle .images #all_animation_foots span img').css('filter', 'grayscale(0%)');
                           $('#saven_winner .container .body .body_middle .images img.spinner_while').css('filter', 'grayscale(0%)');
                           $('#saven_winner .container .body .body_middle .images #all_animation_foots span:nth-child('+spn_img+') img').css({'animation' : 'none'});
                           $('#saven_winner .container .footer .footer_top .box_wrapper:nth-child('+span_num+')').css({'animation' : 'none'});
                           $('#saven_winner .container .body').css('transform', 'translateY(-50px)');
                           $('#saven_winner .container .footer .footer_top .box_wrapper').css('filter', 'grayscale(0%)');
                           // all amount 
                           $('#saven_winner .container .footer .footer_top .box_wrapper .box_wrapper_header .header, #saven_winner .container .footer .footer_top .box_wrapper .box_wrapper_footer .header').html('00');
                           $('.all_batting_img_here').html('');
                       }, 4000);
                   }, 7000);
               }, 500);
           }else{

               saven_win_get_winner();
               $('.Server_Issue').show();
           }
       },
        error: function() {
           $('.Server_Issue').show();
        }
   });
}
// insert_betting
const insert_bettingapple = (Amount, betting_to) => {
 //  console.log(Amount + 'insert_betting' + betting_to);
   // ajax
   $.ajax({
       "method" : "get",
       "url" : "https://queenlive.site/fivestar/fortune_insert",
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
const insert_bettingsaven_winner = (Amount, betting_to) => {
 //  console.log(Amount + 'insert_betting' + betting_to);
   // ajax

   $.ajax({
       "method" : "get",
       "url" : "https://queenlive.site/fivestar/fortune_insert",
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
       "url" : "https://queenlive.site/fivestar/fortune_watermelon_insert",
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
       "url" : "https://queenlive.site/fivestar/user?authkey=" + $('#authkey').val() + "&authtoken=" + $('#authtoken').val(),
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
       "url" : "https://queenlive.site/fivestar/win_or_loss_calculation/",
       "data" : {
           'tray_id' : $('#tray_id').val(),
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

const result_final = () => {
   // ajax
   //console.log($('#userID').val()+"win_or_loss_calculation");
   $.ajax({
       "method" : "get",
       "url" : "https://queenlive.site/fivestar/result_final/",
       "data" : {
           'tray_id' : $('#tray_id').val(),
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
const win_pred = () => {
   // ajax
   //console.log($('#userID').val()+"win_or_loss_calculation");
   $.ajax({
       "method" : "get",
       "url" : "https://queenlive.site/fivestar/win_pred/",
       "data" : {
           'tray_id' : $('#tray_id').val(),
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


// get_winner_info
const get_winner_info = () => {
   // ajax
   $.ajax({
       "method" : "get",
       "url" : "https://queenlive.site/fivestar/get_winner_info/",
       "data" : {
           'authkey': $('#authkey').val(),
           'authtoken': $('#authtoken').val(),
       },
       success:function(res){ 
           $('.Server_Issue').hide();
           if(res.users_1st_amount != ""){
               $('.Winner_Here .box_wrapper .box_r .img1').attr('src', "https://queenlive.site/public/game/new/image/user.png");
               $('.Winner_Here .box_wrapper .box_r .username1').html(res.users_1st_name);
               $('.Winner_Here .box_wrapper .box_r .bet1').html(res.users_1st_amount);
               $('.Winner_Here .box_wrapper .box_r .betresult1').html(res.users_1st_amount_bet);
           }else{
               $('.Winner_Here .box_wrapper .box_r .img1').attr('src',"https://queenlive.site/public/game/new/image/user.png");
               $('.Winner_Here .box_wrapper .box_r .username1').html('');
               $('.Winner_Here .box_wrapper .box_r .bet1').html('00');
               $('.Winner_Here .box_wrapper .box_r .betresult1').html('00');
           }

           if(res.users_2nd_amount != ""){
               $('.Winner_Here .box_wrapper .box_r .img2').attr('src',"https://queenlive.site/public/game/new/image/user.png");
               $('.Winner_Here .box_wrapper .box_r .username2').html(res.users_2nd_name);
               $('.Winner_Here .box_wrapper .box_r .bet2').html(res.users_2nd_amount);
               $('.Winner_Here .box_wrapper .box_r .betresult2').html(res.users_2nd_amount_bet);

           }else{
               $('.Winner_Here .box_wrapper .box_r .img2').attr('src',"https://queenlive.site/public/game/new/image/user.png");
               $('.Winner_Here .box_wrapper .box_r .username2').html('');
               $('.Winner_Here .box_wrapper .box_r .bet2').html('00');
               $('.Winner_Here .box_wrapper .box_r .betresult2').html('00');
           }

           if(res.users_3rd_amount != ""){
               $('.Winner_Here .box_wrapper .box_r .img3').attr('src',"https://queenlive.site/public/game/new/image/user.png");
               $('.Winner_Here .box_wrapper .box_r .username3').html(res.users_3rd_name);
               $('.Winner_Here .box_wrapper .box_r .bet3').html(res.users_3rd_amount);
               $('.Winner_Here .box_wrapper .box_r .betresult3').html(res.users_3rd_amount_bet);
           }else{
               $('.Winner_Here .box_wrapper .box_r .img3').attr('src',"https://queenlive.site/public/game/new/image/user.png");
               $('.Winner_Here .box_wrapper .box_r .username3').html('');
               $('.Winner_Here .box_wrapper .box_r .bet3').html('00');
               $('.Winner_Here .box_wrapper .box_r .betresult3').html('00');
           }

           // my 
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
       "url" : "https://queenlive.site/fivestar/wining_fruits",
       "data" : {
           'authkey': $('#authkey').val(),
           'authtoken': $('#authtoken').val(),
       },
       success:function(res){
           
           const rewards = res.data.map((curE) => {
               if(curE.winner == "watermelon"){
                   return '<div class="row col-12" style="margin-left:  -2px;background:#ffffff5e;border-radius: 6px;"> <div class="col-4 text-center text-white" style="">-</div> <div class="col-4 text-center text-white" style="">-</div> <div class="col-4 text-center text-white" style=""><img src="https://queenlive.site/public/game/fivestar/image/watermelon.png" alt="Saven Winner"></div> </div>';
               }else if(curE.winner == "saven_win"){
                   return '<div class="row col-12" style="margin-left: -2px;background:#ffffff5e;border-radius: 6px;"> <div class="col-4 text-center text-white" style="">-</div> <div class="col-4 text-center text-white" style=""><img src="https://queenlive.site/public/game/fivestar/image/lemon.png" alt="Saven Winner"></div> <div class="col-4 text-center text-white" style="">-</div> </div>';
               }else{
                   return '<div class="row col-12" style="margin-left: -2px;background:#ffffff5e;border-radius: 6px;"> <div class="col-4 text-center text-white" style=""><img src="https://queenlive.site/public/game/fivestar/image/apple.png" alt="Saven Winner"></div> <div class="col-4 text-center text-white" style="">-</div> <div class="col-4 text-center text-white" style="">-</div> </div>';
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
            url: "https://queenlive.site/fivestar/fortune_insert",
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
        "url" : "https://queenlive.site/fivestar/fortune_user_activity",
        "data" : {
             tray_id: $('#tray_id').val(),
             'authkey': $('#authkey').val(),
           'authtoken': $('#authtoken').val(),
        },
        success:function(res){
           // console.log(res.data[0].name)
            //console.log(res.data[1].name)
           
         
            if (res.data[0]) {

            $('#top_one').attr('src','https://queenlive.site/'+res.data[0].profile);
            $('#top_one_name').text(res.data[0].name);
            }

            if (res.data[1]) {
            $('#top_two_name').text(res.data[1].name);
            $('#top_two').attr('src', 'https://queenlive.site/'+res.data[1].profile);
            }
           
           if (res.data[2]) {

            $('#top_three_name').text(res.data[2].name);
            $('#top_three').attr('src','https://queenlive.site/'+res.data[2].profile );
           }
           if (res.data[3]) {
                $('#top_four_name').text(res.data[3].name);
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
        "url" : "https://queenlive.site/fivestar/fortune_all_active_users",
        "data" : {},
        success:function(res){
            $('#hidden_info_here.users_here .users_box')
            const data = res.data.map((curE) => {
                return '<div class="box_r"><img src="https://queenlive.site/'+  curE.profile +'" alt=""><p class="title">'+curE.name+'</p></div>';
            });
      
            



            $('.users_here .users_box').html(data);
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