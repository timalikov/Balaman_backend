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
            return Response::HTTP;
        }

        if (!$this->attemptLogin($request)) {
            return Response::HTTP_UNAUTHORIZED;
        }

        return $this->requestOAuthTokens($request);
    }

    protected function requestOAuthTokens(Request $request)
    {
        // Create a POST request to the OAuth token endpoint
        $tokenRequestData = [
            'grant_type' => 'password',
            'client_id' => env('OAUTH_CLIENT_ID'), // Use actual client_id
            'client_secret' => env('OAUTH_CLIENT_SECRET'), // Use actual client_secret
            'username' => $request->input('email'), // The user's email
            'password' => $request->input('password'), // The user's password
            'scope' => '', // Define scopes here if needed
        ];

        // Create the request instance
        $tokenRequest = Request::create('/oauth/token', 'POST', $tokenRequestData);
        
        // Handle the request internally
        $tokenResult = app()->handle($tokenRequest);

        // Assuming the response is JSON, you can decode it
        $responseContent = json_decode($tokenResult->getContent(), true);

        // You can then access the response status code and body as needed
        $statusCode = $tokenResult->getStatusCode();
        $responseBody = $tokenResult->getContent();

        // Use the response data as required
        if ($statusCode == 200) {
            // Success handling
            $tokens = $responseContent;
            // Do something with the tokens
        } else {
            // Error handling
            Log::error("OAuth token request failed: Status Code: $statusCode, Response Body: $responseBody");
            // Handle the error accordingly
        }

        // Returning or further processing
        return response()->json($tokens);
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
