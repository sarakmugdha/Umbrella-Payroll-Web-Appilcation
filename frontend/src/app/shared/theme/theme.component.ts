import { Component } from '@angular/core';
import { TooltipModule } from '@progress/kendo-angular-tooltip';

@Component({
  selector: 'theme-changer',
  imports: [TooltipModule],
  templateUrl: './theme.component.html',
  styleUrl: './theme.component.css'
})
export class ThemeComponent {

  ngOnInit(){
    const theme = localStorage.getItem('theme');
    console.log(theme);
    if (theme == 'dark-mode'){
      document.body.classList.add('dark-mode');
    }

  }
  public toggleDarkMode(){

    if(!document.body.classList.contains('dark-mode')){
      localStorage.setItem('theme','dark-mode')

      document.body.classList.add('dark-mode');
    }
    else{
      document.body.classList.remove('dark-mode');
      localStorage.setItem('theme','light-mode')
    }


  }

  }




