<?php

namespace App\Models\Ecompleto;

use Illuminate\Database\Eloquent\Model;
use DB;

use App\Http\Controllers\Ecompleto\CarrinhoController;

class Carrinho extends Model
{
    public static function bloqueioTransportadora(int $idLoja, int $idCarrinho, int $idFormaDeEntrega)
	{
		//TODO: Busca o ID dos produtos bloqueados da loja
		$idProdutosBloqueados = DB::table('produtos_bloqueartransportadora')
		->where([
			['id_formaentrega', $idFormaDeEntrega],
			['id_loja', $idLoja]
		])
		->pluck('id_produto');

		//TODO: Verifica se algum dos produtos bloqueados está no carrinho
		return DB::table('basket')
		->where([
			['id_basket', $idCarrinho],
			['reve_cod', $idLoja],
		])
		->whereIn('id_produto', $idProdutosBloqueados)
		->exists();
	}

	public static function freteGratis(int $idLoja, int $idCarrinho, int $idFormaDeEntrega)
	{
		//TODO: Verifica se algum produto tem promoção de frete para todo o carrinho
		return DB::table('basket AS b')
		->select(
			'p.id AS id_produto',
			'p.frete_gratis_todo_carrinho'
		)
		->join('produtos AS p', 'p.id', 'b.id_produto')
		->join('produtos_fretegratis AS pf', 'pf.id_produto', 'p.id')
		->where([
			['b.id_basket', $idCarrinho],
			['p.reve_cod', $idLoja],
			['p.frete_gratis', true],
			['pf.reve_cod', $idLoja],
			['pf.id_formaentrega', $idFormaDeEntrega],
		])
		->exists();
	}

	public static function promocaoFrete(int $idLoja, int $idCarrinho, int $quantidade, int $idFormaDeEntrega, object $faixaCep)
	{

	}

	public static function quantidadeProdutos(int $idLoja, int $idCarrinho)
	{
		return DB::table('basket')
		->where([
			['id_basket', $idCarrinho],
			['reve_cod', $idLoja]
		])
		->count();
	}
}
