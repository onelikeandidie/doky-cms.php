<?php

namespace App\Libraries\Markdown;

use App\Libraries\Markdown\Exceptions\MetaKeyNotSet;
use App\Libraries\Result\Result;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Meta
{
    public array $data;

    public function __construct(
        array $data = []
    )
    {
        // Setup some basic defaults
        $defaults = [
            'title' => '',
            'tags' => [],
            'date' => date('Y-m-d'),
            'priority' => 0,
            'visibility' => 'public',
        ];
        $data = array_merge($defaults, $data);
        $this->data = $data;
    }

    /**
     * @param array $data
     * @return Meta
     * @deprecated Use constructor instead
     */
    public static function fromArray(array $data): Meta
    {
        return new Meta($data);
    }

    /**
     * @param string $key
     * @return Result<int|bool|string|array,MetaKeyNotSet>
     */
    public function get(string $key): Result
    {
        if (!isset($this->data[$key])) {
            return Result::err(new MetaKeyNotSet($key));
        }

        // Cast the data to the correct type
        $value = $this->data[$key];
        if (is_numeric($value)) {
            $value = floatval($value);
        }
        if ($value === 'true' || $value === 'false') {
            $value = $value === 'true';
        }
        if (is_string($value)) {
            if (Str::contains($value, ',')) {
                $value = explode(',', $value);
            }
        }

        return Result::ok($value);
    }

    /**
     * @param string $key
     * @param int|bool|string|array $value
     * @return Meta
     */
    public function set(string $key, $value): Meta
    {
        $data = $this->data;
        Arr::set($data, $key, $value);
        $this->data = $data;
        return $this;
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
