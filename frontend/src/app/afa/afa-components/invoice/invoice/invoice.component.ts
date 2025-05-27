import { CommonModule, CurrencyPipe, DatePipe, formatDate } from '@angular/common';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Component } from "@angular/core";
import { FormControl, FormGroup, FormsModule } from '@angular/forms';
import { SpinnerComponent } from '../../../../shared/spinner/spinner.component';
import {
  AddEvent,
  CancelEvent,
  EditEvent,
  GridDataResult,
  KENDO_GRID,
  SaveEvent,
} from "@progress/kendo-angular-grid";

import { saveAs } from 'file-saver';
import { filePdfIcon, plusIcon, exportIcon, pencilIcon, saveIcon, checkIcon, xIcon, cancelIcon, trashIcon, eyeIcon, searchIcon, filterIcon, filterClearIcon } from '@progress/kendo-svg-icons';
import { Router, RouterLink } from '@angular/router';
import { KENDO_SVGICON } from '@progress/kendo-angular-icons';
import { KENDO_BUTTONS } from '@progress/kendo-angular-buttons';
import { environment } from '../../../../../environments/environment.development';
import { KENDO_ICON } from '@progress/kendo-angular-icons';
import { ReactiveFormsModule } from '@angular/forms';
import { FilterExpression } from '@progress/kendo-angular-filter';
import { KENDO_DIALOGS } from '@progress/kendo-angular-dialog';
import { NotificationServiceWrapper } from '../../../../shared/notification.service';
import { KENDO_DATEPICKER } from '@progress/kendo-angular-dateinputs';

@Component({
  selector: 'app-invoice',
  imports: [CommonModule, FormsModule,KENDO_DIALOGS,KENDO_GRID ,KENDO_SVGICON, KENDO_ICON, KENDO_BUTTONS, SpinnerComponent, CurrencyPipe, ReactiveFormsModule,RouterLink,KENDO_DATEPICKER,DatePipe],
  templateUrl: './invoice.component.html',
})
export class InvoiceComponent {
  //Icons
  public fileIcon = filePdfIcon;
  public exportIcon = exportIcon;
  public pencilIcon = pencilIcon;
  public saveIcon = saveIcon;
  public checkIcon = checkIcon;
  public xIcon = xIcon;
  public cancelIcon = cancelIcon;
  public trashIcon = trashIcon;
  public eyeIcon = eyeIcon;
  public addIcon = plusIcon;
  public searchIcon = searchIcon;
  public filterIcon = filterIcon;
  public filterClearIcon = filterClearIcon;


  public filters: FilterExpression[] = [
    {
      field: "invoice_number",
      title: "Invoice Number",
      editor: "string",
    },
    {
      field: "assignment_id",
      title: "Assignment Id",
      editor: "string",
    },
    {
      field: "contractor_name",
      title: "Contractor Name",
      editor: "string"
    },
    {
      field: "people_name",
      title: "People Name",
      editor: "string"
    },
    {
      field: "due_date",
      title: "Due Date",
      editor: "date"
    }
  ]
  public onFilter: boolean = false;
  public filterForm!: FormGroup;


  public editRowIndex!: number;
  public dialogOpen:boolean=false;
  public invoiceData: GridDataResult = { data: [], total: 0 };
  public lastValue!:any;

  public inAddMode = false;
  public inEditMode = false;
  public date!: any;
  public isOpen!: boolean;
  public deleteIsOpen:boolean=false;
  public deleteRow:any='';
  public companyId!:Number;

  public pageSize: number = 10;
  public skip = 0;
  public isLoading!: boolean;
  public baseUrl = environment.baseUrl;
  public restrictDate!:Date;

  public companyName!:any;
  public invoiceNumber!: any;
  public assignmentID!: number;
  public assignmentIdList!: any;
  public assignmentDetails: any
    = [{ 'name': '', 'customer_name': '' }];
  public form!: any;

  constructor(private http: HttpClient, private router: Router, private notify:NotificationServiceWrapper) { }

  ngOnInit() {
    this.companyId=Number(sessionStorage.getItem('companyId'));
     this.isLoading = true;
    this.filterForm = new FormGroup({
      "invoice_number": new FormControl(null),
      "assignment_id": new FormControl(null),
      "contractor_name": new FormControl(null),
      "people_name": new FormControl(null),
      "due_date":new FormControl(null)
    });
    this.restrictDate=new Date();
    this.getInvoiceData();
  }
  //fetch initial grid Data
  private getInvoiceData() {
    const page = this.skip / this.pageSize + 1;
    const form = this.filterForm.value;
    let params = new HttpParams().set('page', page.toString())
      .set('pageSize', this.pageSize
        .toString())

    
    this.http.post(`${this.baseUrl}/getInvoice/${this.companyId}`, form, { params }).subscribe({
      next: (res: any) => {
        console.log(res);
        setTimeout(() => {
          this.isLoading = false;
        }, 700);

        this.invoiceData = { data: res.data, total: res.total };
        this.assignmentIdList = res.list;
        this.companyName=res.company;
      }, error: (err: any) => {
        console.log('error in fetching data', err);
      }
    });
  }

  public toggle() {
    this.isOpen = !this.isOpen;
  }

  public deleteToggle(args:any=''){
    this.deleteIsOpen=!this.deleteIsOpen;
    this.deleteRow=args.dataItem;
  }

  public dialogToggle(m:Number){
    this.dialogOpen=!this.dialogOpen;
    if (m==1){
      this.router.navigate(["afa/invoice/details/",this.invoiceNumber])
      this.invoiceNumber=null;
    }
  }
  public onFilterValue() {
    this.onFilter = !this.onFilter;
  }

