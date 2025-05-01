<?php
namespace App\Http\Controllers\Swagger\Schemas;

/**
 * @OA\Schema(
 *  schema="FailedValidation",
 *  type="object",
 *  required={"message", "errors"},
 *  
 *  @OA\Property(property="message", type="string"),
 * 
 *  @OA\Property(
 *   property="errors",
 *   type="object",
 *   @OA\Property(
 *    property="fieldName",
 *    type="array",
 *    @OA\Items(type="string") 
 *   )
 *  )
 * )
 */
class FailedValidation {}