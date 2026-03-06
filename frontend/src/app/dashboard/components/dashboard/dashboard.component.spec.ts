import { NO_ERRORS_SCHEMA } from '@angular/core';
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { provideRouter } from '@angular/router';
import { TranslateModule } from '@ngx-translate/core';

import { DashboardComponent } from './dashboard.component';

describe('DashboardComponent', () => {
    let fixture: ComponentFixture<DashboardComponent>;
    let component: DashboardComponent;

    beforeEach(async () => {
        await TestBed.configureTestingModule({
            imports: [DashboardComponent, TranslateModule.forRoot()],
            providers: [provideRouter([])],
            schemas: [NO_ERRORS_SCHEMA],
        }).compileComponents();

        fixture = TestBed.createComponent(DashboardComponent);
        component = fixture.componentInstance;
    });

    it('should create', () => {
        expect(component).toBeTruthy();
    });

    it('should render without errors', () => {
        fixture.detectChanges();
        expect(fixture.nativeElement).toBeTruthy();
    });
});
