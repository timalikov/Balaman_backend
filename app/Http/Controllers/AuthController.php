<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
     * @OA\Post(
     *     path="/api/auth/register",
     *     summary="Register a new user",
     *     operationId="register",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password"},
     *             @OA\Property(property="name", type="string", description="User's name"),
     *             @OA\Property(property="email", type="string", format="email", description="User's email address"),
     *             @OA\Property(property="password", type="string", format="password", description="User's password"),
     *             @OA\Property(property="role_name", type="string", description="Role assigned to the user (optional)", example="admin")
     *         )
     *     ),
     *     @OA\Response(response=201, description="User successfully registered"),
     *     @OA\Response(response=400, description="Validation failed"),
     *     @OA\Response(response=500, description="Failed to create user")
     * )
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
    
        try {
            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => bcrypt($validatedData['password']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create user',
                'error' => $e->getMessage()
            ], 500);
        }

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

    /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     summary="User login",
     *     operationId="login",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", description="User's email address"),
     *             @OA\Property(property="password", type="string", format="password", description="User's password")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Login successful, returns access and refresh tokens"),
     *     @OA\Response(response=400, description="Validation failed"),
     *     @OA\Response(response=401, description="Invalid credentials")
     * )
     */
    public function login(Request $request)
    {
        $validator = $this->validateLogin($request);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed'], 400);
        }

        if (!$this->attemptLogin($request)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $grantType = 'password';

        return $this->requestOAuthTokens($request, $grantType);
    }

    protected function requestOAuthTokens(Request $request, $grantType)
    {
        $tokenRequestData = [
            'grant_type' => $grantType,
            'client_id' => config('jwt.oauth_client_id'), 
            'client_secret' => config('jwt.oauth_client_secret'), 
            'scope' => '', 
        ];

        if ($grantType === 'password') {
            $tokenRequestData['username'] = $request->input('email');
            $tokenRequestData['password'] = $request->input('password');
            
        } elseif ($grantType === 'refresh_token' && $request->has('refresh_token')) {
            $tokenRequestData['refresh_token'] = $request->input('refresh_token');
        }

        $tokenRequest = Request::create('/oauth/token', 'POST', $tokenRequestData);
        
        $tokenResult = app()->handle($tokenRequest);

        Log::info("OAuth token request: " . $tokenRequest->getContent() . " Response: " . $tokenResult->getContent() . " Status Code: " . $tokenResult->getStatusCode());

        $responseContent = json_decode($tokenResult->getContent(), true);

        $statusCode = $tokenResult->getStatusCode();

        if ($statusCode == 200) {
            $user = User::with('roles:id,name')->where('email', $request->input('email'))->first();

            $role = $user->roles->first();  

            $customResponse = [
                'tokens' => [
                    'access_token' => $responseContent['access_token'], 
                    'refresh_token' => $responseContent['refresh_token'], 
                ],
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name, 
                    'email' => $user->email,
                    'photo_url' => $user->photo_url, 
                    'role' => [
                        'id' => $role->id,  
                        'name' => $role->name,
                    ],
                ],
            ];

            return response()->json($customResponse);
        } else {
            Log::error("OAuth token request failed: Status Code: $statusCode, Response: " . $tokenResult->getContent());
            return response()->json(['message' => 'Failed to obtain access token'], $statusCode);
        }
    }

    public function profile(){
        return response()->json(auth()->user());
    }

    public function logout(){
        auth()->logout();
        return response()->json(['message' => 'User successfully logged out']);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/refresh-token",
     *     summary="Refresh an access token",
     *     operationId="refreshToken",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"refresh_token"},
     *             @OA\Property(property="refresh_token", type="string", description="Refresh token for generating new access token")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Tokens refreshed successfully"),
     *     @OA\Response(response=400, description="Validation failed")
     * )
     */
    public function refreshToken(Request $request){
        
        $request->validate([
            'refresh_token' => 'required',
        ]);

        $grantType = 'refresh_token';

        $response = $this->requestOAuthTokens($request, $grantType);

        return $response;
    }

}
