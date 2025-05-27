import { Component } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { KENDO_GRID } from '@progress/kendo-angular-grid';
import { cancelIcon, plusIcon, saveIcon, pencilIcon, checkIcon, trashIcon } from '@progress/kendo-svg-icons';
import { KENDO_ICON, KENDO_SVGICON } from '@progress/kendo-angular-icons';
import { FormGroup, FormControl, Validators, ReactiveFormsModule } from '@angular/forms';
import { CommonModule, NgIf } from '@angular/common';
import { KENDO_DIALOGS } from '@progress/kendo-angular-dialog';
import { KENDO_BUTTONS } from '@progress/kendo-angular-buttons';
import { CurrencyPipe } from '@angular/common';
import { environment } from '../../../../../environments/environment.development';
import { SpinnerComponent } from '../../../../shared/spinner/spinner.component';
import { NotificationServiceWrapper } from '../../../../shared/notification.service';

@Component({
  selector: 'app-invoice-details',
  imports: [KENDO_GRID, KENDO_DIALOGS, KENDO_SVGICON, ReactiveFormsModule, CommonModule, NgIf, KENDO_ICON,
    KENDO_BUTTONS, CurrencyPipe, SpinnerComponent, RouterLink],
  templateUrl: './invoice-details.component.html'
})
export class InvoiceDetailsComponent {
  // Icons
  public addIcon = plusIcon;
  public cancelIcon = cancelIcon;
  public saveIcon = saveIcon;
  public checkIcon = checkIcon;
  public pencilIcon = pencilIcon;
  public trashIcon = trashIcon;


  public pageSize: number = 10;
  public skip = 0;
  public isOpen = false;
  public invoiceDetailData!: any;
  public invoice!: any;
  public companyId: any = sessionStorage.getItem('companyId');
  public companyName: string = ''



  public baseUrl = environment.baseUrl;

  public invoiceNumber!: string;
  public isLoading!: boolean;
  public dialogStatus!: boolean;

  public rowIndex!: number;

  public form!: any;
  public data!: any;
  public isInDraft: boolean = true;

  constructor(private http: HttpClient, private router: Router, private route: ActivatedRoute,private notify:NotificationServiceWrapper) { }

  ngOnInit() {
    this.isLoading = true;
    this.invoiceNumber = this.route.snapshot.paramMap.get('invoiceNumber') || '';
    this.isInDraft = true;
    this.getGridData();
  }

  private getGridData() {
    const page = this.skip / this.pageSize + 1;
    let params = new HttpParams().set('page', page.toString()).set('pageSize', this.pageSize)
    this.http.get(`${this.baseUrl}/invoice/lineItem/${this.invoiceNumber}`, { params }).subscribe({
      next: (res: any) => {
        setTimeout(() => {
          this.isLoading = false;
        }, 700);
        this.invoiceDetailData = res.gridData;
        this.invoice = res.invoice;
        this.companyName = res.invoice.company_name;
        if (this.invoice.status != "Draft") 
          { this.isInDraft = false }

        console.log(res.invoice.status);
        this.form = new FormGroup({
          assignment_id: new FormControl(this.invoice.assignment_id),
          people_name: new FormControl(this.invoice.people_name),
          customer_name: new FormControl(this.invoice.customer_name),
          due_date: new FormControl(this.invoice.due_date),
          description: new FormControl('', Validators.required),
          hours_worked: new FormControl('', [
            Validators.required,
            Validators.pattern(/^\d+(\.\d{1,2})?$/)
          ]),
          hourly_pay: new FormControl('', [
            Validators.required,
            Validators.pattern(/^\d+(\.\d{1,2})?$/)
          ]),
          invoice_detail_number: new FormControl()
        })
        console.log(this.invoice);
      }, error: (err: any) => {
        console.log('error in invoice detail fetch', err);
        this.isLoading = true;
      }
    })

  }
  public pageChange(event: any) {
    this.skip = event.skip;
    this.pageSize = event.take;
    this.getGridData();
  }

  public toggle(arg = 0) {
    this.isOpen = !this.isOpen;
    console.log(arg);
    if (arg == 1) {
      this.form.get('hours_worked').reset();
      this.form.get('description').reset();
      this.form.get('hourly_pay').reset();
      this.form.get('invoice_detail_number').reset();
    }
  }
  addRow() {
    console.log('add row');
    this.dialogStatus = true;
    this.toggle();

  }
  deleteRow(event:any) {
        this.isLoading=true;
        this.http.get(`${this.baseUrl}/invoice/lineItem/delete/${event.dataItem.invoice_id}/
          ${event.dataItem.invoice_detail_id}`).subscribe({
          next:(res:any)=>{
              this.notify.showSuccess(res.data);
              this.getGridData()
          },error:(err:any)=>{
            this.notify.showError('err');
            this.getGridData();
          }
        }

        )
  }

  editRow(event: any) {
    console.log(event.dataItem);
    this.dialogStatus = false;
    this.form.patchValue({
      hourly_pay: event.dataItem.hourly_pay, hours_worked: event.dataItem.hours_worked,
      description: event.dataItem.description, invoice_detail_number: event.dataItem.invoice_detail_id
    })
    this.toggle();
  }

  public onSubmit(isAdd: boolean = true) {
    this.toggle();
    this.isLoading=true;
    this.data = this.form.value;
    this.data.invoice_number = this.invoice.invoice_number;
    console.log('add');
    console.log(isAdd);
    if (isAdd) {
      this.http.post(`${this.baseUrl}/invoice/addLineItem`, this.data).subscribe({
        next: (res: any) => {
          console.log(res);
          this.getGridData();
        },
        error: (err: any) => {
          console.log(err);
          this.notify.showError(err);
        }
      })
    }
    else {
      this.http.post(`${this.baseUrl}/invoice/addLineItem`, this.data).subscribe({
        next: (res: any) => {
          this.notify.showSuccess('Updated successfully');
          this.getGridData();
        },
        error: (err: any) => {
          console.log(err);
          this.notify.showError(err);
        }
      })
    }
    this.invoiceDetailData = [...this.invoiceDetailData];
  }



}
