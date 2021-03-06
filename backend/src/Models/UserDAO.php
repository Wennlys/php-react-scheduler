<?php 
declare(strict_types=1);

namespace Source\Models;

use Source\Core\Connection;
use Source\Core\Database;
use Exception;

class UserDAO
{
    private ?string $id;
    private Database $database;

    public function __construct(Connection $connection)
    {
        $this->database = new Database($connection,"users");
    }

    public function save(User $user): array
    {
        $this->id = $this->database->create([
            "user_name" => $user->getUserName(),
            "first_name" => $user->getFirstName(),
            "last_name" => $user->getLastName(),
            "email" => $user->getEmail(),
            "password" => $user->getPassword(),
            "provider" => $user->isProvider()
        ]);

        return (array)$this->findById();
    }

    public function update(User $user): array
    {
        $this->id = $user->getUserId();

        $email = $user->getEmail();
        $userName = $user->getUserName();

        if ($user->getPassword()) {
            if (!$this->verifyPassword($user)) {
                throw new Exception("Wrong password.");
            }
        }

        $body = array_filter([
          "user_name" => $userName,
          "first_name" => $user->getFirstName(),
          "last_name" => $user->getLastName(),
          "email" => $email,
          "password" => $user->getPassword(),
          "avatar_id" => $user->getAvatarId()
        ]);

        $this->database->update($body, "id = :id", "id=$this->id");

        return (array)$this->findById();
    }

    public function delete(User $user): bool
    {
        $id = $user->getUserId();

        if (!$this->verifyPassword($user))
            throw new Exception("Wrong password.");

        return $this->database->delete("id = :id", "id={$id}");
    }

    public function verifyPassword(User $user): bool
    {
        $currentPass = $user->getCurrentPassword();
        $savedPass = ($this->findById($user))->password;

        if (!password_verify($currentPass, $savedPass))
            return false;
        return true;
    }

    public function findByLogin(User $user): ?object
    {
        if ($user->getEmail()) {
            return $this->database->find("*, users.id id")
                ->join("users.avatar_id = files.id", "files")
                ->and("email='{$user->getEmail()}'")
                ->fetch();
        }
        return $this->database->find("*, users.id id")
            ->join("users.avatar_id = files.id", "files")
            ->and("user_name='{$user->getUserName()}'")
            ->fetch();
    }

    public function findById(?User $user = null): ?object
    {
        $id = $user ? $user->getUserId() : $this->id;

        return $this->database->find("*, users.id id")
            ->join("users.avatar_id = files.id", "files")
            ->and("users.id = $id")
            ->fetch();
    }

    public function findAll(): ?array
    {
        return $this->database->find()->fetch(true);
    }

    public function findAllProviders()
    {
        return $this->database
            ->find("users.id, users.first_name, users.last_name, users.email, files.name, files.path")
            ->join("users.avatar_id = files.id", "files")
            ->and("users.provider = 1")
            ->order("id")
            ->fetch(true);
    }

    public function findProvider(User $user)
    {
        return $this->database
            ->find("*",
                   "id = :id",
                   "id={$user->getUserId()}")
            ->and("provider = true")
            ->fetch();
    }
}
