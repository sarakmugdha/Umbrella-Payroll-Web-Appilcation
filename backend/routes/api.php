<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\AssignmentsController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\Auth\mfaController;
use App\Http\Controllers\PeopleController;
use App\Http\Controllers\TimeSheetController;
use App\Http\Controllers\TimesheetDetailsController;
use App\Http\Controllers\CompaniesController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InvoiceDetailsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CustomerController;



use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;




Route::get('/dashboard', function (Request $request) {
    return $request->user();
});

Route::get('/verifyemail', function (Request $request) {
    return response()->json(['message'=>'success'])->name('verifyemail');
});

Route::post('/forgot-pwd-setup',[AuthController::class,'forgotPassword']);

Route::post('/password-setup/{id}/{hash}',[AuthController::class,'passwordSetup'])->name('verifyemail');

Route::post('/login',[AuthController::class,'login']);
Route::get('/logout',[AuthController::class,'logout']);

//mfa routes
Route::get('generateQrCode',[mfaController::class,'generateQrCode']);
Route::post('verifyMfa',[mfaController::class,'verifyMFA']);


Route::middleware(['auth','role:1'])->group(function () {


//Organization Routes
Route::post('/storeOrganization',[OrganizationController::class,"store"]);
Route::post('/updateOrganization',[OrganizationController::class,"update"]);
Route::post('/deleteOrganization',[OrganizationController::class,"delete"]);
Route::post('/getOrganizationDetails',[OrganizationController::class,"getOrganizationDetails"]);
Route::post("/countOrganization",[OrganizationController::class,"countOrganization"]);
Route::post("/countActiveOrganization",[OrganizationController::class,"countActiveOrganization"]);

//User Routes
Route::post('/getOrgDetails',[OrganizationController::class,'getOrgDetails']);
Route::post('/getUserDetails',[UserController::class,'getUserDetails']);
Route::post('/addUser',[UserController::class,"addUser"]);
Route::post('/deleteUser',[UserController::class,"deleteUser"]);
Route::post('/updateUserStatus',[UserController::class,"updateUserStatus"]);

});


Route::middleware(['auth','role:2'])->group(function () {
// Timesheet Routes
Route::post('/insert',[TimeSheetController::class,'insertTimesheetIntoDb']);
Route::post('/retriveTimesheetData',[TimeSheetController::class,'retriveTimesheetData']);
Route::get('/deleteTimesheet/{id}',[TimeSheetController::class,'deleteTimesheet']);

// TimesheetDetails Route
Route::post('/retriveTimesheetDetails',[TimesheetDetailsController::class,'retriveTimesheetDetails']);
Route::post('/insertTimesheetDetails',[TimesheetDetailsController::class,'insertTimesheetDetails']);
Route::post('/extractCsvData',[TimeSheetDetailsController::class,'extractCsvData']);
Route::get('/deleteTimesheetDetail/{id}',[TimeSheetDetailsController::class,'deleteTimesheetDetail']);
Route::get('/assignmentDetails/{id}',[TimesheetDetailsController::class,'fetchAssignmentDetails']);
Route::get('/deleteUnmappedTimesheet/{id}',[TimeSheetDetailsController::class,'deleteUnmappedTimesheetDetails']);
Route::get('/downloadTimesheet/{id}',[TimesheetDetailsController::class,'downloadTImesheet']);


//People Routes
Route::post('/insertPeople',[PeopleController::class,'insertPeople']);
Route::post('/updatePeople',[PeopleController::class,'updatePeople']);
Route::post('/deletePeople',[PeopleController::class,'deletePeople']);
Route::post('/peopleDetails',[PeopleController::class,'getPeopleDetails']);
Route::post('/getCompaniesList',[CompaniesController::class,'getCompaniesList']);
Route::get("/getOrgLogo",[OrganizationController::class,"getOrgLogo"]);
Route::post('/getCompaniesList',[CompaniesController::class,'getCompaniesList']);


// Companies Routes
Route::post('/insertCompany',[CompaniesController::class,'insertCompany']);
Route::post('/updateCompanies',[CompaniesController::class,'updateCompanyDetails']);
Route::post('/deleteCompanies',[CompaniesController::class,'deleteCompany']);
Route::post('/companiesList',[CompaniesController::class,'CompanyList']);







//Assignments Routes
Route::post('/insertAssignment',[AssignmentsController::class,'insertAssignmentDetails']);
Route::post('/updateAssignment',[AssignmentsController::class,'updateAssignmentDetails']);
Route::post('/deleteAssignments',[AssignmentsController::class,'deleteAssignmentDetails']);
Route::post('/getAssignmentsDetails',[AssignmentsController::class,'getAssignmentDetails']);
Route::post('/getCustomerDetails',[AssignmentsController::class,'getCustomersDetails']);
Route::post('/getPeopleDetails',[AssignmentsController::class,'getPeopleDetailsofCompany']);
Route::post('/isTimesheetCreated',[AssignmentsController::class, 'getTimesheetCreationDetails']);

//Customer Routes
Route::post('/Create',[Customercontroller::class,"store"]);
Route::get('/CreateCompany',[Customercontroller::class,"display"]);
Route::post('/deleteCompany',[Customercontroller::class,'deleteData'])->name('deleteCompany');
Route::get('/editCompany', [Customercontroller::class, 'editData'])->name('editCompany');
Route::post('/updateCompany/{id}',[Customercontroller::class,'updateData'])->name('updateCompany');
Route::post('/getCustomerList',[Customercontroller::class,'getCustomersByCompanyId']);
Route::post('/getCardDetails',[AssignmentsController::class,'getCardDetails']);

//Invoice Routes
Route::post('/getInvoice/{companyId}',[InvoiceController::class,'getInvoiceData']);
Route::get('/invoice/download/{invoiceNumber}',[InvoiceController::class,'downloadPDF']);
Route::get('/invoice/getAssignment/{assignmentId}',[InvoiceController::Class,'getAssignmentDetails']);
Route::post('/addInvoice/{companyId}',[InvoiceController::class,'addInvoice']);
Route::post('/updateInvoice/{companyId}',[InvoiceController::class,'addinvoice']);
Route::get('/sendInvoice/{companyId}/{invoiceNumber}',[InvoiceController::class,'sendInvoiceEmail']);
Route::get('/deleteInvoice/row/{companyId}/{invoiceNumber}',[InvoiceController::class,'deleteInvoice']);
Route::get('/addInvoice/timesheet/{timesheetID}',[InvoiceController::class,'timesheetToInvoice']);

//InvoiceDetails Routes
Route::get('/invoice/lineItem/{invoiceNumber}',[InvoiceDetailsController::class,'getInvoiceDetails']);
Route::post('/invoice/addLineItem',[InvoiceDetailsController::class,'addLineItem']);
Route::get('/invoice/lineItem/delete/{invoiceId}/{invoiceDetailsID}',[InvoiceDetailsController::class,'deleteLineItem']);



//AFA Dashboard Routes
Route::post('/getDashboardDetails', [CompaniesController::class, 'getDashboardDetails']);
Route::post('/getAllChartDetails', [CompaniesController::class, 'ganttDetails']);

//company search
Route::post('/searchCompany', [AssignmentsController:: class, 'searchCompany']);

});


