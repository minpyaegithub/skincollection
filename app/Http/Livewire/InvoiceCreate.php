<?php

namespace App\Http\Livewire;

use App\Models\Clinic;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Pharmacy;
use App\Models\Sale;
use App\Models\Treatment;
use App\Models\TreatmentPackage;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class InvoiceCreate extends Component
{
    // Base Invoice Properties
    public $invoice_no;
    public $patient_id;
    public $invoice_date;
    public $type = 'treatment'; // 'treatment' or 'sale'

    // Collections for dropdowns
    public $patients = [];
    public $treatments = [];
    public $pharmacies = [];

    // Dynamic lines
    public $treatmentLines = [];
    public $saleLines = [];

    // Totals
    public $grand_total = 0;

    protected $rules = [
        'patient_id' => 'required_if:type,treatment',
        'invoice_date' => 'required|date',
        'invoice_no' => 'required|unique:invoices,invoice_no',
        'type' => 'required|in:treatment,sale',
        'treatmentLines' => 'required_if:type,treatment|array|min:1',
        'treatmentLines.*.select_treatment' => 'required|distinct',
        'saleLines' => 'required_if:type,sale|array|min:1',
        'saleLines.*.select_pharmacy' => 'required|distinct',
    ];

    public function mount()
    {
        $this->patients = Patient::orderBy('first_name')->get();
        $this->treatments = Treatment::orderBy('name')->get();
        $this->pharmacies = Pharmacy::orderBy('name')->get();
        $this->invoice_date = today()->format('Y-m-d');
        $this->invoice_no = $this->generateInvoiceNumber();

        // Start with one empty line based on default type
        $this->addTreatmentLine();
    }

    public function updatedType()
    {
        // Reset lines when type changes
        $this->treatmentLines = [];
        $this->saleLines = [];
        if ($this->type === 'treatment') {
            $this->addTreatmentLine();
        } else {
            $this->addSaleLine();
        }
        $this->calculateTotals();
    }

    public function generateInvoiceNumber()
    {
        $clinic = Clinic::find(session('clinic_id'));
        $prefix = $clinic ? $clinic->prefix : 'INV';

        $latest = Invoice::withoutGlobalScopes()->where('clinic_id', $clinic->id ?? null)->latest('id')->first();

        if (!$latest) {
            return $prefix . '0001';
        }

        $string = preg_replace("/[^0-9\.]/", '', $latest->invoice_no);

        return $prefix . sprintf('%04d', $string + 1);
    }

    // Treatment Line Methods
    public function addTreatmentLine()
    {
        $this->treatmentLines[] = ['select_treatment' => '', 'price' => 0, 'discount' => 0, 'discount_type' => 'fixed', 'sub_total' => 0];
    }

    public function removeTreatmentLine($index)
    {
        unset($this->treatmentLines[$index]);
        $this->treatmentLines = array_values($this->treatmentLines);
        $this->calculateTotals();
    }

    public function updatedTreatmentLines($value, $key)
    {
        // key is like "0.select_treatment"
        $parts = explode('.', $key);
        $index = $parts[0];

        if ($parts[1] === 'select_treatment' && !empty($value)) {
            $treatment = Treatment::find($value);
            if ($treatment) {
                $this->treatmentLines[$index]['price'] = $treatment->price;
            }
        }
        $this->calculateTotals();
    }

    // Sale Line Methods
    public function addSaleLine()
    {
        $this->saleLines[] = ['select_pharmacy' => '', 'qty' => 1, 'price' => 0, 'discount' => 0, 'discount_type' => 'fixed', 'sub_total' => 0];
    }

    public function removeSaleLine($index)
    {
        unset($this->saleLines[$index]);
        $this->saleLines = array_values($this->saleLines);
        $this->calculateTotals();
    }

    public function updatedSaleLines($value, $key)
    {
        $parts = explode('.', $key);
        $index = $parts[0];

        if ($parts[1] === 'select_pharmacy' && !empty($value)) {
            $pharmacy = Pharmacy::find($value);
            if ($pharmacy) {
                $this->saleLines[$index]['price'] = $pharmacy->selling_price ?? 0;
            }
        }
        $this->calculateTotals();
    }

    public function calculateTotals()
    {
        $total = 0;
        $lines = $this->type === 'treatment' ? $this->treatmentLines : $this->saleLines;
        $collection = collect($lines);

        foreach ($collection as $i => &$line) {
            $price = floatval($line['price'] ?? 0);
            $qty = intval($line['qty'] ?? 1);
            $discount = floatval($line['discount'] ?? 0);
            $subtotal = $price * $qty;

            if ($line['discount_type'] === 'fixed') {
                $subtotal -= $discount;
            } else { // percentage
                $subtotal -= ($subtotal * $discount / 100);
            }
            $line['sub_total'] = $subtotal;
            $total += $subtotal;
        }

        if ($this->type === 'treatment') {
            $this->treatmentLines = $collection->all();
        } else {
            $this->saleLines = $collection->all();
        }

        $this->grand_total = $total;
    }

    public function saveInvoice()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            $lines = $this->type === 'treatment' ? $this->treatmentLines : $this->saleLines;

            foreach ($lines as $line) {
                Invoice::create([
                    'invoice_no' => $this->invoice_no,
                    'patient_id' => $this->patient_id,
                    'created_time' => $this->invoice_date,
                    'type' => $this->type,
                    'treatment_id' => $line['select_treatment'] ?? null,
                    'phar_id' => $line['select_pharmacy'] ?? null,
                    'qty' => $line['qty'] ?? 1,
                    'price' => $line['price'],
                    'discount' => $line['discount'],
                    'discount_type' => $line['discount_type'],
                    'sub_total' => $line['sub_total'],
                ]);
            }

            DB::commit();
            session()->flash('success', 'Invoice Created Successfully.');
            return redirect()->route('invoices.index');
        } catch (\Throwable $th) {
            DB::rollBack();
            session()->flash('error', $th->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.invoice-create');
    }
}