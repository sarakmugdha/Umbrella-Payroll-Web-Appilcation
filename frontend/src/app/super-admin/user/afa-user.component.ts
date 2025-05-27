import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Component } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { environment } from '../../../environments/environment.development';
import { KENDO_GRID } from '@progress/kendo-angular-grid';
import { FormBuilder, FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { DialogModule } from '@progress/kendo-angular-dialog';
import { InputsModule } from '@progress/kendo-angular-inputs';
import { ButtonsModule } from '@progress/kendo-angular-buttons';
import { KENDO_ICON, KENDO_SVGICON } from '@progress/kendo-angular-icons';
import { CommonModule } from '@angular/common';
import { plusIcon,exportIcon, pencilIcon, saveIcon,searchIcon,checkIcon, xIcon, cancelIcon, trashIcon, eyeIcon, filterClearIcon, filterIcon } from '@progress/kendo-svg-icons';
import { User } from '../../afa/afa-components/Model';
import { OrgType } from '../../afa/afa-components/Model';
import { NotificationServiceWrapper } from '../../shared/notification.service';
import { SpinnerComponent } from '../../shared/spinner/spinner.component';
import { RouterLink } from '@angular/router';
import { GridDataResult } from '@progress/kendo-angular-grid';
import { FilterExpression } from '@progress/kendo-angular-filter';
import { ThemeComponent } from '../../shared/theme/theme.component';

@Component({
  selector: 'app-user',
  imports: [KENDO_GRID,DialogModule,InputsModule,ButtonsModule,KENDO_ICON, ReactiveFormsModule, CommonModule,KENDO_SVGICON,SpinnerComponent,RouterLink,ThemeComponent],
  templateUrl: './afa-user.component.html',
  styleUrl: './afa-user.component.css'
})

export class AfaUserComponent
{
  constructor(private route:ActivatedRoute,private http:HttpClient,private fb: FormBuilder,private notify:NotificationServiceWrapper)
  {}

  public id!:number;
  public apiurl= environment.apiUrl;
  public user = [] as User[];
  public userForm!: FormGroup;
  public isDialogOpen = false;
  public addIcon =  plusIcon;
  public isLoading!:boolean;
  public orgDetails =[] as OrgType[];
  public orgForm!: FormGroup;
  public exportIcon=exportIcon;
  public pencilIcon=pencilIcon;
  public saveIcon=saveIcon;
  public checkIcon=checkIcon;
  public xIcon=xIcon;
  public cancelIcon=cancelIcon;
  public trashIcon=trashIcon;
  public eyeIcon=eyeIcon;
  public searchIcon=searchIcon;
  public filterClearIcon=filterClearIcon;
  public filterIcon = filterIcon;
  public loading=false;
  public emailExists = true;
  public isDeleteDialogOpen: boolean = false;
  public userToDelete: User | null = null;
  public organization_name:string='';
  public currentPage :number = 1;
  public pageSize: number = 5;
  public skip: number = 0;
  public gridData: GridDataResult = { data: [], total: 0};
    public onFilter:boolean= false;
    public filterForm!: FormGroup;
    public filters: FilterExpression[] = [
      {
        field: "name",
        title: "Name",
        editor: "string"
      },
      {
        field: "email",
        title: "Email",
        editor: "string"
      },
      {
        field: "username",
        title: "UserName",
        editor: "string"
      }
    ]

  ngOnInit()
  {
    this.id = this.route.snapshot.params['id'];
    console.log("id",this.id);
    this.isLoading=true;
    
    this.filterForm = new FormGroup({
      name: new FormControl(null),
      email: new FormControl(null),
      username: new FormControl(null),
    })
    this.getfromapi();
    this.getOrgDetails();
    setTimeout(() => {
      this.isLoading=false;}, 3000);
    this.userForm = this.fb.group({
      name: ['', Validators.required],
      email: ['', [Validators.required, Validators.email]],
      username: ['', Validators.required],
    });

  }

  getfromapi()
  {
    const id=this.id;
    const currentPage = this.skip / this.pageSize ;
    console.log("Loading laravel page:", currentPage);
    const details = {page: currentPage, per_page: this.pageSize, id:this.id,filter:this.filterForm.value}
    console.log(details);
   
    this.http.post(`${this.apiurl}/getUserDetails`,details,
      {
      headers: new HttpHeaders({
        'Content-Type' : 'application/json'
      })}).subscribe({
      next:(data:any)=>
      {
        console.log(data);
      this.gridData = {
        data: data.userData,
        total: data.totalData,
      };
       console.log(this.gridData);
    },
    });
    console.log(this.user);
    }
    openDialog() {
      console.log("openDialog called");
      this.isDialogOpen = true;
      this.emailExists = false;
      console.log("isDialogOpen after set:", this.isDialogOpen);
      this.userForm.reset();
    }
    closeDialog()
    {
      this.isDialogOpen = false;
      console.log(this.isDialogOpen,"close");
    }
    saveUser()
    {
      this.isDialogOpen = false;
      this.isLoading=true;
      if (this.userForm.invalid) return;
      const newUser = {
        ...this.userForm.value,
        organization_id: this.id
      };
      console.log(newUser)
      this.http.post(`${this.apiurl}/addUser`, newUser, {
        headers: new HttpHeaders({
          'Content-Type': 'application/json'
        })
      }).subscribe({
        next: (response: any) => {
          if(response.status === 409){
            this.emailExists = true;
            return;
          }
          this.isLoading=false;
          this.getfromapi();   
          console.log(this.isDialogOpen,"close"); 
          this.notify.showSuccess('User Added Successfully');
      },
      error:(err)=>{
        console.log(err);
        this.notify.showError(err);
        this.isLoading = false;
      }});
      }
      
    getOrgDetails() {
      const id = { organization_id: this.id };
      console.log(id);
      this.http.post<OrgType[]>(`${this.apiurl}/getOrgDetails`, id, {
        headers: new HttpHeaders({
          'Content-Type': 'application/json'
        })
      }).subscribe({
        next: (data) => {
          this.orgDetails = data;

          if (this.orgDetails.length > 0) {
            this.organization_name = this.orgDetails[0].organization_name;

            this.orgForm = new FormGroup({
              organization_id: new FormControl(this.id),
              organization_name: new FormControl(this.organization_name),
              email: new FormControl(this.orgDetails[0].email),
              address: new FormControl(this.orgDetails[0].address),
              state: new FormControl(this.orgDetails[0].state),
              organization_logo: new FormControl(this.orgDetails[0].organization_logo),
              pincode: new FormControl(this.orgDetails[0].pincode),
            });
          }
          console.log(this.orgForm.value);
        },
        error: (err) => {
          console.error('Error fetching org details:', err);
        }
      });
    }
  toggleStatus(user: any): void {
    console.log(user);
    const newStatus = user.status === 1 ? 0 : 1;
    const details = {id: user.id,
            status: newStatus,
    }
    console.log(details);
    this.http.post(`${this.apiurl}/updateUserStatus`, details, ({
      headers: new HttpHeaders({
        'Content-Type': 'Application/json'
      })
    })).subscribe({
      next: (response) => {
        console.log(response);
        user.status = newStatus;
        console.log('Status updated', response);

      },
      error: err => {
        console.error('Failed to update status', err);
      }
    });
  }
  public removeHandler(dataItem: User) 
  {
    this.userToDelete = dataItem;
    this.isDeleteDialogOpen = true;
  }
    public pageChange(event:any)
    {
      this.skip = event.skip;
      this.pageSize = event.take;
      this.getfromapi();
    }
    public onFilterValue(){
      if(this.onFilter){
        this.onFilter = false;
        this.searchIcon = this.searchIcon == searchIcon ? xIcon:searchIcon;
        this.pageSize = 4;
        this.getfromapi();
        setTimeout(() => {
          this.isLoading=false;}, 1200);
      }
      else{
        this.onFilter = true;
        this.isLoading =true;
        this.searchIcon = this.searchIcon == searchIcon ? xIcon:searchIcon;
        this.pageSize = 4;
        this.getfromapi();
        setTimeout(() => {
          this.isLoading=false;},1200);
        }
    }
    public onSubmit(){
      console.log(this.filterForm.value);
      this.isLoading = true;
      this.getfromapi();
    setTimeout(() => {
      this.isLoading=false;}, 1200);
    }
    public onClear(){
      this.isLoading = true;
      this.filterForm.reset();
      this.pageSize = 4;
      this.getfromapi();
    setTimeout(() => {
      this.isLoading=false;}, 1200);
    }
    public emailChange(event: any){
      this.emailExists = false;
    }
    public onCancelDelete(): void {
      this.isDeleteDialogOpen = false;
      this.userToDelete = null;
    }
    public onConfirmDelete(): void {
      if (!this.userToDelete) return;
      const id = { id: this.userToDelete.id };
      this.onCancelDelete();
      this.isLoading = true;
    
      console.log(id);
      this.http.post(`${this.apiurl}/deleteUser`, id,{
        headers: new HttpHeaders({
          'Content-Type':'application/json'
        })
      }).subscribe({next:(response) => {

        console.log(response);
        this.getfromapi();
        this.notify.showSuccess('User Deleted Successfully');
        this.isLoading=false;
       
      },error:(error)=>{
        console.error('Error deleting data', error);
        this.notify.showError(error);
      }
    });
    
}
}