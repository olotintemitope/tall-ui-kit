<?php

namespace TallUiKit\Support\DataTable;

use InvalidArgumentException;

/**
 * Thrown in local and testing when a table's columns, filters, actions or
 * defaults do not fit together (an unknown column key, a default sort on a
 * column that cannot sort, a filter type that does not exist). Production
 * never throws it: the table falls back to safe defaults instead.
 */
class InvalidTableDefinition extends InvalidArgumentException
{
    public static function for(string $component, string $message): self
    {
        return new self("Data table [{$component}]: {$message}");
    }
}
