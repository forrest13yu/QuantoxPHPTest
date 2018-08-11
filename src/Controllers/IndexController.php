<?php
namespace Vanila\Controllers;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class IndexController
{
    public function index()
    {
        return "done";
    }

    public function exception()
    {
        throw new Exception('Test exception');
    }

    public function test($request)
    {
        return json_encode($request->all);
    }
}
