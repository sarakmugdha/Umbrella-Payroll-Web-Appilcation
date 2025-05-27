import { Component, NgModule } from '@angular/core';
import { FormsModule, NgModel } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { AuthService } from '../../Service/auth-service.service';
import { RouterLink } from '@angular/router';
import { NotificationServiceWrapper } from '../../shared/notification.service';
@Component({
  selector: 'app-sign-up',
  imports: [FormsModule,CommonModule,RouterLink],
  templateUrl: './sign-up.component.html',
  styleUrl: '../Auth.css'
})
export class SignUpComponent {
            data:any
            msg!:any
            status!:any
            constructor(private authService:AuthService,private notify:NotificationServiceWrapper){
            }

            onSubmit(form:any){
              if(form.valid){
                console.log('auth service call');
                this.authService.register(form).subscribe({
                  next : (data : any) => {
                    console.log(data);
                    this.msg="";
                   this.notify.showSuccess('Password Set-up Link shared to your email');
                  }, 
                  error : (err : any) => {
                    console.log(err);
                    this.notify.showError(err.error.message);
                  }
                })
                
              }
            }
}
