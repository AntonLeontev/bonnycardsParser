<?php

namespace App\Parser;

class Category extends MainMenu
{
    private string $name;
    private string $link;

    public function __construct(string $name, string $link = '')
    {
        $this->name = $name;
        $this->link = $link;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getLink(): string
    {
        return $this->link;
    }
}
