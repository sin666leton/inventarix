<?php

namespace App\Http\Controllers\api\V1;

use App\DTOs\TransactionDTO;
use App\Exceptions\ActionForbiddenException;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateTransactionRequest;
use App\Http\Resources\PaginateCollection;
use App\Services\TransactionService;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *  name="Transactions",
 *  description="Endpoints for managing item lending, returning, and other inventory transactions."
 * )
 */
class TransactionController extends Controller
{
    public function __construct(
        protected TransactionService $transactionService
    ) {}

    /**
     * @OA\Get(
     *   path="/api/v1/transactions",
     *   tags={"Transactions"},
     *   summary="Retrieves a paginated list of item transactions.",
     *   @OA\Response(
     *     response=200,
     *     description="List of transactions",
     *     @OA\JsonContent(
     *       allOf={
     *         @OA\Schema(ref="#/components/schemas/Pagination"),
     *         @OA\Schema(
     *           @OA\Property(
     *             property="data",
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Transaction")
     *           )
     *         )
     *       }
     *     )
     *   ),
     *   security={{"bearerAuth":{}}}
     * )
     * 
     * @param \Illuminate\Http\Request $request
     * @throws \App\Exceptions\ActionForbiddenException
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        if (!$request->user()->tokenCan('viewAny:transaction')) throw new ActionForbiddenException();

        $result = $this->transactionService->paginateTransaction();

        return response()->json(new PaginateCollection($result), 200);
    }

    /**
     * @OA\Post(
     *  path="/api/v1/transactions",
     *  summary="Creates a new transaction (e.g., in or out items)",
     *  security={{"bearerAuth":{}}},
     *  tags={"Transactions"},
     *  
     *  @OA\RequestBody(
     *   required=true,
     * 
     *   @OA\JsonContent(
     *    type="object",
     *    required={"user_id", "item_id", "type", "quantity"},
     * 
     *    @OA\Property(property="user_id", type="integer", example=1),
     *    @OA\Property(property="item_id", type="integer", example=1),
     *    @OA\Property(property="type", type="string", enum={"in", "out"}, example="in"),
     *    @OA\Property(property="quantity", type="integer", example=5),
     *    @OA\Property(property="description", type="string", example="Optional description")
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
     *      ref="#/components/schemas/Transaction"
     *     )
     *    )
     *   )
     *  ),
     * )
     * @param \App\Http\Requests\CreateTransactionRequest $request
     * @throws \App\Exceptions\ActionForbiddenException
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function store(CreateTransactionRequest $request)
    {
        if (!$request->user()->tokenCan('create:transaction')) throw new ActionForbiddenException();

        $transaction = $this->transactionService->createTransaction(TransactionDTO::fromRequest($request));

        return response()->json([
            'data' => $transaction
        ], 200);
    }


    /**
     * @OA\Get(
     *  path="/api/v1/transactions/{id}",
     *  summary="Retrieves details of a specific transaction.",
     *  tags={"Transactions"},
     *  security={{"bearerAuth": {}}},
     * 
     *  @OA\Parameter(
     *   name="id",
     *   in="path",
     *   required=true,
     *   description="Transaction ID",
     *   @OA\Schema(type="integer")
     *  ),
     * 
     *  @OA\Response(
     *   response=200,
     *   description="Success",
     * 
     *   @OA\JsonContent(ref="#/components/schemas/Transaction")
     *  ),
     * 
     *  @OA\Response(
     *   response=401,
     *   description="Unauthenticated"
     *  ),
     * 
     *  @OA\Response(
     *   response=404,
     *   description="Transaction Not Found.",
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
        if (!$request->user()->tokenCan('view:transaction')) throw new ActionForbiddenException();

        $transaction = $this->transactionService->findTransaction(intval($id));

        return response()->json([
            'data' => $transaction
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * @OA\Delete(
     *  path="/api/v1/transactions/{id}",
     *  summary="Deletes a transaction if needed.",
     *  tags={"Transactions"},
     *  security={{"bearerAuth": {}}},
     * 
     *  @OA\Parameter(
     *   name="id",
     *   in="path",
     *   description="Transaction ID",
     *   required=true,
     * 
     *   @OA\Schema(type="integer")
     *  ),
     * 
     *  @OA\Response(
     *   response=404,
     *   description="Transaction Not Found",
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
        if (!$request->user()->tokenCan('delete:transaction')) throw new ActionForbiddenException();

        $deleted = $this->transactionService->deleteTransaction(intval($id));

        if ($deleted) return response()->json(['message' => 'Transaction has been successfully deleted.'], 200);
        else return response()->json(['message' => 'Something Wrong.'], 500);
    }
}
