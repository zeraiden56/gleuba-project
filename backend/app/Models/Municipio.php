<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Municipio extends Model
{
    use HasFactory;

    protected $fillable = [
        'cidade',
        'uf',
        'codigo_ibge',
        'populacao',
        'pib',
        'pib_per_capita',
        'pib_pcpt_mes',
        'cresc_pib_10y',
        'med_pib_10y',
        'cresc_pib_5y',
        'med_pib_5y',
        'acel_pib',
        'curva_pib',
        'pop5y',
        'pop30y',
        'med_pop5',
        'med_pop30',
        'acel_pop',
        'curva_pop',
        'ind_viol',
        'admy',
        'med_admy_vs_pop',
        'admy_vs_medgeral',
        'demy',
        'demy_vs_pop',
        'demy_vs_med',
        'admxdem',
        'admxpop',
        'crvla',
        'remm_mes',
        'abert_emp',
        'rank_ab_emp_uf',
        'abemp_per_capita',
        'curva_abemp_pop',
        'rankemp_uf_pcap',
        'indice',
        'posicao'
    ];
}
