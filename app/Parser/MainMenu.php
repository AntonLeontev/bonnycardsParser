<?php

namespace App\Parser;


class MainMenu
{
    protected array $subcategories = [];

    public function addSubcategory(Category $subcategory): void
    {
        $this->subcategories[] = $subcategory;
    }

    public function hasSubcategories(): bool
    {
        if (empty($this->subcategories)) {
            return false;
        }
        return true;
    }

    /**
     * @return array
     */
    public function getSubcategories(): array
    {
        return $this->subcategories;
    }
}
