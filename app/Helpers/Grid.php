<?php

namespace App\Helpers;

trait Grid
{
    /**
     * @param array $data
     * @param array $extra_data
     * @param null $gridName
     * @param array $attach_data
     * @param array $config
     * @param int $total_count
     * @return \Illuminate\Http\JsonResponse
     */
    protected function setGrid($data = [], $total_count = 0, $config = [], $extra_data= [],$gridName = NULL, $attach_data = []): \Illuminate\Http\JsonResponse
    {
        if(count($config) > 0){
            $result = [
                // 'attach_data' => $attach_data,
                'data' => $data,
                'config' => $config,
                'totalCount' => $total_count,
             ];
        }else{
            $result = [
                //  'attach_data' => $attach_data,
                 'data' => $data,
                //  'config' => $config,
                 'totalCount' => $total_count,
                //  'extra_data' => $extra_data
             ];
        }

        return $this->apiResponse(
            [
                'success' => true,
                'result' => $result
            ]
        );
    }
}
