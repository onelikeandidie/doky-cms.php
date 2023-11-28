<?php

namespace App\Libraries\Markdown;

use App\Libraries\Markdown\Exceptions\EmptyMetaException;
use App\Libraries\Markdown\Exceptions\NoMetaClosingTagException;
use App\Libraries\Markdown\Exceptions\NoMetaStartTagException;
use App\Libraries\Result\Result;
use Illuminate\Support\Str;

class Markdown
{
    public string $content;
    public ?Meta $meta;

    public function meta(): Meta
    {
        if ($this->meta === null) {
            $this->meta = self::extractMeta($this->content)->unwrap();
        }
        return $this->meta;
    }

    /**
     * Extract the metadata from the content.
     *
     * @param string $content
     * @return Result<Meta,\Exception>
     */
    public static function extractMeta(string $content): Result
    {
        // Metadata is stored between two lines of three dashes.
        if (!Str::startsWith($content, '---')) {
            return Result::err(new NoMetaStartTagException());
        }
        // Remove the first line of dashes.
        $content = Str::after($content, '---');
        // Check if there is a second line of dashes.
        if (!Str::contains($content, '---')) {
            return Result::err(new NoMetaClosingTagException());
        }
        // Remove the second line of dashes.
        $content = Str::before($content, '---');
        $content = trim($content);
        // Check if there is any content left.
        if (empty($content)) {
            return Result::err(new EmptyMetaException());
        }
        // Split the content into lines.
        $lines = explode("\n", $content);
        // Create an empty array to store the metadata.
        $meta = [];
        // Loop through each line.
        foreach ($lines as $line) {
            if (empty($line)) {
                continue;
            }
            // Skip lines that start with a hash. These are comments.
            if (Str::startsWith(ltrim($line), '#')) {
                continue;
            }
            // Split the line into key and value.
            $parts = explode(':', $line, 2);
            // Trim the key and value.
            $key = trim($parts[0]);
            $value = trim($parts[1]);
            // Cast values
            if ($value === 'true' || $value === 'false') {
                $value = $value === 'true';
            }
            if (is_numeric($value)) {
                $value = floatval($value);
            }
            if (Str::contains($value, ',')) {
                $value = collect(explode(',', $value))
                    ->map(fn($item) => trim($item))
                    ->toArray();
            }
            // Store the key and value in the metadata array.
            $meta[$key] = $value;
        }
        // Return the meta data.
        return Result::ok(Meta::fromArray($meta));
    }
}
