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
        helper('utility_helper');
        helper('jwt_helper');
        // $this->UserModel = model('UserModel');
    }

    public function index()
    {
        // Get a specific header
        $authToken = $this->request->getHeaderLine('Authorization');

        // $acceptVersion = $this->request->getHeader('Accept-Version');
        // $acceptVersion = $acceptVersion->getValue();
        $version = $this->request->getHeaderLine('Accept-Version');


        $all_params_req_data = $this->request->getGetPost();
        if (is_array($all_params_req_data)) {
            $all_params_req_data = array_map('trim', $all_params_req_data);
        } 
        
        $data['status'] = true;
        $data['metadata'] = array('timestamp' => date('c'),'version' => $version);
        $data['all_params'] = $all_params_req_data;

        return $this->respond($data,200);
    }

    public function login()
    {
        $version = $this->request->getHeaderLine('Accept-Version');
        $userAgent = $this->request->getUserAgent()->getAgentString();
        $ip = $this->request->getIPAddress();

        if ($version === 'v1') {
            $credentials = $this->request->getJSON();
            $user = $this->getUserByCredentials($credentials->username, $credentials->password);

            if (!$user) {
                $data['status'] = false;
                $data['metadata'] = array('timestamp' => date('c'),'version' => $version);
                $data['msg'] = 'Invalid credentials';

                return $this->respond($data, 200);
                // return $this->failUnauthorized($data, 401);
            }

            // Create JWT payload
            $payload = [
                'uid' => $user['id'],
                'username' => $user['username'],
                'iat' => time(),
                'exp' => time() + config(JWTConfig::class)->accessTTL,
            ];

            // Generate access and refresh tokens
            $accessToken = create_jwt($payload, 'access');

            $payload = [
                'uid' => $user['id'],
                'username' => $user['username'],
                'iat' => time(),
                'exp' => time() + config(JWTConfig::class)->refreshTTL,
            ];
            $refreshToken = create_jwt($payload, 'refresh');

            // Set refresh token in secure HTTP-only cookie
            // setcookie('refresh_token', $refreshToken, [
            //     'expires' => time() + 604800,
            //     'httponly' => true,
            //     'secure' => true,
            //     'samesite' => 'Strict'
            // ]);
            // Store refresh token in database
            // $this->authTokenModel->insert([
            //     'user_id' => $user['id'],
            //     'token_hash' => hash('sha256', $refreshToken),
            //     'expires_at' => date('Y-m-d H:i:s', time() + env('JWT_REFRESH_TOKEN_EXPIRY'))
            // ]);

            //date('c')  ISO 8601 standard date format YYYY-MM-DDTHH:MM:SS+00:00
            $data = [
                'status' => true,
                'message' => 'Login successful',
                'data' => [
                    'access_token' => $accessToken,
                    'refresh_token' => $refreshToken,
                    'user' => [
                        'id' => $user['id'],
                        'username' => $user['username'],
                    ]
                ],
                'device_info' => $userAgent ?? 'N/A',
                'ip_address' => $ip ?? 'N/A',
                'metadata' => array('timestamp' => date('c'),'version' => $version)
            ];

            return $this->respond($data, 200); // 200 OK
        } else {
            $data['status'] = false;
            $data['metadata'] = array('timestamp' => date('c'),'version' => $version);
            $data['msg'] = 'Version not supported';
            return $this->respond($data, 501);
            // return $this->failNotImplemented('Version not supported' , 501);
        }
    }

    public function refresh()
    {
        $version = $this->request->getHeaderLine('Accept-Version');
        $userAgent = $this->request->getUserAgent()->getAgentString();
        $ip = $this->request->getIPAddress();

        if ($version === 'v1') {
            $refreshToken = $this->request->getHeader('Authorization');
            if (!$refreshToken) {
                $data['status'] = false;
                $data['metadata'] = array('timestamp' => date('c'),'version' => $version);
                $data['msg'] = 'Refresh token is required';
                return $this->respond($data, 200);
                // return $this->failUnauthorized('Refresh token is required', 401);
            }

            $token = str_replace('Bearer ', '', $refreshToken->getValue());
            $decoded = verify_jwt($token);

            //db check
            // $hashedToken = hash('sha256', $refreshToken);
            // $tokenRecord = $this->authTokenModel->where('token_hash', $hashedToken)->first();

            // if (!$tokenRecord || strtotime($tokenRecord['expires_at']) < time()) {
            //     return $this->failUnauthorized('Invalid or expired refresh token');
            // }


            if (!$decoded) {
                $data['status'] = false;
                $data['metadata'] = array('timestamp' => date('c'),'version' => $version);
                $data['msg'] = 'Invalid or expired refresh token';
                return $this->respond($data, 200);
                // return $this->failUnauthorized('Invalid or expired refresh token', 401);
            }

            // Create new refresh token
             // Optionally rotate refresh token (security best practice)
            $payload = [
                'uid' => $decoded->uid,
                'username' => $decoded->username,
                'iat' => time(),
                'exp' => time() + config(JWTConfig::class)->refreshTTL,
            ];

            $newRefreshToken = create_jwt($payload, 'refresh');

            $payload = [
                'uid' => $decoded->uid,
                'username' => $decoded->username,
                'iat' => time(),
                'exp' => time() + config(JWTConfig::class)->accessTTL,
            ];
            $newAccessToken = create_jwt($payload, 'access');
            
            // // Update refresh token in database
            // $this->authTokenModel->update($tokenRecord['id'], [
            //     'token_hash' => hash('sha256', $newRefreshToken),
            //     'expires_at' => date('Y-m-d H:i:s', time() + env('JWT_REFRESH_TOKEN_EXPIRY'))
            // ]);

            //date('c')  ISO 8601 standard date format YYYY-MM-DDTHH:MM:SS+00:00
            $data = [
                'status' => true,
                'message' => 'Token refreshed successfully',
                'data' => [
                    'access_token' => $newAccessToken,
                    'refresh_token' => $newRefreshToken,
                ],
                'device_info' => $userAgent ?? 'N/A',
                'ip_address' => $ip ?? 'N/A',
                'metadata' => array('timestamp' => date('c'),'version' => $version)
            ];
            
            return $this->respond($data, 200); // 200 OK
        } else {
            $data['status'] = false;
            $data['metadata'] = array('timestamp' => date('c'),'version' => $version);
            $data['msg'] = 'Version not supported';
            return $this->respond($data, 501);
            // return $this->failNotImplemented('Version not supported', 501);
        }
    }


    // Access Protected Route: GET /api/profile

    // Header: Authorization: Bearer <access_token>
    public function profile()
    {
        $userId = $this->request->user_id;
        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if (!$user) {
            return $this->failNotFound('User not found');
        }

        unset($user['password']);

        return $this->respond([
            'status' => 'success',
            'data' => $user
        ], 200);
    }
    private function getUserByCredentials($username, $password)
    {
        // For demo, hard-coded users
        foreach ($this->users as $user) {
            $user['password'] = password_hash($user['password'], PASSWORD_DEFAULT);
            if ($user['username'] === $username && password_verify($password, $user['password'])) {
                return $user;
            }
            
        }
        return null;
    }

    private function failNotImplemented($mesg, $status_code)
    {
        return $this->fail($mesg, $status_code);
    }
    // method signature to match the parent class
    protected function failUnauthorized(string $description = 'Unauthorized', ?string $code = null, string $message = '')
    {
        // Pass the parameters to the parent method
        return $this->fail($description, $code, $message);
    }
    public function logout()
    {
        $refreshToken = $_COOKIE['refresh_token'] ?? null;

        if ($refreshToken) {
            $this->db->table('access_token')
                ->where('token', hash('sha256', $refreshToken))
                ->delete();

            setcookie('refresh_token', '', time() - 3600, '/');
        }

        return $this->respond(['message' => 'Logged out successfully']);
    }

}
