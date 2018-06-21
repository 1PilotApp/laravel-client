<?php

namespace CmsPilot\Client\Controllers;

use CmsPilot\Client\Middelwares\Authentication;
use Illuminate\Routing\Controller;

/**
 * A Simple controller for test the authentication
 */
class PingController extends Controller
{
    public function __construct()
    {
        $this->middleware(Authentication::class);
    }

    /**
     * Retrieve runtime and composer package version information
     */
    public function index()
    {
        return [
            'message' => "pong",
        ];
    }
}
