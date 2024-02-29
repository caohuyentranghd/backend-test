<?php

namespace App\Services\Internals\Movie;

use App\Services\Internals\BaseInternalService;
use App\Services\Repositories\Movie\MovieRepositoryInterface;

class MovieService extends BaseInternalService implements MovieServiceInterface 
{
    /**
     * MovieRepositoryInterface constructor.
     *
     * @param MovieRepositoryInterface $movieRepositoy
     */
    public function __construct(MovieRepositoryInterface $movieRepositoy)
    {
        $this->repository = $movieRepositoy;
    }
}