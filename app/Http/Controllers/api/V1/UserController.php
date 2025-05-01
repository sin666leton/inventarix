<?php

namespace App\Http\Controllers\api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangeEmailRequest;
use App\Http\Requests\ChangeNameRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Services\UserService;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *  name="User",
 *  description="Endpoints for personal user account management."
 * )
 */
class UserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    /**
     * @OA\Put(
     *  path="/api/v1/user/change_name",
     *  summary="Updates the user’s display name.",
     *  tags={"User"},
     *  security={{"bearerAuth": {}}},
     * 
     *  @OA\RequestBody(
     *   required=true,
     * 
     *   @OA\JsonContent(
     *    type="object",
     *    required={"name"},
     * 
     *    @OA\Property(property="name", type="string", example="Balmond alex"),
     *   )
     *  ),
     * 
     *  @OA\Response(
     *   response=401,
     *   description="Unauthenticated"
     *  ),
     * 
     *  @OA\Response(
     *   response=422,
     *   description="Failed validation",
     * 
     *   @OA\JsonContent(ref="#/components/schemas/FailedValidation")
     *  ),
     * 
     *  @OA\Response(
     *   response=200,
     *   description="Success",
     * 
     *   @OA\JsonContent(
     *    type="object",
     * 
     *    @OA\Property(
     *     property="message",
     *     type="string",
     *     example="Name updated successfully."
     *    )
     *   )
     *  )
     * )
     * 
     * @param \App\Http\Requests\ChangeNameRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function changeName(ChangeNameRequest $request)
    {
        $result = $this->userService->updateName($request->user(), $request->validated('name'));
    
        if ($result) return response()->json(['message' => 'Name updated successfully.'], 200);
        else return response()->json(['message' => 'Something wrong.'], 500);
    }

    /**
     * @OA\Put(
     *  path="/api/v1/user/change_email",
     *  summary="Updates the user’s email address.",
     *  tags={"User"},
     *  security={{"bearerAuth": {}}},
     * 
     *  @OA\RequestBody(
     *   required=true,
     * 
     *   @OA\JsonContent(
     *    type="object",
     *    required={"new_email", "password"},
     * 
     *    @OA\Property(property="new_email", type="string", example="mynewemail@gmail.com"),
     *    @OA\Property(property="password", type="string", example="mycurrentpassword"),
     *   )
     *  ),
     * 
     *  @OA\Response(
     *   response=401,
     *   description="Unauthenticated"
     *  ),
     * 
     *  @OA\Response(
     *   response=422,
     *   description="Failed validation",
     * 
     *   @OA\JsonContent(ref="#/components/schemas/FailedValidation")
     *  ),
     * 
     *  @OA\Response(
     *   response=200,
     *   description="Success",
     * 
     *   @OA\JsonContent(
     *    type="object",
     * 
     *    @OA\Property(
     *     property="message",
     *     type="string",
     *     example="Email updated successfully."
     *    )
     *   )
     *  )
     * )
     * 
     * @param \App\Http\Requests\ChangeEmailRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function changeEmail(ChangeEmailRequest $request)
    {
        $result = $this->userService->updateEmail(
            $request->user(),
            $request->validated('new_email'),
            $request->validated('password')
        );

        if ($result) return response()->json(['message' => 'Email updated successfully.'], 200);
        else return response()->json(['message' => 'Something wrong.'], 500);
    }

    /**
     * @OA\Put(
     *  path="/api/v1/user/change_password",
     *  summary="Changes the user’s password.",
     *  tags={"User"},
     *  security={{"bearerAuth": {}}},
     * 
     *  @OA\RequestBody(
     *   required=true,
     * 
     *   @OA\JsonContent(
     *    type="object",
     *    required={"new_password", "old_password"},
     * 
     *    @OA\Property(property="new_password", type="string", example="mynewsafetypassword"),
     *    @OA\Property(property="old_password", type="string", example="mycurrentpassword"),
     *   )
     *  ),
     * 
     *  @OA\Response(
     *   response=401,
     *   description="Unauthenticated"
     *  ),
     * 
     *  @OA\Response(
     *   response=422,
     *   description="Failed validation",
     * 
     *   @OA\JsonContent(ref="#/components/schemas/FailedValidation")
     *  ),
     * 
     *  @OA\Response(
     *   response=200,
     *   description="Success",
     * 
     *   @OA\JsonContent(
     *    type="object",
     * 
     *    @OA\Property(
     *     property="message",
     *     type="string",
     *     example="Password updated successfully."
     *    )
     *   )
     *  )
     * )
     * 
     * @param \App\Http\Requests\ChangePasswordRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        $result = $this->userService->updatePassword(
            $request->user(),
            $request->validated('old_password'),
            $request->validated('new_password')
        );

        if ($result) return response()->json(['message' => 'Password updated successfully.'], 200);
        else return response()->json(['message' => 'Something wrong.'], 500);
    }
}
