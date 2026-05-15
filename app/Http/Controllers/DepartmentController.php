<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class DepartmentController extends Controller
{
    /**
     * Get all departments.
     */
    public function index()
    {
        try {
            $departments = Department::withCount('employees')->orderBy('name')->get();

            return response()->json([
                'success' => true,
                'data' => $departments,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store department.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:departments,name',
            ]);

            $department = Department::create($request->only('name'));

            return response()->json([
                'success' => true,
                'message' => 'Department berhasil ditambahkan',
                'data' => $department,
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update department.
     */
    public function update(Request $request, $id)
    {
        try {
            $department = Department::findOrFail($id);

            $request->validate([
                'name' => 'required|string|max:255|unique:departments,name,' . $id,
            ]);

            $department->update($request->only('name'));

            return response()->json([
                'success' => true,
                'message' => 'Department berhasil diperbarui',
                'data' => $department,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Department tidak ditemukan'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete department.
     */
    public function destroy($id)
    {
        try {
            $department = Department::findOrFail($id);
            $department->delete();

            return response()->json(['success' => true, 'message' => 'Department berhasil dihapus']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Department tidak ditemukan'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}