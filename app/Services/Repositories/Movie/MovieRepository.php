<?php

namespace App\Services\Repositories\Movie;

use App\Models\Movie;
use App\Services\Repositories\BaseRepository;

/**
 * Class MovieRepository
 *
 * @package App\Services\Repositories\Movie
 */
class MovieRepository extends BaseRepository implements MovieRepositoryInterface
{
    /**
     * @return mixed|string
     */
    public function model()
    {
        return Movie::class;
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

        if (!empty($params->option('is_sort_by_id_desc'))) {
            $this->method('orderBy', 'id', 'DESC');
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
