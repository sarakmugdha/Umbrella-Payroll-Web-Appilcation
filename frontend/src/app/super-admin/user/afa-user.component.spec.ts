import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AfaUserComponent } from './afa-user.component';

describe('AfaUserComponent', () => {
  let component: AfaUserComponent;
  let fixture: ComponentFixture<AfaUserComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [AfaUserComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(AfaUserComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
