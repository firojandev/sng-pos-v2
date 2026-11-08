<?php

namespace Modules\Product\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * A shop changes only its own catalogue records; the company's product bank
 * and the shared catalogue are changed by their owners.
 */
trait ChangesOwnCatalogueOnly
{
    protected function ensureOwnRecord(Model $record): void
    {
        abort_unless(
            $record->isEditableBy(auth()->user()),
            403,
            'এটি কোম্পানির পণ্য ব্যাংক বা শেয়ার্ড ক্যাটালগের; দোকান থেকে পরিবর্তন করা যায় না (This belongs to the product bank or shared catalogue and can\'t be changed from a shop)।'
        );
    }
}
