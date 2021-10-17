<?php

namespace App\Models\Concerns;

use App\Models\Transaction;

trait InvoiceTrait
{
    /**
     * The "booting" method of the model.
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            $current_prefix = 'INV';
            $record = Transaction::orderBy('id', 'desc')->first();
            $nextInvoiceNumber = 1;
            if ($record) {
                $nextInvoiceNumber = ($record->id + 1);
            }
            $invoice->invoice = $current_prefix . '-' . str_pad(
                $nextInvoiceNumber,
                5, // as per your requirement.
                '0',
                STR_PAD_LEFT
            );
        });
    }
}
