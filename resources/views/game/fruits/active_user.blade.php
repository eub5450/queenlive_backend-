@foreach($data as $data)
<div style="display: flex;flex-direction: column;margin-right: 49px;justify-content: space-evenly;"><img style="width: 46px;height: 46px;margin-bottom: 20px;margin-top: 10px;margin-left: 8px;" class="userimg" src="{{asset('public/game/fruits/')}}/images/user_icon.png" />
	<span style="color:white;text-align: center;">{{$data->name}}</span></div>
	@endforeach