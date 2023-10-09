<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    protected $guard = 'api';
    protected $client = null;

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'refresh', 'logout', 'registration']]);
        $this->client = new Client();
    }

    public function getUserByEmail($email)
    {
        $apiUrl = env('REGISTRATION_SERVICE', '') . "/user?email=$email";
        $res = $this->client->request('GET', $apiUrl);
        return json_decode($res->getBody(), true);
    }

    public function convertUser($userArray)
    {
        $object = new User();
        foreach ($userArray as $key => $value) {
            $object->$key = $value;
        }
        return $object;
    }

    /**
     * Get a JWT via given credentials.
     *
     * @param  Request  $request
     * @return Response
     */
    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only(['email', 'password']);

        try {
            $user = $this->getUserByEmail($credentials['email']);
            $user = $this->convertUser($user);
            if (!password_verify($credentials['password'], $user->password)) {
                throw new Exception('User not found??');
            }
        } catch (Exception $e) {
            return response()->json([
                'error' => [
                    'code' => 404,
                    'message' => $e->getMessage(),
                ]
            ], 404);
        }

        if (!$token = auth($this->guard)->login($user)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function registration(Request $request)
    {
        $apiUrl = env('REGISTRATION_SERVICE', '') . "/user/registration";

        $input = $request->all();
        if (isset($input['password']) && !empty($input['password'])) {
            $input['password'] = password_hash($input['password'], PASSWORD_BCRYPT);
        }

        try {
            $res = $this->client->request(
                'POST',
                $apiUrl,
                [
                    'form_params' => $input
                ]
            );
        } catch (Exception $e) {
            return response()->json([
                'error' => [
                    'code' => 400,
                    'message' => $e->getMessage(),
                ]
            ], 404);
        }

        return json_decode($res->getBody(), true);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        $token = JWTAuth::getToken();
        return $this->respondWithToken(JWTAuth::refresh($token));
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'user' => auth()->user(),
            'expires_in' => JWTAuth::factory()->getTTL() * 60 * 24
        ]);
    }
}
