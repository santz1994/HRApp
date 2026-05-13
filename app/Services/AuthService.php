<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;

class AuthService
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Register a new user.
     */
    public function register(array $data)
    {
        // Check if user already exists
        if ($this->userRepository->findByEmail($data['email'])) {
            throw new \Exception('Email already registered.');
        }

        return $this->userRepository->create($data);
    }

    /**
     * Authenticate user credentials.
     */
    public function authenticate(string $email, string $password): ?User
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user || !\Hash::check($password, $user->password)) {
            throw new \Exception('Invalid credentials.');
        }

        return $user;
    }

    /**
     * Create API token for user.
     */
    public function createToken($user, string $name = 'auth_token')
    {
        return $user->createToken($name);
    }
}
