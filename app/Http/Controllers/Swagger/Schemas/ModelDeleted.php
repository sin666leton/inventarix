<?php

namespace App\Http\Controllers\Swagger\Schemas;

/**
 * @OA\Schema(
 *  schema="ModelDeleted",
 *  type="object",
 *  required={"message"},
 *  
 *  @OA\Property(
 *   property="message",
 *   type="string",
 *   example="... has been deleted successfully."
 *  )
 * )
 */
class ModelDeleted {}