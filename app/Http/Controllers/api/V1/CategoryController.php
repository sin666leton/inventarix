<?php

namespace App\Http\Controllers\api\V1;

use App\DTOs\CategoryDTO;
use App\Exceptions\ActionForbiddenException;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\PaginateCollection;
use App\Services\CategoryService;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Categories",
 *     description="Endpoints for managing item categories."
 * )
 */
class CategoryController extends Controller
{
    public function __construct(
        protected CategoryService $categoryService
    ){}
    
    /**
     * @OA\Get(
     *  path="/api/v1/categories/all",
     *  summary="Retrieves a list of all item categories.",
     *  tags={"Categories"},
     *  security={{"bearerAuth": {}}},
     * 
     *  @OA\Response(
     *   response=200,
     *   description="Success",
     * 
     *   @OA\JsonContent(
     *    type="object",
     *    
     *    @OA\Property(
     *     property="data",
     *     type="array",
     *     
     *     @OA\Items(ref="#/components/schemas/Category")
     *    )
     *   )
     *  ),
     * 
     *  @OA\Response(
     *   response=401,
     *   description="Unauthenticated"
     *  )
     * )
     * 
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function all()
    {
        $categories = $this->categoryService->getAllCategories();

        return response()->json([
            'data' => $categories
        ], 200);
    }

    /**
     * @OA\Get(
     *   path="/api/v1/categories",
     *   tags={"Categories"},
     *   summary="Retrieves a paginated list of item categories.",
     *   @OA\Response(
     *     response=200,
     *     description="List of categories",
     *     @OA\JsonContent(
     *       allOf={
     *         @OA\Schema(ref="#/components/schemas/Pagination"),
     *         @OA\Schema(
     *           @OA\Property(
     *             property="data",
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Category")
     *           )
     *         )
     *       }
     *     )
     *   ),
     *   security={{"bearerAuth":{}}}
     * )
     */
    public function index()
    {
        $paginateCategory = $this->categoryService->paginateCategory();

        return response()->json(new PaginateCollection($paginateCategory), 200);
    }

    /**
     * @OA\Post(
     *  path="/api/v1/categories",
     *  summary="Creates a new item category.",
     *  security={{"bearerAuth":{}}},
     *  tags={"Categories"},
     *  
     *  @OA\RequestBody(
     *   required=true,
     * 
     *   @OA\JsonContent(
     *    type="object",
     *    required={"name"},
     *    @OA\Property(property="name", type="string", example="Electronic"),
     *    @OA\Property(property="description", type="string", example="Electronic devices")
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
     *      ref="#/components/schemas/Category"
     *     )
     *    )
     *   )
     *  )
     * )
     * 
     * @param \App\Http\Requests\CreateCategoryRequest $request
     * @throws \App\Exceptions\ActionForbiddenException
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function store(CreateCategoryRequest $request)
    {
        if (!$request->user()->tokenCan('create:category')) throw new ActionForbiddenException();

        $result = $this->categoryService->createCategory(new CategoryDTO($request->validated('name'), $request->validated('description')));
    
        return response()->json([
            'data' => $result
        ]);
    }

    /**
     * @OA\Get(
     *  path="/api/v1/categories/{id}",
     *  summary="Retrieves details of a specific category by ID.",
     *  tags={"Categories"},
     *  security={{"bearerAuth": {}}},
     * 
     *  @OA\Parameter(
     *   name="id",
     *   in="path",
     *   required=true,
     *   description="Category ID",
     *   @OA\Schema(type="integer")
     *  ),
     * 
     *  @OA\Response(
     *   response=200,
     *   description="Success",
     * 
     *   @OA\JsonContent(ref="#/components/schemas/Category")
     *  ),
     * 
     *  @OA\Response(
     *   response=401,
     *   description="Unauthenticated"
     *  ),
     * 
     *  @OA\Response(
     *   response=404,
     *   description="Category Not Found.",
     * 
     *   @OA\JsonContent(ref="#/components/schemas/ModelNotFound"),
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
        if (!$request->user()->tokenCan('view:category')) throw new ActionForbiddenException();

        $category = $this->categoryService->findCategory(intval($id));

        return response()->json([
            'data' => $category
        ], 200);
    }

    /**
     * @OA\Put(
     *  path="/api/v1/categories/{id}",
     *  summary="Updates the specified category.",
     *  tags={"Categories"},
     *  security={{"bearerAuth": {}}},
     * 
     *  @OA\Parameter(
     *   name="id",
     *   in="path",
     *   description="Category ID",
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
     *    required={"name"},
     *    @OA\Property(property="name", type="string", example="Electronic"),
     *    @OA\Property(property="description", type="string", example="Electronic devices")
     *   )
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
     *      ref="#/components/schemas/Category"
     *     )
     *    )
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
     *   response=404,
     *   description="Category Not Found",
     * 
     *   @OA\JsonContent(ref="#/components/schemas/ModelNotFound")
     *  )
     * )
     * 
     * @param \App\Http\Requests\UpdateCategoryRequest $request
     * @param string $id
     * @throws \App\Exceptions\ActionForbiddenException
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function update(UpdateCategoryRequest $request, string $id)
    {
        if (!$request->user()->tokenCan('update:category')) throw new ActionForbiddenException();

        $category = $this->categoryService->updateCategory(
            intval($id),
            new CategoryDTO(
                $request->validated('name'),
                $request->validated('description')
            )
        );

        return response()->json([
            'data' => $category
        ], 200);
    }

    /**
     * @OA\Delete(
     *  path="/api/v1/categories/{id}",
     *  summary="Deletes the specified category.",
     *  tags={"Categories"},
     *  security={{"bearerAuth": {}}},
     *  
     *  @OA\Parameter(
     *   name="id",
     *   in="path",
     *   description="Category ID",
     *   required=true,
     * 
     *   @OA\Schema(type="integer")
     *  ),
     * 
     *  @OA\Response(
     *   response=200,
     *   description="Success",
     *   @OA\JsonContent(ref="#/components/schemas/ModelDeleted")
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
     *   response=401,
     *   description="Unauthenticated",
     *  ),
     * 
     *  @OA\Response(
     *   response=404,
     *   description="Category Not Found",
     * 
     *   @OA\JsonContent(ref="#/components/schemas/ModelNotFound")
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
        if (!$request->user()->tokenCan('delete:category')) throw new ActionForbiddenException();
    
        $result = $this->categoryService->deleteCategory(intval($id));

        if ($result) return response()->json(['message' => 'Category has been successfully deleted.'], 200);
        else return response()->json(['message' => 'Something wrong.'], 500);
    }
}
