<?php

namespace App\Services\Repositories\User;

use App\Models\User;
use App\Services\Repositories\BaseRepository;

/**
 * Class UserRepository
 *
 * @package App\Services\Repositories\User
 */
class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    /**
     * @return mixed|string
     */
    public function model()
    {
        return User::class;
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

        if (!empty($params->get('verification_token'))) {
            $this->method('where', 'verification_token', $params->get('verification_token'));
        }

        if (!empty($params->get('is_where_null_email_verified_at'))) {
            $this->method('whereNull', 'email_verified_at');
        }

        if (!empty($params->get('email'))) {
            $this->method('where', 'email', $params->get('email'));
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
