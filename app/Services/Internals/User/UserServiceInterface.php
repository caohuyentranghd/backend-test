<?php

namespace App\Services\Internals\User;

/**
 * Interface UserServiceInterface
 *
 * @package App\Services\Internals\User
 */
interface UserServiceInterface
{
    /**
     * @param $data
     * @param $options
     *
     * @return mixed
     */
    public function all($data = null, $options = null);

    /**
     * @param $data
     * @param $options
     *
     * @return mixed
     */
    public function getList($data = null, $options = null);

    /**
     * @param $data
     * @param $options
     *
     * @return mixed
     */
    public function show($data = null, $options = null);

    /**
     * @param $data
     * @param $options
     *
     * @return mixed
     */
    public function getFirstBy($data = null, $options = null);

    /**
     * @param $data
     * @param $options
     *
     * @return mixed
     */
    public function store($data = null, $options = null);

    /**
     * @param $data
     * @param $options
     *
     * @return mixed
     */
    public function update($data = null, $options = null);

    /**
     * @param $data
     * @param $options
     *
     * @return mixed
     */
    public function updateOrCreate($data = null, $options = null);

    /**
     * @param $data
     * @param $options
     *
     * @return mixed
     */
    public function destroy($data = null, $options = null);
}