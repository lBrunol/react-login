<?php 
namespace Reactlogin\Controllers;

use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
use Firebase\JWT\JWT;

class AuthController {
    public function __construct()
    {
    }

    public function validateToken(Request $request, Response $response)
    {

        $secret = $this->container->get('secretkey');
        $params = $request->getParams();

        $token = $params['token'];

        if (!$token) {
            throw new \Exception("O token é obrigatório.", 500);
        }

        try {
            JWT::$leeway = 60;
            $payload = JWT::decode($token, $secret, array('HS256'));

            if ($payload) {
                return $response->withJson(['valid' => true, 'msg' => 'Token válido'], 200);
            }
        } catch (Exception $exception) {
            return $response->withJson(['valid' => false, 'msg' => 'Deu erro'], 200);
        }
    }

    public function auth(Request $request, Response $response)
    {

        $params = $request->getParams();

        $login = isset($params['login']) ? $params['login'] : false;
        $pass = isset($params['password']) ? $params['password'] : false;

        if (!$login || !$pass) {
            throw new \Exception("Dados de login inválidos", 500);
        }

        $query = $this->container->em->createQuery("SELECT partial u.{id, fullName, email, login, password, userType}, partial r.{id, description}, partial c.{id, description} FROM \Poseidon\Entities\User u JOIN u.roles r JOIN r.capabilities c WHERE u.login = :login")->setParameter('login', $login);

        $user = $query->getArrayResult();

        if ($login == 'admin' && $pass == 'admin') {            
            $this->token = $this->generateToken($user);            
            return $response->withJson(['token' => $this->token], 200);
        } else {
            throw new \Exception("Usuário ou senha inválido(s).", 500);
        }
    }

    private function generateToken($data)
    {
        $secret = $this->container->get('secretkey');

        $iat = time();
        $nbf = $iat + 5;
        $expire = $nbf + 7776000;

        $payload = [
            'iat' => $iat,
            'jti' => base64_encode(random_bytes(32)),
            'nbf' => $nbf,
            'exp' => $expire,
            'data' => $data,
        ];

        return JWT::encode($payload, $secret);
    }
}

?>