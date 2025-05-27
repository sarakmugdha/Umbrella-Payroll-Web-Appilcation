import { ApplicationConfig, provideZoneChangeDetection } from '@angular/core';
import { provideRouter } from '@angular/router';
import { provideAnimations} from '@angular/platform-browser/animations';
import { routes } from './app.routes';
import { provideHttpClient, withInterceptors } from '@angular/common/http';
import { authInterceptor } from './Interceptor/auth.interceptor';
import { provideToastr } from 'ngx-toastr';
import { GANTT_GLOBAL_CONFIG, GANTT_I18N_LOCALE_TOKEN } from '@worktile/gantt';

export const appConfig: ApplicationConfig = {
  providers: [
    provideAnimations(),
    provideZoneChangeDetection({ eventCoalescing: true }),
    provideRouter(routes),
    provideHttpClient(withInterceptors([authInterceptor])),
    provideToastr(),
    {
      provide: GANTT_GLOBAL_CONFIG,
      useValue: {},
      multi: true
    },
    {
      provide: GANTT_I18N_LOCALE_TOKEN,
      useValue: 'en' ,
      multi: true
    }
  ]
};



