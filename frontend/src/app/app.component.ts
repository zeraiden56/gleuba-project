import { Component } from '@angular/core';
import { CommonModule } from '@angular/common'; // ✅ necessário para pipes e ngFor
import { HttpClientModule, HttpClient } from '@angular/common/http';

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [CommonModule, HttpClientModule], // ✅ aqui está a solução!
  templateUrl: './app.component.html',
})
export class AppComponent {
  municipios: any[] = [];

  constructor(private http: HttpClient) {}

  ngOnInit() {
    this.http.get<any[]>('http://localhost:8000/api/municipios')
      .subscribe(data => {
        this.municipios = data.sort((a, b) => a.posicao - b.posicao);
      });
  }
}
