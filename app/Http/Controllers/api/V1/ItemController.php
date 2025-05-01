<?php

namespace App\Http\Controllers\api\V1;

use App\DTOs\CreateItemDTO;
use App\DTOs\UpdateItemDTO;
use App\Exceptions\ActionForbiddenException;
use App\Exceptions\MissingParameterException;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Http\Resources\PaginateCollection;
use App\Services\ItemService;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *  name="Items",
 *  description="Endpoints for managing inventory items."
 * )
 */
class ItemController extends Controller
{
    public function __construct(
        protected ItemService $itemService
    ) {}

    /**
     * @OA\Get(
     *  path="/api/v1/items",
     *  summary="Retrieves a paginated list of item items.",
     *  tags={"Items"},
     *  security={{"bearerAuth": {}}},
     * 
     *  @OA\Parameter(
     *   name="category",
     *   in="query",
     *   description="Category ID",
     *   required=true,
     *   
     *   @OA\Schema(type="string")
     *  ),
     * 
     *  @OA\Response(
     *   response=200,
     *   description="Success",
     * 
     *   @OA\JsonContent(ref="#/components/schemas/Item")
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
     *  )
     * )
     * 
     * @param \Illuminate\Http\Request $request
     * @throws \App\Exceptions\MissingParameterException
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $categoryID = $request->query('category');

        if ($categoryID == null) throw new MissingParameterException('?category');

        $result = $this->itemService->paginateItems(intval($categoryID));

        return response()->json(new PaginateCollection($result), 200);
    }

    /**
     * @OA\Post(
     *  path="/api/v1/items",
     *  summary="Adds a new item to the inventory.",
     *  tags={"Items"},
     *  security={{"bearerAuth": {}}},
     * 
     *  @OA\RequestBody(
     *   required=true,
     * 
     *   @OA\JsonContent(
     *    type="object",
     *    required={"category_id", "name", "code","stock"},
     * 
     *    @OA\Property(property="category_id", type="integer", example=1),
     *    @OA\Property(property="name", type="string", example="Laptop"),
     *    @OA\Property(property="code", type="string", example="#LPT20"),
     *    @OA\Property(property="stock", type="integer", example=20)
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
     *      ref="#/components/schemas/Item"
     *     )
     *    )
     *   )
     *  )
     * )
     * 
     * @param \App\Http\Requests\CreateItemRequest $request
     * @throws \App\Exceptions\ActionForbiddenException
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function store(CreateItemRequest $request)
    {
        if (!$request->user()->tokenCan('create:item')) throw new ActionForbiddenException();

        $item = $this->itemService->createItem(
            $request->validated('category_id'),
            new CreateItemDTO(
                $request->validated('name'),
                $request->validated('code'),
                $request->validated('stock')
            )
        );

        return response()->json([
            'data' => $item
        ], 200);
    }

    /**
     * @OA\Get(
     *  path="/api/v1/items/{id}",
     *  summary=" Retrieves details of a specific item by ID.",
     *  tags={"Items"},
     *  security={{"bearerAuth": {}}},
     *  
     *  @OA\Parameter(
     *   name="id",
     *   in="path",
     *   description="Item ID",
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
     *   response=200,
     *   description="Success",
     * 
     *   @OA\JsonContent(
     *     type="object",
     * 
     *     @OA\Property(
     *      property="data",
     *      type="object",
     *      ref="#/components/schemas/Item"
     *     )
     *    )
     *   )
     *  ),
     * )
     * 
     * @param \Illuminate\Http\Request $request
     * @param string $id
     * @throws \App\Exceptions\ActionForbiddenException
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function show(Request $request, string $id)
    {
        if (!$request->user()->tokenCan('view:item')) throw new ActionForbiddenException();

        $item = $this->itemService->findItem(intval($id));
    
        return response()->json([
            'data' => $item
        ], 200);
    }

    /**
     * @OA\Put(
     *  path="/api/v1/items/{id}",
     *  summary="Updates information for a specific item.",
     *  tags={"Items"},
     *  security={{"bearerAuth": {}}},
     * 
     *  @OA\Parameter(
     *   name="id",
     *   in="path",
     *   description="Item ID",
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
     *    required={"name", "stock"},
     * 
     *    @OA\Property(property="name", type="string", example="Laptop"),
     *    @OA\Property(property="stock", type="integer", example=20)
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
     *      ref="#/components/schemas/Item"
     *     )
     *    )
     *   )
     *  )
     * )
     * 
     * @param \App\Http\Requests\UpdateItemRequest $request
     * @param string $id
     * @throws \App\Exceptions\ActionForbiddenException
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function update(UpdateItemRequest $request, string $id)
    {
        if (!$request->user()->tokenCan('update:item')) throw new ActionForbiddenException();

        $item = $this->itemService->updateItem(
            intval($id),
            UpdateItemDTO::fromRequest($request)
        );

        return response()->json([
            'data' => $item
        ], 200);
    }

    /**
     * @OA\Delete(
     *  path="/api/v1/items/{id}",
     *  summary="Removes an item from the inventory.",
     *  tags={"Items"},
     *  security={{"bearerAuth": {}}},
     * 
     *  @OA\Parameter(
     *   name="id",
     *   in="path",
     *   description="Item ID",
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
     * @param \Illuminate\Http\Request $request
     * @param string $id
     * @throws \App\Exceptions\ActionForbiddenException
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, string $id)
    {
        if (!$request->user()->tokenCan('delete:item')) throw new ActionForbiddenException();
    
        $result = $this->itemService->deleteItem(intval($id));

        if ($result) return response()->json(['message' => 'Item has been successfully deleted.'], 200);
        else return response()->json(['message' => 'Something wrong.'], 500);
    }
}
