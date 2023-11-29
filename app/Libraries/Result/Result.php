<?php

namespace App\Libraries\Result;

use Exception;

/**
 * This class is a port of Rust's Result type.
 *
 * @note Sorry, I love Rust.
 *
 * @template T
 * @template E
 *
 * @property-read T|null $ok
 * @property-read E|null $err
 * @psalm-immutable
 */
class Result
{
    public const EMPTY = 1;

    protected mixed $ok;
    protected mixed $err;

    public function __construct()
    {
        $this->ok = null;
        $this->err = null;
    }

    public static function ok($ok = 1): self
    {
        $result = new self();
        $result->ok = $ok;
        return $result;
    }

    public static function err($err): self
    {
        $result = new self();
        $result->err = $err;
        return $result;
    }


    public function isOk(): bool
    {
        return $this->err === null;
    }

    /**
     * Returns the ok value regardless of whether the result is ok or err.
     *
     * @return T
     */
    public function getOk(): mixed
    {
        return $this->ok;
    }

    public function isErr(): bool
    {
        return $this->err !== null;
    }

    /**
     * Returns the error value regardless of whether the result is ok or err.
     *
     * @return E
     */
    public function getErr(): mixed
    {
        return $this->err;
    }

    /**
     * @return T
     * @throws PanicException
     */
    public function unwrap()
    {
        if ($this->isErr()) {
            $error = $this->err;
            // If the error is an exception, throw it
            if ($error instanceof Exception) {
                throw $error;
            }
            if (!is_string($error)) {
                $error = $error->getMessage() ?? $error->__toString();
            }
            throw new PanicException($error);
        }
        return $this->ok;
    }

    public function getOkOrDefault(mixed $default): mixed
    {
        if ($this->isOk()) {
            return $this->ok;
        }
        return $default;
    }
}
