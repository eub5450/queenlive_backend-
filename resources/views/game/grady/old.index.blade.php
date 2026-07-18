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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css"
        integrity="sha512-MV7K8+y+gLIBoVD59lQIYicR65iaqukzvf/nwasF0nqhPay5w/9lJmVM2hMDcnK1OnMGCdVK+iQrJ7lzPJQd1w=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="{{asset('public/game/grady/')}}/css/style.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="{{asset('public/game/grady/')}}/js/script.js"></script> 
</head>


<body style="background: url('https://img.freepik.com/free-vector/gradient-network-connection-background_23-2148881321.jpg'); " >
<!--<body style="background: url('https://images8.alphacoders.com/362/362134.jpg'); " >-->
    <div class="laodingstart" style="width: 100%; height: 100%; margin: 0; padding: 0; background: #f05a30;position: absolute; z-index: 100;">
        <svg id="preloader" width="240px" height="120px" viewBox="0 0 240 120" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
          
          <path id="loop-normal" class="st1" d="M120.5,60.5L146.48,87.02c14.64,14.64,38.39,14.65,53.03,0s14.64-38.39,0-53.03s-38.39-14.65-53.03,0L120.5,60.5
        L94.52,87.02c-14.64,14.64-38.39,14.64-53.03,0c-14.64-14.64-14.64-38.39,0-53.03c14.65-14.64,38.39-14.65,53.03,0z">
            <animate attributeName="stroke-dasharray" from="500, 50" to="450 50" begin="0s" dur="2s" repeatCount="indefinite" />
            <animate attributeName="stroke-dashoffset" from="-40" to="-540" begin="0s" dur="2s" repeatCount="indefinite" />
          </path>

          <path id="loop-offset" d="M146.48,87.02c14.64,14.64,38.39,14.65,53.03,0s14.64-38.39,0-53.03s-38.39-14.65-53.03,0L120.5,60.5L94.52,87.02c-14.64,14.64-38.39,14.64-53.03,0c-14.64-14.64-14.64-38.39,0-53.03c14.65-14.64,38.39-14.65,53.03,0L120.5,60.5L146.48,87.02z"></path>

          <path id="socket" d="M7.5,0c0,8.28-6.72,15-15,15l0-30C0.78-15,7.5-8.28,7.5,0z">
            <animateMotion
              dur="2s"
              repeatCount="indefinite"
              rotate="auto"
              keyTimes="0;1"
              keySplines="0.42, 0.0, 0.58, 1.0"
            >
              <mpath xlink:href="#loop-offset"/>
            </animateMotion>
          </path>
          
        <path id="plug" d="M0,9l15,0l0-5H0v-8.5l15,0l0-5H0V-15c-8.29,0-15,6.71-15,15c0,8.28,6.71,15,15,15V9z">
          <animateMotion
            dur="2s"
            rotate="auto"
            repeatCount="indefinite"
            keyTimes="0;1"    
            keySplines="0.42, 0, 0.58, 1"
          >
            <mpath xlink:href="#loop-normal"/>
          </animateMotion>
        </path>   
          
        </svg>
        <div class="credit" style="height: 100%; width: 100%; display: flex; flex-direction: column; justify-content: end; align-items: center;">
          <div style="    text-align: center;">Loading</div>
        </div>
    </div>

    <input value="{{ $authkey }}" name="email" id="authkey" hidden>
    <input value="{{$authtoken }}" name="authtoken" id="authtoken" hidden>
    <div class="gameconatiner " id="saven_winner">
        <div class="container">
            <div class="topbar mobilewidth ">
                <div class="row">
                    <div class="col-6">
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center changetop" style="margin-left: 20%;justify-content:flex-start;">
                             <div ><img src="{{asset('public/game/new/image')}}/help.png" class="cursor-pointer icons_header_right_click_5" style="width: 30px;"></div>
                            <div><img src="{{asset('public/game/grady/')}}/image/setting.png" class="cursor-pointer" style="width: 30px;margin-right: 5px;"></div>
                            <div ><img src="{{asset('public/game/grady/')}}/image/users.png" class="cursor-pointer icons_header_right_click_4" style="width: 30px;"></div>
                            
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer">
                <div class="mobilewidth d-flex justify-content-center align-items-center " style="    margin-left: -20px;">
                    <div class="circle-container footer_top">
                        <button disabled class="box_wrapper circle side-circle side-circle-design" data-role="2">
                            <input type="hidden" value="grapes">
                            <div class="coinentry1 box_wrapper_header"><span class="header header2">0</span></div>
                            <div class="box_wrapper_body" id="box_wrapper_bet_2">
                                <img src="{{asset('public/game/grady/')}}/image/grapes1.png" alt="" class="" data-change="2">
                                <span class="all_batting_img_here"></span>
                            </div>
                            <div class="coinentry box_wrapper_footer"><span class="header" id="won_bet_grapes">0</span></div>
                        </button>
                        <button disabled class="box_wrapper circle side-circle side-circle-design" data-role="3">
                            <input type="hidden" value="banana">
                            <div class="coinentry1 box_wrapper_header"><span class="header header3">0</span></div>
                            <div class="box_wrapper_body" id="box_wrapper_bet_3">
                                <img src="{{asset('public/game/grady/')}}/image/banana1.png" alt="" class="" data-change="3">
                                <span class="all_batting_img_here"></span>
                            </div>
                            <div class="coinentry box_wrapper_footer"><span class="header" id="won_bet_banana">0</span></div>
                        </button>
                        <button disabled class="box_wrapper circle side-circle side-circle-design" data-role="4">
                            <input type="hidden" value="lemon">
                            <div class="coinentry1 box_wrapper_header"><span class="header header4">0</span></div>
                            <div class="box_wrapper_body" id="box_wrapper_bet_4">
                                <img src="{{asset('public/game/grady/')}}/image/lemon1.png" alt="" class="" data-change="4">
                                <span class="all_batting_img_here"></span>
                            </div>
                            <div class="coinentry box_wrapper_footer"><span class="header" id="won_bet_lemon">0</span></div>
                        </button>
                        <button disabled class="box_wrapper circle side-circle side-circle-design" data-role="5">
                            <input type="hidden" value="horse">
                            <div class="coinentry1 box_wrapper_header"><span class="header header5">0</span></div>
                            <div class="box_wrapper_body" id="box_wrapper_bet_5">
                                <img src="{{asset('public/game/grady/')}}/image/horse.png" alt="" class="" data-change="5">
                                <span class="all_batting_img_here"></span>
                            </div>
                            <div class="coinentry box_wrapper_footer"><span class="header" id="won_bet_cow">0</span></div>
                        </button>
                        <button disabled class="box_wrapper circle side-circle side-circle-design" data-role="6">
                            <input type="hidden" value="tiger">
                            <div class="coinentry1 box_wrapper_header"><span class="header header6">0</span></div>
                            <div class="box_wrapper_body" id="box_wrapper_bet_6">
                                <img src="{{asset('public/game/grady/')}}/image/tiger.png" alt="" class="" data-change="6">
                                <span class="all_batting_img_here"></span>
                            </div>
                            <div class="coinentry box_wrapper_footer"><span class="header" id="won_bet_dolpin">0</span></div>
                        </button>
                        <button disabled class="box_wrapper circle side-circle side-circle-design" data-role="7">
                            <input type="hidden" value="cat">
                            <div class="coinentry1 box_wrapper_header"><span class="header header7">0</span></div>
                            <div class="box_wrapper_body" id="box_wrapper_bet_7">
                                <img src="{{asset('public/game/grady/')}}/image/cat.png" alt="" class="" data-change="7">
                                <span class="all_batting_img_here"></span>
                            </div>
                            <div class="coinentry box_wrapper_footer"><span class="header " id="won_bet_cat">0</span></div>
                        </button>
                        <button disabled class="box_wrapper circle side-circle side-circle-design" data-role="8">
                            <input type="hidden" value="lion">
                            <div class="coinentry1 box_wrapper_header"><span class="header header8">0</span></div>
                            <div class="box_wrapper_body" id="box_wrapper_bet_8">
                                <img src="{{asset('public/game/grady/')}}/image/lion45.png" alt="" class="" data-change="8">
                                <span class="all_batting_img_here"></span>
                            </div>
                            <div class="coinentry box_wrapper_footer"><span class="header" id="won_bet_owl">0</span></div>
                        </button>
                        <button disabled class="box_wrapper circle side-circle side-circle-design" data-role="1">
                            <input type="hidden" value="apple">
                            <div class="coinentry1 box_wrapper_header"><span class="header header1">0</span></div>
                            <div class="box_wrapper_body" id="box_wrapper_bet_1">
                                <span class="all_batting_img_here"></span>
                                <img src="{{asset('public/game/grady/')}}/image/apple1.png" alt="" class="" data-change="1">
                            </div>
                            <div class="coinentry box_wrapper_footer"><span class="header" id="won_bet_apple">0</span></div>
                        </button>

                        <div class="circle side-circle center-circle mainpot images" style="    font-size: 12px;display: flex;flex-direction: column; justify-content: center; align-items: center;">
                            <div class="text-white" id="countheadline">Waiting..</div>
                            <h1 class="text-white header clock_time_count_down" style="display: none;">0</h1>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-center align-items-center mobilewidth ">
                    <div style="position: absolute; bottom: 10px;width: 100%; margin-left: 8px;height: 100px;" class="footer_bottom">
                        <div class="d-flex">
                        <img src="{{asset('public/game/grady/')}}/image/footerbar.png" alt="" class="w-100 coinbar">
                        <div class="position-absolute leftcoinbar cursor-pointer pt1"  id="animals"  style="background:url('{{asset('public/game/grady/')}}/image/dragons.png');    background-size: cover;">
                            Animals
                        </div>
                        <div class="position-absolute text-white coinbartext w-100 row ">
                            <div class="col-4" >
                                <div class="d-flex flex-row align-items-center" style="">
                                    <img src="{{asset('public/game/grady/')}}/image/bt.png" class="gamecoinimg">
                                    <input style="color: white; height: 18px; text-align: end;background: black; font-weight: bold; font-size: 10px;width: 60px;" type="text" id="total_amount" value="..." disabled>
                                </div>
                            </div>
                            <div class="col-8 d-flex justify-content-evenly align-items-center footer_bottom_right">
                                <div class="images active">
                                    <input type="hidden" value="500" />
                                    <img src="{{asset('public/game/grady/')}}/image/500.png" class="coinsize"> 
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
                                    <img src="{{asset('public/game/grady/')}}/image/1000.png" class="coinsize">
                                    <div id="btn_animation_wrapper">

                                    </div> 
                                </div>
                                <div class="images">
                                    <input type="hidden" value="10000" />
                                    <img src="{{asset('public/game/grady/')}}/image/10k.png" class="coinsize">
                                    <div id="btn_animation_wrapper">

                                    </div>
                                </div>
                                <div class="images">
                                    <input type="hidden" value="50000" />
                                    <img src="{{asset('public/game/grady/')}}/image/50k.png" class="coinsize">
                                    <div id="btn_animation_wrapper">

                                    </div> 
                                </div>
                                <div class="images">
                                    <input type="hidden" value="100000" />
                                    <img src="{{asset('public/game/grady/')}}/image/100k.png" class="coinsize"> 
                                    <div id="btn_animation_wrapper">

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="position-absolute leftcoinbar cursor-pointer pt1" id="vegetable"  style="right: 0;background:url('{{asset('public/game/grady/')}}/image/fruits.png');    background-size: cover;">
                            Fruits
                        </div>
                        </div>
                    </div>

                    <div class="footerbar w-100" style="margin-left: 8px;height: 60px;">
                        <div class="d-flex align-items-center">
                        <img src="{{asset('public/game/grady/')}}/image/footerbar.png" class="footerbarimg" alt="" style="    height: 60px;">
                        <div class="d-flex flex-row justify-content-center align-items-center" style="margin-top: 14px;">
                            {{-- <div class="text-white text-center" style="margin-left: 30px; margin-right: 10px;font-size: 12px;">
                            
                            </div> --}}
                                <div class="reward_here" style=" margin-left: 9px; ">

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="container">
            <div id="hidden_info_here" class="Reminder_Here d-none" style="width: 60%;">
                <div style="" class="container">
                    <div class="body">
                        <p class="title">Insuffisant coins!</p>
                    </div>
                </div>
            </div>
            <div id="hidden_info_here" class="Server_Issue" style="display:none;width: 60%;">
                <div style="" class="container">
                    <div class="body">
                        <p class="title">Connecting Server....</p>
                    </div>
                </div>
            </div>


            <div id="hidden_info_here" class="Winner_Here d-none" style="width: 80%;">
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
                                    <img src="{{URL::to('/')}}/public//user.png"  id="last_winner_image" alt="">
                                </div>
                                <p style="color: white" class="title username4"> Yours :</p>
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




            <div id="hidden_info_here" class="users_here d-none" style="width: 60%;">
                <div class="container">
                    <img src="{{URL::to('/')}}/public/game/new/image/close.png" class="close_bar" style="width: 30px;" alt="">

                    <div style="width: 100%; max-height: 200px; overflow: scroll;     justify-content: flex-start;" class="body">

                        <div class="users_box" id="">

                        </div>



                    </div>
                </div>
            </div>
            <div id="hidden_info_here" class="account_list d-none" style="width: 60%;">
                <div class="container" >
                    <img src="{{URL::to('/')}}/public/game/new/image/close.png" class="close_bar" style="width: 30px;" alt="">

                    <div style="width: 100%; max-height: 200px; overflow: scroll;     justify-content: flex-start;" class="body ">
                        <table class=" responsive" style="font-size: 10px;width: 100%;    color: wheat;">
                             <thead>
                                <tr>
                                  <th scope="col">Tray</th>
                                  <th scope="col">Winner</th>
                                  <th scope="col">Total Bet</th>
                                  
                                  <th scope="col">Win Amount</th>
                                </tr>
                              </thead>
                            <tbody class="account_list_data">
                               
                               
                            </tbody>
                            
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div> 




    <input type="hidden" id="tray_id" value="{{ time()}}">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js" integrity="sha512-3gJwYpMe3QewGELv8k/BX9vcqhryRdzRMxVfq6ngyWXwo03GFEzjsUm8Q7RZcHPHksttq7/GFoxjCVUjkjvPdw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script>
    $(document).ready(function() {
        checkMobileScreen();
    });
    $(document).ready(function() {
        function checkMobileScreen() {
          var width = $(window).width();
          //var width = width -40;
          var height = $(window).height();
            if ($(window).height() > 345 && $(window).width() <= 450) { // Adjust the threshold as needed
                $('.gameconatiner').css('transform', 'rotate(90deg)');
                $('.gameconatiner').css('height', width);
                $('.mobilewidth').css('width', height);
                 var screenHeight = $(window).height();
                var usersHereHeight = $('.users_here').height();
                var topPosition = (screenHeight - usersHereHeight) / 2 - 100;
                var topPosition2 = (screenHeight - usersHereHeight) / 2 - 150;
                
                $('.users_here').css('top', '20%');
                $('.users_here').css('left', topPosition + 'px');
                $('.users_here').css('position', 'fixed');
                $('.users_here').css('transform', 'none');
                
                $('.Reminder_Here').css('top', '20%');
                $('.Reminder_Here').css('left', topPosition + 'px');
                $('.Reminder_Here').css('position', 'fixed');
                $('.Reminder_Here').css('transform', 'none');
                
                $('.account_list').css('top', '20%');
                $('.account_list').css('left', topPosition + 'px');
                $('.account_list').css('position', 'fixed');
                $('.account_list').css('transform', 'none');
                
                $('.Server_Issue').css('top', '40%');
                $('.Server_Issue').css('left', topPosition + 'px');
                $('.Server_Issue').css('position', 'fixed');
                $('.Server_Issue').css('transform', 'none');
                
                $('.Winner_Here').css('top', '20%');
                $('.Winner_Here').css('left', topPosition2 + 'px');
                $('.Winner_Here').css('position', 'fixed');
                $('.Winner_Here').css('transform', 'none');
                

                $('.changetop').css('justify-content', 'flex-start');
                $('.circle-container').css('zoom', '75%');
                $('.circle-container').css('margin-top', '0px');

                
            }
            else if ($(window).height() <= 345 && $(window).width() <= 1024) { // Adjust the threshold as needed
                $('.gameconatiner').css('transform', 'none');
                $('.gameconatiner').css('height', height);
                $('.mobilewidth').css('width', '97%');
                $('.changetop').css('justify-content', 'flex-end');
                $('.circle-container').css('zoom', '60%');
            } else {
                $('.gameconatiner').css('transform', 'none');
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
<script type="text/javascript">

let counter = 1;
let isTimerRunning = false;
let isCounter = 0;

function changeBorderColor() {
  $('img[data-change]').removeClass('active-border');
  $('img[data-change="' + counter + '"]').addClass('active-border');
}

function startBorderColorChange() {
  isTimerRunning = true;
  isCounter = 0;

    const interval = setInterval(function () {
    if(!isTimerRunning && isCounter == counter) {
        $('img[data-change]').removeClass('active-border');
      clearInterval(interval);
       isCounter = 0;
      return;
    }
    changeBorderColor();
    counter++;

    if (counter > 8) {
      counter = 1; // Reset the counter when it reaches the end
    }
  }, 150); // Change color every 1000 milliseconds (1 second)
}

function stopBorderColorChange(counter) {
  isTimerRunning = false;
  if(counter == 8){
      isCounter = 1;
  }
  else{
      isCounter = counter+1;
  }
  
}


</script>
<script type="text/javascript">
$('#saven_winner .container .footer .footer_bottom .footer_bottom_right .images').click(function(){
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


$(document).ready(function(){
   setTimeout(() => {
      $('.laodingstart').hide();
   }, 2000);
});
$(document).ready(function(){
   settimeout_here();
   get_users_amount();
   win_or_loss_calculation();
   get_fruits_results();
   input_online_or_oflline();
});

// responsive design
/*
|---------------------
| All Animation End
|---------------------
*/

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


$('.icons_header_right_click_3').click(function(){
   // settings 
   $('.Rules_here').removeClass('d-none');
   get_last_my_result();
});
$('.icons_header_right_click_5').click(function(){
   // settings 
   $('.account_list').removeClass('d-none');
   account_list_users();
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


$('#hidden_info_here.account_list  .close_bar').click(function(){
   // settings 
   $('#hidden_info_here.account_list').addClass('d-none');
});
$('#hidden_info_here.Rules_here .container .close_bar').click(function(){
   // settings 
   $('#hidden_info_here.Rules_here').addClass('d-none');
});

$('#hidden_info_here.users_here  .close_bar').click(function(){
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
       "url" : "https://bplive.site/grady/tray_id",
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
                  // start
                   nowTime++; 
                  // console.log(data)
                   let time = Number(data - nowTime).toFixed(0);
                  // console.log(time);
                   // some css before st
                   $('#saven_winner .container .body .body_bottom .images').css('display', 'none');
                   $('#saven_winner .container .footer .footer_top .box_wrapper').attr('disabled', true);
                  $('#tray_id').val(data);
                    if(time == 35){ 
                       $('#countheadline').text('Start Bet');
                       
                   }
                   // time bottom 
                   if(time > 5 && time < 35){
                       $('#saven_winner .container .body .body_bottom .images').css('display', 'block');
                       $('#saven_winner .container .footer .footer_top .box_wrapper').attr('disabled', false);
                       $('#countheadline').text('Countdown');
                       $('.clock_time_count_down').show();
                       $('.clock_time_count_down').html(time-5);
                       
                   }
                   if(time > 39){
                    
                       win_or_loss_calculation();
                       
                   }
                   if(time == 36){
                        
                       get_winner_info();
                       
                        input_online_or_oflline();
                       $('.Winner_Here').removeClass('d-none');
                       
                       get_fruits_results();
                       setTimeout(() => {
                           $('.Winner_Here').addClass('d-none');
                           
                       }, 4000);
                      // get_users_amount();
                       get_users_amount();
                   }
                   if(time == 35){
                        
                    	 get_users_amount();
                       $('.This_is_notification').removeClass('d-none');
                       $('.This_is_notification .body .title').html('Start Batting');
                      
                       setTimeout(() => {
                           $('.This_is_notification').addClass('d-none');
                       }, 1500);
                   }
                   if (time==29) {
                   
                  // robot();
                    
                   }
                   if(time == 5){
                       //win_pred();
                       $('#saven_winner .container .footer .footer_top .box_wrapper').attr('disabled', true);
                       $('.This_is_notification').removeClass('d-none');
                     //  StopBetplayAudio();
                        $('#countheadline').text('Stop Batting');
                       $('.clock_time_count_down').hide();
                       $('.This_is_notification .body .title').html('Stop Batting');
                       $('#saven_winner .container .footer .footer_top .box_wrapper').removeClass('active');
                       startBorderColorChange();
                      win_pred();
                   }
                   if (time ==4) {
                    $('#countheadline').text('Waiting Result');
                    result_final();
                   }
                   
                   if (time ==4) {
                        
                     //  win_pred();
                     //  result_final();
                     //  result_final();
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
                   if(time == '-1'){
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
       "url" : "https://bplive.site/grady/winner_saven_win",
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
                //   console.log(res.data);
                   if(res.data == "animals" || res.data == "vegetable"){
                        if(res.data == "vegetable"){
                           setTimeout(() => {
                           stopBorderColorChange(1);
                               $('#saven_winner .container .footer .footer_top .box_wrapper').css('filter', 'grayscale(100%)');
                               $('#saven_winner .container .body .body_middle .images #all_animation_foots span img').css('filter', 'grayscale(100%)');
                               $('#saven_winner .container .body .body_middle .images img.spinner_while').css('filter', 'grayscale(100%)');
                               $('#saven_winner .container .body .body_middle .images #all_animation_foots span:nth-child('+1+')').css('filter', 'grayscale(0%)');
                               $('#saven_winner .container .body .body_middle .images #all_animation_foots span:nth-child('+2+')').css('filter', 'grayscale(0%)');
                               $('#saven_winner .container .body .body_middle .images #all_animation_foots span:nth-child('+3+')').css('filter', 'grayscale(0%)');
                                $('#saven_winner .container .body .body_middle .images #all_animation_foots span:nth-child('+8+')').css('filter', 'grayscale(0%)');
                                $('#vegetable').addClass('blinking');
                               $('#saven_winner .container .body .body_middle .images #all_animation_foots span:nth-child('+1+') img').css({'animation' : 'box_animation_apple 4s ease forwards', 'filter' : 'grayscale(0%)'});
                               $('#saven_winner .container .body .body_middle .images #all_animation_foots span:nth-child('+2+') img').css({'animation' : 'box_animation_apple 4s ease forwards', 'filter' : 'grayscale(0%)'});
                               $('#saven_winner .container .body .body_middle .images #all_animation_foots span:nth-child('+3+') img').css({'animation' : 'box_animation_apple 4s ease forwards', 'filter' : 'grayscale(0%)'});
                               $('#saven_winner .container .body .body_middle .images #all_animation_foots span:nth-child('+8+') img').css({'animation' : 'box_animation_apple 4s ease forwards', 'filter' : 'grayscale(0%)'});

                               $('#saven_winner .container .footer .footer_top .box_wrapper:nth-child('+1+')').css({'animation' : 'box_animation_apple 4s ease forwards', 'filter' : 'grayscale(0%)'});
                               $('#saven_winner .container .footer .footer_top .box_wrapper:nth-child('+2+')').css({'animation' : 'box_animation_apple 4s ease forwards', 'filter' : 'grayscale(0%)'});
                               $('#saven_winner .container .footer .footer_top .box_wrapper:nth-child('+3+')').css({'animation' : 'box_animation_apple 4s ease forwards', 'filter' : 'grayscale(0%)'});
                               $('#saven_winner .container .footer .footer_top .box_wrapper:nth-child('+8+')').css({'animation' : 'box_animation_apple 4s ease forwards', 'filter' : 'grayscale(0%)'});
                               setTimeout(() => {
                                   // css 
                                   $('#saven_winner .container .body .body_middle .images img.spinner_while').css('animation', 'none');
                                   $('#saven_winner .container .body .body_middle .images #all_animation_foots').css('animation', 'none');
                                   $('#saven_winner .container .body .body_middle .images #all_animation_foots span img').css('filter', 'grayscale(0%)');
                                   $('#saven_winner .container .body .body_middle .images img.spinner_while').css('filter', 'grayscale(0%)');
                                   $('#saven_winner .container .body .body_middle .images #all_animation_foots span:nth-child('+1+') img').css({'animation' : 'none'});
                                   $('#saven_winner .container .body .body_middle .images #all_animation_foots span:nth-child('+2+') img').css({'animation' : 'none'});
                                   $('#saven_winner .container .body .body_middle .images #all_animation_foots span:nth-child('+3+') img').css({'animation' : 'none'});
                                   $('#saven_winner .container .body .body_middle .images #all_animation_foots span:nth-child('+8+') img').css({'animation' : 'none'});
                                   $('#saven_winner .container .footer .footer_top .box_wrapper:nth-child('+1+')').css({'animation' : 'none'});
                                   $('#saven_winner .container .footer .footer_top .box_wrapper:nth-child('+2+')').css({'animation' : 'none'});
                                   $('#saven_winner .container .footer .footer_top .box_wrapper:nth-child('+3+')').css({'animation' : 'none'});
                                   $('#saven_winner .container .footer .footer_top .box_wrapper:nth-child('+8+')').css({'animation' : 'none'});
                                   $('#vegetable').removeClass('blinking');

                                   $('#saven_winner .container .footer .footer_top .box_wrapper').css('filter', 'grayscale(0%)');
                                   // all amount 
                                   $('#saven_winner .container .footer .footer_top .box_wrapper .box_wrapper_header .header, #saven_winner .container .footer .footer_top .box_wrapper .box_wrapper_footer .header').html('00');
                                   $('.all_batting_img_here').html('');
                               }, 4000);
                           }, 7000);
                       }else{
                           setTimeout(() => {
                           stopBorderColorChange(8);
                               $('#saven_winner .container .footer .footer_top .box_wrapper').css('filter', 'grayscale(100%)');
                               $('#saven_winner .container .body .body_middle .images #all_animation_foots span img').css('filter', 'grayscale(100%)');
                               $('#saven_winner .container .body .body_middle .images img.spinner_while').css('filter', 'grayscale(100%)');
                               $('#saven_winner .container .body .body_middle .images #all_animation_foots span:nth-child('+4+')').css('filter', 'grayscale(0%)');
                               $('#saven_winner .container .body .body_middle .images #all_animation_foots span:nth-child('+5+')').css('filter', 'grayscale(0%)');
                               $('#saven_winner .container .body .body_middle .images #all_animation_foots span:nth-child('+6+')').css('filter', 'grayscale(0%)');
                                $('#saven_winner .container .body .body_middle .images #all_animation_foots span:nth-child('+7+')').css('filter', 'grayscale(0%)');
                                $('#animals').addClass('blinking');
                               $('#saven_winner .container .body .body_middle .images #all_animation_foots span:nth-child('+4+') img').css({'animation' : 'box_animation_apple 4s ease forwards', 'filter' : 'grayscale(0%)'});
                               $('#saven_winner .container .body .body_middle .images #all_animation_foots span:nth-child('+5+') img').css({'animation' : 'box_animation_apple 4s ease forwards', 'filter' : 'grayscale(0%)'});
                               $('#saven_winner .container .body .body_middle .images #all_animation_foots span:nth-child('+6+') img').css({'animation' : 'box_animation_apple 4s ease forwards', 'filter' : 'grayscale(0%)'});
                               $('#saven_winner .container .body .body_middle .images #all_animation_foots span:nth-child('+7+') img').css({'animation' : 'box_animation_apple 4s ease forwards', 'filter' : 'grayscale(0%)'});

                               $('#saven_winner .container .footer .footer_top .box_wrapper:nth-child('+4+')').css({'animation' : 'box_animation_apple 4s ease forwards', 'filter' : 'grayscale(0%)'});
                               $('#saven_winner .container .footer .footer_top .box_wrapper:nth-child('+5+')').css({'animation' : 'box_animation_apple 4s ease forwards', 'filter' : 'grayscale(0%)'});
                               $('#saven_winner .container .footer .footer_top .box_wrapper:nth-child('+6+')').css({'animation' : 'box_animation_apple 4s ease forwards', 'filter' : 'grayscale(0%)'});
                               $('#saven_winner .container .footer .footer_top .box_wrapper:nth-child('+7+')').css({'animation' : 'box_animation_apple 4s ease forwards', 'filter' : 'grayscale(0%)'});
                               setTimeout(() => {
                                   // css 
                                   $('#saven_winner .container .body .body_middle .images img.spinner_while').css('animation', 'none');
                                   $('#saven_winner .container .body .body_middle .images #all_animation_foots').css('animation', 'none');
                                   $('#saven_winner .container .body .body_middle .images #all_animation_foots span img').css('filter', 'grayscale(0%)');
                                   $('#saven_winner .container .body .body_middle .images img.spinner_while').css('filter', 'grayscale(0%)');
                                   $('#saven_winner .container .body .body_middle .images #all_animation_foots span:nth-child('+4+') img').css({'animation' : 'none'});
                                   $('#saven_winner .container .body .body_middle .images #all_animation_foots span:nth-child('+5+') img').css({'animation' : 'none'});
                                   $('#saven_winner .container .body .body_middle .images #all_animation_foots span:nth-child('+6+') img').css({'animation' : 'none'});
                                   $('#saven_winner .container .body .body_middle .images #all_animation_foots span:nth-child('+7+') img').css({'animation' : 'none'});
                                   $('#saven_winner .container .footer .footer_top .box_wrapper:nth-child('+4+')').css({'animation' : 'none'});
                                   $('#saven_winner .container .footer .footer_top .box_wrapper:nth-child('+5+')').css({'animation' : 'none'});
                                   $('#saven_winner .container .footer .footer_top .box_wrapper:nth-child('+6+')').css({'animation' : 'none'});
                                   $('#saven_winner .container .footer .footer_top .box_wrapper:nth-child('+7+')').css({'animation' : 'none'});
                                   $('#animals').removeClass('blinking');

                                   $('#saven_winner .container .footer .footer_top .box_wrapper').css('filter', 'grayscale(0%)');
                                   // all amount 
                                   $('#saven_winner .container .footer .footer_top .box_wrapper .box_wrapper_header .header, #saven_winner .container .footer .footer_top .box_wrapper .box_wrapper_footer .header').html('00');
                                   $('.all_batting_img_here').html('');
                               }, 4000);
                           }, 7000);
                       }

                   }else{
                        if(res.data == "apple"){
                           var span_num = 8;

                           var anim_while = "rotet_while_2 4s ease forwards";
                           var anim_while_f = "rotet_while_2_fruits 4s ease forwards";

                           var spn_img = 8;
                       }else if(res.data == "grapes"){
                           var span_num = 1;

                           var anim_while = "saven_winner 4s ease forwards";
                           var anim_while_f = "saven_winner_fruits 4s ease forwards";

                           var spn_img = 1;
                       }else if(res.data == "banana"){
                           var span_num = 2;

                           var anim_while = "saven_winner 4s ease forwards";
                           var anim_while_f = "saven_winner_fruits 4s ease forwards";

                           var spn_img = 2;
                       }else if(res.data == "lemon"){
                           var span_num = 3;

                           var anim_while = "saven_winner 4s ease forwards";
                           var anim_while_f = "saven_winner_fruits 4s ease forwards";

                           var spn_img = 3;
                       }else if(res.data == "horse"){
                           var span_num = 4;

                           var anim_while = "saven_winner 4s ease forwards";
                           var anim_while_f = "saven_winner_fruits 4s ease forwards";

                           var spn_img = 4;
                       }else if(res.data == "tiger"){
                           var span_num = 5;

                           var anim_while = "saven_winner 4s ease forwards";
                           var anim_while_f = "saven_winner_fruits 4s ease forwards";

                           var spn_img = 5;
                       }else if(res.data == "lion"){
                           var span_num = 7;

                           var anim_while = "saven_winner 4s ease forwards";
                           var anim_while_f = "saven_winner_fruits 4s ease forwards";

                           var spn_img = 7;
                       }
                       else{
                           var span_num = 6;

                           var anim_while = "rotet_while_3 4s ease forwards";
                           var anim_while_f = "rotet_while_3_fruits 4s ease forwards";

                           var spn_img = 6;
                       }

                       $('#saven_winner .container .body .body_middle .images img.spinner_while').css('animation', anim_while);
                       $('#saven_winner .container .body .body_middle .images #all_animation_foots').css('animation', anim_while_f);

                       // winner
                       setTimeout(() => {
                           stopBorderColorChange(span_num);
                           
                           function waitForActiveBordersToClear(callback) {
                              const activeBordersCount = $('img[data-change]').filter('.active-border').length;
                            
                              if (activeBordersCount === 0) {
                                callback(true); // Condition is met, call the callback with true
                              } else {
                                // Continue waiting for the condition to be met
                                setTimeout(function () {
                                  waitForActiveBordersToClear(callback);
                                }, 100); // Adjust the delay as needed
                              }
                            }
                            
                            waitForActiveBordersToClear(function (result) {
                              let asdf = result;
                              
                              if(asdf == true){
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
                                   $('#saven_winner .container .footer .footer_top .box_wrapper').css('filter', 'grayscale(0%)');
                                   // all amount 
                                   $('#saven_winner .container .footer .footer_top .box_wrapper .box_wrapper_header .header, #saven_winner .container .footer .footer_top .box_wrapper .box_wrapper_footer .header').html('00');
                                   $('.all_batting_img_here').html('');
                                }, 4000);
                                }
                            });
                           
                            
                       }, 7000);
                   }

                   
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
       "url" : "https://bplive.site/grady/fortune_insert",
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
       "url" : "https://bplive.site/grady/robot/",
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
const account_list_users = () => {
    //$('.account_list_data').html('<h2 class="title">Loading...</h2>');
    // AJAX
    $.ajax({
        method: "get",
        url: "https://bplive.site/grady/account_list_data",
        data: {
            authkey: $('#authkey').val(),
            authtoken: $('#authtoken').val(),
        },
        success: function (res) {
            $('#hidden_info_here.users_here .account_list_data').empty(); // Clear existing data
            
            // Generate HTML table rows from the response data
            const rows = res.data.map((curE) => {
                let imageSrc = ''; // Initialize image source variable
                
                // Set the image source based on the winner value
                if (curE.winner == "grapes") {
                    imageSrc = "{{asset('public/game/grady/image/grapes.jpeg')}}";
                } else if (curE.winner == "banana") {
                    imageSrc = "{{asset('public/game/grady/image/banana.jpeg')}}";
                } else if (curE.winner == "apple") {
                    imageSrc = "{{asset('public/game/grady/image/apple.jpeg')}}";
                } else if (curE.winner == "lemon") {
                    imageSrc = "{{asset('public/game/grady/image/lemon.jpeg')}}";
                } else if (curE.winner == "lion") {
                    imageSrc = "{{asset('public/game/grady/image/lion.jpeg')}}";
                } else if (curE.winner == "cat") {
                    imageSrc = "{{asset('public/game/grady/image/cat.jpeg')}}";
                } else if (curE.winner == "tiger") {
                    imageSrc = "{{asset('public/game/grady/image/tiger.jpeg')}}";
                } else if (curE.winner == "horse") {
                    imageSrc = "{{asset('public/game/grady/image/horse.jpeg')}}";
                } else if (curE.winner == "vegetables") {
                    imageSrc = "{{asset('public/game/grady/image/fruits.png')}}";
                } else if (curE.winner == "animals") {
                    imageSrc = "{{asset('public/game/grady/image/dragons.png')}}";
                }
                
                return `
                    <tr>
                        <td>${curE.tray_id}</td>
                        <td><img src="${imageSrc}" class="trendcoing" ></td>
                        <td>${curE.bet_amount}</td>
                        <td>${curE.total_win_balance}</td>
                    </tr>
                `;
            }).join(''); // Join the array of rows into a single string
            
            // Append the generated rows to the tbody
            $('.account_list_data').html(rows);
        }
    });
}

const insert_bettingsaven_winner = (Amount, betting_to) => {
 //  console.log(Amount + 'insert_betting' + betting_to);
   // ajax

   $.ajax({
       "method" : "get",
       "url" : "https://bplive.site/grady/fortune_insert",
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
       "url" : "https://bplive.site/grady/fortune_watermelon_insert",
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
       "url" : "https://bplive.site/grady/user?authkey=" + $('#authkey').val() + "&authtoken=" + $('#authtoken').val(),
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
       "url" : "https://bplive.site/grady/win_or_loss_calculation/",
       "data" : {
           'tray_id' : $('#tray_id').val(),
           'authkey': $('#authkey').val(),
           'authtoken': $('#authtoken').val(),
       },
       success:function(res){
           $('.Server_Issue').hide();
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
       "url" : "https://bplive.site/grady/result_final/",
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
       "url" : "https://bplive.site/grady/win_pred/",
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
    $('.Winner_Here .box_wrapper .box_r .img1').attr('src',"https://bplive.site/public/game/new/image/user.png");
    $('.Winner_Here .box_wrapper .box_r .username1').html('');
    $('.Winner_Here .box_wrapper .box_r .bet1').html('');
    $('.Winner_Here .box_wrapper .box_r .betresult1').html('');
    $('.Winner_Here .box_wrapper .box_r .img2').attr('src',"https://bplive.site/public/game/new/image/user.png");
    $('.Winner_Here .box_wrapper .box_r .username2').html('');
    $('.Winner_Here .box_wrapper .box_r .bet2').html('');
    $('.Winner_Here .box_wrapper .box_r .betresult2').html('');
    $('.Winner_Here .box_wrapper .box_r .img3').attr('src',"https://bplive.site/public/game/new/image/user.png");
    $('.Winner_Here .box_wrapper .box_r .username3').html('');
    $('.Winner_Here .box_wrapper .box_r .bet3').html('');
    $('.Winner_Here .box_wrapper .box_r .betresult3').html('');
   $.ajax({
       "method" : "get",
       "url" : "https://bplive.site/grady/get_winner_info/",
       "data" : {
           'authkey': $('#authkey').val(),
           'authtoken': $('#authtoken').val(),
       },
       success:function(res){ 
           $('.Server_Issue').hide();
           if(res.users_1st_amount != ""){
               $('.Winner_Here .box_wrapper .box_r .img1').attr('src', "https://bplive.site/public/game/new/image/user.png");
               $('.Winner_Here .box_wrapper .box_r .username1').html(res.users_1st_name);
               $('.Winner_Here .box_wrapper .box_r .bet1').html(res.users_1st_amount);
               $('.Winner_Here .box_wrapper .box_r .betresult1').html(res.users_1st_amount_bet);
           }else{
               $('.Winner_Here .box_wrapper .box_r .img1').attr('src',"https://bplive.site/public/game/new/image/user.png");
               $('.Winner_Here .box_wrapper .box_r .username1').html('');
               $('.Winner_Here .box_wrapper .box_r .bet1').html('00');
               $('.Winner_Here .box_wrapper .box_r .betresult1').html('00');
           }

           if(res.users_2nd_amount != ""){
               $('.Winner_Here .box_wrapper .box_r .img2').attr('src',"https://bplive.site/public/game/new/image/user.png");
               $('.Winner_Here .box_wrapper .box_r .username2').html(res.users_2nd_name);
               $('.Winner_Here .box_wrapper .box_r .bet2').html(res.users_2nd_amount);
               $('.Winner_Here .box_wrapper .box_r .betresult2').html(res.users_2nd_amount_bet);

           }else{
               $('.Winner_Here .box_wrapper .box_r .img2').attr('src',"https://bplive.site/public/game/new/image/user.png");
               $('.Winner_Here .box_wrapper .box_r .username2').html('');
               $('.Winner_Here .box_wrapper .box_r .bet2').html('00');
               $('.Winner_Here .box_wrapper .box_r .betresult2').html('00');
           }

           if(res.users_3rd_amount != ""){
               $('.Winner_Here .box_wrapper .box_r .img3').attr('src',"https://bplive.site/public/game/new/image/user.png");
               $('.Winner_Here .box_wrapper .box_r .username3').html(res.users_3rd_name);
               $('.Winner_Here .box_wrapper .box_r .bet3').html(res.users_3rd_amount);
               $('.Winner_Here .box_wrapper .box_r .betresult3').html(res.users_3rd_amount_bet);
           }else{
               $('.Winner_Here .box_wrapper .box_r .img3').attr('src',"https://bplive.site/public/game/new/image/user.png");
               $('.Winner_Here .box_wrapper .box_r .username3').html('');
               $('.Winner_Here .box_wrapper .box_r .bet3').html('00');
               $('.Winner_Here .box_wrapper .box_r .betresult3').html('00');
           }

           // my 
                if(res.last_winner_image == "grapes"){
                $('#last_winner_image').attr('src',"{{asset('public/game/grady/')}}/image/grapes.jpeg");
               }else if(res.last_winner_image == "banana"){
                $('#last_winner_image').attr('src',"{{asset('public/game/grady/')}}/image/banana.jpeg");
                   
               }else if(res.last_winner_image == "apple"){
                $('#last_winner_image').attr('src',"{{asset('public/game/grady/')}}/image/apple.jpeg");
                
               }else if(res.last_winner_image == "lemon"){
                $('#last_winner_image').attr('src',"{{asset('public/game/grady/')}}/image/lemon.jpeg");
                  
               }else if(res.last_winner_image == "lion"){
                $('#last_winner_image').attr('src',"{{asset('public/game/grady/')}}/image/lion.jpeg");
                   
               }else if(res.last_winner_image == "cat"){
                $('#last_winner_image').attr('src',"{{asset('public/game/grady/')}}/image/cat.jpeg");
                 
               }else if(res.last_winner_image == "tiger"){
                $('#last_winner_image').attr('src',"{{asset('public/game/grady/')}}/image/tiger.jpeg");
                
               }else if(res.last_winner_image == "horse"){
                $('#last_winner_image').attr('src',"{{asset('public/game/grady/')}}/image/horse.jpeg");
                 
               }else if(res.last_winner_image == "vegetable"){
                $('#last_winner_image').attr('src',"{{asset('public/game/grady/')}}/image/fruits.png");
                  
               }else if(res.last_winner_image == "animals"){
                $('#last_winner_image').attr('src',"{{asset('public/game/grady/')}}/image/dragons.png");
                 
               }
           $('.Winner_Here .my_wining_info .myBet').html(res.my_tota_bet);
           $('.Winner_Here .my_wining_info .myBetWin').html(res.my_tota_bet_winning);
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
       "url" : "https://bplive.site/grady/wining_fruits",
       "data" : {
           'authkey': $('#authkey').val(),
           'authtoken': $('#authtoken').val(),
       },
       success:function(res){
           
           const rewards = res.data.map((curE) => {
               if(curE.winner == "grapes"){
                   return '<img src="{{asset('public/game/grady/')}}/image/grapes.jpeg" class="trendcoing" >';
               }else if(curE.winner == "banana"){
                   return '<img src="{{asset('public/game/grady/')}}/image/banana.jpeg" class="trendcoing" >';
               }else if(curE.winner == "apple"){
                   return '<img src="{{asset('public/game/grady/')}}/image/apple.jpeg" class="trendcoing" >';
               }else if(curE.winner == "lemon"){
                   return '<img src="{{asset('public/game/grady/')}}/image/lemon.jpeg" class="trendcoing" >';
               }else if(curE.winner == "lion"){
                   return '<img src="{{asset('public/game/grady/')}}/image/lion.jpeg" class="trendcoing" >';
               }else if(curE.winner == "cat"){
                   return '<img src="{{asset('public/game/grady/')}}/image/cat.jpeg" class="trendcoing" >';
               }else if(curE.winner == "tiger"){
                   return '<img src="{{asset('public/game/grady/')}}/image/tiger.jpeg" class="trendcoing" >';
               }else if(curE.winner == "horse"){
                   return '<img src="{{asset('public/game/grady/')}}/image/horse.jpeg" class="trendcoing" >';
               }else if(curE.winner == "vegetable"){
                   return '<img src="{{asset('public/game/grady/')}}/image/fruits.png" style="background: red;" class="trendcoing" >';
               }else if(curE.winner == "animals"){
                   return '<img src="{{asset('public/game/grady/')}}/image/dragons.png" style="background: red;" class="trendcoing" >';
               }
           });
           $('.reward_here').html(rewards);
         
       }
   });
}

$(document).ready(function () {
    // Click event handler for box_wrapper elements
    $("#saven_winner .container .footer .footer_top .box_wrapper").click(function () {
        var betAmount = $(".images.active").children("input").val();
        var totalAmount = Number($("#total_amount").val());

        $(this).addClass("active");

        // Check if totalAmount is not a number
        if (isNaN(totalAmount)) {
            showNotification("Please Refresh Your Game!");
            return false;
        }

        if ($(".box_wrapper.active").length > 9) {
            showNotification("You can't select more than 9 boards at a time");
            $(this).removeClass("active");
            return false;
        }

        // Check if there are enough coins
        if (totalAmount - betAmount < 0 || totalAmount < 1) {
            showNotification("Insufficient coins!");
            return false;
        }

        // Update headers based on selected box
        updateHeaders(betAmount, $(this).children("input").val());

        // Perform an AJAX request
        sendAjaxRequest(betAmount, $(this).children("input").val());
    });

    // Function to show a notification
    function showNotification(message) {
        $(".This_is_notification").removeClass("d-none");
        $(".This_is_notification .body .title").html(message);
        setTimeout(function () {
            $(".This_is_notification").addClass("d-none");
        }, 500);
    }

    // Function to update headers
    function updateHeaders(betAmount, boxValue) {
        var $boxWrapper;

        if (boxValue === "apple") {
            $boxWrapper = $("#saven_winner .container .footer .footer_top .box_wrapper:nth-child(8)");
        }
        else if (boxValue === "grapes") {
            $boxWrapper = $("#saven_winner .container .footer .footer_top .box_wrapper:nth-child(1)");
        } 
        else if (boxValue === "banana") {
            $boxWrapper = $("#saven_winner .container .footer .footer_top .box_wrapper:nth-child(2)");
        } 
        else if (boxValue === "lemon") {
            $boxWrapper = $("#saven_winner .container .footer .footer_top .box_wrapper:nth-child(3)");
        } 
        else if (boxValue === "horse") {
            $boxWrapper = $("#saven_winner .container .footer .footer_top .box_wrapper:nth-child(4)");
        } 
        else if (boxValue === "tiger") {
            $boxWrapper = $("#saven_winner .container .footer .footer_top .box_wrapper:nth-child(5)");
        } 
        else if (boxValue === "lion") {
            $boxWrapper = $("#saven_winner .container .footer .footer_top .box_wrapper:nth-child(7)");
        } 
        else {
            $boxWrapper = $("#saven_winner .container .footer .footer_top .box_wrapper:nth-child(6)");
        }

        var $header = $boxWrapper.find(".box_wrapper_footer .header");
        var currentHeaderVal = Number($header.html());
        $header.html(currentHeaderVal + Number(betAmount));
        $("#total_amount").val(Number($("#total_amount").val()) - Number(betAmount));
    }

    // Function to send an AJAX request
    function sendAjaxRequest(betAmount, boxValue) {
        $.ajax({
            method: "get",
            url: "https://bplive.site/grady/fortune_insert",
            data: {
                'authkey': $('#authkey').val(),
                'authtoken': $('#authtoken').val(),
                tray_id: $('#tray_id').val(),
                bord_name: boxValue,
                amount: betAmount
            },
            success: function (i) {
                // Handle the AJAX response if needed
            }
        });
    }
});

//input_online_or_oflline
const input_online_or_oflline = () => {
    // ajax
    $.ajax({
        "method" : "get",
        "url" : "https://bplive.site/grady/fortune_user_activity",
        "data" : {
             tray_id: $('#tray_id').val(),
             'authkey': $('#authkey').val(),
           'authtoken': $('#authtoken').val(),
        },
        success:function(res){
           // console.log(res.data[0].name)
            //console.log(res.data[1].name)
           
         
            if (res.data[0]) {

            $('#top_one').attr('src','https://bplive.site/'+res.data[0].profile);
            $('#top_one_name').text(res.data[0].name);
            }

            if (res.data[1]) {
            $('#top_two_name').text(res.data[1].name);
            $('#top_two').attr('src', 'https://bplive.site/'+res.data[1].profile);
            }
           
           if (res.data[2]) {

            $('#top_three_name').text(res.data[2].name);
            $('#top_three').attr('src','https://bplive.site/'+res.data[2].profile );
           }
           if (res.data[3]) {
                $('#top_four_name').text(res.data[3].name);
            $('#top_four').attr('src','https://bplive.site/'+res.data[3].profile);
            
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
        "url" : "https://bplive.site/grady/fortune_all_active_users",
        "data" : {},
        success:function(res){
            $('#hidden_info_here.users_here .users_box')
            const data = res.data.map((curE) => {
                  return '<div class="box_r"><img src="https://bplive.site/'+  curE.profile +'" alt=""><p class="title">'+curE.name+'</p></div>';;
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
        "url" : "https://bplive.site/grady/last_user_result",
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
        <div class="col-4 text-center text-white">${curE.tray_id}</div> 
        <div class="col-3 text-center text-white">
          <img src="https://bplive.site/public/game/new/image/watermelon.png" alt="Saven Winner" style="width: 21px;">
        </div> 
        <div class="col-2 text-center text-white">
          ${result}
        </div>
		<div class="col-3 text-center text-white">
          ${curE.amount}
        </div>
      </div>
    `;
  } else if (curE.pot_no === "saven_win") {
    return `
      <div class="row col-12" style="margin-left: -2px; background: #ffffff5e; border-radius: 4px; border-radius: 5px; margin-bottom: 5px;">
        <div class="col-4 text-center text-white">${curE.tray_id}</div> 
        <div class="col-3 text-center text-white">
          <img src="https://bplive.site/public/game/new/image/lemon.png" alt="Saven Winner" style="width: 21px;">
        </div>
        <div class="col-2 text-center text-white">
          ${result}
        </div> 
		<div class="col-3 text-center text-white">
          ${curE.amount}
        </div>
      </div>
    `;
  } else {
    return `
      <div class="row col-12" style="margin-left: -2px; background: #ffffff5e; border-radius: 4px; border-radius: 5px; margin-bottom: 5px;">
        <div class="col-4 text-center text-white">${curE.tray_id}</div> 
        <div class="col-3 text-center text-white">
          <img src="https://bplive.site/public/game/new/image/apple.png" alt="Saven Winner" style="width: 21px;">
        </div> 
        <div class="col-2 text-center text-white">
          ${result}
        </div>
		<div class="col-3 text-center text-white">
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
function StartBetplayAudio() { 
    var x = document.getElementById("start_bet"); 

  x.play(); 
}
function StopBetplayAudio() { 
    var y = document.getElementById("stop_bet"); 

  y.play(); 
}

// Remove all cache from localStorage
localStorage.clear();

// Remove all cache from sessionStorage
sessionStorage.clear();

</script>
</body>

</html>