<?php

namespace App\Http\Controllers\api\V2;

use App\Exceptions\ActionForbiddenException;
use App\Http\Controllers\Controller;
use App\Services\TransactionService;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function __construct(
        protected TransactionService $transactionService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * @OA\Get(
     *  path="/api/v2/transactions/{id}",
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
     *   @OA\MediaType(
     *    mediaType="application/json",
     *    @OA\Examples(
     *     example="Admin",
     *     summary="Admin response",
     *     value={
     *      "data": {
     *       "id": 1,
     *       "user_id": 1,
     *       "item_id": 1,
     *       "type": "in",
     *       "quantity": 1,
     *       "description": "Optional description",
     *       "created_at": "2024-01-01T12:00:00Z",
     *       "updated_at": "2024-01-02T15:30:00Z",
     *       "user": {
     *        "id": 1,
     *        "name": "someusername"
     *       },
     *       "item": {
     *        "id": 1,
     *        "name": "Laptop"
     *       }
     *      }
     *     }
     *    ),
     *    @OA\Examples(
     *     example="Staff",
     *     summary="Staff response",
     *     value={
     *      "data": {
     *       "id": 1,
     *       "user_id": 1,
     *       "item_id": 1,
     *       "type": "in",
     *       "quantity": 1,
     *       "description": "Optional description",
     *       "created_at": "2024-01-01T12:00:00Z",
     *       "updated_at": "2024-01-02T15:30:00Z"
     *      }
     *     }
     *    )
     *   )
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

        $transaction = $this->transactionService->findTransactionWithUserAndItem($request->user(), intval($id));

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
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
