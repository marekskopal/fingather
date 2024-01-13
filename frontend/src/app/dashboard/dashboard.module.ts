import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';

import { DashboardRoutingModule } from './dashboard-routing.module';
import { SharedModule } from '../shared/shared.module';
import {LayoutComponent} from "@app/dashboard/components/layout/layout.component";
import {DashboardComponent} from "@app/dashboard/components/dashboard/dashboard.component";

@NgModule({
    imports: [
        CommonModule,
        ReactiveFormsModule,
        SharedModule,
        DashboardRoutingModule,
    ],
    declarations: [
        LayoutComponent,
        DashboardComponent,
    ]
})
export class DashboardModule { }
