<?php

namespace TallUiKit\Support\DataTable;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * A bulk action (runs on the selection) or a row action (runs on one row) of a
 * WithDataTable table (docs/ui-kit-tables.md).
 *
 *   Action::bulk('archive', 'Archive')->ability('orders.write')->destructive('Archive orders?', 'They leave every list.')
 *       ->handle(fn (Builder $rows, int $count) => ...)
 *   Action::row('open', 'Open')->href(fn ($row) => route(...))
 *   Action::row('refund', 'Refund')->visible(fn ($row) => $row->status === 'completed')->handle(fn (Model $row) => ...)
 *
 * Fields:
 *  - key, label, kind (bulk or row).
 *  - ability: capability checked on the server before the action runs and before it is shown.
 *  - destructive: danger styling and a confirm dialog (confirmTitle, confirmDescription,
 *    confirmLabel). The server refuses a destructive action that was not confirmed.
 *  - handler: bulk gets (Builder $rows, int $count) where $rows is already re-scoped
 *    through tableQuery(); row gets (Model $row), loaded through tableQuery().
 *  - url (row): Closure(Model): string, rendered as a link instead of a button.
 *  - visibleWhen (row): Closure(Model): bool, per-row availability (e.g. pending only).
 *  - icon: optional kit icon name.
 */
final class Action
{
    public ?string $ability = null;

    public bool $destructive = false;

    public ?string $confirmTitle = null;

    public ?string $confirmDescription = null;

    public string $confirmLabel = 'Confirm';

    public ?Closure $handler = null;

    public ?Closure $url = null;

    public ?Closure $visibleWhen = null;

    public ?string $icon = null;

    private function __construct(public readonly string $key, public readonly string $label, public readonly string $kind) {}

    public static function bulk(string $key, string $label): self
    {
        return self::make($key, $label, 'bulk');
    }

    public static function row(string $key, string $label): self
    {
        return self::make($key, $label, 'row');
    }

    private static function make(string $key, string $label, string $kind): self
    {
        if (! preg_match('/^[a-z][a-z0-9_-]*$/', $key)) {
            throw new InvalidTableDefinition("Action key [{$key}] must be lower case (a-z, 0-9, _ and -).");
        }

        return new self($key, $label, $kind);
    }

    public function ability(?string $capability): self
    {
        $this->ability = $capability;

        return $this;
    }

    public function destructive(string $title, string $description, string $confirmLabel = 'Delete'): self
    {
        $this->destructive = true;

        return $this->confirm($title, $description, $confirmLabel);
    }

    /** A confirm dialog without the danger styling. */
    public function confirm(string $title, string $description, string $confirmLabel = 'Confirm'): self
    {
        $this->confirmTitle = $title;
        $this->confirmDescription = $description;
        $this->confirmLabel = $confirmLabel;

        return $this;
    }

    /** @param  Closure(Builder, int): mixed|Closure(Model): mixed  $handler */
    public function handle(Closure $handler): self
    {
        $this->handler = $handler;

        return $this;
    }

    /** @param  Closure(Model): string  $url */
    public function href(Closure $url): self
    {
        $this->url = $url;

        return $this;
    }

    /** @param  Closure(Model): bool  $when */
    public function visible(Closure $when): self
    {
        $this->visibleWhen = $when;

        return $this;
    }

    public function icon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function needsConfirmation(): bool
    {
        return $this->confirmTitle !== null;
    }

    public function availableFor(Model $row): bool
    {
        return $this->visibleWhen === null || (bool) ($this->visibleWhen)($row);
    }

    /** @return array<string, string> */
    public function describe(): array
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'kind' => $this->kind,
            'ability' => $this->ability ?? '',
            'confirm' => $this->needsConfirmation() ? ($this->destructive ? 'destructive' : 'yes') : 'no',
            'runs' => $this->url ? 'link' : ($this->handler ? 'handler' : 'none'),
            'per row' => $this->visibleWhen ? 'visible closure' : '',
        ];
    }
}
