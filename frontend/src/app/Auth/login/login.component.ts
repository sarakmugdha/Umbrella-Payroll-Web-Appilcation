import { Component } from '@angular/core';
import { RouterLink,Router } from '@angular/router';
import { FormsModule} from '@angular/forms';
import { AuthService } from '../../Service/auth-service.service';
import { CommonModule } from '@angular/common';
import { NotificationServiceWrapper } from '../../shared/notification.service';
import { SpinnerComponent } from '../../shared/spinner/spinner.component';


@Component({
  selector: 'app-user-login',
  imports: [RouterLink,FormsModule,CommonModule,SpinnerComponent],
  templateUrl: './login.component.html',
  styleUrl: '../Auth.css'
})


export class UserLoginComponent {
          public isLoading!:boolean;
          constructor(private authService:AuthService,private router:Router,private notify:NotificationServiceWrapper){

          }
          ngOnInit(){
            if(sessionStorage.getItem('access_token')){
                this.router.navigate(['/afa/dashboard']);
            }

          }

          onLogin(form:any){
            this.isLoading=true;
            console.log(form.value);
              this.authService.login(form).subscribe({
                next:(res:any)=>{
                  sessionStorage.setItem('access_token',res.access_token);
                  sessionStorage.setItem('refresh_token',res.refresh_token);
                  sessionStorage.setItem('expires_at',(Date.now() + (res.expiry*1000)).toString());
                  console.log(Date.now() , res.expiry,Date.now() + res.expiry);
                  this.isLoading=false;
                  this.router.navigate(['/qrtest']);
                },
                error:(err:any)=>{
                  this.isLoading=false;
                    this.notify.showError('Invalid Credentials');
                }
              })

          }



  }

