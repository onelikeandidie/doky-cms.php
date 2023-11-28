<?php

namespace App\Libraries\Markdown;

use App\Libraries\Markdown\Exceptions\MetaKeyNotSet;
use App\Libraries\Result\Result;

class Meta
{
    public string $title;
    public array $authors;
    public string $date;
    public array $tags;

    public array $data;

    public function __construct(
        string $title,
        array  $authors,
        string $date,
        array  $tags,
        array  $data
    )
    {
        $this->data = $data;
        $this->title = $title;
        $this->authors = $authors;
        $this->tags = $tags;
        $this->date = $date;
    }

    public static function fromArray(array $data): Meta
    {
        return new Meta(
            $data['title'],
            $data['authors'] ?? ['Unknown Author'],
            $data['date'] ?? '',
            $data['tags'] ?? [],
            $data
        );
    }

    public function get(string $key): Result
    {
        if (!isset($this->data[$key])) {
            return Result::err(new MetaKeyNotSet($key));
        }

        return Result::ok($this->data[$key]);
    }
}
