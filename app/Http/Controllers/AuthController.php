<?php

namespace App\Http\Controllers;

use App\Models\Logs;
use App\Http\Controllers\Controller;
use App\Models\User;
use Validator;

/**
 * @OA\Info(
 *     title="API Documentation hackr",
 *     version="1.0.0",
 *     description="API for 'hacking' ! Thx Kevin Niel for this awesome Idea."
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Enter your bearer token in the format: Bearer {token}"
 * )
 */
class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/auth/register",
     *     summary="Register a new user",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", description="User's name"),
     *             @OA\Property(property="email", type="string", format="email", description="User's email"),
     *             @OA\Property(property="password", type="string", format="password", description="User's password")
     *         )
     *     ),
     *     @OA\Response(response=201, description="User registered successfully"),
     *     @OA\Response(response=400, description="Validation error")
     * )
     */
    public function register()
    {
        $validator = Validator::make(request()->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = new User;
        $user->name = request()->name;
        $user->email = request()->email;
        $user->password = bcrypt(request()->password);
        $user->save();

        Logs::create([
            'user_id' => auth()->id(),
            'target_id' => auth()->id(),
            'action' => 'Inscription de : ' . request()->name,
        ]);

        return response()->json($user, 201);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     summary="Log in a user",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", format="email", description="User's email"),
     *             @OA\Property(property="password", type="string", format="password", description="User's password")
     *         )
     *     ),
     *     @OA\Response(response=200, description="User logged in successfully", @OA\JsonContent(
     *         @OA\Property(property="access_token", type="string", description="JWT access token"),
     *         @OA\Property(property="token_type", type="string", description="Type of the token, e.g., Bearer"),
     *         @OA\Property(property="expires_in", type="integer", description="Expiration time in seconds")
     *     )),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function login()
    {
        $credentials = request(['email', 'password']);

        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        Logs::create([
            'user_id' => auth()->id(),
            'target_id' => auth()->id(),
            'action' => 'Connexion de : ' . auth()->user()->name,
        ]);

        return $this->respondWithToken($token);
    }

    /**
     * @OA\Get(
     *     path="/api/auth/me",
     *     summary="Get the authenticated user",
     *     tags={"Auth"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(response=200, description="Authenticated user details"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * @OA\Post(
     *     path="/api/auth/logout",
     *     summary="Log out the authenticated user",
     *     tags={"Auth"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(response=200, description="Successfully logged out"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function logout()
    {
        Logs::create([
            'user_id' => auth()->id(),
            'target_id' => auth()->id(),
            'action' => 'Déconnexion de : ' . auth()->user()->name,
        ]);
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/refresh",
     *     summary="Refresh the authentication token",
     *     tags={"Auth"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(response=200, description="Token refreshed"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Respond with the token structure.
     *
     * @param  string $token
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}
