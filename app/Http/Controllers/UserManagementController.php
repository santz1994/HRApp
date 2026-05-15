<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * User Management Controller
 * Untuk HR mengelola akun pengguna (CRUD, Reset Password, Mapping NIK)
 * Sesuai Project.md Poin D: "Manajemen Pengguna (Khusus HR & IT)"
 */
class UserManagementController extends Controller
{
    protected $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    /**
     * Get list of users with pagination
     * Access: HR & IT
     */
    public function index(Request $request)
    {
        try {
            $perPage = min((int) $request->input('per_page', 20), 100);
            $search = $request->input('search', '');
            $roleFilter = $request->input('role', '');

            $query = User::with('role');

            // Search by name, email, or NIK
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('nik', 'like', "%{$search}%");
                });
            }

            // Filter by role
            if ($roleFilter) {
                $query->whereHas('role', function ($q) use ($roleFilter) {
                    $q->where('slug', $roleFilter);
                });
            }

            $users = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $users->items(),
                'total' => $users->total(),
                'per_page' => $users->perPage(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal mengambil daftar user', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil daftar user',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get single user details
     */
    public function show($id)
    {
        try {
            $user = User::with('role')->find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan',
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'success' => true,
                'data' => $user,
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal mengambil detail user', [
                'user_id' => auth()->id(),
                'target_user_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail user',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Create new user account
     * Input: name, email, nik, role_id, password
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:150',
                'email' => 'required|email|unique:users,email',
                'nik' => 'required|string|unique:users,nik',
                'role_id' => 'required|exists:roles,id',
                'password' => 'required|string|min:8',
            ]);

            $user = User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'nik' => $request->input('nik'),
                'role_id' => $request->input('role_id'),
                'password' => Hash::make($request->input('password')),
            ]);

            $this->auditLogService->log(
                auth()->id(),
                'CREATE_USER',
                'users',
                [],
                $user->only(['id', 'name', 'email', 'nik', 'role_id'])
            );

            return response()->json([
                'success' => true,
                'message' => 'User berhasil dibuat',
                'data' => $user->load('role'),
            ], Response::HTTP_CREATED);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            Log::error('Gagal membuat user', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat user',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update user (name, email, role, NIK)
     */
    public function update(Request $request, $id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan',
                ], Response::HTTP_NOT_FOUND);
            }

            $request->validate([
                'name' => 'sometimes|required|string|max:150',
                'email' => [
                    'sometimes',
                    'required',
                    'email',
                    Rule::unique('users', 'email')->ignore($id),
                ],
                'nik' => [
                    'sometimes',
                    'required',
                    'string',
                    Rule::unique('users', 'nik')->ignore($id),
                ],
                'role_id' => 'sometimes|required|exists:roles,id',
            ]);

            $oldData = $user->only(['name', 'email', 'nik', 'role_id']);

            $user->update($request->only(['name', 'email', 'nik', 'role_id']));

            $this->auditLogService->log(
                auth()->id(),
                'UPDATE_USER',
                'users',
                $oldData,
                $user->only(['name', 'email', 'nik', 'role_id'])
            );

            return response()->json([
                'success' => true,
                'message' => 'User berhasil diperbarui',
                'data' => $user->fresh('role'),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui user', [
                'user_id' => auth()->id(),
                'target_user_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui user',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete user account
     */
    public function destroy($id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan',
                ], Response::HTTP_NOT_FOUND);
            }

            // Prevent deleting self
            if ($user->id === auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menghapus akun sendiri',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $userData = $user->only(['id', 'name', 'email', 'nik', 'role_id']);
            $user->delete();

            $this->auditLogService->log(
                auth()->id(),
                'DELETE_USER',
                'users',
                $userData,
                []
            );

            return response()->json([
                'success' => true,
                'message' => 'User berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal menghapus user', [
                'user_id' => auth()->id(),
                'target_user_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus user',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Reset password for specific user
     * HR/IT dapat reset password user lain
     * Input: new_password
     */
    public function resetPassword(Request $request, $id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan',
                ], Response::HTTP_NOT_FOUND);
            }

            $request->validate([
                'new_password' => 'required|string|min:8',
            ]);

            $user->update([
                'password' => Hash::make($request->input('new_password')),
            ]);

            $this->auditLogService->log(
                auth()->id(),
                'RESET_PASSWORD_USER',
                'users',
                ['user_id' => $id],
                ['password_reset' => true]
            );

            return response()->json([
                'success' => true,
                'message' => 'Password user berhasil di-reset',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            Log::error('Gagal reset password user', [
                'user_id' => auth()->id(),
                'target_user_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal reset password user',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get all available roles
     */
    public function getRoles()
    {
        try {
            $roles = Role::select('id', 'name', 'slug', 'description')->get();

            return response()->json([
                'success' => true,
                'data' => $roles,
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal mengambil daftar role', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil daftar role',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Bulk import users from template
     * Format: Excel dengan columns: name, email, nik, role_slug
     */
    public function bulkImportUsers(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls,csv',
            ]);

            // TODO: Implement Excel import logic
            // Menggunakan library Maatwebsite/Laravel-Excel

            return response()->json([
                'success' => false,
                'message' => 'Fitur bulk import users sedang dalam pengembangan',
            ], Response::HTTP_NOT_IMPLEMENTED);
        } catch (\Exception $e) {
            Log::error('Gagal import users', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal import users',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
