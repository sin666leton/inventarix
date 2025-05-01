<?php
namespace App\Http\Controllers\Swagger\Schemas;

/**
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     description="Bearer Authentication using Sanctum token"
 * )
 */
class Security {}