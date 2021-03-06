<?php 
declare(strict_types=1);

namespace Source\App;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response;
use ReallySimpleJWT\Token;
use Source\Core\Connection;
use Source\Models\User;
use Source\Models\UserDAO;
use Exception;

class SessionStoreController
{
    private Connection $connection;
    private ResponseInterface $response;

    public function __construct(Connection $connection, ResponseInterface $response)
    {
        $this->connection = $connection;
        $this->response = $response;
    }

    public function store(ServerRequestInterface $request): Response
    {
        [
            'login' => $login, 'password' => $password,
        ] = json_decode((string)$request->getBody(), true);

        $userDao = new UserDAO($this->connection);
        $user = new User();

        if (is_email($login)) {
            $user->setEmail($login);
        } else {
            $user->setUserName($login);
        }

        $user = $userDao->findByLogin($user);

        if (!$user) {
            $this->response->getBody()->write(json_encode("User not found."));
            return $this->response->withStatus(401);
        }

        if (!password_verify($password, $user->password)) {
            $this->response->getBody()->write(json_encode("Wrong password."));
            return $this->response->withStatus(401);
        }

        $this->response->getBody()->write(
            json_encode([
                "user" => [
                    "id" => $user->id,
                    "user_name" => $user->user_name,
                    "first_name" => $user->first_name,
                    "last_name" =>  $user->last_name,
                    "email" => $user->email,
                    "provider" => $user->provider === "1",
                    "avatar" => [
                        "url" => "http://{$_SERVER['HTTP_HOST']}/tmp/uploads/{$user->path}",
                        "name" => $user->name,
                        "path" => $user->path,
                    ],
                ],
                "token" => Token::create(
                    $user->id,
                    JWT_SECRET,
                    JWT_EXPIRATION,
                    JWT_ISSUER)
            ], JSON_UNESCAPED_SLASHES));
        return $this->response->withStatus(200);
    }
}
