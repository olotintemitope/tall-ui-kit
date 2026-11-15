{{-- Platform fee cell on the workbench /tables page (super admin only, the column's ability is *). --}}
<x-ui.money lean :value="$row->fee" :currency="$row->currency" />
