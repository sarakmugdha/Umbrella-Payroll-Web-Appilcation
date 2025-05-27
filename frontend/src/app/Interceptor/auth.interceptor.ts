import {HttpErrorResponse,HttpInterceptorFn} from '@angular/common/http';
import { inject } from '@angular/core';
import { Router } from '@angular/router';

import { catchError, throwError } from 'rxjs';
import { NotificationServiceWrapper } from '../shared/notification.service';

export const authInterceptor: HttpInterceptorFn = (req, next) => {
  const toasterService = inject(NotificationServiceWrapper)
  const router = inject(Router);
  const token = sessionStorage.getItem('access_token');
  const expires = sessionStorage.getItem('expires_at');
  const refreshToken = sessionStorage.getItem('refresh_token');
  const isExpired = Date.now() > Number(expires);

  if (!token) {
    router.navigateByUrl('/');
    return throwError(() => new Error('No access token found'));
  }

  const authReq = req.clone({
    setHeaders: {
      Authorization: `Bearer ${token}`,
      ...(isExpired && refreshToken ? { 'refresh_token': refreshToken } : {})
    }
  });

  return next(authReq).pipe(
    catchError((error: HttpErrorResponse) => {
      if (error.status === 401 && error.error?.access_token) {
        console.log('401 error: refreshing token',error);


        sessionStorage.setItem('access_token', error.error.access_token);
        sessionStorage.setItem('refresh_token', error.error.refresh_token);
        sessionStorage.setItem('expires_at',(Date.now() + (error.error.expires_in*1000)).toString());

        const newToken = error.error.access_token;


        const retryReq = req.clone({
          setHeaders: {
            Authorization: `Bearer ${newToken}`
          }
        });

        return next(retryReq);
      }
      else {
        const newToken = sessionStorage.getItem('access_token');
        const retryNextReq = req.clone({
          setHeaders: {
            Authorization: `Bearer ${newToken}`
          }
        });
        console.log('Error:',error);
        return next(retryNextReq).pipe(
          catchError(err => {
            if(err.status === 401) {
            sessionStorage.clear();
            router.navigateByUrl('/');
            toasterService.showError('Session expired, please login again');}
            return throwError(() => new Error('error',err));}));
      }




    })
  );
};
