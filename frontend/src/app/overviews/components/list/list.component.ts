import {Component, OnInit} from '@angular/core';
import {first} from "rxjs/operators";
import {YearCalculatedData} from "@app/models";
import {OverviewService} from "@app/services";


@Component({
    templateUrl: './list.component.html'
})
export class ListComponent implements OnInit {
    public yearCalculatedDatas: YearCalculatedData[] = [];

    constructor(
        private overviewService: OverviewService,
    ) {
    }

    ngOnInit() {
        this.overviewService.getYearCalculatedData()
            .pipe(first())
            .subscribe(yearCalculatedDatas => this.yearCalculatedDatas = yearCalculatedDatas);
    }
}
