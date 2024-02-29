<?php

namespace App\Services\Internals\User;

use App\Services\Internals\BaseInternalService;
use App\Services\Repositories\User\UserRepositoryInterface;

class UserService extends BaseInternalService implements UserServiceInterface 
{
    /**
     * UserRepositoryInterface constructor.
     *
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->repository = $userRepository;
    }
}