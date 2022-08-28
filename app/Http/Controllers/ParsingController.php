<?php

namespace App\Http\Controllers;

use App\Parser\MenuHandler;
use DiDom\Document;
use Illuminate\Http\Request;
use App\Parser\BonnyParser;

class ParsingController extends Controller
{
    public function index(BonnyParser $parser, MenuHandler $handler)
    {
        $menu = $parser->parseMenu('https://bonnycards.ru/');
        $handler->scheduleJobs($menu, $parser);
    }

    public function test(BonnyParser $parser, MenuHandler $handler)
    {
        $menu = $parser->parseMenu('https://bonnycards.ru/');
        dd($parser->parseCategory($menu->getSubcategories()[1], new Document()));
    }
}
