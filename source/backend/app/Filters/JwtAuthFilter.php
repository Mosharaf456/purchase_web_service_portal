<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Config\JWT as JWTConfig;

class JwtAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $authHeader = $request->getHeader('Authorization');
        if (!$authHeader) {
            return redirect()->to('/login');
        }

        $token = str_replace('Bearer ', '', $authHeader->getValue());

        // Verify token
        $decoded = verify_jwt($token);

        if (!$decoded) {
            return redirect()->to('/login');
        }

        // Optionally, store decoded token data for later use in controllers
        $request->setGlobal('decoded_token', $decoded);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Optional: modify response if needed
    }
}
