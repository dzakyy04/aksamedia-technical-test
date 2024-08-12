<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\Division;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DivisionController extends Controller
{
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
