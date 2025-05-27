<?php

namespace App\Services;

use App\Models\Assignments;
use App\Models\Companies;
use App\Models\Customers;
use App\Models\InvoiceDetails;
use App\Models\Invoices;
use App\Models\Peoples;
use App\Models\Timesheet;
use App\Models\TimesheetDetails;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoiceService
{
    public function getInvoiceData($request)
    {

        $companyId = $request['companyId'] ?? null;
        $pageSize = $request['pageSize'] ?? 10;
        $page = $request['page'] ?? 1;

        $query = DB::table('invoices')
            ->join('assignments', 'invoices.assignment_id', '=', 'assignments.assignment_id')
            ->join('customers', 'assignments.customer_id', '=', 'customers.customer_id')
            ->join('peoples', 'assignments.people_id', '=', 'peoples.people_id')
            ->where('invoices.company_id', $companyId)
            ->where('invoices.is_deleted', 0)
            ->select(
                'invoices.invoice_number',
                'invoices.assignment_id',
                'invoices.due_date',
                'invoices.status',
                'customers.customer_name',
                'peoples.name as people_name',
                'invoices.total_pay'
            );
        Log::info($request['search']);
        if ($request['search'] !== null) {
            $query->where(function ($q) use ($request) {
                if (!empty($request['search']['invoice_number'])) {
                    $q->Where('invoices.invoice_number', 'like', '%' . $request['search']['invoice_number'] . '%');
                }

                if (!empty($request['search']['customer_name'])) {
                    $q->Where('customers.customer_name', 'like', '%' . $request['search']['customer_name'] . '%');
                }

                if (!empty($request['search']['people_name'])) {
                    $q->Where('people_name', 'like', '%' . $request['search']['people_name'] . '%');
                }

                if (!empty($request['search']['assignment_id'])) {
                    $q->Where('invoices.assignment_id', 'like', '%' . $request['search']['assignment_id'] . '%');
                }
                if (!empty($request['search']['due_date'])) {
                    $date = Carbon::parse($request['search']['due_date'])->setTimezone('Asia/Kolkata')->format('Y-m-d');
                    $q->Where('invoices.due_date', $date);
                }
            });
        }



        return $query->orderBy('invoices.updated_at', 'desc')
            ->paginate($pageSize, ['*'], 'page', $page);
    }


    public function getAssignmentDetails($assignmentId)
    {
        $assignment = Assignments::where('assignment_id', $assignmentId)->first();

        $assignmentDetails = [
            'name' => $assignment->peoples->name ?? null,
            'customer_name' => $assignment->customers->customer_name ?? null,
        ];


        return $assignmentDetails;

    }
    public function addInvoice($companyId, $request)
    {
        Log::info($request);
        Log::info('habdha');
        $orgID = 2;
        $invoice = Invoices::where('invoice_number', $request->invoice_number)->first();
        if (!$invoice) {
            if ($request->type == 'add') {
                $invoice = Invoices::where('assignment_id', $request->assignment_id)->where('due_date', $request->due_date)->first();
                Log::info($invoice);
                if ($invoice) {
                    return ['status' => 211, 'message' => 'invoice with same period end date already exists', 'invoice_number' => $invoice['invoice_number']];
                }
            }

            $assignment = Assignments::select('customer_id', 'people_id')
                ->where('assignment_id', $request->assignment_id)
                ->first();

            if ($assignment) {
                $invoice = new Invoices();
                $invoice->assignment_id = $request->assignment_id;
                $invoice->people_id = $assignment->people_id;
                $invoice->customer_id = $assignment->customer_id;
                $invoice->company_id = $companyId;
                $invoice->organization_id = $orgID;
                $invoice->due_date = $request->due_date;
                $invoice->type = 'manual';
                $invoice->save();
            }
            return ['status' => 200, 'message' => 'Invoice Created Successfully'];

        } else {

            $invoice = Invoices::where('invoice_number', $request->invoice_number)->
                update(['due_date' => $request->due_date]);
            Log::info('updated successfully');
            return ['status' => 200, 'message' => 'Invoice Updated Successfully'];
        }
    }


    public function getAssignmentIDList($companyId)
    {
        $assignmentIdList = Assignments::where('company_id', $companyId)
            ->pluck('assignment_id')
            ->where('status','=',1)
            ->where('is_deleted','=',0)
            ->toArray();

        return $assignmentIdList;
    }

    public function deleteInvoice($companyId, $invoiceNumber)
    {
    	Invoices::where('invoice_number', $invoiceNumber)
        ->update([
            'is_deleted' => 1,
            'updated_at' => now()
        ]);

    	return ;
    }

    public function fetchCompanyName($companyId){
        return Companies::where('company_id',$companyId)->value('company_name');
    }

    public function invoiceDetailsEmail($invoiceNumber)
    {



        $invoice=InvoiceDetails::select(
            'invoices.invoice_number',
            'invoices.assignment_id',
            'invoice_details.hours_worked',
            'invoice_details.hourly_pay',
            'invoice_details.total_pay',
            'invoice_details.description'
        ) ->join('invoices', 'invoice_details.invoice_id', '=', 'invoices.invoice_id')
        ->where('invoices.invoice_number', $invoiceNumber)
        ->get();

        $totalPay=Invoices::where('invoice_number','=',$invoiceNumber)->value('total_pay');

        $customer=Customers::join('invoices','invoices.customer_id','=','customers.customer_id')
        ->where('invoices.invoice_number',$invoiceNumber)
        ->select('customers.*')
        ->first();
        $company=Companies::join('invoices','invoices.company_id','=','companies.company_id')
        ->where('invoices.invoice_number',$invoiceNumber)
        ->select('companies.*')
        ->first();

        $people=Peoples::join('invoices','invoices.people_id','=','peoples.people_id')
                ->where('invoices.invoice_number',$invoiceNumber)
                ->select('peoples.*',)
                ->first();

        return ['invoice'=>['invoiceDetails'=>$invoice,'total'=>$totalPay],'infos'=>['customer'=>$customer,'company'=>$company,'people'=>$people]];
    }

    public function fetchEmail($invoiceNumber)
    {

        Invoices::where('invoice_number',$invoiceNumber)->
                  where('status','Draft')->
                  update(['status'=>'Sent']);

        $email = DB::table('invoices')->
            join('customers', 'invoices.customer_id', '=', 'customers.customer_id')->
            where('invoices.invoice_number', $invoiceNumber)->
            value('customers.email');


        return $email;
    }

    public function timesheetToInvoice($timesheetId)
    {

        $timesheet = Timesheet::select('invoice_sent')->where('timesheet_id', $timesheetId)->value('invoice_sent');

        if ($timesheet == 0) {
            $timesheetDetails = TimesheetDetails::where('timesheet_id', $timesheetId)->where('is_deleted', 0)->
                whereNot('assignment_id', NULL)->get();
        }

        $grouped = collect($timesheetDetails)->groupBy('assignment_id');

        DB::beginTransaction();
        try {
            foreach ($grouped as $assignmentId => $entries) {
                $assignment = Assignments::select('people_id', 'company_id', 'organization_id', 'customer_id')->where('assignment_id', $assignmentId)->first();
                $invoice = Invoices::create([
                    'assignment_id' => $assignmentId,
                    'customer_id' => $assignment->customer_id,
                    'people_id' => $assignment->people_id,
                    'timesheet_id' => $timesheetId,
                    'organization_id' => $assignment->organization_id,
                    'company_id' => $assignment->company_id,
                    'total_pay' => 0,
                    'type' => 'automatic',
                    'status' => 'Draft',
                    'due_date' => now()->addDays(7),
                    'invoice_date' => now()
                ]);

                $total_amount = 0;
                foreach ($entries as $entry) {
                    InvoiceDetails::create([
                        'invoice_id' => $invoice->invoice_id,
                        'assignment_id' => $assignmentId,
                        'hours_worked' => $entry['hours_worked'],
                        'hourly_pay' => $entry['hourly_pay'],
                        'total_pay' => $entry['total_pay'],
                        'description'=>$entry['description'],
                        'organization_id' => $assignment->organization_id,
                        'company_id' => $assignment->company_id,
                        'timesheet_detail_id' => $entry->timesheet_detail_id
                    ]);
                    $total_amount += $entry['total_pay'];
                }

                $invoice->update(['total_pay' => $total_amount]);
                Timesheet::where('timesheet_id', $timesheetId)->update(['invoice_sent' => 1]);
                TimesheetDetails::where('timesheet_id', $timesheetId)->where('assignment_id', Null)->update(['is_deleted' => 1]);

                DB::commit();

            }
            return ['status' => true, 'message' => 'Invoice created successfully'];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
}




?>