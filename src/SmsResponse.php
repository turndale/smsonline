<?php

namespace Turndale\SmsOnline;

use ArrayAccess;
use JsonSerializable;
use Illuminate\Http\Client\Response;

class SmsResponse implements ArrayAccess, JsonSerializable
{
    /**
     * The underlying Laravel HTTP response.
     *
     * @var \Illuminate\Http\Client\Response
     */
    protected Response $response;

    /**
     * The decoded JSON response data.
     *
     * @var array|null
     */
    protected ?array $decoded = null;

    /**
     * Create a new SMS response instance.
     *
     * @param \Illuminate\Http\Client\Response $response
     */
    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    /**
     * Get the JSON decoded body of the response as an array.
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    public function json(?string $key = null, mixed $default = null): mixed
    {
        if ($this->decoded === null) {
            $this->decoded = $this->response->json() ?? [];
        }

        if ($key === null) {
            return $this->decoded;
        }

        return data_get($this->decoded, $key, $default);
    }

    /**
     * Get the response body as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->json() ?? [];
    }

    /**
     * Get the 'data' key from the response.
     *
     * @param string|null $key Optional dot notation key within data
     * @param mixed $default
     * @return mixed
     */
    public function getData(?string $key = null, mixed $default = null): mixed
    {
        $data = $this->json('data', []);

        if ($key === null) {
            return $data;
        }

        return data_get($data, $key, $default);
    }

    /**
     * Get the batch ID from the response.
     *
     * @return string|null
     */
    public function getBatchId(): ?string
    {
        return $this->getData('batch_id');
    }

    /**
     * Get the response body as a string.
     *
     * @return string
     */
    public function body(): string
    {
        return $this->response->body();
    }

    /**
     * Get the HTTP status code of the response.
     *
     * @return int
     */
    public function status(): int
    {
        return $this->response->status();
    }

    /**
     * Determine if the request was successful (2xx status code).
     *
     * @return bool
     */
    public function successful(): bool
    {
        return $this->response->successful();
    }

    /**
     * Determine if the request was successful (alias for successful).
     *
     * @return bool
     */
    public function ok(): bool
    {
        return $this->successful();
    }

    /**
     * Determine if the request failed (4xx or 5xx status code).
     *
     * @return bool
     */
    public function failed(): bool
    {
        return $this->response->failed();
    }

    /**
     * Determine if the response was a client error (4xx status code).
     *
     * @return bool
     */
    public function clientError(): bool
    {
        return $this->response->clientError();
    }

    /**
     * Determine if the response was a server error (5xx status code).
     *
     * @return bool
     */
    public function serverError(): bool
    {
        return $this->response->serverError();
    }

    /**
     * Get the error message from the response if available.
     *
     * @return string|null
     */
    public function getError(): ?string
    {
        return $this->json('error') ?? $this->json('message');
    }

    /**
     * Get the underlying Laravel HTTP response.
     *
     * @return \Illuminate\Http\Client\Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }

    /**
     * Get the response headers.
     *
     * @return array
     */
    public function headers(): array
    {
        return $this->response->headers();
    }

    /**
     * Determine if the given offset exists.
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->toArray()[$offset]);
    }

    /**
     * Get the value at the given offset.
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->toArray()[$offset] ?? null;
    }

    /**
     * Set the value at the given offset.
     *
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        // Response is immutable, do nothing
    }

    /**
     * Unset the value at the given offset.
     *
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        // Response is immutable, do nothing
    }

    /**
     * Get the JSON serializable representation of the response.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Convert the response to a string (JSON encoded).
     *
     * @return string
     */
    public function __toString(): string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }
}
