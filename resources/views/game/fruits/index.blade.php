<!DOCTYPE html>
<html>
  <head>
    <title>LINDA</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" type="text/css" href="{{asset('public/game/fruits/style.css')}}" />
    <link rel="stylesheet" type="text/css" href="{{asset('public/game/fruits/responsive.css')}}" />


    <style>
        body{
          background-image: url('{{asset('public/game/fruits/')}}/images/Game_Background_190.png');
        }
    </style>
  </head>


  <body >
    <div class="header">
    <img class="headerimage" src="{{asset('public/game/fruits/')}}/images/Linda Fruits.png" />
    </div>
    <audio id="background_audio" src="{{asset('public/game/fruits/')}}/audio/bg.mp3"></audio>
    <audio id="click_audio" src="{{asset('public/game/fruits/')}}/audio/click.wav"></audio>
    <audio id="coins_audio" src="{{asset('public/game/fruits/')}}/audio/coin.mp3"></audio>


      <div class="start" id="gamenew">Waiting for Next Round</div>
      <div class="start" id="start" style="display: none;">Start Bet</div>
      <div class="start" id="place" style="display: none;">Place Your Bet</div>
      <div class="start" id="nomore" style="display: none;">No More Bet</div>
      <div class="start" id="nobalance" style="display: none;">Low Balance</div>
      <div class="start" id="maximum" style="display: none;">Maximum Pot 2</div>
      <div class="start" id="result" style="display: none;"></div>
      <div class="start" id="nextround" style="display: none;">Waiting for Next Round</div>
    
    
    <div class="d-flex setting">
      <img class="profileopen" src="{{asset('public/game/fruits/')}}/images/profile.png" />
      <img class="settingopen" src="{{asset('public/game/fruits/')}}/images/Setting.png" />
      <img class="rankingopen" src="{{asset('public/game/fruits/')}}/images/arrow.png" />
    </div>
    <div id="app">
      <img class="marker" src="{{asset('public/game/fruits/')}}/images/wheel.png" />
      <img class="wheel" src="{{asset('public/game/fruits/')}}/images/wheelimage.png" />
      <img class="button" id="button" style="display: none;" src="" />
    </div>
    <div class="d-flex timer">
      <div class="clock">
        <p class="clocktime">00</p>
        <img class="" src="{{asset('public/game/fruits/')}}/images/time.png" />
      </div>
    </div>
    <div class="pot" id="pot">

        <div class="topuser">

          <div>
           <img class="userimg" id="user1" src="{{asset('public/game/fruits/')}}/images/user_icon.png" />
          </div>
          <div>
            <img class="userimg" id="user2" src="{{asset('public/game/fruits/')}}/images/user_icon.png" />
          </div>
          
        </div>
        <div class="pot1 potboard " id="pot1" >
            <div class="totalpot">Pot: <span id="pot1_total">0</span></div>
            <div class="potcoin"><img class="apple" src="{{asset('public/game/fruits/')}}/images/boardapple.png" /></div>
            <div class="yourpot">You: <span class="yourbet1">0</span></div>
        </div>
        <div class="pot2 potboard" id="pot2" >
            <div class="totalpot">Pot: <span id="pot2_total">0</span></div>
            <div class="potcoin"><img class="lemon" src="{{asset('public/game/fruits/')}}/images/boardlemon.png" /></div>
            <div class="yourpot">You: <span class="yourbet2">0</span></div>
        </div>
        <div class="pot3 potboard" id="pot3" >
            <div class="totalpot">Pot: <span id="pot3_total">0</span></div>
            <div class="potcoin"><img class="watermelon" src="{{asset('public/game/fruits/')}}/images/boardwatermelon.png" /></div>
            <div class="yourpot">You: <span class="yourbet3">0</span></div>
        </div>
        <div class="topuser">
          <div><img class="userimg" id="user3" src="{{asset('public/game/fruits/')}}/images/user_icon.png" /></div>
          <div><img class="userimg" id="user4" src="{{asset('public/game/fruits/')}}/images/user_icon.png" /></div>
        </div>
    </div>
    <div class="bottombar" id="bottombar">
        <div class="bottompart">
            <div class="balancebefore"><img src="{{asset('public/game/fruits/')}}/images/bt.png" alt=""> <div class="balance"></div></div>
            <div class="coin" data-id="500" data-selected="No"><img class="coinimage" src="{{asset('public/game/fruits/')}}/images/1coin.png" alt=""></div>
            <div class="coin" data-id="1000" data-selected="No"><img class="coinimage" src="{{asset('public/game/fruits/')}}/images/2coin.png" alt=""></div>
            <div class="coin" data-id="10000" data-selected="No"><img class="coinimage" src="{{asset('public/game/fruits/')}}/images/3coin.png" alt=""></div>
            <div class="coin" data-id="50000" data-selected="No"><img class="coinimage" src="{{asset('public/game/fruits/')}}/images/4coin.png" alt=""></div>
            <div class="coin" data-id="100000" data-selected="No"><img class="coinimage" src="{{asset('public/game/fruits/')}}/images/5coin.png" alt=""></div>
        </div>
    </div>
    <div class="hidden_content_wrapper all_users d-none" style="display:none;">
        <div class="container">
            <h2 class="header thhis_is_first_header">All Users</h2> <span class="closeuser">X</span>
            <i class="fa-solid fa-circle-xmark header"></i>
            <div class="content">
                <span id="all_active_users">
                    
                </span>
            </div>
        </div>
    </div>
    <div class="hidden_content_wrapper ranking d-none" style="display:none;">
        <div class="container">
            <h2 class="header thhis_is_first_header">Ranking</h2> <span class="closeranking">X</span>
            <i class="fa-solid fa-circle-xmark header"></i>
            <div class="content" id="">
                <span id="last_ranking"><h2 class="title" style="color: white;">Loadding...</h2></span>
            </div>
        </div>
    </div>
      <div class="hidden_content_wrapper hidden_sound d-none" style="display:none;">
        <div class="container">
            <h2 class="header thhis_is_first_header">Settings</h2><span class="closesetting">X</span>
            <i class="fa-solid fa-circle-xmark header"></i>
            <div class="content">
                <div class="box mb-4">
                    <span style="color: white" class="title">Music</span>
                    <span><input type="checkbox" class="music_1_checkbox"></span>
                </div>

                <div class="box">
                    <span style="color: white" class="title">Sound</span>
                    <span><input type="checkbox" class="sound_checkbox"></span>
                </div>
            </div>
        </div>
    </div>


    <script src="https://code.jquery.com/jquery-1.9.1.min.js"></script>
   <script src="https://js.pusher.com/5.0/pusher.min.js"></script>
    <script src="{{asset('public/game/fruits/script.js')}}"></script>
  </body>
</html>