<?php

namespace App\Models\Ecompleto;

use Illuminate\Database\Eloquent\Model;
use DB;

class Frete extends Model
{
    public static function cepBloqueado(int $idLoja, string $cep)
	{
		return DB::table('frete_bloqueio_cep')
		->where([
			['id_loja', $idLoja],
			['cep_ini', '<=', $cep],
			['cep_fim', '>=', $cep],
		])
		->first();
	}

	public static function regraPorCep(int $idLoja, string $cep, int $idFormaDeEntrega)
	{
		return DB::table('frete_regra_cep')
		->select(
			'valor_frete'
		)
		->where([
			['cep_ini', '<=', $cep],
			['cep_fim', '>=', $cep],
			['id_loja', $idLoja],
			['id_formaentrega', $idFormaDeEntrega]
		])
		->first();
	}

	public static function regra(int $idLoja, object $faixaCep, int $idFormaDeEntrega, float $peso)
	{
		return DB::table('frete_regra')
		->select(
			'prazo_entrega_dias',
			'valor_frete',
			'valor_adicional_kg',
			'valor_adicional_percnota',
			'valor_adicional_despacho',
			'peso_ini',
			'peso_fim'
		)
		->where([
			['peso_ini', '<=', $peso],
			['peso_fim', '>=', $peso],
			['reve_cod', $idLoja],
			['esta_cod', $faixaCep->esta_cod],
			['id_capital', $faixaCep->id_capital],
			['id_formaentrega', $idFormaDeEntrega]
		])
		->first();
	}

	public static function diasAdicionaisFeriado(int $idLoja, string $dataDeEntrega)
	{
		return DB::table('feriado')
		->where([
			['id_loja', $idLoja],
			['data', '<=', $dataDeEntrega]
			])
		->whereRaw('data > CURRENT_DATE')
		->count();
	}
}
