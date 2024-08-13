<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\Employee;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EmployeeController extends Controller
{
    /**
     * @OA\Get(
     *     path="/employees",
     *     summary="Get Employees",
     *     description="Retrieve a paginated list of employees with optional filters by name and division ID. Requires authentication.",
     *     tags={"Employees"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Filter employees by name",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="division_id",
     *         in="query",
     *         description="Filter employees by division ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Employees retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Employees retrieved successfully"),
     *             @OA\Property(
     *                 property="data", type="object",
     *                 @OA\Property(
     *                     property="employees", type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="string", example="uuid-of-employee"),
     *                         @OA\Property(property="image", type="string", example="url-to-employee-image"),
     *                         @OA\Property(property="name", type="string", example="Employee Name"),
     *                         @OA\Property(property="phone", type="string", example="phone-number-of-employee"),
     *                         @OA\Property(
     *                             property="division", type="object",
     *                             @OA\Property(property="id", type="string", example="uuid-of-division"),
     *                             @OA\Property(property="name", type="string", example="Division Name")
     *                         ),
     *                         @OA\Property(property="position", type="string", example="Employee Position")
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
     *                 @OA\Property(property="next_page_url", type="string", example="http://example.com/api/employees?page=2"),
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
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred while retrieving employee data")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            $query = Employee::with('division');

            if ($request->has('name')) {
                $query->where('name', 'like', '%' . $request->input('name') . '%');
            }

            if ($request->has('division_id')) {
                $query->where('division_id', $request->input('division_id'));
            }

            $employees = $query->paginate(10);

            $formattedEmployees = collect($employees->items())->map(function ($employee) {
                return [
                    'id' => $employee->id,
                    'image' => url('storage/' . $employee->image),
                    'name' => $employee->name,
                    'phone' => $employee->phone,
                    'division' => [
                        'id' => $employee->division->id,
                        'name' => $employee->division->name,
                    ],
                    'position' => $employee->position,
                ];
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Employees retrieved successfully',
                'data' => [
                    'employees' => $formattedEmployees,
                ],
                'pagination' => [
                    'total' => $employees->total(),
                    'per_page' => $employees->perPage(),
                    'current_page' => $employees->currentPage(),
                    'last_page' => $employees->lastPage(),
                    'from' => $employees->firstItem(),
                    'to' => $employees->lastItem(),
                    'next_page_url' => $employees->nextPageUrl(),
                    'prev_page_url' => $employees->previousPageUrl(),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred while retrieving employee data',
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/employees",
     *     summary="Create Employee",
     *     description="Create a new employee with the provided details. Requires authentication.",
     *     tags={"Employees"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="image", type="string", format="binary", example="image-of-employee.png"),
     *                 @OA\Property(property="name", type="string", example="Employee Name"),
     *                 @OA\Property(property="phone", type="string", example="phone-number-of-employee"),
     *                 @OA\Property(property="division", type="string", example="uuid-of-division"),
     *                 @OA\Property(property="position", type="string", example="Employee Position"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Employee created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Employee created successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="image", type="array", @OA\Items(type="string", example="The image field is required.")),
     *                 @OA\Property(property="name", type="array", @OA\Items(type="string", example="The name field is required.")),
     *                 @OA\Property(property="phone", type="array", @OA\Items(type="string", example="The phone field is required.")),
     *                 @OA\Property(property="division", type="array", @OA\Items(type="string", example="The selected division is invalid.")),
     *                 @OA\Property(property="position", type="array", @OA\Items(type="string", example="The position field is required."))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Unexpected error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred while creating employee data")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image',
            'name' => 'required',
            'phone' => 'required',
            'division' => 'required|exists:divisions,id',
            'position' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $image = $request->file('image');
            $extension = $image->getClientOriginalExtension();
            $fileName = strtolower(str_replace(' ', '-', $request->name)) . '-' . time() . '.' . $extension;

            $imagePath = $image->storeAs('employees/images', $fileName, 'public');

            Employee::create([
                'image' => $imagePath,
                'name' => $request->name,
                'phone' => $request->phone,
                'division_id' => $request->division,
                'position' => $request->position,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Employee created successfully',
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred while creating employee data',
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/employees/{id}",
     *     summary="Update Employee",
     *     description="Update the details of an existing employee using method spoofing with form-data. Requires authentication.",
     *     tags={"Employees"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the employee to update",
     *         required=true,
     *         @OA\Schema(type="string", example="uuid-of-employee")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="_method", type="string", example="PUT"),
     *                 @OA\Property(property="image", type="string", format="binary", example="updated-employee-image.jpg"),
     *                 @OA\Property(property="name", type="string", example="Employee Name"),
     *                 @OA\Property(property="phone", type="string", example="phone-number-of-employee"),
     *                 @OA\Property(property="division", type="string", example="uuid-of-division"),
     *                 @OA\Property(property="position", type="string", example="Employee Position")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Employee updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Employee updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Invalid Request"),
     *             @OA\Property(property="error", type="string", example="Please use method spoofing for PUT requests by setting the request method to POST and including _method=PUT in the request body.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Employee not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Employee not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Unexpected error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred while updating employee data")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'nullable|image',
            'name' => 'nullable|string',
            'phone' => 'nullable|string',
            'division' => 'nullable|exists:divisions,id',
            'position' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $employee = Employee::findOrFail($id);

            if ($request->hasFile('image')) {
                if ($employee->image && Storage::disk('public')->exists($employee->image)) {
                    Storage::disk('public')->delete($employee->image);
                }

                $image = $request->file('image');
                $extension = $image->getClientOriginalExtension();
                $fileName = strtolower(str_replace(' ', '-', $request->input('name', $employee->name))) . '-' . time() . '.' . $extension;
                $imagePath = $image->storeAs('employees/images', $fileName, 'public');
                $employee->image = $imagePath;
            }

            if ($request->filled('name')) {
                $employee->name = $request->input('name');
            }

            if ($request->filled('phone')) {
                $employee->phone = $request->input('phone');
            }

            if ($request->filled('division')) {
                $employee->division_id = $request->input('division');
            }

            if ($request->filled('position')) {
                $employee->position = $request->input('position');
            }

            $employee->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Employee updated successfully',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Employee not found',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred while updating employee data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/employees/{id}",
     *     summary="Delete Employee",
     *     description="Delete an existing employee by ID. Requires authentication.",
     *     tags={"Employees"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the employee to delete",
     *         required=true,
     *         @OA\Schema(type="string", example="e9d5b8a9-2a2b-4c7d-baf7-9152b5f517b4")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Employee deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Employee deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Employee not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Employee not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Unexpected error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred while deleting employee data")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
            $employee = Employee::findOrFail($id);

            if ($employee->image) {
                Storage::disk('public')->delete($employee->image);
            }

            $employee->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Employee deleted successfully',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Employee not found',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred while deleting employee data',
            ], 500);
        }
    }
}
