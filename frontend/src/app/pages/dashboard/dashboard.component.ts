import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { HttpClient } from '@angular/common/http';

@Component({
  selector: 'app-dashboard',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './dashboard.component.html',
})
export class DashboardComponent implements OnInit {
  municipios: any[] = [];
  meta: any;
  search: string = '';
  currentPage: number = 1;
  loading = false;

  constructor(private http: HttpClient) {}

  ngOnInit(): void {
    this.fetchData();
  }

  fetchData(page: number = 1) {
    this.loading = true;
    const params = new URLSearchParams();
    if (this.search) params.append('search', this.search);
    params.append('page', String(page));

    this.http
      .get<any>(`http://localhost:8000/api/municipios?${params.toString()}`)
      .subscribe((res) => {
        this.municipios = res.data;
        this.meta = res.meta;
        this.currentPage = res.meta.current_page;
        this.loading = false;
      });
  }

  formatCurrency(value: number | null) {
    return value?.toLocaleString('pt-BR', {
      style: 'currency',
      currency: 'BRL',
    }) ?? '-';
  }
}
