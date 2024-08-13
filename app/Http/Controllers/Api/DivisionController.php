<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\Division;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DivisionController extends Controller
{
    /**
     * @OA\Get(
     *     path="/divisions",
     *     summary="Get Divisions",
     *     description="Retrieves a paginated list of divisions with optional filtering by name. Requires authentication.",
     *     tags={"Divisions"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Filter divisions by name",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Divisions retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Divisions retrieved successfully"),
     *             @OA\Property(
     *                 property="data", type="object",
     *                 @OA\Property(
     *                     property="divisions", type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="string", example="uuid-of-division"),
     *                         @OA\Property(property="name", type="string", example="Division Name")
     *                     )
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="pagination", type="object",
     *                 @OA\Property(property="total", type="integer", example=100),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=10),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="to", type="integer", example=10),
     *                 @OA\Property(property="next_page_url", type="string", example="http://example.com/api/divisions?page=2"),
     *                 @OA\Property(property="prev_page_url", type="string", example=null)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Unexpected error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred while retrieving division data")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            $query = Division::query();

            if ($request->has('name')) {
                $query->where('name', 'like', '%' . $request->name . '%');
            }

            $divisions = $query->paginate(10);

            return response()->json([
                'status' => 'success',
                'message' => 'Divisions retrieved successfully',
                'data' => [
                    'divisions' => $divisions->items(),
                ],
                'pagination' => [
                    'total' => $divisions->total(),
                    'per_page' => $divisions->perPage(),
                    'current_page' => $divisions->currentPage(),
                    'last_page' => $divisions->lastPage(),
                    'from' => $divisions->firstItem(),
                    'to' => $divisions->lastItem(),
                    'next_page_url' => $divisions->nextPageUrl(),
                    'prev_page_url' => $divisions->previousPageUrl(),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred while retrieving division data',
            ], 500);
        }
    }
}
