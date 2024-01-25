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
            'password' => 'required|string|confirmed|min:6',
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

    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if($validator->fails()){
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()->toJson()
            ], 400);
        }
        if(!$token = auth()->attempt($validator->validated())){
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }
        return $this->createNewToken($token);
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
