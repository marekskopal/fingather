import {
    ChangeDetectionStrategy, Component, inject
} from '@angular/core';
import {
    GroupAllocationComponent
} from "@app/dashboard/components/group-data/components/group-allocation/group-allocation.component";
import {GroupDataTabEnum} from "@app/dashboard/components/group-data/enums/GroupDataTabEnum";
import {GroupWithGroupDataService} from "@app/services";
import {CountryWithCountryDataService} from "@app/services/country-with-country-data.service";
import {IndustryWithIndustryDataService} from "@app/services/industry-with-industry-data.service";
import {SectorWithSectorDataService} from "@app/services/sector-with-sector-data.service";
import {ScrollShadowDirective} from "@marekskopal/ng-scroll-shadow";
import {NgbNav, NgbNavContent, NgbNavItem, NgbNavLinkButton, NgbNavOutlet} from "@ng-bootstrap/ng-bootstrap";
import { TranslatePipe} from "@ngx-translate/core";

@Component({
    selector: 'fingather-group-data',
    templateUrl: 'group-data.component.html',
    standalone: true,
    imports: [
        NgbNav,
        NgbNavItem,
        NgbNavLinkButton,
        NgbNavContent,
        NgbNavOutlet,
        TranslatePipe,
        GroupAllocationComponent,
        ScrollShadowDirective
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class GroupDataComponent {
    protected readonly groupWithGroupDataService = inject(GroupWithGroupDataService);
    protected readonly countryWithCountryDataService = inject(CountryWithCountryDataService);
    protected readonly sectorWithSectorDataService = inject(SectorWithSectorDataService);
    protected readonly industryWithIndustryDataService = inject(IndustryWithIndustryDataService);

    protected activeTab: GroupDataTabEnum = GroupDataTabEnum.GroupAllocation;
    protected readonly GroupDataTabEnum = GroupDataTabEnum;
}
