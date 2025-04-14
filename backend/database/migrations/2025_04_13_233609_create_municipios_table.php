<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMunicipiosTable extends Migration
{
    public function up()
    {
        Schema::create('municipios', function (Blueprint $table) {
            $table->id();
            $table->integer('posicao')->nullable();
            $table->decimal('traf_pago', 15, 2)->nullable();
            $table->decimal('indice', 15, 2)->nullable();
            $table->string('cidade', 255);
            $table->string('uf', 2);
            $table->bigInteger('codigo_ibge')->unique();
            $table->bigInteger('populacao')->nullable();
            $table->bigInteger('pib')->nullable(); // PIB total, em reais
            $table->decimal('pib_per_capita', 15, 2)->nullable();
            $table->decimal('pib_pcpt_mes', 15, 2)->nullable();
            $table->integer('rankemp_uf_pcap')->nullable();
            $table->decimal('cresc_pib_10y', 15, 2)->nullable();
            $table->decimal('med_pib_10y', 15, 2)->nullable();
            $table->decimal('cresc_pib_5y', 15, 2)->nullable();
            $table->decimal('med_pib_5y', 15, 2)->nullable();
            $table->decimal('acel_pib', 15, 2)->nullable();
            $table->string('curva_pib', 50)->nullable();
            $table->decimal('remm_mes', 15, 2)->nullable();
            $table->decimal('pop30y', 15, 2)->nullable();
            $table->decimal('med_pop30', 15, 2)->nullable();
            $table->decimal('pop5y', 15, 2)->nullable();
            $table->decimal('med_pop5', 15, 2)->nullable();
            $table->decimal('acel_pop', 15, 2)->nullable();
            $table->string('curva_pop', 50)->nullable();
            $table->decimal('ind_viol', 15, 2)->nullable();
            $table->decimal('abert_emp', 15, 2)->nullable();
            $table->integer('rank_ab_emp_uf')->nullable();
            $table->decimal('abemp_per_capita', 15, 2)->nullable();
            $table->string('curva_abemp_pop', 50)->nullable();
            $table->decimal('admy', 15, 2)->nullable();
            $table->decimal('med_admy_vs_pop', 15, 2)->nullable();
            $table->decimal('admy_vs_medgeral', 15, 2)->nullable();
            $table->decimal('demy', 15, 2)->nullable();
            $table->decimal('demy_vs_pop', 15, 2)->nullable();
            $table->decimal('demy_vs_med', 15, 2)->nullable();
            $table->decimal('admxdem', 15, 2)->nullable();
            $table->decimal('admxpop', 15, 2)->nullable();
            $table->string('crvla', 50)->nullable();
            $table->text('destaques')->nullable();
            $table->text('organico')->nullable();
            $table->text('cont_mun')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('municipios');
    }
}
