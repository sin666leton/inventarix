<?php

namespace App\Http\Controllers\Swagger;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @OA\Info(
 *     title="Inventarix API Documentation",
 *     version="1.0.0",
 *     description="Inventarix is an inventory management system designed to help organizations efficiently manage items, track transactions, and control user access. The API provides structured endpoints for authentication, category and item management, staff administration, transaction tracking, and personal user settings.",
 *     @OA\Contact(
 *         email="ahmadzidan1316@gmail.com"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="API Server"
 * )
 */
class SwaggerInfo
{
    //
}
