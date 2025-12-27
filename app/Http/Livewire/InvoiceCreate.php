<?php

namespace App\Http\Livewire;

use Livewire\Component;

class InvoiceCreate extends Component
{
    /**
     * @deprecated This component has been replaced by InvoicesManager.
     */
    public function render()
    {
        abort(410, 'InvoiceCreate component has been replaced. Use the InvoicesManager interface.');
    }
}