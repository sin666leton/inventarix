<?php

namespace App\Http\Controllers\Swagger\Schemas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @OA\Schema(
 *   schema="Category",
 *   type="object",
 *   title="Category",
 *   required={"id", "name", "description"},
 *   @OA\Property(property="id", type="integer", example=1),
 *   @OA\Property(property="name", type="string", example="Electronics"),
 *   @OA\Property(property="description", type="string", example="Devices and gadgets")
 * )
 */
class Category
{
    //
}
