<?php

namespace App\Models\Ecompleto;

use Illuminate\Database\Eloquent\Model;
use DB;

class Produto extends Model
{
	public static function medidas(int $idLoja, int $idProduto)
	{
		return DB::table('produtos')
		->select(
			'peso',
			'largura',
			'profundidade',
			'altura',
			'valor_venda'
		)
		->where([
			['id', $idProduto],
			['reve_cod', $idLoja],
		])
		->first();
	}

	public static function freteGratis(int $idLoja, int $idProduto, int $idFormaDeEntrega)
	{
		return DB::table('produtos AS p')
		->join('produtos_fretegratis AS pf', 'pf.id_produto', 'p.id')
		->where([
			['p.id', $idProduto],
			['p.reve_cod', $idLoja],
			['p.frete_gratis', 'true'],
			['pf.reve_cod', $idLoja],
			['pf.id_formaentrega', $idFormaDeEntrega]
		])
		->exists();
	}

	public static function bloqueioTransportadora(int $idLoja, int $idProduto, int $idFormaDeEntrega)
	{
		return DB::table('produtos_bloqueartransportadora')
		->where([
			['id_produto', $idProduto],
			['id_formaentrega', $idFormaDeEntrega],
			['id_loja', $idLoja]
		])
		->exists();
	}

	public static function promocaoFrete(int $idLoja, int $idProduto, int $idFormaDeEntrega, object $faixaCep, int $quantidade)
	{
		return  DB::table('produtos AS p')
		->select('fr.*')
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
		->whereRaw('(p.valor_venda * ?) > fr.compra_minima', [$quantidade])
		->where([
			['p.id', $idProduto],
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
