<?php

namespace App\Http\Controllers\Swagger\Schemas;

/**
 * @OA\Schema(
 *   schema="Transaction",
 *   type="object",
 *   title="Transaction",
 *   required={"user_id", "item_id", "type", "quantity"},
 * 
 *   @OA\Property(property="id", type="integer", example=1),
 *   @OA\Property(property="user_id", type="integer", example=1),
 *   @OA\Property(property="item_id", type="integer", example=1),
 *   @OA\Property(property="type", type="string", enum={"in", "out"}, example="in"),
 *   @OA\Property(property="quantity", type="integer", example=10),
 *   @OA\Property(property="description", type="string", example="Description about this transaction"),
 *   @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T12:00:00Z"),
 *   @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-02T15:30:00Z")
 * )
 */
class Transaction {}