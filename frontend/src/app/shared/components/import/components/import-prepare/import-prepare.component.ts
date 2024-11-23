import {
    ChangeDetectionStrategy,
    Component, effect, inject, Injector, input, output, signal,
} from '@angular/core';
import {MatIcon} from "@angular/material/icon";
import {RouterLink} from "@angular/router";
import {ImportPrepare, ImportStart, Ticker, TransactionActionType} from '@app/models';
import { ImportMapping } from '@app/models/import-mapping';
import {ImportPrepareTicker} from "@app/models/import-prepare-ticker";
import { ImportService,
} from '@app/services';
import {FakeLoadingService} from "@app/services/fake-loading.service";
import {SaveButtonComponent} from "@app/shared/components/save-button/save-button.component";
import {TickerSelectorComponent} from "@app/shared/components/ticker-selector/ticker-selector.component";
import {objectKeyValues, objectValues} from "@app/utils/object-utils";
import { TranslatePipe} from "@ngx-translate/core";

@Component({
    templateUrl: 'import-prepare.component.html',
    selector: 'fingather-import-prepare',
    imports: [
        TranslatePipe,
        TickerSelectorComponent,
        RouterLink,
        MatIcon,
        SaveButtonComponent,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ImportPrepareComponent {
    private readonly importDataService = inject(ImportService);
    private readonly injector = inject(Injector);
    private readonly fakeLoadingService = Injector
        .create({ providers: [FakeLoadingService], parent: this.injector })
        .get(FakeLoadingService);

    public readonly importPrepares = input.required<ImportPrepare[]>();
    public readonly showCancel = input<boolean>(true);
    public readonly afterImportFinish = output<void>();

    protected readonly multipleFoundTickers = signal<Record<string, ImportPrepareTicker>>({});

    protected readonly selectedTickers = signal<Record<string, number>>({});

    protected readonly creatingImport = signal<boolean>(false);
    protected readonly processed = this.fakeLoadingService.processed;

    public constructor() {
        effect(() => {
            const multipleFoundTickers: Record<string, ImportPrepareTicker> = {};
            const selectedTickers: Record<string, number> = {};

            for (const importPrepare of this.importPrepares()) {
                for (const importPrepareTicker of importPrepare.multipleFoundTickers) {
                    const key = `${importPrepareTicker.brokerId}-${importPrepareTicker.ticker}`;

                    multipleFoundTickers[key] = importPrepareTicker;
                    selectedTickers[key] = importPrepareTicker.tickers[0].id;
                }
            }

            this.multipleFoundTickers.set(multipleFoundTickers);
            this.selectedTickers.set(selectedTickers);
        }, {
            allowSignalWrites: true,
        });
    }

    protected onChangeTicker(ticker: Ticker, key: string): void {
        const selectedTickers = this.selectedTickers();
        selectedTickers[key] = ticker.id;
        this.selectedTickers.set(selectedTickers);
    }

    protected async createImport(): Promise<void> {
        this.creatingImport.set(true);
        this.fakeLoadingService.startLoading();

        const importStart: ImportStart = {
            uuid: this.importPrepares()[0].uuid,
            importMappings: [],
        };

        for (const selectedTicker of objectKeyValues(this.selectedTickers())) {
            const [brokerId, importTicker] = selectedTicker.key.split('-');

            const importMapping: ImportMapping = {
                brokerId: parseInt(brokerId, 10),
                importTicker,
                tickerId: selectedTicker.value,
            };

            importStart.importMappings.push(importMapping);
        }

        await this.importDataService.createImportStart(importStart);

        this.fakeLoadingService.finishLoading();
        this.creatingImport.set(false);

        this.afterImportFinish.emit();
    }

    protected readonly objectValues = objectValues;
    protected readonly TransactionActionType = TransactionActionType;
}
