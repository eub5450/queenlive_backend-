<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OnlineCoinProduct;
use App\Models\OfflineRecharge;
class OnlineCoinPurchaseController extends Controller
{
    public function GetCoinList(Request $request){
    	$response = array();
        $token = $request->access_token;
        if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
    	$data=OnlineCoinProduct::all();
    	$offline_sellers=OfflineRecharge::all();
   		  array_push($response,array('message'=>'Coin List Show Success','data'=>$data,'offline_sellers'=>$offline_sellers,'code'=>'200'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);

        }else{
            array_push($response,array('message'=>'Unauthorized','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    }
}
