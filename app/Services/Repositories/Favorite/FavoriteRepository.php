<?php

namespace App\Services\Repositories\Favorite;

use App\Models\Favorite;
use App\Services\Repositories\BaseRepository;

/**
 * Class FavoriteRepository
 *
 * @package App\Services\Repositories\Favorite
 */
class FavoriteRepository extends BaseRepository implements FavoriteRepositoryInterface
{
    /**
     * @return mixed|string
     */
    public function model()
    {
        return Favorite::class;
    }

    /**
     * @param object $params
     *
     * @return mixed
     */
    protected function filter($params)
    {
        if (!empty($params->get('id'))) {
            $this->method('where', 'id', $params->get('id'));
        }

        if (!empty($params->get('user_id'))) {
            $this->method('where', 'user_id', $params->get('user_id'));
        }

        if (!empty($params->get('movie_id'))) {
            $this->method('where', 'movie_id', $params->get('movie_id'));
        }

        return parent::filter($params);
    }

    /**
     * @param object $params
     *
     * @return mixed
     */
    public function first($params)
    {
        $this->resetModel();

        return parent::first($params);
    }

    /**
     * @param object $params
     *
     * @return mixed
     */
    public function update($params)
    {
        $this->resetModel();

        return parent::update($params);
    }

    /**
     * @param object $params
     *
     * @return mixed
     */
    public function mask($params)
    {
        if (!empty($params->option('id'))) {
            $this->method('where', 'id', $params->option('id'));
        }

        return parent::mask($params);
    }

    /**
     * @param object $params
     *
     * @return mixed
     */
    public function destroy($params)
    {
        $this->resetModel();

        return parent::destroy($params);
    }
}
