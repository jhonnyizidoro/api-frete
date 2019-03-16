<?php

namespace App\Models\Ecompleto;

use Illuminate\Database\Eloquent\Model;
use DB;

class Carrinho extends Model
{
	public static function bloqueioTransportadora(int $idLoja, int $idCarrinho, int $idFormaDeEntrega)
	{
		return DB::table('produtos_bloqueartransportadora AS pb')
		->join('basket AS b', 'b.id', 'pb.id_produto')
		->where([
			['b.id_basket', $idCarrinho],
			['pb.id_formaentrega', $idFormaDeEntrega],
			['pb.id_loja', $idLoja]
		])
		->exists();
	}

	public static function freteGratis(int $idLoja, int $idCarrinho, int $idFormaDeEntrega)
	{
		return DB::table('basket AS b')
		->join('produtos AS p', 'p.id', 'b.id_produto')
		->join('produtos_fretegratis AS pf', 'pf.id_produto', 'p.id')
		->where([
			['b.id_basket', $idCarrinho],
			['p.reve_cod', $idLoja],
			['pf.reve_cod', $idLoja],
			['pf.id_formaentrega', $idFormaDeEntrega],
			['p.frete_gratis', true]
		])
		->get();
	}

	public static function quantidadeItens(int $idLoja, int $idCarrinho)
	{
		return DB::table('basket')
		->where([
			['id_basket', $idCarrinho],
			['reve_cod', $idLoja]
		])
		->count();
	}

	public static function promocaoFrete(int $idLoja, int $idCarrinho, int $idFormaDeEntrega, object $faixaCep)
	{
		
	}
}
