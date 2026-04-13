<?php

namespace Relaticle\Comments\Http\Controllers;

use Illuminate\Http\Response;

class CommentsStyleController
{
    public function __invoke(): Response
    {
        return response(
            file_get_contents(__DIR__.'/../../../resources/css/comments.css'),
            200,
            ['Content-Type' => 'text/css']
        );
    }
}
