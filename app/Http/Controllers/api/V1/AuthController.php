<?php

namespace App\Http\Controllers\api\V1;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *  name="Auth",
 *  description="Endpoints for user authentication and session control."
 * )
 */
class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * @OA\Post(
     *  path="/api/v1/admin/login",
     *  summary="Authenticates a admin using email and password. Returns an access token on success",
     *  tags={"Auth"},
     * 
     *  @OA\RequestBody(
     *   required=true,
     * 
     *   @OA\JsonContent(
     *    type="object",
     *    required={"email", "password"},
     *    @OA\Property(property="email", type="string", example="admin@example.com"),
     *    @OA\Property(property="password", type="string", example="123")
     *   )
     *  ),
     * 
     *  @OA\Response(
     *   response=200,
     *   description="Login successful",
     *   @OA\JsonContent(
     *    type="object",
     *    @OA\Property(property="token", type="string")
     *   )
     *  ),
     * 
     *  @OA\Response(
     *   response=401,
     *   description="Wrong email or password."
     *  )
     * )
     * 
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function loginAdmin(Request $request)
    {
        $token = $this->authService->loginAsAdmin($request->input('email', ""), $request->input('password', ""));
        
        return response()->json([
            'token' => $token
        ]);
    }

    /**
     * @OA\Post(
     *  path="/api/v1/staff/login",
     *  summary="Authenticates a staff using email and password. Returns an access token on success",
     *  tags={"Auth"},
     * 
     *  @OA\RequestBody(
     *   required=true,
     * 
     *   @OA\JsonContent(
     *    type="object",
     *    required={"email", "password"},
     *    @OA\Property(property="email", type="string", example="staff@example.com"),
     *    @OA\Property(property="password", type="string", example="123")
     *   )
     *  ),
     * 
     *  @OA\Response(
     *   response=200,
     *   description="Login successful",
     *   @OA\JsonContent(
     *    type="object",
     *    @OA\Property(property="token", type="string")
     *   )
     *  ),
     * 
     *  @OA\Response(
     *   response=401,
     *   description="Wrong email or password."
     *  )
     * )
     * 
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function loginStaff(Request $request)
    {
        $token = $this->authService->loginAsStaff($request->input('email', ''), $request->input('password', ''));
        
        return response()->json([
            'token' => $token
        ]);
    }

    /**
     * @OA\Delete(
     *  path="/api/v1/admin/logout",
     *  summary="Logs out the currently authenticated admin and revokes their token.",
     *  tags={"Auth"},
     *  security={{"bearerAuth": {}}},
     * 
     *  @OA\Response(
     *   response=200,
     *   description="Logged out successfully.",
     *  
     *   @OA\JsonContent(
     *    type="object",
     *    
     *    @OA\Property(
     *     property="message",
     *     type="string",
     *     example="Logged out successfully."
     *    )
     *   )
     *  ),
     * 
     *  @OA\Response(
     *   response=401,
     *   description="Unauthorized"
     *  )
     * ),
     * 
     * @OA\Delete(
     *  path="/api/v1/staff/logout",
     *  summary="Logs out the currently authenticated staff and revokes their token.",
     *  tags={"Auth"},
     *  security={{"bearerAuth": {}}},
     * 
     *  @OA\Response(
     *   response=200,
     *   description="Logged out successfully.",
     *  
     *   @OA\JsonContent(
     *    type="object",
     *    
     *    @OA\Property(
     *     property="message",
     *     type="string",
     *     example="Logged out successfully."
     *    )
     *   )
     *  ),
     * 
     *  @OA\Response(
     *   response=401,
     *   description="Unauthorized"
     *  )
     * )
     * 
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $this->authService->logoutAnyRole();

        return response()->json([
            'message' => 'Logged out successfully.'
        ], 200);
    }
}
