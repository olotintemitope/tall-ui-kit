<?php

namespace Workbench\App\DataTable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * An invented customer for /admin/ui/tables (see DemoTables).
 */
class DemoCustomer extends Model
{
    public $timestamps = false;

    protected $connection = DemoTables::CONNECTION;

    protected $table = 'demo_customers';

    protected $guarded = [];

    public function getConnection()
    {
        DemoTables::ensureSeeded();

        return parent::getConnection();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(DemoPayment::class, 'customer_id');
    }
}
