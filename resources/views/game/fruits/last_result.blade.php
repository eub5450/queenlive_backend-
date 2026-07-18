 <table class="table" id="customers" style="color: white;width: 100%;text-align: center;">
 	@foreach($data  as $row)

 	<tr style="border: 1px solid white !important;">
 		<td>@if($row->pots==1) <img class="rankingimage" src="{{asset('public/game/fruits/')}}/images/Rapple.png" style="width: 45px;" /> @else - @endif</td>
 		<td>@if($row->pots==2)<img class="rankingimage" src="{{asset('public/game/fruits/')}}/images/lemonR.png" style="width: 45px;"/> @else - @endif</td>
 		<td>@if($row->pots==3)<img class="rankingimage" src="{{asset('public/game/fruits/')}}/images/watermelonR.png"style="width: 45px;" /> @else - @endif</td>
 	</tr>
 	@endforeach
 	
 </table>