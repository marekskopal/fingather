import '@angular/compiler';
import 'zone.js';
import 'zone.js/testing';

import { getTestBed } from '@angular/core/testing';
import { BrowserTestingModule, platformBrowserTesting } from '@angular/platform-browser/testing';

globalThis.ResizeObserver ??= class {
    public observe(): void {}
    public unobserve(): void {}
    public disconnect(): void {}
};

getTestBed().initTestEnvironment(
    BrowserTestingModule,
    platformBrowserTesting(),
);
