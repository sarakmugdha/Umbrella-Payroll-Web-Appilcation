<?php

namespace App\Services;

use App\Models\Assignments;
use App\Models\InvoiceDetails;
use App\Models\Invoices;
use Illuminate\Support\Facades\Log;

class InvoiceDetailsService
{
    public function getInvoiceDetails($invoiceNumber, $request)
    {
        $pageSize = json_decode($request->input('pageSize', 10));
        $page = json_decode($request->input('page', 1));
        $data = InvoiceDetails::Join('invoices', 'invoices.invoice_id', '=', 'invoice_details.invoice_id')
            ->where('invoices.invoice_number', $invoiceNumber)
            ->where('invoice_details.is_deleted', 0)
            ->select('invoice_details.*')
            ->orderBy('updated_at', 'desc')


            ->paginate($pageSize, ['*'], 'page', $page);
        $invoice = Invoices::join('assignments', 'invoices.assignment_id', '=', 'assignments.assignment_id')
            ->join('companies', 'invoices.company_id', '=', 'companies.company_id')
            ->join('customers', 'assignments.customer_id', '=', 'customers.customer_id')
            ->join('peoples', 'assignments.people_id', '=', 'peoples.people_id')
            ->where('invoices.invoice_number', $invoiceNumber)
            ->where('invoices.is_deleted', 0)
            ->select(
                'invoices.invoice_number',
                'invoices.assignment_id',
                'invoices.due_date',
                'invoices.status',
                'customers.customer_name',
                'companies.company_name',
                'peoples.name as people_name',
                'invoices.total_pay'
            )->first();
        return [$data, $invoice];
    }

    public function addLineItem($request)
    {
        $invoice = Invoices::where('invoice_number', $request->invoice_number)->first();
        if ($request->invoice_detail_number == null) {
            $total_pay = $request->hourly_pay * $request->hours_worked;
            InvoiceDetails::create([

                'organization_id' => $invoice->organization_id,
                'company_id' => $invoice->company_id,
                'description' => $request->description,
                'hourly_pay' => $request->hourly_pay,
                'hours_worked' => $request->hours_worked,
                'total_pay' => $total_pay,
                'type' => 'manual',
                'invoice_id' => $invoice->invoice_id,
            ])->first();

            $invoice->total_pay += $total_pay;
            $invoice->save();
            Log::info('add invoice line item');
        } else {
            $total_pay = $request->hourly_pay * $request->hours_worked;
            InvoiceDetails::where('invoice_id', $invoice->invoice_id)
                ->where('invoice_detail_id', $request->invoice_detail_number)
                ->update([

                    'description' => $request->description,
                    'hourly_pay' => $request->hourly_pay,
                    'hours_worked' => $request->hours_worked,
                    'total_pay' => $total_pay,
                ]);

            $invoice->total_pay -= $total_pay;
            $invoice->save();

            Log::info('update line item');
        }

        return;
    }

    public function deleteLineItem($request, $invoiceId, $invoiceDetailId)
    {

        $detail = InvoiceDetails::where('invoice_detail_id', $invoiceDetailId)->first();
        if ($detail) {
            Invoices::where('invoice_id', $detail->invoice_id)->decrement('total_pay', $detail->total_pay);
        }

        InvoiceDetails::where('invoice_id', $invoiceId)
            ->where('invoice_detail_id', $invoiceDetailId)
            ->update(['is_deleted' => 1]);

            return;

    }
}






?>