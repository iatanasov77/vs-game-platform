import { TestBed } from '@angular/core/testing';

import { BridgeBeloteProvider } from './bridge-belote-provider';

describe('BridgeBeloteProvider', () => {
  let service: BridgeBeloteProvider;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(BridgeBeloteProvider);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
