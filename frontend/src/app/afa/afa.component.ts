import { Component } from '@angular/core';
import { RouterOutlet } from '@angular/router';
import { SidebarComponent } from './afa-components/sidebar/sidebar.component';
import { ThemeComponent } from '../shared/theme/theme.component';
import { SearchBarComponent } from '../shared/SearchBar/SearchBar.component';

@Component({
  selector: 'app-afa',
  imports: [RouterOutlet,SidebarComponent,ThemeComponent,SearchBarComponent],
  templateUrl: './afa.component.html'
})
export class AfaComponent {

}
