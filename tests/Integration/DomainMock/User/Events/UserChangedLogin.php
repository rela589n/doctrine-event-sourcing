<?php

declare(strict_types=1);

namespace Tests\Integration\DomainMock\User\Events;

use Tests\Integration\DomainMock\User\Doctrine\User;
use Tests\Integration\DomainMock\User\VO\Login;

class UserChangedLogin extends UserEvent
{
    private Login $oldLogin;

    private Login $newLogin;

    public function __construct(User $user, Login $oldLogin, Login $newLogin)
    {
        parent::__construct($user);

        $this->oldLogin = $oldLogin;
        $this->newLogin = $newLogin;
    }

    public static function fromInto(User $user, Login $oldLogin, Login $newLogin): self
    {
        return new self($user, $oldLogin, $newLogin);
    }

    public function getOldLogin(): Login
    {
        return $this->oldLogin;
    }

    public function getNewLogin(): Login
    {
        return $this->newLogin;
    }

    public function NAME(): string
    {
        return 'user_changed_login';
    }
}
