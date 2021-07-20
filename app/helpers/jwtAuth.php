<?php

namespace App\helpers;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use DomainException;

class JwtAuth {

    public $key;

    public function __construct(){
        $this->key = "esto_es_una_clave_super_secreta_941214443";
    }

    public function signup($email, $password, $getoken=null){

        $user = User::where([
            'email' => $email,
            'password' => $password
        ])->first();

        $signup = false;
        if( is_object($user) ){
            $signup = true;
        }

        if( $signup ){
            $token = array(
                'sub' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'surname' => $user->surname,
                'description' => $user->description,
                'iat' => time(),
                'exp' => time() + (7 * 24 * 60 * 60)
            );

            $jwt = JWT::encode($token, $this->key, 'HS256');
            $decode = JWT::decode($jwt, $this->key, ['HS256']);

            if( is_null($getoken) ){
                $data = $jwt;
            }else{
                $data = $decode;
            }


        }else{
            $data = [
                'status' => 'error',
                'mensaje' => 'Login incorrecto',
            ];
        }

        return $data;
    }

    public function checkToken($jwt, $getIdentity=false){
        $auth = false;

        try {
            $jwt = str_replace('"', '', $jwt);
            $decoded = JWT::decode($jwt, $this->key, ['HS256']);
        } catch (\UnexpectedValueException $e) {
            $auth = false;
        } catch (\DomainException $e){
            $auth = false;
        }

        if( !empty($decoded) && is_object($decoded) && isset($decoded->sub) ){
            $auth = true;
        }else{
            $auth = false;
        }

        if( $getIdentity ){
            return $decoded;
        }

        return $auth;
    }
    
}
