<?php
  
namespace App\Http\Controllers;
  
use Illuminate\Http\Request;
use PDF;
use Illuminate\Support\Facades\DB;

class PDFController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function generateInvoicePDF(Request $request, $invoice_no)
    {
        //dd($invoice_no);
        $invoice_no = 'SKC0001';
        $query = "select pa.first_name, pa.last_name, pa.phone, treat.name treatment_name, inv.invoice_no, DATE_FORMAT(inv.created_time, '%d %M %Y') created_time, inv.price, inv.discount, inv.discount_type, inv.sub_total  from invoices inv LEFT JOIN patients pa ON inv.patient_id = pa.id LEFT JOIN treatments treat ON inv.treatment_id = treat.id where inv.invoice_no="."'$invoice_no'";
        $invoices = DB::select($query);
        $grand_total = 0;
        foreach($invoices as $invoice){
            $grand_total += $invoice->sub_total;
        }
        //dd($grand_total);
        $pdf = PDF::loadView('myPDF', compact('invoices', 'grand_total'));
        return $pdf->stream('invoice.pdf');
        //return $pdf->download('nicesnippets.pdf');
    }

    public function generateInvoice(Request $request, $invoice_no, $type){
        $query = '';
        if($type == 'treatment'){
            $query = "select pa.first_name, pa.last_name, pa.phone, treat.name treatment_name, inv.invoice_no, inv.type, DATE_FORMAT(inv.created_time, '%d %M %Y') created_time, inv.price, inv.discount, inv.discount_type, inv.sub_total  from invoices inv LEFT JOIN patients pa ON inv.patient_id = pa.id LEFT JOIN treatments treat ON inv.treatment_id = treat.id where inv.invoice_no="."'$invoice_no'";
        }else{
            $query = "select phar.name phar_name, inv.invoice_no, inv.qty, inv.type, DATE_FORMAT(inv.created_time, '%d %M %Y') created_time, inv.price, inv.discount, inv.discount_type, inv.sub_total  from invoices inv LEFT JOIN pharmacies phar ON inv.phar_id = phar.id where inv.invoice_no="."'$invoice_no'"; 
        }
 
        $invoices = DB::select($query);
        $grand_total = 0;
        foreach($invoices as $invoice){
            $grand_total += $invoice->sub_total;
        }
        return view('myPDF', compact('invoices','grand_total'));
    }

    public function saleInvoice(Request $request, $invoice_no){
        $query = "select phar.name phar_name, inv.invoice_no, inv.type, DATE_FORMAT(inv.created_time, '%d %M %Y') created_time, inv.price, inv.discount, inv.discount_type, inv.sub_total  from invoices inv LEFT JOIN pharmacies phar ON inv.patient_id = pa.id where inv.invoice_no="."'$invoice_no'";
        $invoices = DB::select($query);
        $grand_total = 0;
        foreach($invoices as $invoice){
            $grand_total += $invoice->sub_total;
        }
        return view('myPDF', compact('invoices','grand_total'));
    }
}