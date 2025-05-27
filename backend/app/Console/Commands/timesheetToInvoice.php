<?php

namespace App\Console\Commands;

use App\Models\Assignments;
use App\Models\InvoiceDetails;
use App\Models\Invoices;
use App\Models\Timesheet;
use App\Models\TimesheetDetails;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Log;


class timesheetToInvoice extends Command
{

    protected $signature = 'app:timesheet';


    protected $description = 'Command description';

    public function handle()
    {
        $today = Carbon::today();

        $timesheet = Timesheet::where('period_end_date', '<=', $today)->where('invoice_sent', 0)->where('is_deleted', 0)->get();



        foreach ($timesheet as $timesheets) {
            Log::channel('timesheet')->info($timesheets);
            if($timesheets->timesheet_count == 0){
                $timesheets->invoice_sent = 2;
                $timesheets->save();
                continue;
            }

            $this->addToInvoice($timesheets->timesheet_id);



        }

        Log::channel('timesheet')->info('Scheduler task ran at' . now());
    }



    public function addToInvoice($timesheetId)
    {



        $timesheetDetails = TimesheetDetails::where('timesheet_id', $timesheetId)->where('is_deleted', 0)->
            whereNot('assignment_id', NULL)->get();


        $grouped = collect($timesheetDetails)->groupBy('assignment_id');


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
                $invoiceDetails = InvoiceDetails::create([
                    'invoice_id' => $invoice->invoice_id,
                    'assignment_id' => $assignmentId,
                    'hours_worked' => $entry['hours_worked'],
                    'hourly_pay' => $entry['hourly_pay'],
                    'total_pay' => $entry['total_pay'],
                    'organization_id' => $assignment->organization_id,
                    'company_id' => $assignment->company_id,
                    'timesheet_detail_id' => $entry->timesheet_detail_id
                ]);
                $total_amount += $entry['total_pay'];
            }

            $invoice->update(['total_pay' => $total_amount]);



        }
        Timesheet::where('timesheet_id', $timesheetId)->update(['invoice_sent' => 1]);
        TimesheetDetails::where('timesheet_id', $timesheetId)->where('assignment_id', Null)->update(['is_deleted' => 1]);
    }
}
