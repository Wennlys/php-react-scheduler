<?php 
declare(strict_types=1);

namespace Source\App;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Source\Core\Connection;
use Source\Models\UserDAO;
use Source\Models\User;
use Exception;

class UserStoreController
{
    private ResponseInterface $response;
    private Connection $connection;

    public function __construct(Connection $connection, ResponseInterface $response)
    {
        $this->connection = $connection;
        $this->response = $response;
    }

    public function store(ServerRequestInterface $request): ResponseInterface
    {
        $reqBody = json_decode((string)$request->getBody(), true);

        $user = new User();
        $userDao = new UserDAO($this->connection);

        $user->setUserName($reqBody['user_name']);
        $user->setFirstName($reqBody['first_name']);
        $user->setLastName($reqBody['last_name']);
        $user->setEmail($reqBody['email']);
        $user->setPassword($reqBody['password']);
        $user->setProvider($reqBody['provider']);

        $user = $userDao->save($user);

        $this->response->getBody()->write(json_encode((object)[
            "id" => $user['id'],
            "user_name" => $user['user_name'],
            "first_name" => $user['first_name'],
            "last_name" =>  $user['last_name'],
            "email" => $user['email'],
            "provider" => $user['provider'] === "1",
            "avatar" => [
                "url" => "http://{$_SERVER['HTTP_HOST']}/tmp/uploads/{$user['path']}",
                "name" => $user['name'],
                "path" => $user['path'],
            ]
        ], JSON_UNESCAPED_SLASHES));
        return $this->response->withStatus(200);
    }
}
