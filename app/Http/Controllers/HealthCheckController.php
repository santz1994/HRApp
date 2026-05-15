<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;

class HealthCheckController extends Controller
{
    /**
     * Check application health
     */
    public function check()
    {
        try {
            // Test database connection
            $users = DB::table('users')->select('id', 'email', 'nik')->get();
            
            return response()->json([
                'status' => 'ok',
                'app' => [
                    'name' => config('app.name'),
                    'env' => config('app.env'),
                    'debug' => config('app.debug'),
                    'url' => config('app.url'),
                ],
                'database' => [
                    'connection' => config('database.default'),
                    'status' => 'connected',
                ],
                'users' => [
                    'total' => $users->count(),
                    'list' => $users->map(function ($user) {
                        return [
                            'id' => $user->id,
                            'email' => $user->email,
                            'nik' => $user->nik,
                        ];
                    }),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
