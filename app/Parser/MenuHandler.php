<?php

namespace App\Parser;

use App\Jobs\ParseCategory;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MenuHandler
{
    private const BASE_PATH = '/public/bonnycards';
    private string $currentPath = self::BASE_PATH;

    public function scheduleJobs(MainMenu $menu)
    {
        $this->clearBaseDirectory();
        $this->makeDirectories($menu);
    }

    private function clearBaseDirectory(): void
    {
        if (Storage::exists(self::BASE_PATH)) {
            Storage::deleteDirectory(self::BASE_PATH);
        }

        Storage::makeDirectory(self::BASE_PATH);
    }

    private function makeDirectories(MainMenu $menu)
    {
        $number = 1;
        foreach ($menu->getSubcategories() as $menuItem) {
            Storage::makeDirectory(sprintf('%s/%s-%s', $this->currentPath, $number, $menuItem->getName()));

            if ($menuItem->hasSubcategories()) {
                $this->currentPath = sprintf('%s/%s-%s', $this->currentPath, $number, $menuItem->getName());
                $this->makeDirectories($menuItem);
                $this->currentPath = Str::replace(
                    sprintf('/%s-%s', $number, $menuItem->getName()),
                    '',
                    $this->currentPath
                );
                $number++;
                continue;
            }

            ParseCategory::dispatch($menuItem, sprintf('%s/%s-%s', $this->currentPath, $number, $menuItem->getName()));
            $number++;
        }
    }
}
