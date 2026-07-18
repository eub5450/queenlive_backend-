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
    <link rel="stylesheet" href="{{asset('public/game/grady/')}}/css/style_v15.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="{{asset('public/game/grady/')}}/js/script.js"></script> 
</head>

<style>
.circle img {
    border-radius: 50%;
    width: 80px;
    height: 80px;
    border: 5px solid #fa3547;
}
.firework {
    --initialSize: 0.5vmin;
    --finalSize: 45vmin;
    --particleSize: 0.2vmin;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    animation: firework 2s infinite;
    width: var(--initialSize);
    aspect-ratio: 1;
    background: radial-gradient(circle, yellow var(--particleSize), #0000 0);
    background-size: var(--initialSize) var(--initialSize);
    background-repeat: no-repeat;
}

@keyframes firework {
    0% {
        transform: translate(-50%, -50%) scale(1);
        opacity: 1;
    }
    100% {
        transform: translate(-50%, -50%) scale(var(--finalSize));
        opacity: 0;
    }
}


/* General styles for gaming effects */
.gaming-effect {
    background: linear-gradient(135deg, #7d0167, #463ce1);
    padding: 20px;
    border-radius: 15px;
    color: #fff;
    box-shadow: 0 0 20px #1fc1ff, 0 0 50px #ffeb68;
    max-width: 100%;
    margin: 0 auto;
}
.title{
    color: #ffffff !important;
  
}.titler {
    font-size: 20px !important;
    text-align: center !important;
    color: #ffdd00 !important;
    text-shadow: 0 0 10px #ffdd00, 0 0 20px #ff6600;
    margin-bottom: 20px !important;
}
.box_wrapper {
    display: flex;
    justify-content: space-around;
    flex-wrap: wrap;
    gap: 20px;
}
.box_rr {
  background: linear-gradient(135deg, #2f1fff, #f729d4);
    border: 2px solid #1fc1ff96;
    border-radius: 10px;
    padding: 15px;
    text-align: center;
    width: 30%;
    box-shadow: 0 0 10px rgb(255 235 104);
    transform: translateY(50px);
    opacity: 0
}
.box_rr .images img {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    box-shadow: 0 0 10px #ff006e, 0 0 20px #00f5d4;
}
.username1, .username2, .username3 {
    color: #ffd700;
    font-size: 18px;
    margin-top: 10px;
    font-weight: bold;
}
.info {
    color: #aaa;
}
.info_r {
    color: #fff;
    font-weight: bold;
}
.my_wining_info {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    padding-top: 20px;
    border-top: 1px dashed #555;
    margin-top: 20px;
    flex-wrap: wrap;
}
.my_wining_info .images img {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    box-shadow: 0 0 10px #ff006e, 0 0 20px #00f5d4;
}
.my_wining_info .right, .my_wining_info .left {
    flex: 1;
}

/* Media Queries */
@media (max-width: 768px) {
    .box_rr {
        width: 45%;
    }
    .title {
        font-size: 20px;
    }
    .my_wining_info .right .images img,
    .my_wining_info .left .images img {
        width: 80px;
        height: 80px;
    }
}
@media (max-width: 480px) {
    .box_rr {
        width: 100%;
    }
    .my_wining_info {
        flex-direction: column;
    }
    .title {
        font-size: 18px;
    }
    .my_wining_info .right .images img,
    .my_wining_info .left .images img {
        width: 70px;
        height: 70px;
    }
}

/* Animations */
@keyframes fade-in-up {
    from {
        opacity: 0;
        transform: translateY(50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.animate-winner {
    animation: fade-in-up 0.6s ease-in-out forwards;
}
.animate-my-info {
    animation: fade-in-up 0.6s ease-in-out 0.8s forwards;
}


.image-container img.fade {
    opacity: 0;
    transform: scale(0.95);
}
      .image-container {
        display: flex;
        justify-content: center;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px; /* Space between images */
        margin: 20px 0; /* Spacing around the container */
    }

    .image-container img {
        width: 80px; /* Default size */
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #1fc1ff;

          transition: opacity 0.3s ease, transform 0.3s ease;
    }

    /* Hover effect */
    .image-container img:hover {
        transform: scale(1.1);
        border-color: #007bff; /* Optional hover border color */
    }

    /* Media query for smaller screens */
    @media (max-width: 600px) {
        .image-container img {
            width: 60px;
            height: 60px;
        }
    }

    @media (max-width: 450px) {
        .image-container img {
            width: 55px;
            height: 55px;
        }
    } 

    /* Hide images on screens with height under 400px */
    @media (max-height: 400px) {
        .image-container {
            display: none;
        }
    }
   @media (max-height: 350px) {
        .image-container {
        display: flex;
        flex-direction: column;
        gap: 9px;
        position: fixed;
        left: 90%;
        transform: translateX(-50%);
        margin-top: 33px;
    }
    .image-container img {
            width: 30px;
            height: 30px;
        }
        .three_reward_here {
        display: flex;
        flex-direction: column;
        gap: 9px;
        position: fixed;
        left: 10%;
        transform: translateX(-50%);
        margin-top: 22px;
    }
    .gaming-effect {
        padding: 15px;
        height: 300px;
    }
    .box_wrapper {
        gap: 10px;
    }
    .box_rr {
        width: 100%;
        padding: 10px;
    }
        .my_wining_info {
        padding-top: 5px !important;
        margin-top: 5px !important;
    }

    .username1, .username2, .username3 {
        font-size: 8px;
    }
    .info, .info_r {
        font-size: 8px;
    }
    .box_rr .images img {
         width: 30px;
        height: 30px;
    }
    
    .my_wining_info .right .images img,
    .my_wining_info .left .images img {
        width: 30px;
        height: 30px;
    }
    .titler {
        margin-bottom: 5px !important;
        font-size: 11px !important;
    }
       #hidden_info_here.users_here .container .body .users_box {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    grid-gap: 1rem;
    align-items: stretch;
    justify-content: stretch;
    gap: 0.5rem;
    flex-wrap: wrap;
}
     
}
    </style>
   <body style="    overflow: hidden;background: url('https://queenlive.site/public/game/grady/image/magicpattern-polka-dot-pattern-1724481643290.png') ;    background-size: cover;" >
<!-- <body style="background: url('https://t3.ftcdn.net/jpg/02/94/45/92/360_F_294459225_deOinf0uW2Ci6osQzwJAX9j67sIZvTwP.jpg') ;    background-size: cover; background-repeat:no-repeat;height:100vh" > -->
<!--<body style="background: url('https://images8.alphacoders.com/362/362134.jpg'); " >-->


    <div class="laodingstart" style="    overflow: hidden;width: 100%; height: 100%; margin: 0; padding: 0; background: rgb(241,93,194);background: url('https://queenlive.site/public/game/grady/image/magicpattern-polka-dot-pattern-1724481643290.png') ;    background-size: cover;position: absolute; z-index: 100;">
        <img class="" style="    overflow: hidden;z-index: -1;border-radius: 0; border: none; width: 100%;position: absolute; top: 25%; left: 0;" src="{{asset('public/game/grady/')}}/image/bd_greedy.png" alt="">
    </div>

    <input value="{{ $authkey }}" name="email" id="authkey" hidden>
    <input value="{{$authtoken }}" name="authtoken" id="authtoken" hidden>
    <div class="gameconatiner " id="saven_winner" style="display: none;">
        <div class="container">
            <div class="topbar mobilewidth ">
                <div class="row">
                    <div class="col-6">
                    </div>
                    <div class="col-6 p-0">
                        <div class="d-flex align-items-center changetop" style="justify-content:flex-end;">
                            <div ><img src="{{asset('public/game/grady/image')}}/help.png" class="cursor-pointer icons_header_right_click_5" style="width: 30px;margin-right: 5px;"></div>
                            <!-- <div><img src="{{asset('public/game/grady/')}}/image/setting.png" class="cursor-pointer" style="width: 30px;margin-right: 5px;"></div> -->
                            <div ><img src="{{asset('public/game/grady/')}}/image/users.png" class="cursor-pointer icons_header_right_click_4" style="width: 30px;margin-right: 5px;"></div>
                            <!--<div ><img src="{{asset('public/game/grady/image')}}/rank.png" class="cursor-pointer icons_header_right_click_6 rewardbottombar2" style="width: 30px;margin-right: 5px;"></div>-->

                        </div>
                    </div>
                <div class="image-container">
                    <img id="top_one" src="https://queenlive.site/store/profile/default.png" alt="Image 1">
                    <img id="top_two" src="https://queenlive.site/store/profile/default.png" alt="Image 2">
                    <img id="top_three" src="https://queenlive.site/store/profile/default.png" alt="Image 3">
                    <img id="top_four" src="https://queenlive.site/store/profile/default.png" alt="Image 4">
                    <img id="top_five" src="https://queenlive.site/store/profile/default.png" alt="Image 5">
                </div>
               <div class="reward_here three_reward_here text-center" style="">

                        </div>
                </div>
            </div>
            <div class="footer">
                <div class="mobilewidth d-flex justify-content-center align-items-center newbartdfesin " style="   ">
                    <div class="circle-container footer_top" style="margin-left: -95px;">
                        <button disabled class="box_wrapper circle side-circle side-circle-design" data-role="2">
                            <input type="hidden" value="grapes">
                            <div class="box_wrapper_body" id="box_wrapper_bet_2">
                                <img src="{{asset('public/game/grady/')}}/image/cabbage.png" alt="" class="" data-change="2">
                                <span class="all_batting_img_here"></span>
                                <div  style="margin-top:-50px;">
                                    <div  style=" font-size: 12px; font-weight: 700;">win 5 times</div>
                             {{--        <div class="coinentry1 box_wrapper_header"><span class="header header2">0</span></div> --}}
                                    <div class="coinentry box_wrapper_footer"><span class="header" id="won_bet_grapes">0</span></div>
                                </div>
                            </div>
                        </button>
                        <button disabled class="box_wrapper circle side-circle side-circle-design" data-role="3">
                            <input type="hidden" value="banana">
                            <div class="box_wrapper_body" id="box_wrapper_bet_3">
                                <img src="{{asset('public/game/grady/')}}/image/corn.png" alt="" class="" data-change="3">
                                <span class="all_batting_img_here"></span>
                                <div  style="margin-top:-50px;">
                                    <div  style=" font-size: 12px; font-weight: 700;">win 5 times</div>
                                  {{--   <div class="coinentry1 box_wrapper_header"><span class="header header3">0</span></div> --}}
                                    <div class="coinentry box_wrapper_footer"><span class="header" id="won_bet_banana">0</span></div>
                                </div>
                            </div>
                        </button>
                        <button disabled class="box_wrapper circle side-circle side-circle-design" data-role="4">
                            <input type="hidden" value="lemon">
                            <div class="box_wrapper_body" id="box_wrapper_bet_4">
                                <img src="{{asset('public/game/grady/')}}/image/carrot.png" alt="" class="" data-change="4">
                                <span class="all_batting_img_here"></span>
                                <div  style="margin-top:-50px;">
                                    <div  style=" font-size: 12px; font-weight: 700;">win 5 times</div>
                             {{--        <div class="coinentry1 box_wrapper_header"><span class="header header4">0</span></div> --}}
                                    <div class="coinentry box_wrapper_footer"><span class="header" id="won_bet_lemon">0</span></div>
                                </div>
                                
                            </div>
                        </button>
                        <button disabled class="box_wrapper circle side-circle side-circle-design" data-role="5">
                            <input type="hidden" value="horse">
                            <div class="box_wrapper_body" id="box_wrapper_bet_5">
                                <img src="{{asset('public/game/grady/')}}/image/hotdog.png" alt="" class="" data-change="5">
                                <span class="all_batting_img_here"></span>
                                <div  style="margin-top:-50px;">
                                    <div  style=" font-size: 12px; font-weight: 700;">win 15 times</div>
                                  {{--   <div class="coinentry1 box_wrapper_header"><span class="header header5">0</span></div> --}}
                                    <div class="coinentry box_wrapper_footer"><span class="header" id="won_bet_cow">0</span></div>
                                </div>
                                
                            </div>
                        </button>
                        <button disabled class="box_wrapper circle side-circle side-circle-design" data-role="6">
                            <input type="hidden" value="tiger">
                            <div class="box_wrapper_body" id="box_wrapper_bet_6">
                                <img src="{{asset('public/game/grady/')}}/image/meat.png" alt="" class="" data-change="6">
                                <span class="all_batting_img_here"></span>
                                <div  style="margin-top:-50px;">
                                    <div  style=" font-size: 12px; font-weight: 700;">win 25 times</div>
                                   {{--  <div class="coinentry1 box_wrapper_header"><span class="header header6">0</span></div> --}}
                                    <div class="coinentry box_wrapper_footer"><span class="header" id="won_bet_dolpin">0</span></div>
                                </div>
                                
                            </div>
                        </button>
                        <button disabled class="box_wrapper circle side-circle side-circle-design" data-role="7">
                            <input type="hidden" value="cat">
                            <div class="box_wrapper_body" id="box_wrapper_bet_7" style="margin-top: -15px;">
                                <img src="{{asset('public/game/grady/')}}/image/kabab.png" alt="" class="" data-change="7">
                                <span class="all_batting_img_here"></span>
                                <div  style="margin-top:-50px;">
                                    <div  style=" font-size: 12px; font-weight: 700;">win 10 times</div>
                                {{--     <div class="coinentry1 box_wrapper_header"><span class="header header7">0</span></div> --}}
                                    <div class="coinentry box_wrapper_footer"><span class="header " id="won_bet_cat">0</span></div>
                                </div>
                                
                            </div>
                        </button>
                        <button disabled class="box_wrapper circle side-circle side-circle-design" data-role="8">
                            <input type="hidden" value="lion">
                            <div class="box_wrapper_body" id="box_wrapper_bet_8" style="margin-top: -9px;">
                                <img src="{{asset('public/game/grady/')}}/image/steak.png" alt="" class="" data-change="8">
                                <span class="all_batting_img_here"></span>
                                <div  style="margin-top:-50px;">
                                    <div  style=" font-size: 12px; font-weight: 700;">win 45 times</div>
                                    {{-- <div class="coinentry1 box_wrapper_header"><span class="header header8">0</span></div> --}}
                                    <div class="coinentry box_wrapper_footer"><span class="header" id="won_bet_owl">0</span></div>
                                </div>
                                
                            </div>
                        </button>
                        <button disabled class="box_wrapper circle side-circle side-circle-design" data-role="1">
                            <input type="hidden" value="apple">
                            <div class="box_wrapper_body" id="box_wrapper_bet_1" style="margin-top: -9px;">
                                <img src="{{asset('public/game/grady/')}}/image/tomato.png" alt="" class="" data-change="1">
                                <span class="all_batting_img_here"></span>
                                <div  style="margin-top:-50px;">
                                    <div  style=" font-size: 12px; font-weight: 700;">win 5 times</div>
                                 {{--    <div class="coinentry1 box_wrapper_header"><span class="header header1">0</span></div> --}}
                                 <div class="coinentry1 box_wrapper_header"></div>
                                    <div class="coinentry box_wrapper_footer"><span class="header" id="won_bet_apple">0</span></div>
                                </div>
                            </div>
                        </button>

                        <div class="circle side-circle center-circle mainpot images" style=" z-index:-1;   margin-top: 118px; width: 100%; height: 100%;   font-size: 12px;display: flex;flex-direction: column; justify-content: center; align-items: center;">
                            <img style="z-index: -1;border-radius: 0; border: none; position: absolute; width: 150%; height: 150%;" src="{{asset('public/game/grady/')}}/image/circalenew.png" alt="">
                            <div class="text-white" id="countheadline" style="margin-top: -90px;font-size: 19px;font-weight: 900;">Waiting..</div>
                            <h1 class="text-white header clock_time_count_down" style="display: none;">0</h1>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-center align-items-center mobilewidth ">
                    <div style="position: absolute; bottom: 10px;width: 90%;height: auto;border-radius: 0 0 20px 20px;" class="footer_bottom footer_bottomresponsive">
                        <div class="d-flex flex-column">
                            
                            <div class="" style="display: flex; justify-content: space-between;align-items: baseline;">
                                <div style="display: flex; flex-direction: column; justify-content: center; align-items: center;">
                                    <img src="{{asset('public/game/grady/')}}/image/meatbox.png" style="border-radius: 50% 50% 0 0; width:50px;  background-size: cover;border: 5px double #b30837; border-bottom: none; margin-bottom: -10px;" alt="">
                                    <span style="width: 100%; text-align: center; background: #ff212b; border: 1px solid; border-radius: 4px; font-size: 11px;">4.37x</span>
                                </div>
                                <div style="display: flex; flex-direction: column; justify-content: center; align-items: center;">
                                    <img src="{{asset('public/game/grady/')}}/image/pizza.png" style="border-radius: 50% 50% 0 0; width:50px;  background-size: cover;margin-bottom: -10px;" alt="">
                                    <span style="width: 100%; text-align: center; background: #FFF421; border: 1px solid; border-radius: 4px; font-size: 11px;">Pizza</span>
                                </div>
                                <div style="background: #FBFFFA; border-radius: 7px; padding: 0 8px;">
                                    <img src="{{asset('public/game/grady/')}}/image/bt.png" class="gamecoinimg">
                                    <input style="color: white; height: 18px; text-align: end;background: #0000003b; filter: drop-shadow(0px 0px 2px black);   border-radius: 5px; font-weight: bold; font-size: 10px;width: 60px;" type="text" id="total_amount" value="..." disabled>
                                </div>
                                <div style="display: flex; flex-direction: column; justify-content: center; align-items: center;">
                                    <img src="{{asset('public/game/grady/')}}/image/salad.png" style="border-radius: 50% 50% 0 0; width:50px;  background-size: cover;margin-bottom: -10px;" alt="">
                                    <span style="width: 100%; text-align: center; background: #FFF421; border: 1px solid; border-radius: 4px; font-size: 11px;">Salad</span>
                                </div>
                                <div style="display: flex; flex-direction: column; justify-content: center; align-items: center;">
                                    <img src="{{asset('public/game/grady/')}}/image/fruitsbox.png" style="border-radius: 50% 50% 0 0; width:50px;  background-size: cover;border: 5px double #b30837; border-bottom: none; margin-bottom: -10px;" alt="">
                                    <span style="width: 100%; text-align: center; background: #ff212b; border: 1px solid; border-radius: 4px; font-size: 11px;">1.25x </span>
                                </div>
                            </div>
                            <div style="width: 100%; border: 10px solid; border-color: #1fc1ff; margin-top: -18px; z-index: -2;"></div>
                            <div class="text-white coinbartext w-100 row " style="border: 2px solid #0a3a5a;border-top: 5px solid rgb(10, 58, 90);background: rgb(31, 193, 255); margin: 0 auto; height: 65px;">
                                <div class="col-12 d-flex justify-content-evenly align-items-center footer_bottom_right">
                                    <div class="images coin active">
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
                                    <div class="images coin">
                                        <input type="hidden" value="1000" />
                                        <img src="{{asset('public/game/grady/')}}/image/1000.png" class="coinsize">
                                        <div id="btn_animation_wrapper">

                                        </div> 
                                    </div>
                                    <div class="images coin">
                                        <input type="hidden" value="10000" />
                                        <img src="{{asset('public/game/grady/')}}/image/10k.png" class="coinsize">
                                        <div id="btn_animation_wrapper">

                                        </div>
                                    </div>
                                    <div class="images coin">
                                        <input type="hidden" value="50000" />
                                        <img src="{{asset('public/game/grady/')}}/image/50k.png" class="coinsize">
                                        <div id="btn_animation_wrapper">

                                        </div> 
                                    </div>
                                    <div class="images coin">
                                        <input type="hidden" value="100000" />
                                        <img src="{{asset('public/game/grady/')}}/image/100k.png" class="coinsize"> 
                                        <div id="btn_animation_wrapper">

                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                        <div class="footerbar rewardbottombar w-100" style="border: 2px solid #0a3a5a;height: 50px;background: #CC1C2B; border-radius: 0 0 18px 18px;">
                            <div class="d-flex align-items-center" style="width: 95%; background: #8de0ff; margin: 0 auto;border-radius: 10px;    margin-top: 4px;">
                                <div class="d-flex flex-row justify-content-center align-items-center w-100" style="margin-top: 2px; padding-bottom: 4px;">
                                    <div class="reward_here" style=" margin-left: 9px; ">
    
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        
        <div class="container">
            <div id="hidden_info_here" class="Reminder_Here d-none" style="width: 90%;">
                <div style="" class="container">
                    <div class="body">
                        <p class="title">Insuffisant coins!</p>
                    </div>
                </div>
            </div>
            <div id="hidden_info_here" class="Server_Issue" style="display:none;width: 90%;">
                <div style="" class="container">
                    <div class="body">
                        <p class="title">Connecting Server....</p>
                    </div>
                </div>
            </div>
           
            <div id="hidden_info_here" class="pots_count_bid d-none" style="width: 90%;">
                <div class="container">
                    <div class="body">
                        <p class="title">You can't select more than 7 boards at a time</p>
                    </div>
                </div>
            </div>

            <div id="hidden_info_here" class="reward_here_top d-none" style="width: 90%;">
                <div style="" class="container">
                    <img src="{{URL::to('/')}}/public/game/grady/image/close.png" class="close_bar" style="width: 30px;" alt="">
                    <div class="body">
                        
                    </div>
                </div>
            </div>


            <div id="hidden_info_here" class="Winner_Here d-none" style="width: 90%;">
            <div class="container" style="background: #f7f7f700; border: none; box-shadow: none;">
                <div class="firework"></div>
                <div class="firework"></div>
                <div class="firework"></div>
                <div class="body gaming-effect">
                    <p class="titler">
                        <span id="emoji">🎮 Congratulations</span> to the following winner(s)
                    </p>
                    <div class="box_wrapper">
                        <div class="box_rr animate-winner">
                            <div class="images">
                                <img class="img1" src="{{URL::to('/')}}/public//user.png" alt="">
                            </div>
                            <p class="title username1">User Name</p>
                            <li>
                                <span class="info">Bet: </span>
                                <span class="info_r bet1">...</span>
                            </li>
                            <li>
                                <span class="info">Win: </span>
                                <span class="info_r betresult1">...</span>
                            </li>
                        </div>
                        <div class="box_rr animate-winner" style="animation-delay: 0.3s;">
                            <div class="images">
                                <img class="img2" src="{{URL::to('/')}}/public//user.png" alt="">
                            </div>
                            <p class="title username2">User Name</p>
                            <li>
                                <span class="info">Bet: </span>
                                <span class="info_r bet2">...</span>
                            </li>
                            <li>
                                <span class="info">Win: </span>
                                <span class="info_r betresult2">...</span>
                            </li>
                        </div>
                        <div class="box_rr animate-winner" style="animation-delay: 0.6s;">
                            <div class="images">
                                <img class="img3" src="{{URL::to('/')}}/public//user.png" alt="">
                            </div>
                            <p class="title username3">User Name</p>
                            <li>
                                <span class="info">Bet: </span>
                                <span class="info_r bet3">...</span>
                            </li>
                            <li>
                                <span class="info">Win: </span>
                                <span class="info_r betresult3">...</span>
                            </li>
                        </div>
                    </div>
                    <div class="my_wining_info animate-my-info">
                        <div class="right">
                            <div class="images">
                                <img src="{{URL::to('/')}}/public//user.png" id="last_winner_image" alt="">
                            </div>
                            <p style="color: white" class="titler username4">Yours:</p>
                        </div>
                        <div class="left">
                            <li>
                                <span class="info">Bet: </span>
                                <span class="info_r myBet">...</span>
                            </li>
                            <li>
                                <span class="info">Win: </span>
                                <span class="info_r myBetWin">...</span>
                            </li>
                        </div>
                    </div>
                </div>
            </div>
        </div>




            <div id="hidden_info_here" class="users_here d-none" style="width: 90%;">
                <div class="container">
                    <img src="{{URL::to('/')}}/public/game/grady/image/close.png" class="close_bar" style="width: 30px;" alt="">

                    <div style="width: 100%;align-items:start;overflow-y: scroll; height: 100%;" class="body">

                        <div class="users_box w-100" id="">

                        </div>
                    </div>
                </div>
            </div>
            <div id="hidden_info_here" class="account_list d-none" style="width: 90%;">
                <div class="container" >
                    <img src="{{URL::to('/')}}/public/game/grady/image/close.png" class="close_bar" style="width: 30px;" alt="">

                    <div style="width: 100%; max-height: 200px; overflow: scroll;     justify-content: flex-start;" class="body ">
                        <table class=" responsive" style="font-size: 10px;width: 100%;    color: #0a3a5a;">
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
          var cal = height/6;
          var asdfas = height/9;
            if ($(window).height() <= 345) { // Adjust the threshold as needed
                $('.gameconatiner').css('transform', 'none');
                $('.gameconatiner').css('height', height);
                $('.footer_bottomresponsive').css('height', 'auto');
                $('.rewardbottombar').css('display', 'none');
                $('.rewardbottombar2').css('display', 'block');
                $('.changetop').css('justify-content', 'flex-end');
                $('.circle-container').css('zoom', '57%');
                $('.circle-container').css('margin-bottom', '-74px');
                $('.circle-container').css('z-index', '0');
                $('.footer_bottom.footer_bottomresponsive').css('zoom', '80%');
                $('.newbartdfesin').css('margin-top', '5%');
                  console.log('325');
                   $('.three_reward_here').css('display', 'flex');
                if(width <350){
                    $('.circle-container').css('zoom', '57%');
                  
                }
            } else {
                $('.three_reward_here').css('display', 'none');
                $('.gameconatiner').css('transform', 'none');
                $('.gameconatiner').css('height', height);
                $('.rewardbottombar').css('display', 'block');
                $('.rewardbottombar2').css('display', 'none');
                $('.footer_bottomresponsive').css('height', '175px');
                $('.circle-container').css('zoom', asdfas+'%');

                $('.circle-container').css('margin-bottom', '0px');
                $('.circle-container').css('z-index', '0');
                $('.footer_bottom.footer_bottomresponsive').css('zoom', '100%');

                $('.newbartdfesin').css('margin-top', cal);
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
  // Delay the display of the element after 1 second
  setTimeout(function() {
    document.getElementById("saven_winner").style.display = "block";
  }, 1000);
</script>
<script type="text/javascript">
let counter = 1; // Current active box index
let isTimerRunning = false; // Animation running flag
let currentInterval; // Interval reference

// Function to update the border color based on the current counter
function changeBorderColor() {
    let change = counter; // Assuming counter is defined and holds the desired value
    console.log(change);

    // Reset grayscale and animation for all elements
    $('#saven_winner .container .footer .footer_top .box_wrapper').css({
    
        'filter': 'grayscale(100%)'
    }).removeClass('active-border');
    $('.circle.side-circle.center-circle.mainpot.images').css({
    
        'filter': 'grayscale(100%)'
    });
    

    // Target the specific element based on the counter
    let boxWrapperElement = $('#saven_winner .container .footer .footer_top .box_wrapper:nth-child(' + change + ')');
    if (boxWrapperElement.length) {
        boxWrapperElement.css({
            
            'filter': 'grayscale(0%)'
        }).addClass('active-border');
    }
}



// Function to start the animation
function startBorderColorChange() {
  isTimerRunning = true;
  isCounter = 1;

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
  }, 275); // Change color every 1000 milliseconds (1 second)
}

function stopBorderColorChange(counter) {
  isTimerRunning = false; // Stop the timer or process
  if (counter == 8) {
      isCounter = 1; // Reset counter
  } else {
      isCounter = counter + 1; // Increment counter
  }

  // Debug logs to verify the state
  console.log("Timer running:", isTimerRunning);
  console.log("Counter:", counter);
  console.log("isCounter:", isCounter);
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
$('.icons_header_right_click_6').click(function(){
   // settings 
   $('.reward_here_top').removeClass('d-none');
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
$('#hidden_info_here.reward_here_top  .close_bar').click(function(){
   // settings 
   $('#hidden_info_here.reward_here_top').addClass('d-none');
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
       "url" : "https://queenlive.site/grady/tray_id",
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
                   console.log(time);
                   // some css before st
                   $('#saven_winner .container .body .body_bottom .images').css('display', 'none');
                   $('#saven_winner .container .footer .footer_top .box_wrapper').attr('disabled', true);
                  $('#tray_id').val(data);
                    if(time == 35){ 
                       $('#countheadline').text('Start Bet');
                      // startBorderColorChange(1000);
                        console.log('Start Bet');
                   }
                   // time bottom 
                   if(time > 5 && time < 35){
                       $('#saven_winner .container .body .body_bottom .images').css('display', 'block');
                       $('#saven_winner .container .footer .footer_top .box_wrapper').attr('disabled', false);
                       $('#countheadline').text('Countdown');
                       $('.clock_time_count_down').show();
                       $('.clock_time_count_down').html(time-5);
                      

                   }
                  
                   if(time == 40){
                         console.log('get_winner_info & get_fruits_results & input_online_or_oflline ');
                       get_winner_info();
                       
                        
                       $('.Winner_Here').removeClass('d-none');
                       
                       
                       setTimeout(() => {
                           $('.Winner_Here').addClass('d-none');
                           
                       }, 4000);
                      
                       
                   }
                    if (time ==36) {
                     console.log('get_users_amount');
                      // get_users_amount();
                       get_users_amount();
                       get_fruits_results();
                       input_online_or_oflline();
                   }
                     
                   if(time == 42){
                       
                        win_or_loss_calculation();
                       $('.This_is_notification').removeClass('d-none');
                       $('.This_is_notification .body .title').html('Start Batting');
                      // stopBorderColorChange(1);
                        $('#saven_winner .container .footer .footer_top .box_wrapper').removeClass('active-border');
                       $('#saven_winner .container .footer .footer_top .box_wrapper').css('filter', 'grayscale(0%)');
                       setTimeout(() => {
                           $('.This_is_notification').addClass('d-none');
                       }, 1000);
                   }
                   
                   if(time == 5){
                       console.log('Stop Batting');
                       //win_pred();
                       $('#saven_winner .container .footer .footer_top .box_wrapper').attr('disabled', true);
                       $('.This_is_notification').removeClass('d-none');
                     //  StopBetplayAudio();
                        $('#countheadline').text('Stop Batting');
                       $('.clock_time_count_down').hide();
                       $('.This_is_notification .body .title').html('Stop Batting');
                      
                      $('#saven_winner .container .footer .footer_top .box_wrapper').removeClass('active');
                      startBorderColorChange(150);
                       $('.blinkpoint').addClass('rotate-image');
                     
                        
                    
                   }
                   if (time ==4) {
                       console.log('win_pred');
                     win_pred();
                    $('#countheadline').text('Waiting Result');
                   }
                       
                     
                  
                   if (time ==2) {
                        result_final();
                        console.log('final');
                     //  win_pred();
                     //  result_final();
                     //  result_final();
                     


                       setTimeout(() => {
                          console.log('saven_win_get_winner');
                           saven_win_get_winner();
                           
                           
                           $('.This_is_notification').addClass('d-none');
                       }, 3000);
                   }
                   if(time < 0){
                       clearInterval(x);
                   }
                   if(time == -1){
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


const saven_win_get_winner = () => {
   // ajax
   $.ajax({
       "method" : "get",
       "url" : "https://queenlive.site/grady/winner_saven_win",
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
                            stopBorderColorChange(3);
                           setTimeout(() => {
                           
                           $('.blinkpoint').removeClass('rotate-image');
                           
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
                                   $('.circle.side-circle.center-circle.mainpot.images').css({'filter': 'grayscale(0%)'});
                                   $('#vegetable').removeClass('blinking');

                                   $('#saven_winner .container .footer .footer_top .box_wrapper').css('filter', 'grayscale(0%)');
                                   // all amount 
                                   $('#saven_winner .container .footer .footer_top .box_wrapper .box_wrapper_header .header, #saven_winner .container .footer .footer_top .box_wrapper .box_wrapper_footer .header').html('0');
                                   $('.all_batting_img_here').html('');
                               }, 2000);
                           }, 2350);
                       }else{
                            stopBorderColorChange(7);
                           setTimeout(() => {
                          
                           $('.blinkpoint').removeClass('rotate-image');
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
                                   $('.circle.side-circle.center-circle.mainpot.images').css({'filter': 'grayscale(0%)'});
                                   $('#animals').removeClass('blinking');

                                   $('#saven_winner .container .footer .footer_top .box_wrapper').css('filter', 'grayscale(0%)');
                                   // all amount 
                                   $('#saven_winner .container .footer .footer_top .box_wrapper .box_wrapper_header .header, #saven_winner .container .footer .footer_top .box_wrapper .box_wrapper_footer .header').html('0');
                                   $('.all_batting_img_here').html('');
                               }, 2000);
                           }, 2350);
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
                       stopBorderColorChange(span_num);
                       // winner
                       setTimeout(() => {
                           
                           $('.blinkpoint').removeClass('rotate-image');
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
                                $('#saven_winner .container .body .body_middle .images #all_animation_foots span:nth-child('+span_num+')').css('filter', 'grayscale(0%)');
                                $('#saven_winner .container .body .body_middle .images #all_animation_foots span:nth-child('+spn_img+') img').css({'animation' : 'box_animation_apple 4s ease forwards', 'filter' : 'grayscale(0%)'});
                                $('#saven_winner .container .footer .footer_top .box_wrapper:nth-child('+span_num+')').css({'animation' : 'box_animation_apple 4s ease forwards', 'filter' : 'grayscale(0%)'});
                                //$('#saven_winner .container .footer .footer_top .box_wrapper:nth-child('+span_num+') .box_wrapper_body').removeClass('hideouteffect');
                                
                                setTimeout(() => {
                                   // css 
                                   //$('#saven_winner .container .footer .footer_top .box_wrapper .box_wrapper_body').removeClass('hideouteffect');
                                   $('#saven_winner .container .body .body_middle .images img.spinner_while').css('animation', 'none');
                                   $('#saven_winner .container .body .body_middle .images #all_animation_foots').css('animation', 'none');
                                   $('#saven_winner .container .body .body_middle .images #all_animation_foots span img').css('filter', 'grayscale(0%)');
                                   $('#saven_winner .container .body .body_middle .images img.spinner_while').css('filter', 'grayscale(0%)');
                                   $('#saven_winner .container .body .body_middle .images #all_animation_foots span:nth-child('+spn_img+') img').css({'animation' : 'none'});
                                   $('#saven_winner .container .footer .footer_top .box_wrapper:nth-child('+span_num+')').css({'animation' : 'none'});
                                   $('#saven_winner .container .footer .footer_top .box_wrapper').css('filter', 'grayscale(0%)');
                                   $('.circle.side-circle.center-circle.mainpot.images').css({'filter': 'grayscale(0%)'});
                                   // all amount 
                                   $('#saven_winner .container .footer .footer_top .box_wrapper .box_wrapper_header .header, #saven_winner .container .footer .footer_top .box_wrapper .box_wrapper_footer .header').html('0');
                                   $('.all_batting_img_here').html('');
                                }, 2000);
                                }
                            });
                           
                            
                       }, 2000);
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

const robot = () => {
  // win_or_loss_calculation();
   // ajax
   $.ajax({
       "method" : "get",
       "url" : "https://queenlive.site/grady/robot/",
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
        url: "https://queenlive.site/grady/account_list_data",
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


const insert_betting_watermellon = (Amount, betting_to) => {
 //  console.log(Amount + 'insert_betting' + betting_to);
   // ajax
   $.ajax({
       "method" : "get",
       "url" : "https://queenlive.site/grady/fortune_watermelon_insert",
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
       "url" : "https://queenlive.site/grady/user?authkey=" + $('#authkey').val() + "&authtoken=" + $('#authtoken').val(),
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
       "url" : "https://queenlive.site/grady/win_or_loss_calculation/",
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
       "url" : "https://queenlive.site/grady/result_final/",
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
       "url" : "https://queenlive.site/grady/win_pred/",
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
    $('.img1').attr('src',"https://queenlive.site/public/game/new/image/user.png");
    $('.username1').html('');
    $('.bet1').html('');
    $('.betresult1').html('');
    $('.img2').attr('src',"https://queenlive.site/public/game/new/image/user.png");
    $('.username2').html('');
    $('.bet2').html('');
    $('.betresult2').html('');
    $('.img3').attr('src',"https://queenlive.site/public/game/new/image/user.png");
    $('.username3').html('');
    $('.bet3').html('');
    $('.betresult3').html('');
   $.ajax({
       "method" : "get",
       "url" : "https://queenlive.site/grady/get_winner_info/",
       "data" : {
           'authkey': $('#authkey').val(),
           'authtoken': $('#authtoken').val(),
       },
       success:function(res){ 
           console.log(res);
           $('.Server_Issue').hide();
           if(res.users_1st_amount != ""){
               //console.log(res.users_1st_amount);
               $('.img1').attr('src',res.users_1st_img);
               $('.username1').html(res.users_1st_name);
               $('.bet1').html(res.users_1st_amount);
               $('.betresult1').html(res.users_1st_amount_bet);
           }else{
               $('.img1').attr('src',"https://queenlive.site/public/game/new/image/user.png");
               $('.username1').html('');
               $('.bet1').html('00');
               $('.betresult1').html('00');
           }

           if(res.users_2nd_amount != ""){
               $('.img2').attr('src',res.users_2nd_img);
               $('.username2').html(res.users_2nd_name);
               $('.bet2').html(res.users_2nd_amount);
               $('.betresult2').html(res.users_2nd_amount_bet);

           }else{
               $('.img2').attr('src',"https://queenlive.site/public/game/new/image/user.png");
               $('.username2').html('');
               $('.bet2').html('00');
               $('.betresult2').html('00');
           }

           if(res.users_3rd_amount != ""){
               $('.img3').attr('src',res.users_3rd_img);
               $('.username3').html(res.users_3rd_name);
               $('.bet3').html(res.users_3rd_amount);
               $('.betresult3').html(res.users_3rd_amount_bet);
           }else{
               $('.img3').attr('src',"https://queenlive.site/public/game/new/image/user.png");
               $('.username3').html('');
               $('.bet3').html('00');
               $('.betresult3').html('00');
           }

           // my 
                if(res.last_winner_image == "grapes"){
                $('#last_winner_image').attr('src',"{{asset('public/game/grady/')}}/image/cabbage_win.png");
               }else if(res.last_winner_image == "banana"){
                $('#last_winner_image').attr('src',"{{asset('public/game/grady/')}}/image/corn_win.png");
                   
               }else if(res.last_winner_image == "apple"){
                $('#last_winner_image').attr('src',"{{asset('public/game/grady/')}}/image/tomoto_win.png");
                
               }else if(res.last_winner_image == "lemon"){
                $('#last_winner_image').attr('src',"{{asset('public/game/grady/')}}/image/carrot_win.png");
                  
               }else if(res.last_winner_image == "lion"){
                $('#last_winner_image').attr('src',"{{asset('public/game/grady/')}}/image/steak_win.png");
                   
               }else if(res.last_winner_image == "cat"){
                $('#last_winner_image').attr('src',"{{asset('public/game/grady/')}}/image/kabab_win.png");
                 
               }else if(res.last_winner_image == "tiger"){
                $('#last_winner_image').attr('src',"{{asset('public/game/grady/')}}/image/meat_win.png");
                
               }else if(res.last_winner_image == "horse"){
                $('#last_winner_image').attr('src',"{{asset('public/game/grady/')}}/image/hotdog_win.png");
                 
               }else if(res.last_winner_image == "vegetable"){
                $('#last_winner_image').attr('src',"{{asset('public/game/grady/')}}/image/salad.png");
                  
               }else if(res.last_winner_image == "animals"){
                $('#last_winner_image').attr('src',"{{asset('public/game/grady/')}}/image/pizza.png");
                 
               }
           $('.myBet').html(res.my_tota_bet);
           $('.myBetWin').html(res.my_tota_bet_winning);
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
       "url" : "https://queenlive.site/grady/wining_fruits",
       "data" : {
           'authkey': $('#authkey').val(),
           'authtoken': $('#authtoken').val(),
       },
       success:function(res){
           
                  const rewards = res.data.map((curE) => {
            if (curE.winner == "grapes") {
                return '<img src="{{asset('public/game/grady/')}}/image/cabbage_win.png" class="trendcoing">';
            } else if (curE.winner == "banana") {
                return '<img src="{{asset('public/game/grady/')}}/image/corn_win.png" class="trendcoing">';
            } else if (curE.winner == "apple") {
                return '<img src="{{asset('public/game/grady/')}}/image/tomoto_win.png" class="trendcoing">';
            } else if (curE.winner == "lemon") {
                return '<img src="{{asset('public/game/grady/')}}/image/carrot_win.png" class="trendcoing">';
            } else if (curE.winner == "lion") {
                return '<img src="{{asset('public/game/grady/')}}/image/steak_win.png" class="trendcoing">';
            } else if (curE.winner == "cat") {
                return '<img src="{{asset('public/game/grady/')}}/image/kabab_win.png" class="trendcoing">';
            } else if (curE.winner == "tiger") {
                return '<img src="{{asset('public/game/grady/')}}/image/meat_win.png" class="trendcoing">';
            } else if (curE.winner == "horse") {
                return '<img src="{{asset('public/game/grady/')}}/image/hotdog_win.png" class="trendcoing">';
            } else if (curE.winner == "vegetable") {
                return '<img src="{{asset('public/game/grady/')}}/image/salad.png" style="background: red;" class="trendcoing">';
            } else if (curE.winner == "animals") {
                return '<img src="{{asset('public/game/grady/')}}/image/pizza.png" style="background: red;" class="trendcoing">';
            }
        });
        
        // Display all rewards
        $('.reward_here').html(rewards.join(''));
        
        // Get the first six rewards
        const firstSixRewards = rewards.slice(0, 6);
        
        // Add rewards with animation
        $('.three_reward_here').html('');
        firstSixRewards.forEach((reward, index) => {
            setTimeout(() => {
                $('.three_reward_here').append(
                    `<div class="animated-reward">${reward}</div>`
                );
            }, index * 100); // Delay of 500ms for each item
        });

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

        if ($(".box_wrapper.active").length > 7) {
            showNotification("You can't select more than 7 boards at a time");
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
    const notification = $(".pots_count_bid");
    notification.removeClass("d-none"); // Show the element
    notification.find(".body .title").html(message);
   // console.log(message);
    setTimeout(function () {
        notification.addClass("d-none"); // Hide the element
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
            url: "https://queenlive.site/grady/fortune_insert",
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
        "url" : "https://queenlive.site/grady/fortune_user_activity",
        "data" : {
             tray_id: $('#tray_id').val(),
             'authkey': $('#authkey').val(),
           'authtoken': $('#authtoken').val(),
        },
        success:function(res){
           // console.log(res.data[0].name)
            //console.log(res.data[1].name)
           
         
          if (res.data[0]) {
            updateImageWithAnimation('#top_one', 'https://queenlive.site/' + res.data[0].profile);
            $('#top_one_name').text(res.data[0].name);
        }
        
        if (res.data[1]) {
            updateImageWithAnimation('#top_two', 'https://queenlive.site/' + res.data[1].profile);
            $('#top_two_name').text(res.data[1].name);
        }
        
        if (res.data[2]) {
            updateImageWithAnimation('#top_three', 'https://queenlive.site/' + res.data[2].profile);
            $('#top_three_name').text(res.data[2].name);
        }
        
        if (res.data[3]) {
            updateImageWithAnimation('#top_four', 'https://queenlive.site/' + res.data[3].profile);
            $('#top_four_name').text(res.data[3].name);
        }
        
        if (res.data[4]) {
            updateImageWithAnimation('#top_five', 'https://queenlive.site/' + res.data[4].profile);
            $('#top_five_id').val(res.data[4].id);
        }
           
        }
    });
}
function updateImageWithAnimation(imgSelector, newSrc) {
    const imgElement = $(imgSelector);
    imgElement.addClass('fade'); // Add fade class
    setTimeout(() => {
        imgElement.attr('src', newSrc).removeClass('fade'); // Update src and remove fade class
    }, 300);
}
// input_online_or_oflline_get_users
const input_online_or_oflline_get_users = () => {
    $('.users_here .users_box').html('<h2 class="title">Loadding...</h2>');
    // ajax
    $.ajax({
        "method" : "get",
        "url" : "https://queenlive.site/grady/fortune_all_active_users",
        "data" : {},
        success:function(res){
            $('#hidden_info_here.users_here .users_box')
            const data = res.data.map((curE) => {
                  return '<div class="box_r"><img src="https://queenlive.site/'+  curE.profile +'" alt=""><p class="title">'+curE.name+'</p></div>';;
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
        "url" : "https://queenlive.site/grady/last_user_result",
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
          <img src="https://queenlive.site/public/game/new/image/watermelon.png" alt="Saven Winner" style="width: 21px;">
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
          <img src="https://queenlive.site/public/game/new/image/lemon.png" alt="Saven Winner" style="width: 21px;">
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
          <img src="https://queenlive.site/public/game/new/image/apple.png" alt="Saven Winner" style="width: 21px;">
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