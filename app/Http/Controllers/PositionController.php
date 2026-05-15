<?php

namespace App\Http\Controllers;

use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PositionController extends Controller
{
    /**
     * Get all positions.
     */
    public function index()
    {
        try {
            $positions = Position::withCount('employees')->orderBy('name')->get();

            return response()->json([
                'success' => true,
                'data' => $positions,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store position.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:positions,name',
            ]);

            $position = Position::create($request->only('name'));

            return response()->json([
                'success' => true,
                'message' => 'Jabatan berhasil ditambahkan',
                'data' => $position,
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update position.
     */
    public function update(Request $request, $id)
    {
        try {
            $position = Position::findOrFail($id);

            $request->validate([
                'name' => 'required|string|max:255|unique:positions,name,' . $id,
            ]);

            $position->update($request->only('name'));

            return response()->json([
                'success' => true,
                'message' => 'Jabatan berhasil diperbarui',
                'data' => $position,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Jabatan tidak ditemukan'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete position.
     */
    public function destroy($id)
    {
        try {
            $position = Position::findOrFail($id);
            $position->delete();

            return response()->json(['success' => true, 'message' => 'Jabatan berhasil dihapus']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Jabatan tidak ditemukan'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}