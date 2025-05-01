<?php

namespace App\Http\Controllers\Swagger\Schemas;

/**
 * @OA\Schema(
 *  schema="ActionForbidden",
 *  type="object",
 *  required={"message"},
 * 
 *  @OA\Property(property="message", type="string", example="You don't have permission for this action.")
 * )
 */
class ActionForbidden {}