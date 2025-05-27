import { CommonModule } from '@angular/common';
import { Component } from '@angular/core';
import { FormsModule, NgForm } from '@angular/forms';
import { RouterLink } from '@angular/router';
import { AuthService } from '../../Service/auth-service.service';
import { NotificationServiceWrapper } from '../../shared/notification.service';

@Component({
  selector: 'app-forgot-password',
  imports: [RouterLink, FormsModule, CommonModule],
  templateUrl: './forgot-password.component.html',
  styleUrl: '../Auth.css'
})
export class ForgotPasswordComponent {
  constructor(private notify: NotificationServiceWrapper, private authService: AuthService) {

  }
  onSubmit(form: NgForm) {

    this.authService.forgotPassword(form.value).subscribe({
      next: (res: any) => {
        form.reset();
        this.notify.showSuccess('Password set-up Link shared to your email')
      },
      error: (err: any) => {
        this.notify.showError('User doesnt exist with given email');
        form.reset();
      }
    })

  }

}
