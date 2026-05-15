<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Settings Controller
 * Mengelola pengaturan pengguna (Profile, Password, Notifikasi)
 * Sesuai Project.md Poin D: Akun Saya (Semua Role)
 */
class SettingsController extends Controller
{
    protected $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    /**
     * Get current user profile settings
     */
    public function getProfile(Request $request)
    {
        try {
            $user = $request->user()->load('role');

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'nik' => $user->nik,
                    'role' => $user->role->name,
                    'role_slug' => $user->role->slug,
                    'email_verified_at' => $user->email_verified_at,
                    'created_at' => $user->created_at,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal mengambil profile', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data profile',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update user profile (name only)
     */
    public function updateProfile(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:150',
            ]);

            $user = $request->user();
            $oldName = $user->name;

            $user->update([
                'name' => $request->input('name'),
            ]);

            $this->auditLogService->log(
                auth()->id(),
                'UPDATE_PROFILE',
                'users',
                ['name' => $oldName],
                ['name' => $user->name]
            );

            return response()->json([
                'success' => true,
                'message' => 'Profile berhasil diperbarui',
                'data' => $user->fresh(['role']),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui profile', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui profile',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Change password
     * Wajib untuk login pertama kali (default password123)
     */
    public function changePassword(Request $request)
    {
        try {
            $request->validate([
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:8|confirmed',
            ]);

            $user = $request->user();

            // Verify current password
            if (!Hash::check($request->input('current_password'), $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password saat ini tidak sesuai',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Check if new password is same as current
            if (Hash::check($request->input('new_password'), $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password baru harus berbeda dengan password saat ini',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $user->update([
                'password' => Hash::make($request->input('new_password')),
            ]);

            $this->auditLogService->log(
                auth()->id(),
                'CHANGE_PASSWORD',
                'users',
                [],
                ['password_changed' => true]
            );

            return response()->json([
                'success' => true,
                'message' => 'Password berhasil diubah',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            Log::error('Gagal mengubah password', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah password',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get active sessions (login history)
     * Dari Project.md Poin D: "peninjauan sesi login aktif"
     */
    public function getActiveSessions(Request $request)
    {
        try {
            $user = $request->user();

            // Get personal access tokens
            $tokens = $user->tokens()
                ->select('id', 'name', 'created_at', 'last_used_at', 'expires_at')
                ->get()
                ->map(function ($token) use ($request) {
                    return [
                        'id' => $token->id,
                        'name' => $token->name,
                        'created_at' => $token->created_at,
                        'last_used_at' => $token->last_used_at,
                        'expires_at' => $token->expires_at,
                        'is_current' => $token->token === $request->bearerToken(),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $tokens,
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal mengambil session aktif', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data session',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Logout from specific session (revoke token)
     */
    public function logoutSession(Request $request, $tokenId)
    {
        try {
            $user = $request->user();
            $token = $user->tokens()->find($tokenId);

            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token tidak ditemukan',
                ], Response::HTTP_NOT_FOUND);
            }

            $token->delete();

            $this->auditLogService->log(
                auth()->id(),
                'LOGOUT_SESSION',
                'personal_access_tokens',
                ['token_id' => $tokenId],
                ['status' => 'revoked']
            );

            return response()->json([
                'success' => true,
                'message' => 'Session berhasil ditutup',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal logout session', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menutup session',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Logout from all sessions (revoke all tokens)
     */
    public function logoutAllSessions(Request $request)
    {
        try {
            $user = $request->user();
            $tokenCount = $user->tokens()->count();

            $user->tokens()->delete();

            $this->auditLogService->log(
                auth()->id(),
                'LOGOUT_ALL_SESSIONS',
                'personal_access_tokens',
                ['token_count' => $tokenCount],
                ['status' => 'all_revoked']
            );

            return response()->json([
                'success' => true,
                'message' => 'Semua session berhasil ditutup',
                'revoked_count' => $tokenCount,
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal logout semua session', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menutup semua session',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get notification preferences
     */
    public function getNotificationPreferences(Request $request)
    {
        try {
            // Placeholder untuk notification preferences
            // Dapat dikembangkan lebih lanjut dengan settings table
            $preferences = [
                'email_on_import_complete' => true,
                'email_on_employee_added' => false,
                'email_on_employee_deleted' => true,
                'email_audit_log_digest' => false,
            ];

            return response()->json([
                'success' => true,
                'data' => $preferences,
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal mengambil preference notifikasi', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil preferensi notifikasi',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update notification preferences
     */
    public function updateNotificationPreferences(Request $request)
    {
        try {
            $request->validate([
                'email_on_import_complete' => 'boolean',
                'email_on_employee_added' => 'boolean',
                'email_on_employee_deleted' => 'boolean',
                'email_audit_log_digest' => 'boolean',
            ]);

            $this->auditLogService->log(
                auth()->id(),
                'UPDATE_NOTIFICATION_PREFERENCES',
                'users',
                [],
                $request->all()
            );

            return response()->json([
                'success' => true,
                'message' => 'Preferensi notifikasi berhasil diperbarui',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui preference notifikasi', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui preferensi notifikasi',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
