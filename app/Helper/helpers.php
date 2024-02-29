<?php

use Illuminate\Http\Response;

if (!function_exists('successPaginateResponse')) {
    /**
     * Build success paginate response
     *
     * @param $data
     * @param $code
     * @param string $dataKey
     * 
     * @return mixed
     */
    function successPaginateResponse($data, $code = Response::HTTP_OK, $dataKey = 'items')
    {
        $data->setPath('/' . app()->make('request')->path());
        $tmpResult = $data->toArray();
        $data = $tmpResult['data'];
        unset($tmpResult['links']);
        unset($tmpResult['data']);
        $result[$dataKey] = $data;
        $result['pagination'] = $tmpResult;

        return response()->json([
            'success' => true,
            'data' => $result,
            'code' => $code,
        ], $code);
    }
}

if (!function_exists('successResponse')) {
    /**
     * Build success response
     *
     * @param $code
     * @param $data
     * @param $message
     * 
     * @return mixed
     */
    function successResponse($code = Response::HTTP_OK, $data = [], $message = '')
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'code' => $code
        ], $code);
    }
}

if (!function_exists('failResponse')) {
    /**
     * Build fail response
     *
     * @param $code
     * @param $message
     * @param $error
     * 
     * @return mixed
     */
    function failResponse($code = Response::HTTP_BAD_REQUEST, $message = '', $error = [])
    {
        return response()->json([
            'success' => false,
            'error' => $error,
            'message' => $message,
            'code' => $code
        ], $code);
    }
}
