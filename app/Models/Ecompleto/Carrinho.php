<?php

namespace App\Models\Ecompleto;

use Illuminate\Database\Eloquent\Model;
use DB;

use App\Http\Controllers\Ecompleto\CarrinhoController;

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

	public static function valor(int $idLoja, int $idCarrinho)
	{
		return DB::table('basket')
		->where([
			['id_basket', $idCarrinho],
			['reve_cod', $idLoja]
		])
		->sum('preco_vendatotal');
	}

	public static function medidas(int $idLoja, int $idCarrinho)
	{
		return DB::table('produtos AS p')
		->select(
			'p.peso',
			'p.largura',
			'p.profundidade',
			'p.altura',
			'b.preco_vendatotal',
			'b.quantidade'
		)
		->join('basket AS b', 'b.id_produto', 'p.id')
		->where([
			['p.reve_cod', $idLoja],
			['b.id_basket', $idCarrinho]
		])
		->get();
	}

	public static function promocaoFrete(int $idLoja, int $idCarrinho, int $idFormaDeEntrega, object $faixaCep)
	{
		$valorTotalCarrinho = CarrinhoController::buscarValor($idLoja, $idCarrinho);
		return  DB::table('produtos AS p')
		->select('fr.*')
		->join('basket AS b', 'b.id_produto', 'p.id')
		->leftJoin('produtos_multicategoria AS pm', 'pm.id_produto', 'p.id')
		->leftJoin('frete_regrapromocao_fabricante AS frf', 'frf.id_fabricante', 'p.id_fabricante')
		->leftJoin('frete_regrapromocao_categorias AS frc', function($join) {
			$join->on('frc.id_cateprod', 'p.id_categoria');
			$join->orOn('frc.id_cateprod', 'pm.id_categoria_1');
			$join->orOn('frc.id_cateprod', 'pm.id_categoria_2');
			$join->orOn('frc.id_cateprod', 'pm.id_categoria_3');
			$join->orOn('frc.id_cateprod', 'pm.id_categoria_4');
			$join->orOn('frc.id_cateprod', 'pm.id_categoria_5');
		})
		->join('frete_regrapromocao AS fr', function($join) {
			$join->on('fr.id', 'frc.id_regrapromocao');
			$join->orOn('fr.id', 'frf.id_regrapromocao');
		})
		->join('frete_regrapromocao_regiao AS frr', 'frr.id_regrapromocao', 'fr.id')
		->whereRaw('? > fr.compra_minima', [$valorTotalCarrinho])
		->where([
			['b.id_basket', $idCarrinho],
			['p.reve_cod', $idLoja],
			['fr.id_formaentrega', $idFormaDeEntrega],
			['fr.cupom', false],
			['fr.status', true],
			['frr.esta_cod', $faixaCep->esta_cod],
			['frr.id_capital', $faixaCep->id_capital],
		])
		->orderBy('fr.aplicavel_todocarrinho', 'DESC')
		->orderBy('fr.desconto', 'DESC')
		->distinct()
		->first();
	}
}
