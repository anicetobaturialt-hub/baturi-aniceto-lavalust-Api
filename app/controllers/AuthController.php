<?php

defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

/**
 * AuthController
 *
 * Token based authentication using the LavaLust Api library
 * (JWT access token + rotating refresh token).
 */
class AuthController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->call->database();
        $this->call->library('api');
    }

    /**
     * POST /api/auth/register
     */
    public function register()
    {
        $this->api->require_method('POST');
        $this->api->rate_limit('register_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 10, 60);

        $input    = $this->api->body();
        $username = $input['username'] ?? '';
        $email    = $input['email'] ?? '';
        $password = $input['password'] ?? '';

        $errors = [];
        if ($username === '' || strlen($username) < 3 || strlen($username) > 50) {
            $errors['username'] = 'Username is required (3-50 characters).';
        }
        if (!filter_var(html_entity_decode($email), FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'A valid email is required.';
        }
        if (strlen($password) < 6) {
            $errors['password'] = 'Password must be at least 6 characters.';
        }
        if ($errors) {
            $this->api->respond(['error' => 'Validation failed', 'status' => 422, 'errors' => $errors], 422);
        }

        $exists = $this->db->raw(
            'SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1',
            [$username, $email]
        )->fetch(PDO::FETCH_ASSOC);

        if ($exists) {
            $this->api->respond_error('Username or email is already taken.', 409);
        }

        $this->db->raw(
            'INSERT INTO users (username, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())',
            [$username, $email, password_hash($password, PASSWORD_BCRYPT), 'user']
        );

        $this->api->respond(['message' => 'User registered successfully'], 201);
    }

    /**
     * POST /api/auth/login
     */
    public function login()
    {
        $this->api->require_method('POST');
        $this->api->rate_limit('login_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 10, 60);

        $input    = $this->api->body();
        $username = $input['username'] ?? '';
        $password = $input['password'] ?? '';

        if ($username === '' || $password === '') {
            $this->api->respond_error('Username and password are required.', 422);
        }

        // Allow login with username or email.
        $user = $this->db->raw(
            'SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = 1 LIMIT 1',
            [$username, $username]
        )->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password'])) {
            $this->api->respond_error('Invalid credentials', 401);
        }

        $tokens = $this->api->issue_tokens([
            'id'   => $user['id'],
            'role' => $user['role'],
        ]);

        $tokens['user'] = [
            'id'       => (int) $user['id'],
            'username' => $user['username'],
            'email'    => $user['email'],
            'role'     => $user['role'],
        ];

        $this->api->respond($tokens);
    }

    /**
     * POST /api/auth/refresh
     */
    public function refresh()
    {
        $this->api->require_method('POST');
        $input = $this->api->body();
        $this->api->refresh_access_token($input['refresh_token'] ?? '');
    }

    /**
     * POST /api/auth/logout
     */
    public function logout()
    {
        $this->api->require_method('POST');
        $input = $this->api->body();

        if (!empty($input['refresh_token'])) {
            $this->api->revoke_refresh_token($input['refresh_token']);
        }

        $this->api->respond(['message' => 'Logged out']);
    }

    /**
     * GET /api/auth/me
     */
    public function me()
    {
        $auth = $this->api->require_jwt();

        $user = $this->db->raw(
            'SELECT id, username, email, role, created_at FROM users WHERE id = ? LIMIT 1',
            [$auth['sub']]
        )->fetch(PDO::FETCH_ASSOC);

        $this->api->respond($user ?: ['message' => 'User not found']);
    }
}
