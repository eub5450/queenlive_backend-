<?php

namespace App\Http\Controllers\Fontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cryptocurrency;
use App\Models\PaymentMethod;
use App\Models\Post;
use App\Models\Message;
use Pusher\Pusher;
use Auth;
use Session;

class PostController extends Controller
{
    public function BuyPost($name)
    {
    	$crypto=Cryptocurrency::where('name',$name)->first();
    	if ($crypto) {
		    	$data=Post::where('sale_buy',2)->where('status','!=','released')->orderby('id','desc')->get();
		    	return view('fontend.page.buy_crypto',compact('data','crypto'));	
	    }
	    else
	    {
	    	$notification=array(
                 'messege'=>'Something Wrong!!',
                 'alert-type'=>'error'
                       );
            return Redirect()->back()->with($notification);
	    }
    }
    public function SellPost($name)
    {
    	$crypto=Cryptocurrency::where('name',$name)->first();
    	if ($crypto) {
		    	$data=Post::where('sale_buy',2)->where('status','!=','released')->orderby('id','desc')->get();
		    	return view('fontend.page.sell_crypto',compact('data','crypto'));	
	    }
	    else
	    {
	    	$notification=array(
                 'messege'=>'Something Wrong!!',
                 'alert-type'=>'error'
                       );
            return Redirect()->back()->with($notification);
	    }
    }

    public function StartChat($id,$user_id)
    {

    	if (Auth::user()) {

    		$from = Auth::id();
    		$to = $user_id;
    		$post=Post::find($id);

    		$data=new Message;
    		$data->from=$from;
    		$data->to=$to;
    		$data->messages='Hello';
    		$data->subject=$post->offer_price;
    		$data->is_read=0;
    		$data->save();

    		 // pusher
        $options = array(
            'cluster' => 'ap4',
            'useTLS' => true,
        );

        $pusher = new Pusher(
            env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            $options,
        );

        //return  env('PUSHER_APP_KEY');
        $data = ['from' => $from, 'to' => $to]; 


        // sending from and to user id when pressed enter
        $pusher->trigger('my-channel','my-event', $data);
        Session::put('session_receiver_id', $to);
        return Redirect()->route('chat');
    	}
    	else
    	{
    		$notification=array(
                 'messege'=>'Plseas Login To Start Chat',
                 'alert-type'=>'warning'
                       );
            return Redirect('/login')->with($notification);
    	}
    }
}
