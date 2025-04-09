<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Config\JWT as JWTConfig;

function create_jwt($payload, $type = 'access')
{
    log_me('ABC MH');
    $key = config(JWTConfig::class)->key;
    $ttl = ($type === 'refresh') ? config(JWTConfig::class)->refreshTTL : config(JWTConfig::class)->accessTTL;
    $payload['exp'] = time() + $ttl;
    

    return JWT::encode($payload, $key, config(JWTConfig::class)->alg);
}

function verify_jwt($token)
{
    $key = config(JWTConfig::class)->key;
    try {
        return JWT::decode($token, new Key($key, config(JWTConfig::class)->alg));
    } catch (Exception $e) {
        return null;
    }
}
