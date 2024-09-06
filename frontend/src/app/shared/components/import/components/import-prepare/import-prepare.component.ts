import {
    ChangeDetectionStrategy,
    Component, effect, inject, input, output, signal
} from '@angular/core';
import {MatIcon} from "@angular/material/icon";
import {RouterLink} from "@angular/router";
import {ImportPrepare, ImportStart, Ticker, TransactionActionType} from '@app/models';
import { ImportMapping } from '@app/models/import-mapping';
import {ImportPrepareTicker} from "@app/models/import-prepare-ticker";
import { ImportService
} from '@app/services';
import {TickerSelectorComponent} from "@app/shared/components/ticker-selector/ticker-selector.component";
import {objectKeyValues, objectValues} from "@app/utils/object-utils";
import {TranslateModule} from "@ngx-translate/core";

@Component({
    templateUrl: 'import-prepare.component.html',
    selector: 'fingather-import-prepare',
    standalone: true,
    imports: [
        TranslateModule,
        TickerSelectorComponent,
        RouterLink,
        MatIcon
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ImportPrepareComponent {
    private readonly importDataService = inject(ImportService);

    public $importPrepares = input.required<ImportPrepare[]>({
        'alias': 'importPrepares',
    });
    public $showCancel = input<boolean>(true, {
        alias: 'showCancel',
    });
    public onImportFinish$ = output<void>({
        'alias': 'onImportFinish',
    });

    protected $multipleFoundTickers = signal<Record<string, ImportPrepareTicker>>({});

    protected $selectedTickers = signal<Record<string, number>>({});

    public constructor() {
        effect(() => {
            const multipleFoundTickers: Record<string, ImportPrepareTicker> = {};
            const selectedTickers: Record<string, number> = {};

            for (const importPrepare of this.$importPrepares()) {
                for (const importPrepareTicker of importPrepare.multipleFoundTickers) {
                    const key = `${importPrepareTicker.brokerId}-${importPrepareTicker.ticker}`;

                    multipleFoundTickers[key] = importPrepareTicker;
                    selectedTickers[key] = importPrepareTicker.tickers[0].id;
                }
            }

            this.$multipleFoundTickers.set(multipleFoundTickers);
            this.$selectedTickers.set(selectedTickers);
        }, {
            allowSignalWrites: true,
        });
    }

    protected onChangeTicker(ticker: Ticker, key: string): void {
        const selectedTickers = this.$selectedTickers();
        selectedTickers[key] = ticker.id;
        this.$selectedTickers.set(selectedTickers);
    }

    protected async createImport(): Promise<void> {
        const importStart: ImportStart = {
            importId: this.$importPrepares()[0].importId,
            importMappings: [],
        };

        for (const selectedTicker of objectKeyValues(this.$selectedTickers())) {
            const [brokerId, importTicker] = selectedTicker.key.split('-');

            const importMapping: ImportMapping = {
                brokerId: parseInt(brokerId, 10),
                importTicker,
                tickerId: selectedTicker.value,
            };

            importStart.importMappings.push(importMapping);
        }

        await this.importDataService.createImportStart(importStart);

        this.onImportFinish$.emit();
    }

    protected readonly objectValues = objectValues;
    protected readonly TransactionActionType = TransactionActionType;
}
