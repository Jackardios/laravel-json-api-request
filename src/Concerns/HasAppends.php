<?php

namespace Jackardios\JsonApiRequest\Concerns;

use Illuminate\Support\Collection;
use Jackardios\JsonApiRequest\Exceptions\InvalidAppendQuery;

trait HasAppends
{
    protected ?Collection $requestedAppends = null;
    protected ?Collection $allowedAppends = null;

    private static string $appendsArrayValueDelimiter = ',';

    public static function setAppendsArrayValueDelimiter(string $appendsArrayValueDelimiter): void
    {
        static::$appendsArrayValueDelimiter = $appendsArrayValueDelimiter;
    }

    public static function getAppendsArrayValueDelimiter(): string
    {
        return static::$appendsArrayValueDelimiter;
    }

    protected function allowedAppends(): array
    {
        return [];
    }

    public function setAllowedAppends($appends): self
    {
        $appends = is_array($appends) ? $appends : func_get_args();

        $this->allowedAppends = collect($appends)
            ->filter()
            ->unique();

        $this->ensureAllAppendsAllowed();

        return $this;
    }

    public function getAllowedAppends(): ?Collection
    {
        if (!($this->allowedAppends instanceof Collection)) {
            $allowedAppendsFromCallback = $this->allowedAppends();

            if ($allowedAppendsFromCallback) {
                $this->setAllowedAppends($allowedAppendsFromCallback);
            }
        }

        return $this->allowedAppends;
    }

    public function appends(): Collection
    {
        if ($this->requestedAppends instanceof Collection) {
            return $this->requestedAppends;
        }

        // ensure all appends allowed
        $this->getAllowedAppends();

        $appendParameterName = config('json-api-request.parameters.append');
        $appendParts = $this->getRequestData($appendParameterName);

        if (is_string($appendParts)) {
            $appendParts = explode(static::getAppendsArrayValueDelimiter(), $appendParts);
        }

        $this->requestedAppends = collect($appendParts)
            ->filter()
            ->unique()
            ->values();

        return $this->requestedAppends;
    }

    protected function ensureAllAppendsAllowed(): self
    {
        $appends = $this->appends();

        $diff = $appends->diff($this->allowedAppends);

        if ($diff->isNotEmpty()) {
            throw InvalidAppendQuery::appendsNotAllowed($diff, $this->allowedAppends);
        }

        return $this;
    }
}
