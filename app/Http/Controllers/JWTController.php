<?php

namespace App\Http\Controllers;

use Auth;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Validator;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\UserController;

class JWTController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','loginAsAppUser', 'register']]);
    }

    /**
     * Register user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        /**
         * Port register logic to User model
         *
         * @var $user
         */
        $user = User::register($request);

        /**
         * If registration returns a JsonResponse and not a User
         * we return the response as validation
         */
        if ($user instanceof JsonResponse) {
            return $user;
        }

        /**
         * We get a User model returned, show success
         */
        return response()->json([
            'message' => 'User successfully registered',
            'user' => $user
        ], 201);
    }

    /**
     * login user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {

        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $token = auth('api')->attempt(['email' => $request->input('email'), 'password' => $request->input('password')]);

        if (!$token) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = auth('api')->user();

        //Only active users can login

        if (!$user->active) {
            return response()->json(['error' => 'Inactive account'], 403);
        }

        //Update last login time

        $user->last_login_at = Carbon::now();
        $user->save();
       $data=$this->responseWithTokenAndUser($token, $user);

        return $data;
    }
    public function loginAsAppUser(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $token = auth('api')->attempt(['email' => $request->input('email'), 'password' => $request->input('password')]);

        if (!$token) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = auth('api')->user();

        //Only active users can login

        if (!$user->active) {
            return response()->json(['error' => 'Inactive account'], 403);
        }

        //check if user have app access

        if(!in_array('app access',json_decode($user->all_permissions))){
            return response()->json(['error' => 'App login access denied'], 403);
        }

        //Update last login time

        $user->last_login_at = Carbon::now();
        $user->save();
       return $this->responseWithTokenAndUser($token, $user);


    }

    public function loginAs(Request $request, $id)
    {
        $user = User::findOrfail($id);
        $token = auth('api')->login($user);
        $user = auth('api')->user();

        return $this->responseWithTokenAndUser($token, $user);
    }

    /**
     * Logout user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth('api')->logout();

        return response()->json(['message' => 'User successfully logged out.']);
    }

    /**
     * Refresh token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        $user = auth('api')->user();

        return $this->responseWithTokenAndUser(auth('api')->refresh(), $user);
        //return $this->respondWithToken(auth('api')->refresh());
    }

    /**
     * Get user profile.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile()
    {
        return response()->json(auth('api')->user());
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }

    protected function responseWithTokenAndUser($token, $user)
    {
        return response()->json([
            'access_token' => $token,
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => $user->only('id', 'odoo_user_id', 'odoo', 'email', 'first_name', 'last_name', 'all_permissions', 'is_account', 'is_admin', 'is_super_admin', 'is_client', 'is_portal', 'locations', 'addressRequests', 'addressRequests.request','app_access'),
            'permissions' => $user->getPermissionNames(),
        ]);
    }
}
