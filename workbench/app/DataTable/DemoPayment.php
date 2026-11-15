<?php

namespace Workbench\App\DataTable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An invented payment for /admin/ui/tables (see DemoTables).
 *
 * @property int $id
 * @property string $reference
 */
class DemoPayment extends Model
{
    public $timestamps = false;

    protected $connection = DemoTables::CONNECTION;

    protected $table = 'demo_payments';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'amount' => 'integer',
            'fee' => 'integer',
            'slots' => 'integer',
            'flagged' => 'boolean',
        ];
    }

    public function getConnection()
    {
        DemoTables::ensureSeeded();

        return parent::getConnection();
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(DemoCustomer::class, 'customer_id');
    }
}
