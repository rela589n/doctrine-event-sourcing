<?php

declare(strict_types=1);

namespace Tests\Integration\DomainMock\User\Events;

use Tests\Integration\DomainMock\User\Doctrine\User;
use Tests\Integration\DomainMock\User\VO\Login;
use Tests\Integration\DomainMock\User\VO\Password;
use Tests\Integration\DomainMock\User\VO\UserName;

class UserRegistered extends UserEvent
{
    private Login $login;

    private Password $password;

    private UserName $userName;

    private function __construct(User $user, Login $login, Password $password, UserName $name)
    {
        parent::__construct($user);
        $this->login = $login;
        $this->password = $password;
        $this->userName = $name;
    }

    public static function withCredentials(User $user, Login $login, Password $password, UserName $name): self
    {
        return new self($user, $login, $password, $name);
    }

    public function getLogin(): Login
    {
        return $this->login;
    }

    public function getPassword(): Password
    {
        return $this->password;
    }

    public function getUserName(): UserName
    {
        return $this->userName;
    }

    public static function NAME(): string
    {
        return 'user_registered';
    }
}
