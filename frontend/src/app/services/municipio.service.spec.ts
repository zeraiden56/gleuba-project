import { TestBed } from '@angular/core/testing';

import { MunicipioService } from './municipio.service';

describe('MunicipioService', () => {
  let service: MunicipioService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(MunicipioService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
