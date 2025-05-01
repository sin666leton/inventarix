<?php

namespace App\Http\Controllers\api\V1;

use App\DTOs\CreateUserDTO;
use App\DTOs\UpdateUserDTO;
use App\Exceptions\ActionForbiddenException;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateStaffRequest;
use App\Http\Requests\UpdateStaffRequest;
use App\Http\Resources\PaginateCollection;
use App\Services\StaffService;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *  name="Staff",
 *  description="Endpoints for managing staff users with administrative or operational roles."
 * )
 */
class StaffController extends Controller
{
    public function __construct(
        protected StaffService $staffService
    ) {}

    /**
     * @OA\Get(
     *  path="/api/v1/staff",
     *  summary="Retrieves a paginated list of item staff.",
     *  tags={"Staff"},
     *  security={{"bearerAuth": {}}},
     * 
     *  @OA\Response(
     *   response=200,
     *   description="Success",
     * 
     *   @OA\JsonContent(
     *    allOf={
     *     @OA\Schema(ref="#/components/schemas/Pagination"),
     *     @OA\Schema(
     *      @OA\Property(
     *       property="data",
     *       type="array",
     *       @OA\Items(ref="#/components/schemas/Staff")
     *      )
     *     )
     *    }
     *   )
     *  ),
     * 
     *  @OA\Response(
     *   response=401,
     *   description="Unauthenticated"
     *  ),
     * 
     *  @OA\Response(
     *   response=403,
     *   description="Forbidden",
     * 
     *   @OA\JsonContent(ref="#/components/schemas/ActionForbidden")
     *  )
     * )
     * 
     * @param \Illuminate\Http\Request $request
     * @throws \App\Exceptions\ActionForbiddenException
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        if (!$request->user()->tokenCan('viewAny:staff')) throw new ActionForbiddenException();

        return response()->json(new PaginateCollection($this->staffService->paginateStaff()), 200);
    }

    /**
     * @OA\Post(
     *  path="/api/v1/staff",
     *  summary="Creates a new staff user.",
     *  tags={"Staff"},
     *  security={{"bearerAuth": {}}},
     * 
     *  @OA\RequestBody(
     *   required=true,
     * 
     *   @OA\JsonContent(
     *    type="object",
     *    required={"name", "email", "password"},
     * 
     *    @OA\Property(property="name", type="string", example="Balmond"),
     *    @OA\Property(property="email", type="string", example="balmondantony@gmail.com"),
     *    @OA\Property(property="password", type="string", example="mysecurepassword123"),
     *   )
     *  ),
     * 
     *  @OA\Response(
     *   response=403,
     *   description="Forbidden",
     * 
     *   @OA\JsonContent(ref="#/components/schemas/ActionForbidden")
     *  ),
     * 
     * 
     *  @OA\Response(
     *   response=401,
     *   description="Unauthenticated"
     *  ),
     * 
     *  @OA\Response(
     *   response=422,
     *   description="Failed Validation",
     *   
     *   @OA\JsonContent(ref="#/components/schemas/FailedValidation")
     *  ),
     * 
     *  @OA\Response(
     *   response=200,
     *   description="Success",
     * 
     *   @OA\JsonContent(
     *     type="object",
     * 
     *     @OA\Property(
     *      property="data",
     *      type="object",
     *      ref="#/components/schemas/Staff"
     *     )
     *    )
     *   )
     *  )
     * )
     * 
     * @param \App\Http\Requests\CreateStaffRequest $request
     * @throws \App\Exceptions\ActionForbiddenException
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function store(CreateStaffRequest $request)
    {
        if (!$request->user()->tokenCan('create:staff')) throw new ActionForbiddenException();

        $user = $this->staffService->createStaff(CreateUserDTO::fromRequest($request));
    
        return response()->json([
            'data' => $user
        ]);
    }

    /**
     * @OA\Get(
     *  path="/api/v1/staff/{id}",
     *  summary="Retrieves details of a specific staff member.",
     *  tags={"Staff"},
     *  security={{"bearerAuth": {}}},
     *  
     *  @OA\Parameter(
     *   name="id",
     *   in="path",
     *   description="Staff ID",
     *   required=true,
     * 
     *   @OA\Schema(type="integer")
     *  ),
     * 
     *  @OA\Response(
     *   response=404,
     *   description="Item Not Found",
     * 
     *   @OA\JsonContent(ref="#/components/schemas/ModelNotFound")
     *  ),
     * 
     *  @OA\Response(
     *   response=401,
     *   description="Unauthenticated",
     *  ),
     *  
     *  @OA\Response(
     *   response=403,
     *   description="Forbidden",
     *   @OA\JsonContent(ref="#/components/schemas/ActionForbidden")
     *  ),
     * 
     *  @OA\Response(
     *   response=200,
     *   description="Success",
     * 
     *   @OA\JsonContent(
     *     type="object",
     * 
     *     @OA\Property(
     *      property="data",
     *      type="object",
     *      ref="#/components/schemas/Staff"
     *     )
     *    )
     *   )
     *  )
     * )
     * 
     * @param \Illuminate\Http\Request $request
     * @param string $id
     * @throws \App\Exceptions\ActionForbiddenException
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function show(Request $request, string $id)
    {
        if (!$request->user()->tokenCan('view:staff')) throw new ActionForbiddenException();

        $user = $this->staffService->findStaff(intval($id));

        return response()->json([
            'data' => $user
        ], 200);
    }

    /**
     * @OA\Put(
     *  path="/api/v1/staff/{id}",
     *  summary="Updates the information of a specific staff member.",
     *  tags={"Staff"},
     *  security={{"bearerAuth": {}}},
     * 
     *  @OA\Parameter(
     *   name="id",
     *   in="path",
     *   description="Staff ID",
     *   required=true,
     * 
     *   @OA\Schema(type="integer")
     *  ),
     * 
     *  @OA\RequestBody(
     *   required=true,
     * 
     *   @OA\JsonContent(
     *    type="object",
     *    required={"name", "email"},
     * 
     *    @OA\Property(property="name", type="string", example="Steve"),
     *    @OA\Property(property="email", type="string", example="stevealex@gmail.com")
     *   )
     *  ),
     * 
     *  @OA\Response(
     *   response=403,
     *   description="Forbidden",
     * 
     *   @OA\JsonContent(ref="#/components/schemas/ActionForbidden")
     *  ),
     * 
     *  @OA\Response(
     *   response=422,
     *   description="Failed Validation",
     * 
     *   @OA\JsonContent(ref="#/components/schemas/FailedValidation")
     *  ),
     * 
     *  @OA\Response(
     *   response=401,
     *   description="Unauthenticated"
     *  ),
     * 
     *  @OA\Response(
     *   response=404,
     *   description="Item Not Found",
     * 
     *   @OA\JsonContent(ref="#/components/schemas/ModelNotFound")
     *  ),
     * 
     *  @OA\Response(
     *   response=200,
     *   description="Success",
     * 
     *   @OA\JsonContent(
     *     type="object",
     * 
     *     @OA\Property(
     *      property="data",
     *      type="object",
     *      ref="#/components/schemas/Staff"
     *     )
     *    )
     *   )
     *  )
     * )
     * 
     * @param \App\Http\Requests\UpdateStaffRequest $request
     * @param string $id
     * @throws \App\Exceptions\ActionForbiddenException
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function update(UpdateStaffRequest $request, string $id)
    {
        if (!$request->user()->tokenCan('update:staff')) throw new ActionForbiddenException();

        $user = $this->staffService->updateStaff(intval($id), UpdateUserDTO::fromRequest($request));
    
        return response()->json([
            'data' => $user
        ], 200);
    }

    /**
     * @OA\Delete(
     *  path="/api/v1/staff/{id}",
     *  summary="Removes a staff member from the system.",
     *  tags={"Staff"},
     *  security={{"bearerAuth": {}}},
     * 
     *  @OA\Parameter(
     *   name="id",
     *   in="path",
     *   description="Staff ID",
     *   required=true,
     * 
     *   @OA\Schema(type="integer")
     *  ),
     * 
     *  @OA\Response(
     *   response=404,
     *   description="Item Not Found",
     *   
     *   @OA\JsonContent(ref="#/components/schemas/ModelNotFound")
     *  ),
     * 
     *  @OA\Response(
     *   response=401,
     *   description="Unauthenticated"
     *  ),
     * 
     *  @OA\Response(
     *   response=403,
     *   description="Forbidden",
     * 
     *   @OA\JsonContent(ref="#/components/schemas/ActionForbidden")
     *  ),
     * 
     *  @OA\Response(
     *   response=200,
     *   description="Success",
     * 
     *   @OA\JsonContent(ref="#/components/schemas/ModelDeleted")
     *  )
     * )
     * 
     * @param \Illuminate\Http\Request $request
     * @param string $id
     * @throws \App\Exceptions\ActionForbiddenException
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, string $id)
    {
        if (!$request->user()->tokenCan('delete:staff')) throw new ActionForbiddenException();

        $deleted = $this->staffService->deleteStaff(intval($id));

        if ($deleted) return response()->json(['message' => 'Staff has been deleted successfully.']);
        else return response()->json(['message' => 'Something Wrong.'], 500);
    }
}
