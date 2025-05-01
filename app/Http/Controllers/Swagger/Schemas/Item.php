<?php

namespace App\Http\Controllers\Swagger\Schemas;

/**
 * @OA\Schema(
 *   schema="Item",
 *   type="object",
 *   title="Item",
 *   required={"id", "category_id", "name", "code", "stock"},
 * 
 *   @OA\Property(property="id", type="integer", example=1),
 *   @OA\Property(property="category_id", type="integer", example=1),
 *   @OA\Property(property="name", type="string", example="Laptop"),
 *   @OA\Property(property="code", type="string", example="#LPT20"),
 *   @OA\Property(property="stock", type="integer", example=20),
 *   @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T12:00:00Z"),
 *   @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-02T15:30:00Z")
 * )
 */
class Item {}