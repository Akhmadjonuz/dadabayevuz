<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;


trait HttpResponse
{
  /**
   * Error message http response json
   * @param \Exception $e
   * @return JsonResponse
   */
  protected function log($e)
  {
    Log::error($e->getMessage() . "\n" . $e->getTraceAsString());
    return response()->json(['data' => '500 Error Server'], 500);
  }
}
