<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use Firebase\JWT\JWT;
use Config\JWT as JWTConfig;

class Api extends ResourceController
{
    // Sample user data for demo purposes
    private $users = [
        ['username' => 'user1', 'password' => 'password1', 'id' => 1],
        ['username' => 'user2', 'password' => 'password2', 'id' => 2],
    ];

    public function __construct()
    {
        // Determine version from the Accept-Version header
        $version = $this->request->getHeaderLine('Accept-Version');
        // Default to 'v1' if no version header is provided
        $this->version = $version ?: 'v1';
    }

    /**
     * Login endpoint to authenticate user and provide JWT access and refresh tokens
     */
    public function login()
    {
        // Handle versioning logic
        $version = $this->version;

        if ($version === 'v1') {
            // Get JSON body from request
            $credentials = $this->request->getJSON();

            // Check user credentials
            $user = $this->getUserByCredentials($credentials->username, $credentials->password);

            if (!$user) {
                // Invalid credentials, return 401 Unauthorized
                return $this->failUnauthorized('Invalid credentials', 401);
            }

            // Create JWT payload for access and refresh tokens
            $payload = [
                'uid' => $user['id'],
                'username' => $user['username'],
                'iat' => time(),
                'exp' => time() + config(JWTConfig::class)->accessTTL,
            ];

            // Generate access and refresh tokens
            $accessToken = $this->createJwt($payload, 'access');
            $refreshToken = $this->createJwt($payload, 'refresh');

            // Return tokens in response
            return $this->respond([
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
            ], 200); // 200 OK
        } else {
            // Unsupported version, return 501 Not Implemented
            return $this->failNotImplemented('Version not supported', 501);
        }
    }

    /**
     * Refresh token endpoint to issue a new access token using the refresh token
     */
    public function refresh()
    {
        // Handle versioning logic
        $version = $this->version;

        if ($version === 'v1') {
            // Get refresh token from Authorization header
            $refreshToken = $this->request->getHeader('Authorization');
            if (!$refreshToken) {
                // Missing refresh token, return 401 Unauthorized
                return $this->failUnauthorized('Refresh token is required', 401);
            }

            // Extract token value from Bearer prefix
            $token = str_replace('Bearer ', '', $refreshToken->getValue());

            // Verify refresh token
            $decoded = $this->verifyJwt($token);

            if (!$decoded) {
                // Invalid or expired refresh token, return 401 Unauthorized
                return $this->failUnauthorized('Invalid or expired refresh token', 401);
            }

            // Create new access token using the decoded refresh token payload
            $payload = [
                'uid' => $decoded->uid,
                'username' => $decoded->username,
                'iat' => time(),
                'exp' => time() + config(JWTConfig::class)->accessTTL,
            ];

            // Generate new access token
            $accessToken = $this->createJwt($payload, 'access');

            // Return new access token in response
            return $this->respond([
                'access_token' => $accessToken,
            ], 200); // 200 OK
        } else {
            // Unsupported version, return 501 Not Implemented
            return $this->failNotImplemented('Version not supported', 501);
        }
    }

    /**
     * Sample method to check user credentials (username and password)
     */
    private function getUserByCredentials($username, $password)
    {
        // For demo purposes, check against hard-coded users
        foreach ($this->users as $user) {
            if ($user['username'] === $username && $user['password'] === $password) {
                return $user;
            }
        }
        return null;
    }

    /**
     * Create JWT token (either access or refresh token)
     */
    private function createJwt($payload, $type)
    {
        $secretKey = config(JWTConfig::class)->secretKey;

        // Set JWT expiration based on token type
        if ($type === 'access') {
            $payload['exp'] = time() + config(JWTConfig::class)->accessTTL;
        } elseif ($type === 'refresh') {
            $payload['exp'] = time() + config(JWTConfig::class)->refreshTTL;
        }

        return JWT::encode($payload, $secretKey);
    }

    /**
     * Verify JWT token (used for both access and refresh tokens)
     */
    private function verifyJwt($token)
    {
        $secretKey = config(JWTConfig::class)->secretKey;

        try {
            return JWT::decode($token, $secretKey, ['HS256']);
        } catch (\Exception $e) {
            // Return false if token is invalid or expired
            return false;
        }
    }
}
