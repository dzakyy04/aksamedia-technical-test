<?php

namespace App\Http\Controllers\Api;

use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/login",
     *     summary="Admin Login",
     *     description="Logs in an admin and returns a JWT token.",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"username", "password"},
     *                 @OA\Property(property="username", description="Admin username", type="string", example="admin"),
     *                 @OA\Property(property="password", description="Admin password", type="string", example="pastibisa")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Login successfully"),
     *             @OA\Property(
     *                 property="data", type="object",
     *                 @OA\Property(property="token", type="string", example="authentication-token"),
     *                 @OA\Property(
     *                     property="admin", type="object", description="Logged-in admin details",
     *                     @OA\Property(property="id", type="string", example="uuid-of-admin"),
     *                     @OA\Property(property="name", type="string", example="Admin Name"),
     *                     @OA\Property(property="username", type="string", example="admin-username"),
     *                     @OA\Property(property="phone", type="string", example="admin-phone-number"),
     *                     @OA\Property(property="email", type="string", example="admin-email@example.com")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid username or password",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Invalid username or password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(
     *                 property="errors", type="object",
     *                 @OA\Property(property="username", type="array", @OA\Items(type="string"), example={"The username field is required."}),
     *                 @OA\Property(property="password", type="array", @OA\Items(type="string"), example={"The password field is required."})
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Unexpected error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred while logging in")
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $credentials = $request->only('username', 'password');

        try {
            if (!$token = Auth::guard('admin')->attempt($credentials)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid username or password',
                ], 401);
            }

            $admin = Auth::guard('admin')->user();

            return response()->json([
                'status' => 'success',
                'message' => 'Login successfully',
                'data' => [
                    'token' => $token,
                    'admin' => $admin
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred while logging in',
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/logout",
     *     summary="Admin Logout",
     *     description="Logs out the currently authenticated admin. Requires authentication.",
     *     tags={"Authentication"},
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="Logout successful",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Logout successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Unexpected error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred while logging out")
     *         )
     *     )
     * )
     *
     * @OA\SecurityScheme(
     *     securityScheme="bearerAuth",
     *     type="http",
     *     scheme="bearer",
     *     bearerFormat="JWT",
     *     description="JWT Authorization header using the Bearer scheme."
     * )
     */
    public function logout(Request $request)
    {
        try {
            Auth::guard('api')->logout();

            return response()->json([
                'status' => 'success',
                'message' => 'Logout successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred while logging out',
            ], 500);
        }
    }
}
