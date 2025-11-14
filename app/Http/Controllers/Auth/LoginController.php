<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

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
        $this->middleware('auth')->only('logout');
    }

    /**
     * Handle a login request to the application.
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            // Check if user is a client BEFORE sending login response
            $user = $this->guard()->user();
            
            if ($user && $user->isClient()) {
                // Logout the client
                $this->guard()->logout();
                
                // Invalidate the session
                $request->session()->invalidate();
                
                // Regenerate CSRF token
                $request->session()->regenerateToken();

                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Clients are not allowed to login.',
                        'errors' => [
                            'email' => ['Clients are not allowed to login.']
                        ],
                        'csrf_token' => csrf_token() // Send new CSRF token
                    ], 403);
                }

                return redirect('/login')->with('error', 'Clients are not allowed to login.');
            }

            // User is admin, proceed with login
            if ($request->expectsJson()) {
                return $this->sendLoginResponse($request);
            }

            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Send the response after the user was authenticated.
     */
    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();

        $this->clearLoginAttempts($request);

        if ($response = $this->authenticated($request, $this->guard()->user())) {
            return $response;
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Login successful',
                'redirect' => $this->redirectPath()
            ]);
        }

        return redirect()->intended($this->redirectPath());
    }

    /**
     * Get the failed login response instance.
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'These credentials do not match our records.',
                'errors' => [
                    $this->username() => ['These credentials do not match our records.']
                ]
            ], 422);
        }

        throw ValidationException::withMessages([
            $this->username() => [trans('auth.failed')],
        ]);
    }

    /**
     * The user has been authenticated.
     */
    protected function authenticated(Request $request, $user)
    {
        // Check if user is a client
        if ($user->isClient()) {
            $this->guard()->logout();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Clients are not allowed to login.',
                    'errors' => [
                        'email' => ['Clients are not allowed to login.']
                    ]
                ], 403);
            }

            return redirect('/login')->with('error', 'Clients are not allowed to login.');
        }

        return null;
    }
}