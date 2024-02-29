<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Services\Internals\User\UserServiceInterface;
use Illuminate\Support\Facades\Auth;
use Throwable;

class UserController extends Controller
{
    private $service;

    public function __construct(UserServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * @param Request $request
     * 
     * @return mixed
     */
    public function getInfo(Request $request)
    {
        $user = $request->user();
        $favorites = $user->favorites;
        $result = [
            'name' => $user->name,
            'email' => $user->email,
            'updated_at' => $user->updated_at->format('Y-m-d H:i:s'),
            'list_movie_favorite' => $favorites,
        ];

        return successResponse(Response::HTTP_OK, $result);
    }

    /**
     * @param Request $request
     * 
     * @return mixed
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email',
        ]);

        if ($user->email != $request->get('email')) {
            return failResponse(Response::HTTP_BAD_REQUEST, __('common.msg_update_error'));
        }

        try {
            $this->service->update(
                collect([
                    'name' => $request->get('name'),
                ]),
                collect(['id' => $user->id])
            );
            $result = [
                'name' => $request->get('name'),
                'email' => $user->email,
                'updated_at' => $user->updated_at->format('Y-m-d H:i:s'),
            ];

            return successResponse(Response::HTTP_OK, $result);
        } catch (Throwable $thow) {
            return failResponse(Response::HTTP_BAD_REQUEST, __('common.msg_update_error'));
        }
    }
}
