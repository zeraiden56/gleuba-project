// src/app/services/municipio.service.ts
import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';

@Injectable({ providedIn: 'root' })
export class MunicipioService {
  private apiUrl = 'http://localhost:8000/api/municipios'; // ajuste se for diferente

  constructor(private http: HttpClient) {}

  getMunicipios() {
    return this.http.get<any[]>(this.apiUrl);
  }
}
