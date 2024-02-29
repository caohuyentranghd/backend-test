<?php

namespace App\Services\Internals\Favorite;

use App\Services\Internals\BaseInternalService;
use App\Services\Repositories\Favorite\FavoriteRepositoryInterface;

class FavoriteService extends BaseInternalService implements FavoriteServiceInterface 
{
    /**
     * FavoriteRepositoryInterface constructor.
     *
     * @param FavoriteRepositoryInterface $favoriteRepository
     */
    public function __construct(FavoriteRepositoryInterface $favoriteRepository)
    {
        $this->repository = $favoriteRepository;
    }
}