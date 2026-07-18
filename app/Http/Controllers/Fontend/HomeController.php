<?php

namespace App\Http\Controllers\Fontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cryptocurrency;

class HomeController extends Controller
{
    
   public function Seven(Request $request)
  {
	$data='<h1>Game Start</h1>';
     return $data;
    
  }

}
