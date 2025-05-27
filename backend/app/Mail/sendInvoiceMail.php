<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Mpdf\Mdpf;
use Log;
use Mpdf\Mpdf;

class sendInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $invoiceDetails;
    public $customer;
    public $company;
    public $people;
    public $date;public $total;

    public function __construct($invoice,$invoiceInfo)
    {
        log::info('wecw');
        Log::info($invoice['invoiceDetails']);
        $this->invoiceDetails=$invoice['invoiceDetails'];   
        $this->total=$invoice['total'];
        $this->customer=$invoiceInfo['customer'];
        $this->company=$invoiceInfo['company'];
        $this->people=$invoiceInfo['people'];
 
    }


    public function build()
    {
        $this->date=Carbon::now('Asia/Kolkata')->format('d-m-Y h:i A');
        $logoPath = public_path('paypal.png'); // D:/xampp8.2/htdocs/project/public/paypal.png
$logoBase64 = base64_encode(file_get_contents($logoPath));
$logoSrc = 'data:image/png;base64,' . $logoBase64;

         $html = view('pdf.invoice', [
        'invoiceDetails' => $this->invoiceDetails,
        'customer' => $this->customer,
        'company' => $this->company,
        'people' => $this->people,
        'date' => $this->date,
        'total' => $this->total,'logo'=>$logoSrc
    ])->render();
    $mpdf = new Mpdf();

    $mpdf->SetProtection(['copy','print'], 'user123',null);
    $mpdf->WriteHTML($html);
    
        return $this->subject('Invoice for ...')
                    ->view('pdf.invoice',['invoiceDetails'=>$this->invoiceDetails,'customer'=>$this->customer,
                    'company'=>$this->company,'people'=>$this->people,'date'=>$this->date,'total'=>$this->total,'logo'=>$logoSrc])
                    ->attachData($mpdf->output('','S'), "invoice.pdf", [
                        'mime' => 'application/pdf',
                    ]);
    }
    

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invoice Mail',
        );
    }


}
