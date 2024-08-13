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
                    'image' => url($employee->image),
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

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|max:10240',
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

    public function update(Request $request, $id)
    {
        if (empty($request->all())) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Request',
                'error' => 'Please use method spoofing for PUT requests by setting the request method to POST and including _method=PUT in the request body.',
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'image' => 'sometimes|required|image|mimes:jpeg,png,jpg,gif,svg|max:10240',
            'name' => 'sometimes|required',
            'phone' => 'sometimes|required',
            'division' => 'sometimes|required|exists:divisions,id',
            'position' => 'sometimes|required',
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
                $fileName = strtolower(str_replace(' ', '-', $request->name ?? $employee->name)) . '-' . time() . '.' . $extension;
                $imagePath = $image->storeAs('employees/images', $fileName, 'public');
                $employee->image = $imagePath;
            }

            $employee->name = $request->input('name', $employee->name);
            $employee->phone = $request->input('phone', $employee->phone);
            $employee->division_id = $request->input('division', $employee->division_id);
            $employee->position = $request->input('position', $employee->position);

            $employee->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Employee updated successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred while updating employee data',
            ], 500);
        }
    }



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
