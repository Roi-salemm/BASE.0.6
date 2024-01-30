<?php

namespace App\Service;

use DateTimeImmutable;

class JWTService {

    //^^ Générations du json web token 

    /**
     * generation du json web token
     * @param array $header
     * @param array $payload
     * @param string $secret
     * @param int $validity
     * @return string
     */ 

    public function generate(array $header, array $payload, string $secret, int $validity = 10800): string 
    {
        if($validity > 0 ){
            $now = new DateTimeImmutable();
            $exp = $now->getTimestamp() + $validity;

            $payload['iat'] = $now->getTimestamp();
            $payload['exp'] = $exp;
        }


     
        // encodage en base 64
        $base64Header = base64_encode(json_encode($header));
        $base64Payload = base64_encode(json_encode($payload));
        
        // Nétoyage des valeurs encodé (retrait des + / = )
        $base64Header = str_replace(['+','/','='], ['-', '_', '']
        , $base64Header); 

        $base64Payload = str_replace(['+','/','='], ['-', '_', '']
        , $base64Payload); 

        // génération de la signature
        $secret = base64_encode($secret);

        $signature = hash_hmac('sha256', $base64Header. '.' .$base64Payload, $secret, true);

        $base64Signature = base64_encode($signature);

        $base64Signature = str_replace(['+','/','='], ['-', '_', '']
        , $base64Signature);
        
        // Création du token :
        $jwt = $base64Header . '.' . $base64Payload . '.' . $base64Signature;

        return $jwt;
    }


    //^^ Verification que le token est correctement 
    //? est'il correctement former ?
    public function isValid(string $token): bool
    {
        return preg_match(
            '/^[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+$/', $token
        ) === 1;
    }

    //? est'il expiré ? récuperation du payload
    public function getPayload(string $token): array
    {
        //* démonter le token
        $array = explode('.', $token);

        //* décoder le payload
        $payload = json_decode(base64_decode($array[1]), true);

        return $payload;
    }

    //? récuperation du header 
    public function getHeader(string $token): array
    {
        //* démonter le token
        $array = explode('.', $token);

        //* décoder le header
        $header = json_decode(base64_decode($array[0]), true);

        return $header;
    }

    //? verifications de si le token a expiré 
    public function isExpired(string $token): bool 
    {
        $payload = $this->getPayload($token);

        $now = new DateTimeImmutable();

        return $payload['exp'] < $now->getTimestamp();
    }

    //? Verifications de la signature du token
    public function check(string $token, string $secret)
    {
       // récuperations du header et payload
       $header = $this->getHeader($token);
       $payload = $this->getPayload($token);

       // régénérations d'un token
       $verifToken = $this->generate($header, $payload, $secret, 0);

       return $token === $verifToken;
    }

}