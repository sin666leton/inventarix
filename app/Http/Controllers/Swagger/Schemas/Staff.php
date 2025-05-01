<?php
namespace App\Http\Controllers\Swagger\Schemas;

/**
 * @OA\Schema(
 *   schema="Staff",
 *   type="object",
 *   title="Transaction",
 *   required={"id", "role_id", "name", "email"},
 * 
 *   @OA\Property(property="id", type="integer", example=1),
 *   @OA\Property(property="role_id", type="integer", example=2),
 *   @OA\Property(property="name", type="string", example="Balmond"),
 *   @OA\Property(property="email", type="string", example="balmondantony@example.com"),
 *   @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T12:00:00Z"),
 *   @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-02T15:30:00Z")
 * )
 */
class Staff {}