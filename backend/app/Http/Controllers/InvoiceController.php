<?php

namespace App\Http\Controllers;

use App\Mail\sendInvoiceMail;
use App\Services\InvoiceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Mpdf\Mpdf;


class InvoiceController extends Controller
{
    public $invoiceService;
    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    public function getInvoiceData($companyId, Request $request)
    {

        $pageSize = json_decode($request->input('pageSize', 10));
        $page = json_decode($request->input('page', 1));

        $invoice = $this->invoiceService->getInvoiceData(['companyId' => $companyId, 'pageSize' => $pageSize, 'page' => $page, 'search' => $request]);

        $assignmentIdList = $this->invoiceService->getAssignmentIDList($companyId);

        $companyName = $this->invoiceService->fetchCompanyName($companyId);



        Log::info($invoice);
        return response()->json(['data' => $invoice->items(), 'list' => $assignmentIdList, 'total' => $invoice->total(), 'company' => $companyName]);
    }



    public function getAssignmentDetails($assignmentId)
    {
        $assignmentDetails = $this->invoiceService->getAssignmentDetails($assignmentId);

        return response()->json($assignmentDetails);
    }

    public function addInvoice($companyId, Request $request)
    {
        $response = $this->invoiceService->addInvoice($companyId, $request);
        Log::info('add invoice');
        return response()->json($response, $response['status']);

    }

    public function sendInvoiceEmail($companyId, $invoiceNumber)
    {
        $response = $this->invoiceService->invoiceDetailsEmail($invoiceNumber);
        $invoice = $this->utf8ize($response['invoice']);

        $invoiceInfo = $this->utf8ize($response['infos']);

        $email = $this->invoiceService->fetchEmail($invoiceNumber);

        Mail::to($email)->
            send(new sendInvoiceMail($invoice, $invoiceInfo));

        return response()->json(['message' => 'Invoice sent to email']);
    }

    public function timesheetToInvoice($timesheetId)
    {
        $this->invoiceService->timesheetToInvoice($timesheetId);

        return response()->json(['status' => 'success', 'message' => 'New Invoice created successfully']);
    }


    public function deleteInvoice($invoiceNumber, $companyId)
    {
        $this->invoiceService->deleteInvoice($invoiceNumber, $companyId);
        return response()->json(['status' => 'success', 'message' => 'Deleted Successfully']);

    }
    public function downloadPDF($invoiceNumber, Request $request)
    {
        $response = $this->invoiceService->invoiceDetailsEmail($invoiceNumber);
        if (!$response) {
            return response()->json(['message' => 'invoice number not present', 'status' => 400]);
        }
        $invoice = $this->utf8ize($response['invoice']);

        $invoiceInfo = $this->utf8ize($response['infos']);
        $invoiceDetails = $invoice['invoiceDetails'];
        $total = $invoice['total'];
        $customer = $invoiceInfo['customer'];
        $company = $invoiceInfo['company'];
        $people = $invoiceInfo['people'];
        $date = Carbon::now('Asia/Kolkata')->format('d-m-Y h:i A');
        $logoPath = public_path('paypal.png'); 
        $logoBase64 = base64_encode(file_get_contents($logoPath));
        $logoSrc = 'data:image/png;base64,' . $logoBase64;
        $html = view('pdf.invoice', [
            'invoiceDetails' => $invoiceDetails,
            'customer' => $customer,
            'company' => $company,
            'people' => $people,
            'date' => $date,
            'total' => $total
            ,
            'logo' => $logoSrc
        ])->render();

        $mpdf = new Mpdf();

        $mpdf->SetProtection(['copy', 'print'], 'user123', null);

        $mpdf->WriteHTML($html);

        return response($mpdf->Output('', 'S'), 200)
            ->header('Content-Type', 'application/pdf');

    }

    protected function utf8ize($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->utf8ize($value);
            }
        } elseif (is_object($data)) {
            foreach ($data as $key => $value) {
                $data->$key = $this->utf8ize($value);
            }
        } elseif (is_string($data)) {
            return mb_convert_encoding($data, 'UTF-8', 'UTF-8');
        }

        return $data;
    }

}
