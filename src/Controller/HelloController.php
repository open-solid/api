<?php

namespace OpenSolid\Api\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HelloController
{
    public function __invoke(Request $request): Response
    {
        return new Response('Hello Api!');
    }
}
