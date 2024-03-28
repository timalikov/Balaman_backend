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
        // Base token request data
        $tokenRequestData = [
            'grant_type' => $grantType,
            'client_id' => config('jwt.oauth_client_id'), // Use actual client_id from your .env or config
            'client_secret' => config('jwt.oauth_client_secret'), // Use actual client_secret from your .env or config
            'scope' => '', // Define scopes here if needed
        ];

        // Conditional logic based on grant type
        if ($grantType === 'password') {
            // For password grant type, include user credentials
            $tokenRequestData['username'] = $request->input('email');
            $tokenRequestData['password'] = $request->input('password');
        } elseif ($grantType === 'refresh_token' && $request->has('refresh_token')) {
            // For refresh token grant type, include the refresh token
            $tokenRequestData['refresh_token'] = $request->input('refresh_token');
        }

        // Create the request instance
        $tokenRequest = Request::create('/oauth/token', 'POST', $tokenRequestData);
        
        // Handle the request internally
        $tokenResult = app()->handle($tokenRequest);

        // Assuming the response is JSON, you can decode it
        $responseContent = json_decode($tokenResult->getContent(), true);

        // Check the response status code
        $statusCode = $tokenResult->getStatusCode();

        // Changes start here: Modify the success response to include user info
        if ($statusCode == 200) {
            // Obtain user info. Note: Ensure the user is already authenticated at this point.
            $user = User::where('email', $request->input('email'))->first();

            // Prepare the custom response structure with tokens and user info
            $customResponse = [
                'tokens' => [
                    'access_token' => $responseContent['access_token'], // Assuming these keys exist
                    'refresh_token' => $responseContent['refresh_token'], // Assuming these keys exist
                ],
                'user' => [
                    'id' => $user->id,
                    'displayName' => $user->name, // Adjust this if your user model uses a different field for the name
                    'email' => $user->email,
                    'photoURL' => $user->photo_url, // Adjust this field based on your user model
                    'role' => $user->roles, // Adjust this to match how roles are stored/retrieved in your app
                ],
            ];

            // Return the custom response
            return response()->json($customResponse);
        } else {
            // Error handling remains the same
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
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refreshToken(Request $request){
        
         // Validate that a refresh token is provided
        $request->validate([
            'refresh_token' => 'required',
        ]);

        $grantType = 'refresh_token';

        // Attempt to refresh the token
        $response = $this->requestOAuthTokens($request, $grantType);

        return $response;
    }

}
