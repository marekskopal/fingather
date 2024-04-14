import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { HistoryComponent } from '@app/history/components/history/history.component';
import { LayoutComponent } from '@app/history/components/layout/layout.component';
import { SharedModule } from '@app/shared/shared.module';

import { HistoryRoutingModule } from './history-routing.module';

@NgModule({
    imports: [
        CommonModule,
        ReactiveFormsModule,
        HistoryRoutingModule,
        SharedModule,
    ],
    declarations: [
        LayoutComponent,
        HistoryComponent,
    ]
})
export class HistoryModule {
}
