<?php

namespace App\Parser;

use DiDom\Document;
use DiDom\Element;
use DOMElement;
use Illuminate\Support\Facades\Log;


class BonnyParser
{
    private Document $document;


    public function __construct(Document $document)
    {
        $this->document = $document;
    }

    public function parseMenu(string $filename): MainMenu
    {
        $html = $this->getHtml($filename);
        $this->document->loadHtml($html);
        $DOMCategories = $this->document->find('#nav_list_first > li');

        if (empty($DOMCategories)) {
            throw new ParserException('Main menu not found in document');
        }

        $menu = new MainMenu();

        foreach ($DOMCategories as $DOMCategory) {
            if (! $DOMCategory->has('ul')) {
                $DOMCategory = $DOMCategory->first('a');
                $category = new Category($DOMCategory->text(), $DOMCategory->getAttribute('href'));
                $menu->addSubcategory($category);
                continue;
            }

            $DOMCategoryTitle = $DOMCategory->first('p');
            $category = new Category($DOMCategoryTitle->text());

            $DOMSubcategories = $DOMCategory->find('ul > li > a');

            foreach ($DOMSubcategories as $DOMSubcategory) {
                if ($DOMSubcategory->text() === 'с Днём рождения') {
                    continue;
                }

                $subcategory = new Category($DOMSubcategory->text(), $DOMSubcategory->getAttribute('href'));
                $category->addSubcategory($subcategory);
            }

            $menu->addSubcategory($category);
        }

        $this->addHiddenSubcategories($menu);

        return $menu;
    }

    public function parseCategory(Category $category, Document $document)
    {
        $html = $this->getHtml($category->getLink());

        $document->loadHtml($html);

        $pagination = $document->first('.str');

        if (! $pagination) {
            return $this->parseImages($category->getLink());
        }

        $lastPageNumber = $this->parseLastPageNumber($pagination);
        $links = $this->makePagesLinks($category->getLink(), $lastPageNumber);

        $allPagesResult = [];
        foreach ($links as $link) {
            $allPagesResult[] = $this->parseImages($link);
        }
        return array_merge(...$allPagesResult);
    }

    private function getHtml(string $url): string
    {
        $cURL = curl_init($url);
        curl_setopt_array($cURL, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $result = curl_exec($cURL);
        if (! $result) {
            throw new \Exception(curl_error($cURL), curl_errno($cURL));
        }
        return $result;
    }

    private function parseLastPageNumber(Element|DOMElement $pagination): int
    {
        $pages = $pagination->find('li > a');
        $lastPage = end($pages);
        return (int) $lastPage->text();
    }

    private function parseImages(string $url): array
    {
        $html = $this->getHtml($url);
        $this->document->loadHtml($html);
        $images = $this->document->find('.a1');

        if (empty($images)) {
            return [];
        }

        $result = [];
        foreach ($images as $image) {
            $href = $image->getAttribute('href');
            $text = $image->text();
            $imageLink = $this->parseImageLink($href);
            $result[] = compact('imageLink', 'text');
        }
        return $result;
    }

    private function parseImageLink(string $url): string
    {
        $html = $this->getHtml($url);
        $this->document->loadHtml($html);

        $image = $this->document->first('.share42init');

        if (empty($image)) {
            throw new ParserException('Не найдена картинка по адресу ' . $url);
        }

        return $image->getAttribute('data-image');
    }

    private function addHiddenSubcategories(MainMenu $menu): void
    {
        $categories = $menu->getSubcategories();
        foreach ($categories as $category) {
            if ($category->hasSubcategories()) {
                $subcategories = $category->getSubcategories();

                foreach ($subcategories as $subcategory) {
                    if ($subcategory->getName() === 'по годам') {
                        $this->addSubsPerYear($subcategory);
                    }

                    if ($subcategory->getName() === 'по именам') {
                        $this->addSubsPerName($subcategory);
                    }
                }
            }
        }
    }

    private function addSubsPerYear(Category $category): void
    {
        if (empty($category->getLink())) {
            return;
        }

        $html = $this->getHtml($category->getLink());
        $this->document->loadHtml($html);
        $DOMSubcategories = $this->document->find('.t5 > a');

        if (count($DOMSubcategories) <= 0) {
            return;
        }

        foreach ($DOMSubcategories as $DOMSubcategory) {
            $subcategory = new Category($DOMSubcategory->text(), $DOMSubcategory->getAttribute('href'));
            $category->addSubcategory($subcategory);
        }
    }

    private function addSubsPerName(Category $category): void
    {
        if (empty($category->getLink())) {
            return;
        }

        $html = $this->getHtml($category->getLink());
        $this->document->loadHtml($html);
//        $this->document->loadHtmlFile($category->getLink());
        $DOMSubcategories = $this->document->find('.woman > a.button');

        if (count($DOMSubcategories) <= 0) {
            return;
        }

        foreach ($DOMSubcategories as $DOMSubcategory) {
            $subcategory = new Category($DOMSubcategory->text(), $DOMSubcategory->getAttribute('href'));
            $category->addSubcategory($subcategory);
        }
    }

    private function makePagesLinks(string $link, int $lastPage): array
    {
        $result = [$link];
        $head = substr($link, 0,  -4);

        for ($page = 2; $page <= $lastPage; $page++) {
            $result[] = sprintf("%s-%s.php", $head, $page);
        }

        return $result;
    }
}
