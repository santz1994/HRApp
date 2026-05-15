<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    protected $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    /**
     * Get user by ID.
     */
    public function findById(int $id): ?User
    {
        return $this->model->with('role')->find($id);
    }

    /**
     * Get user by email.
     */
    public function findByEmail(string $email): ?User
    {
        return $this->model->with('role')->where('email', $email)->first();
    }

    /**
     * Get user by NIK.
     */
    public function findByNIK(string $nik): ?User
    {
        return $this->model->with('role')->where('nik', $nik)->first();
    }

    /**
     * Get all users.
     */
    public function all()
    {
        return $this->model->with('role')->get();
    }

    /**
     * Create a new user.
     */
    public function create(array $data): User
    {
        return $this->model->create($data);
    }

    /**
     * Update a user.
     */
    public function update(int $id, array $data): bool
    {
        $user = $this->findById($id);
        if (!$user) {
            return false;
        }
        return $user->update($data);
    }

    /**
     * Delete a user.
     */
    public function delete(int $id): bool
    {
        $user = $this->findById($id);
        if (!$user) {
            return false;
        }
        return $user->delete();
    }

    /**
     * Get users by role.
     */
    public function getByRole(string $roleSlug)
    {
        return $this->model->whereHas('role', function ($query) use ($roleSlug) {
            $query->where('slug', $roleSlug);
        })->with('role')->get();
    }
}
