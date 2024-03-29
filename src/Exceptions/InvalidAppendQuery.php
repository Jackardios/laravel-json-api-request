<?php

namespace Jackardios\JsonApiRequest\Exceptions;

use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class InvalidAppendQuery extends InvalidQuery
{
    public Collection $appendsNotAllowed;
    public Collection $allowedAppends;

    public function __construct(Collection $appendsNotAllowed, Collection $allowedAppends)
    {
        $this->appendsNotAllowed = $appendsNotAllowed;
        $this->allowedAppends = $allowedAppends;

        $appendsNotAllowed = $appendsNotAllowed->implode(', ');
        $allowedAppends = $allowedAppends->implode(', ');
        $message = "Requested append(s) `{$appendsNotAllowed}` are not allowed. Allowed append(s) are `{$allowedAppends}`.";

        parent::__construct(Response::HTTP_BAD_REQUEST, $message);
    }

    public static function appendsNotAllowed(Collection $appendsNotAllowed, Collection $allowedAppends)
    {
        return new static(...func_get_args());
    }
}
