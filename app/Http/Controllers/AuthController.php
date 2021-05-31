<?php

namespace App\Http\Controllers;

use App\Jobs\AddUserToGrowsurf;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function signup() {
        AddUserToGrowsurf::dispatchAfterResponse();
    }
}
