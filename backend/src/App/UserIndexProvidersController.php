<?php declare(strict_types=1);


namespace Source\App;

use Psr\Http\Message\ResponseInterface;
use Source\Core\Connection;
use Source\Models\UserDAO;

/**
 * Class UserIndexProvidersController
 *
 * @package Source\App
 */
class UserIndexProvidersController
{
    /** @var ResponseInterface */
    private ResponseInterface $response;

    /** @var Connection */
    private Connection $connection;

    /**
     * UserIndexProvidersController constructor.
     *
     * @param Connection        $connection
     * @param ResponseInterface $response
     */
    public function __construct(Connection $connection, ResponseInterface $response)
    {
        $this->connection = $connection;
        $this->response = $response;
    }

    /**
     * @return ResponseInterface
     */
    public function index(): ResponseInterface
    {
        $userDao = new UserDAO($this->connection);
        $providers = array_map(function ($provider) {
            return [
                "id" => $provider->id,
                "full_name" => $provider->first_name . " " . $provider->last_name,
                "email" => $provider->email,
                "provider" => $provider->provider === "1",
                "avatar" => [
                    "url" => "http://{$_SERVER['HTTP_HOST']}/tmp/uploads/{$provider->path}",
                    "name" => $provider->name,
                    "path" => $provider->path,
                ],
            ];
        }, $userDao->findAllProviders());

        $this->response->getBody()->write(json_encode($providers, JSON_UNESCAPED_SLASHES));
        return $this->response->withStatus(200);
    }
}
