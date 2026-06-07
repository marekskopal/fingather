import '@angular/compiler';
import 'zone.js';
import 'zone.js/testing';

import { getTestBed } from '@angular/core/testing';
import { BrowserDynamicTestingModule, platformBrowserDynamicTesting } from '@angular/platform-browser-dynamic/testing';

globalThis.ResizeObserver ??= class {
    public observe(): void {}
    public unobserve(): void {}
    public disconnect(): void {}
};

getTestBed().initTestEnvironment(
    BrowserDynamicTestingModule,
    platformBrowserDynamicTesting(),
);
