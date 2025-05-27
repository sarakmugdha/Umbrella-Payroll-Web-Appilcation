<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoices;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
class Scheduler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:scheduler';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today=Carbon::today();

        $invoices = Invoices::whereIn('status', ['Draft', 'Sent'])
                   ->where('due_date', '<=', $today)
                   ->get();

        Log::info($invoices);
        foreach($invoices as $invoice){
                $invoice->status='Overdue';

                $invoice->save();
        }

        Log::info('Scheduler task ran at'.now());

    }
}
