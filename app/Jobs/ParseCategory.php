<?php

namespace App\Jobs;

use App\Parser\Category;
use App\Parser\ParserException;
use DiDom\Document;
use Facades\App\Parser\BonnyParser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\SimpleExcel\SimpleExcelWriter;
use Throwable;

class ParseCategory implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    private Category $category;
    private string $path;
    private string $fullName;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Category $category, string $path)
    {
        $this->category = $category;
        $this->path = $path;
        $this->fullName = storage_path('app' . $this->path . '/images.xlsx');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $document = new Document();

        $images = BonnyParser::parseCategory($this->category, $document);

        $writer = SimpleExcelWriter::create($this->fullName, 'xlsx');
        foreach ($images as $image) {
            $writer->addRow([
                'imageLink' => $image['imageLink'],
                'text' => $image['text'],
            ]);
        }
    }

    public function fail(Throwable $exception = null)
    {
        if (!is_null($exception)) {
            Log::notice($exception->getMessage());
        }
    }
}
