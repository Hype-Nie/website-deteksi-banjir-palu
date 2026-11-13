<?php

namespace App\Exceptions;

use Illuminate\Http\Request;
use RuntimeException;

class PreparedDataUnavailable extends RuntimeException
{
    public function render(Request $request)
    {
        return $request->expectsJson()
            ? response()->json(['message' => $this->getMessage()], 503)
            : response()->view('flood.setup', ['message' => $this->getMessage()], 503);
    }
}