  public onSubmit() {
    this.isLoading = true;
    this.getInvoiceData();
  }

  public onClear() {
    this.isLoading = true;
    this.onFilter = false;
    this.filterForm.reset();
    this.getInvoiceData();
  }


  //Mail Invoice
  mailInvoice(args: any) {
    this.isLoading=true;
    console.log(args.invoice_number);
    this.http.get(`${this.baseUrl}/sendInvoice/${this.companyId}/${args.invoice_number}`).subscribe({
      next: (res: any) => {
        this.getInvoiceData();
        this.notify.showSuccess('Invoice have been sent to email');

      },
      error: (err: any) => {
        console.log(err);
      }
    })

  }

  public pageChange(event: any) {
    this.skip = event.skip;
    this.pageSize = event.take;
    this.getInvoiceData();
  }

  //to Add new invoice entry
  addInvoiceRow(event: AddEvent) {
    if((this.invoiceData.data.length)>=this.pageSize){
    this.lastValue = this.invoiceData.data.pop();}
    // event.sender.closeRow(this.editRowIndex);
    this.inAddMode = true;
    console.log(event);
    const group = new FormGroup({
      due_date: new FormControl(),
      status: new FormControl('Draft'),
      total_pay: new FormControl(0)
    });


    event.sender.addRow(group);
  }

  //edit Invoice Row
  editInvoice(event: EditEvent) {
    this.inEditMode = true;
    event.sender.closeRow(this.editRowIndex);
    console.log(event);
    this.editRowIndex = event.rowIndex;
    const group = new FormGroup({
      due_date: new FormControl(new Date(event.dataItem.due_date))

    })
    event.sender.editRow(event.rowIndex, group);

  }

  //to add/update an invoice
  saveInvoice(args: SaveEvent) {
    this.isLoading=true;
    let data = args.dataItem;
    this.inAddMode = false;
    console.log(args);


    data.due_date = formatDate(args.formGroup.value.due_date, 'yyyy-MM-dd','en-US', 'IST');
    console.log(data);
    if (args.rowIndex == -1) {
      data.assignment_id = this.assignmentID;
      data.people_name = this.assignmentDetails.name;
      data.customer_name = this.assignmentDetails.customer_name;
      data.type='add';
      this.invoiceData.data.unshift(data);
      this.http.post(`${this.baseUrl}/addInvoice/${this.companyId}`, data).subscribe({
        next: (res: any) => {
          console.log(res);
          console.log('added successfully');
          if(res['status']==211){
            this.invoiceNumber=res['invoice_number'];
            this.dialogToggle(0);
          }
          this.getInvoiceData();
        },

        error: (err: any) => {
          console.log('error', err['error']);
          if(err['error']['status']==302){
            console.log('err');
          }
        }
      })
      this.assignmentDetails= [{ 'name': '', 'customer_name': '' }];

    }
    else {
      this.invoiceData.data[args.rowIndex] = data;
      this.http.post(`${this.baseUrl}/updateInvoice/${this.companyId}`, data).subscribe({
        next: (res: any) => {
          console.log('updated successfully');
          this.getInvoiceData();
        }, error: (err: any) => {
          console.log('error=>', err);
        }
      });

    }

    args.sender.closeRow(args.rowIndex);

    this.inAddMode = false;
    this.inEditMode = false

  }

  //to route to invoice line items page
  viewDetails(args: any) {
    console.log(args.invoice_number);
    this.router.navigate(["afa/invoice/details/", args.invoice_number]);
  }



  refreshGrid() {
    this.invoiceData.data = [...this.invoiceData.data];
  }

  getAssignment(args: any) {
    this.assignmentID = args.target.value;
    this.http.get(`${this.baseUrl}/invoice/getAssignment/${this.assignmentID}`).subscribe({
      next: (res: any) => {
        console.log(res['customer_name']);
        console.log(res);
        this.assignmentDetails = res;
        console.log(this.assignmentDetails);


      },
      error: (err: any) => {
        console.log(err);
      }
    })
  }

  cancelNewInvoice(event: CancelEvent) {
    if(this.lastValue){
    this.invoiceData.data.push(this.lastValue);}
    this.inAddMode = false;
    this.inEditMode = false;
    event.sender.closeRow(event.rowIndex);
    this.assignmentDetails= [{ 'name': '', 'customer_name': '' }];

  }

  removeInvoiceRow() {

    this.isLoading=true;
    this.deleteIsOpen=false;
    this.http.get(`${this.baseUrl}/deleteInvoice/row/${this.companyId}/${this.deleteRow.invoice_number}`).subscribe({
      next: (res: any) => {
        console.log('invoice row deleted successfully', res);
        this.notify.showSuccess('Invoice Deleted Successfully');

      },
      error: (err: any) => {
        console.log(err);
      }
    });
    this.getInvoiceData();

  }

  //Download invoice as PDF
  downloadPDF(invoiceNumber: number) {
    console.log(invoiceNumber);
    this.notify.showSuccess(`Downloading ${invoiceNumber}`);
    this.http.get(`${this.baseUrl}/invoice/download/${invoiceNumber}`, { responseType: 'blob' }).subscribe(
      {
        next: (res: any) => {
          const file=new Blob([res],{type:'application/pdf'})
          saveAs(file, `invoice_${invoiceNumber}.pdf`)
        },
        error: (err: any) => {
          console.log(err);
        }
      }
    )
  }
}
