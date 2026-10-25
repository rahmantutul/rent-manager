<?php

namespace App\Events;

use App\Models\Invoice;

class InvoiceCreated
{
    public function __construct(public Invoice $invoice) {}
}
