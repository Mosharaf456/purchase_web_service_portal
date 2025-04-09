<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use Firebase\JWT\JWT;
use Config\JWT as JWTConfig;
use CodeIgniter\API\ResponseTrait;

class Api extends ResourceController
{
    use ResponseTrait;
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
        $this->version = $version ?: 'N/A';


        // $all_params_req_data = $this->request->getGetPost();
        // if (is_array($all_params_req_data)) {
        //     $all_params_req_data = array_map('trim', $all_params_req_data);
        // } 
        // $data['all_params'] = $all_params_req_data;
        
        $data['status'] = false;
        $data['metadata'] = array('version' => $this->version);

        return $this->respond($data,200);
    }

    public function login()
    {
        $version = $this->request->getHeaderLine('Accept-Version');

        if ($version === 'v1') {
            $credentials = $this->request->getJSON();
            $user = $this->getUserByCredentials($credentials->username, $credentials->password);

            if (!$user) {
                $data['status'] = false;
                $data['metadata'] = array('timestamp' => date('c'),'version' => $version);
                $data['msg'] = 'Invalid credentials';

                return $this->failUnauthorized($data, 401);
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
            $refreshToken = create_jwt($payload, 'refresh');

            $data['status'] = true;
            // ISO 8601 standard date format YYYY-MM-DDTHH:MM:SS+00:00
            $data['metadata'] = array('timestamp' => date('c'),'version' => $version);
            $data['access_token'] = $accessToken;
            $data['refresh_token'] = $refreshToken;

            return $this->respond($data, 200); // 200 OK
        } else {
            $data['status'] = false;
            $data['metadata'] = array('timestamp' => date('c'),'version' => $version);
            $data['msg'] = 'Version not supported';
            return $this->failNotImplemented($data , 501);
        }
    }

    public function refresh()
    {
        $version = $this->request->getHeaderLine('Accept-Version');

        if ($version === 'v1') {
            $refreshToken = $this->request->getHeader('Authorization');
            if (!$refreshToken) {
                $data['status'] = false;
                $data['metadata'] = array('timestamp' => date('c'),'version' => $version);
                $data['msg'] = 'Refresh token is required';
                return $this->failUnauthorized($data, 401);
            }

            $token = str_replace('Bearer ', '', $refreshToken->getValue());
            $decoded = verify_jwt($token);

            if (!$decoded) {
                $data['status'] = false;
                $data['metadata'] = array('timestamp' => date('c'),'version' => $version);
                $data['msg'] = 'Invalid or expired refresh token';

                return $this->failUnauthorized($data, 401);
            }

            // Create new access token
            $payload = [
                'uid' => $decoded->uid,
                'username' => $decoded->username,
                'iat' => time(),
                'exp' => time() + config(JWTConfig::class)->accessTTL,
            ];

            $accessToken = create_jwt($payload, 'access');

            $data['status'] = true;
            // ISO 8601 standard date format YYYY-MM-DDTHH:MM:SS+00:00
            $data['metadata'] = array('timestamp' => date('c'),'version' => $version);
            $data['access_token'] = $accessToken;
            
            return $this->respond($data, 200); // 200 OK
        } else {
            $data['status'] = false;
            $data['metadata'] = array('timestamp' => date('c'),'version' => $version);
            $data['msg'] = 'Version not supported';

            return $this->failNotImplemented($data, 501);
        }
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
}
