<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Cart;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

   
    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';
   
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
   
    public function login(Request $request)
    {   
        $input = $request->all();
   
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required',
        ]);
   
        $credentials = ['email' => $input['email'], 'password' => $input['password']];
        $remember = $request->boolean('remember');

        if (auth()->attempt($credentials, $remember))
        {
            $request->session()->regenerate();

            // Boss 2026-07-19: subadmins (is_admin=3) and other permission-based
            // admins have is_admin != 1, so the old `is_admin == 1` check sent them
            // to the AUTHOR dashboard and the admin panel never showed. Route anyone
            // the admin panel accepts (full admin OR the `sidebar_access` permission
            // — the SAME gate AdminMiddleware uses) to admin.dashboard.
            $u = auth()->user();
            if ((int) $u->is_admin === 1
                || \App\Models\AdminParmisiton::allowed((int) $u->id, 'sidebar_access', false)) {
                return redirect()->route('admin.dashboard');
            } else {
                return redirect()->route('author.dashboard');
            }
        }else{
            return redirect()->route('login')
                ->with('error','Email-Address And Password Are Wrong.');
        }
          
    }

}