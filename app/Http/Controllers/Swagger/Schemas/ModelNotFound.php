<?php

namespace App\Http\Controllers\Swagger\Schemas;

/**
 * @OA\Schema(
 *  schema="ModelNotFound",
 *  type="object",
 *  required={"message"},
 *  @OA\Property(
 *   property="message",
 *   type="string",
 *   example="... Not Found."
 *  )
 * )
 */
class ModelNotFound {}