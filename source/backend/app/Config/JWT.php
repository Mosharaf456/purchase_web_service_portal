<?php 

namespace Config;

use CodeIgniter\Config\BaseConfig;

class JWT extends BaseConfig
{
    public $key;
    public $alg; // Default to RS256
    public $accessTTL; // Access token lifetime
    public $refreshTTL; // Refresh token lifetime
    
    public function __construct()
    {
        $this->key = env('JWT_SECRET_KEY');
        $this->alg = env('JWT_ALG');
        $this->accessTTL = env('JWT_ACCESS_TTL');
        $this->refreshTTL = env('JWT_REFRESH_TTL');
        
        
        // If you want to ensure it's set and not empty
        if (empty($this->key)) {
            throw new \RuntimeException('JWT_SECRET_KEY is not set in .env file');
        }
        if (empty($this->key) || strlen($this->key) < 32) {
            throw new \RuntimeException('JWT_SECRET_KEY must be at least 32 chars long!');
        }
    }
}
