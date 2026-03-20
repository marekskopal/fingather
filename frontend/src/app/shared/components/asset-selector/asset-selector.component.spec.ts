import { NO_ERRORS_SCHEMA } from '@angular/core';
import { ComponentFixture, TestBed } from '@angular/core/testing';
import {Asset, Ticker} from '@app/models';
import { TickerTypeEnum } from '@app/models/enums/ticker-type-enum';
import { TranslateModule } from '@ngx-translate/core';

import { AssetSelectorComponent } from './asset-selector.component';

function makeTicker(id: number, name: string): Ticker {
    return {
        id,
        ticker: name,
        name,
        marketId: 1,
        currencyId: 1,
        type: TickerTypeEnum.Stock,
        isin: null,
        logo: null,
        sector: null,
        industry: null,
        website: null,
        description: null,
        country: null,
        market: { id: 1, name: 'NYSE', mic: 'XNYS', acronym: 'NYSE', countryCode: 'US' },
    };
}

const mockAssets: Asset[] = [
    { id: 1, tickerId: 1, groupId: 1, price: 100, ticker: makeTicker(1, 'AAPL') },
    { id: 2, tickerId: 2, groupId: 1, price: 200, ticker: makeTicker(2, 'TSLA') },
    { id: 3, tickerId: 3, groupId: 1, price: 300, ticker: makeTicker(3, 'MSFT') },
];

function buildComponent(): ComponentFixture<AssetSelectorComponent> {
    TestBed.configureTestingModule({
        imports: [AssetSelectorComponent, TranslateModule.forRoot()],
        schemas: [NO_ERRORS_SCHEMA],
    }).compileComponents();

    const fixture = TestBed.createComponent(AssetSelectorComponent);
    fixture.componentRef.setInput('assets', mockAssets);
    return fixture;
}

describe('AssetSelectorComponent', () => {
    let fixture: ComponentFixture<AssetSelectorComponent>;
    let component: AssetSelectorComponent;

    beforeEach(() => {
        fixture = buildComponent();
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it('should create', () => {
        expect(component).toBeTruthy();
    });

    it('selectedAsset is null when no selectedAssetId is provided', () => {
        expect(component['selectedAsset']()).toBeNull();
    });

    it('selectedAsset reflects the asset matching selectedAssetId input', () => {
        fixture.componentRef.setInput('selectedAssetId', 2);
        fixture.detectChanges();
        expect(component['selectedAsset']()?.id).toBe(2);
    });

    it('selectedAsset is null when selectedAssetId does not match any asset', () => {
        fixture.componentRef.setInput('selectedAssetId', 999);
        fixture.detectChanges();
        expect(component['selectedAsset']()).toBeNull();
    });

    it('selectedAsset updates reactively when selectedAssetId changes', () => {
        fixture.componentRef.setInput('selectedAssetId', 1);
        fixture.detectChanges();
        expect(component['selectedAsset']()?.id).toBe(1);

        fixture.componentRef.setInput('selectedAssetId', 3);
        fixture.detectChanges();
        expect(component['selectedAsset']()?.id).toBe(3);
    });

    it('changeAsset updates the selectedAsset signal', () => {
        component['changeAsset'](mockAssets[1]);
        expect(component['selectedAsset']()?.id).toBe(2);
    });

    it('changeAsset emits the selected asset via afterChangeAsset output', () => {
        const emitted: Asset[] = [];
        component.afterChangeAsset.subscribe((asset) => emitted.push(asset));

        component['changeAsset'](mockAssets[0]);

        expect(emitted).toHaveLength(1);
        expect(emitted[0].id).toBe(1);
    });

    it('changing selectedAssetId to null clears the selection', () => {
        fixture.componentRef.setInput('selectedAssetId', 1);
        fixture.detectChanges();
        fixture.componentRef.setInput('selectedAssetId', null);
        fixture.detectChanges();
        expect(component['selectedAsset']()).toBeNull();
    });
});
