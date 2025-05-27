import { CommonModule} from '@angular/common';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Component} from '@angular/core';
import { FormBuilder, FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { GridDataResult,GridModule, KENDO_GRID} from "@progress/kendo-angular-grid";
import{ KENDO_ICONS} from '@progress/kendo-angular-icons';
import { environment } from '../../../../environments/environment.development';
import {  KENDO_SVGICON } from '@progress/kendo-angular-icons';
import {  plusIcon, exportIcon, pencilIcon, saveIcon, checkIcon, xIcon, cancelIcon, trashIcon, eyeIcon} from '@progress/kendo-svg-icons';
import { InputsModule } from '@progress/kendo-angular-inputs';
import { DialogModule } from '@progress/kendo-angular-dialog';
import { KENDO_BUTTONS } from '@progress/kendo-angular-buttons';
import { NotificationServiceWrapper } from '../../../shared/notification.service';
import { Customer } from '../Model';
import { SpinnerComponent } from '../../../shared/spinner/spinner.component';
import { RouterModule } from '@angular/router';
import { FilterExpression } from '@progress/kendo-angular-filter';
import { SVGIcon, searchIcon, filterIcon, filterClearIcon } from "@progress/kendo-svg-icons";


@Component({
  selector: 'app-customer',
  imports: [KENDO_GRID,

    KENDO_ICONS,
    KENDO_SVGICON,
    CommonModule,
    ReactiveFormsModule,
    DialogModule,
    InputsModule,
    GridModule,
    KENDO_BUTTONS,
    SpinnerComponent,
    RouterModule ],
  templateUrl: './customer.component.html',
  styleUrl: './customer.component.css'
})

export class CustomerComponent {
  constructor( private http:HttpClient, private fb:FormBuilder, private notify:NotificationServiceWrapper ) { }

  formGroup: any;
  public addIcon =plusIcon;
  public exportIcon=exportIcon;
  public pencilIcon=pencilIcon;
  public saveIcon=saveIcon;
  public checkIcon=checkIcon;
  public isLoading!:boolean;
  public xIcon=xIcon;
  public cancelIcon=cancelIcon;
  public trashIcon=trashIcon;
  public eyeIcon=eyeIcon;
  public searchIcon: SVGIcon = searchIcon;
  public filterIcon: SVGIcon = filterIcon;
  public filterClearIcon: SVGIcon = filterClearIcon;
  public currentPage :number = 1;
  public pageSize: number = 5;
  public skip: number = 0;
  public company_details = [{
    company_name: null,
    company_logo: null,
    city: null,
    country: null,
    domain: null,
    email: null,
    phone_number: null
  }];
  private apiUrl = environment.apiUrl;
  public emailExists: boolean = false;
  gridData: GridDataResult = { data: [], total: 0};
  customerForm!: FormGroup;
  selectedCustomer: Customer | null=null;
  isDialogOpen = false;
  loading:boolean = true;
  public companyDetails:any;
  data: any;
  public id!:any;
  //backend filteration
  public filters: FilterExpression[] = [
    {
      field: "customer_name",
      title: "Customer Name",
      editor: "string"
    },
    {
      field: "email",
      title: "Email",
      editor: "string"
    },
    {
      field: "tax_no",
      title: "Tax No.",
      editor: "string"
    },
    {
      field: "address",
      title: "Address",
      editor: "string"
    },
    {
      field: "pincode",
      title: "Pincode",
      editor: "number"
    },
  ]
  public onFilter: boolean = false;
  public filterForm!: FormGroup;
  // delete confirmation
  public isDeleteDialogOpen: boolean = false;
  public deleteDataItem: any;

  getFromApi(){
    const currentPage = this.skip / this.pageSize + 1;
    const details = {
      page: currentPage,
      per_page: this.pageSize,
      id: this.id,
      filter: this.filterForm.value,
    }
    this.http.post<{data: Customer[], total: number}>(`${this.apiUrl}/getCustomerList`, details)
    .subscribe((response: any)=>{
      this.gridData = {
        data: response.data,
        total: response.total
      };
      setTimeout(() => {
        this.isLoading=false;
     },700);
    });
  }

    ngOnInit(){
    this.skip = 0;
    this.isLoading=true;
    this.id =sessionStorage.getItem('companyId');
    this.skip = 0;
    this.isLoading=true;

    this.filterForm = new FormGroup ({
      "customer_name": new FormControl(null),
      "email": new FormControl(null),
      "tax_no": new FormControl(null),
      "address" : new FormControl(null),
      "pincode" : new FormControl(null),
    });

    this.getFromApi();
    const companyId = {company_id: this.id};
    this.http.post(`${this.apiUrl}/getCardDetails`, companyId)
    .subscribe({
      next: (response: any) => {
        this.company_details = response;
      }
    });
  }

  openDialog(Customer?:Customer){
    this.selectedCustomer = Customer||null;
    this.emailExists = false;
    this.isDialogOpen = true;

    this.customerForm = this.fb.group({
      organization_id: [1],
      company_id:[Customer?.company_id||this.id],
      customer_id:[Customer?.customer_id],
      customer_name:[Customer?.customer_name||'',[Validators.required, Validators.pattern(/^[a-zA-Z ].*$/)]],
      email:[Customer?.email||'',[Validators.required,Validators.email]],
      tax_no:[Customer?.tax_no||'',[Validators.required,Validators.pattern(/^[0-9]{1,10}$/)]],
      address:[Customer?.address||'',[Validators.required]],
      state:[Customer?.state||'',[Validators.required, Validators.pattern(/^[a-zA-Z ].*$/)]],
      pincode:[Customer?.pincode||'',[Validators.required,Validators.pattern(/^[0-9]{1,10}$/)]],

    });
  }

  closeDialog() {
    this.isDialogOpen = false;
  }

  save(){
      this.closeDialog();

      if(this.customerForm.invalid) return;
      this.isLoading=true;
      if(this.selectedCustomer){

        const id = this.customerForm.value.customer_id;

        this.http.post(`${this.apiUrl}/updateCompany/${id}`,this.customerForm.value,{
          headers: new HttpHeaders({
            'Content-Type':'application/json'
          })
        })
        .subscribe(()=>{



          this.getFromApi();
          this.isLoading=false;
          this.notify.showSuccess('Customer Updated Successfully');


        });
        }
      else{
      this.http.post(`${this.apiUrl}/Create`, this.customerForm.value,{
        headers: new HttpHeaders ({
          'Content-Type':'application/json'
        })
      })
      .subscribe(
        (response: any) => {
          if(response.status === 409){
            this.emailExists = true;
            return;
          }

          this.isLoading=true;
          this.closeDialog();
          this.getFromApi();
          this.notify.showSuccess('Customer Created Successfully');
          this.isLoading=false;

        },
        (error) => {
          this.notify.showError(error);
        }
      );
    }
  }


  public removeHandler(item: Customer): void {
    const id = {customer_id:item.customer_id};

    this.http.post(`${this.apiUrl}/deleteCompany`,id,{
      headers:new HttpHeaders({
        'Content-Type':'application/json'
      })
    }).subscribe((response)=>{
      this.gridData = {
        data: this.gridData.data.filter((customer: Customer) => customer.customer_id !== item.customer_id),
        total: this.gridData.total - 1
      };
          this.notify.showSuccess("Customer Deleted Successfully");
      });
      (error: string) => {
        this.notify.showError(error);
      }
  }

  // backend pagination
  pageChange(event:any){
      this.skip = event.skip;
      this.pageSize = event.take;
      this.getFromApi()
  }

  // backend filteration
  public onFilterValue(){
    this.onFilter = !this.onFilter;
    this.searchIcon = this.searchIcon == searchIcon ? xIcon : searchIcon;
  }

  public onSubmit(){
    this.isLoading = true;
    this.getFromApi();
  }

  public onClear(){
    this.isLoading=true;
    this.filterForm.reset();
    this.pageSize = 5;
    this.getFromApi();
  }

  //email backend validation
  public emailChange(event: any){
    this.emailExists = false;
  }

  //delete confirmation
  public onConfirmDelete(dataItem?: any){
    if(dataItem){
      this.deleteDataItem = dataItem;
      this.isDeleteDialogOpen = true;
      return;
    }
    this.removeHandler(this.deleteDataItem);
    this.isDeleteDialogOpen = false;
  }

  public onCancelDelete(){
    this.isDeleteDialogOpen = false;
    this.deleteDataItem = null;
  }

}

