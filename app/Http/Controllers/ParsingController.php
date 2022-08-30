<?php

namespace App\Http\Controllers;

use App\Parser\MenuHandler;
use DiDom\Document;
use Illuminate\Http\Request;
use App\Parser\BonnyParser;
use Illuminate\Support\Facades\Artisan;

class ParsingController extends Controller
{
    public function index(BonnyParser $parser, MenuHandler $handler)
    {
        $menu = $parser->parseMenu('https://bonnycards.ru/');
        $handler->scheduleJobs($menu);
        return 'Job is done';
    }
}
