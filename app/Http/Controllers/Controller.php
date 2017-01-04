<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    private function jsonOutput($code, $msg = null, $result=null) {
        $data = [
            'code' => $code, 
            'msg' => $msg, 
            'result'=> $result,
        ];
        return response()->json($data);
    }

    protected function error($msg, $result=null) {
        return $this->jsonOutput(1, $msg, $result);
    }

    protected function success($result = null, $msg = 'success') {
        return $this->jsonOutput(0, $msg, $result);
    }
}
