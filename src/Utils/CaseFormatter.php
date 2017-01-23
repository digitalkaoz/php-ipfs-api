<?php

declare(strict_types=1);

/*
 * This file is part of the "php-ipfs" package.
 *
 * (c) Robert Schönthal <robert.schoenthal@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace IPFS\Utils;

use Camel\CaseTransformer;
use Camel\Format\CamelCase;

class CaseFormatter
{
    public static function camelToColon($value)
    {
        return (new CaseTransformer(new CamelCase(), new ConfigurableFormatter(':')))->transform($value);
    }

    public static function colonToCamel($value)
    {
        return (new CaseTransformer(new ConfigurableFormatter(':'), new CamelCase()))->transform($value);
    }

    public static function dashToCamel($value)
    {
        return (new CaseTransformer(new ConfigurableFormatter('-'), new CamelCase()))->transform($value);
    }

    public static function camelToDash($value)
    {
        return (new CaseTransformer(new CamelCase(), new ConfigurableFormatter('-')))->transform($value);
    }

    public static function dashToCamelArray(array $values): array
    {
        return array_combine(array_map(function ($name) {
            return self::dashToCamel($name);
        }, array_keys($values)), array_values($values));
    }

    public static function stringToBool($value)
    {
        return is_string($value) && in_array(strtolower($value), ['true', 'false'], true) ? filter_var($value, FILTER_VALIDATE_BOOLEAN) : $value;
    }
}
