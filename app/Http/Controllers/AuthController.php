<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Validator;
use App\Models\User;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash; 
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class AuthController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'refreshToken']]);
    }

    /**
     * Register a User.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'role_name' => 'sometimes|string' 
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }
    
        $validatedData = $validator->validated();
    
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => bcrypt($validatedData['password']),
        ]);

        // Assign default role if not provided or use the provided role
        $roleName = $request->input('role_name', 'user');
        $user->assignRole($roleName);
    
        return response()->json([
            'message' => 'User successfully registered',
            'user' => $user
        ], 201);
    }

    protected function validateLogin(Request $request)
    {
        return Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);
    }

    protected function validationErrorResponse($errors)
    {
        return response()->json([
            'message' => 'Validation failed',
            'errors' => $errors
        ], 400);
    }

    protected function attemptLogin(Request $request)
    {
        $credentials = $request->only('email', 'password');
        return Auth::guard('web')->attempt($credentials);
    }

    public function login(Request $request)
    {
        $validator = $this->validateLogin($request);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        if (!$this->attemptLogin($request)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        Log::info("User authenticated: " . Auth::guard('web')->user()->email);

        // return $this->requestOAuthTokens($request);

        $response = Http::timeout(10)->post('http://127.0.0.1:8002/oauth/token', [
            'grant_type' => 'password',
            'client_id' => 4,
            'client_secret' => 'gFGELOlNGUbhqDKpOKzPGHrd2yJPeoREYNILz7Jr',
            'username' => 'hey1@mail.ru',
            'password' => '123456',
            'scope' => '',
        ]);

        return $response;
    }

    protected function requestOAuthTokens(Request $request)
    {
        $response = Http::post(env('OAUTH_TOKEN_URL'), [
            'grant_type' => 'password',
            'client_id' => env('OAUTH_CLIENT_ID'), // Use environment variable
            'client_secret' => env('OAUTH_CLIENT_SECRET'), // Use environment variable
            'username' => $request->email,
            'password' => $request->password,
            'scope' => '' // define scopes here if needed
        ]);
        
        if (!$response->ok()) {
            Log::error("OAuth token request failed: Status Code: " . $response->status() . ", Response Body: " . $response->body());
            throw new \Exception("OAuth token request failed.");
        }

        $tokens = $response->json();

        // $this->storeTokensInCache($tokens['access_token'], $tokens['refresh_token'], $tokens['expires_in']);

        return response()->json($tokens);
    }


    public function createNewToken($token){
        $user = auth()->user();
        $role = $user->getRoleNames()->first();

        // Add custom claims with user role
        $customClaims = ['role' => $role];
        
        $customToken = auth()->claims($customClaims)->login($user);
    
        return response()->json([
            'access_token' => $customToken,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => $user
        ]);
    }
    

    public function profile(){
        return response()->json(auth()->user());
    }

    public function logout(){
        auth()->logout();
        return response()->json(['message' => 'User successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refreshToken(){
        return $this->createNewToken(auth()->refresh());
    }

}
