<?php

namespace App\Models\Ecompleto;

use Illuminate\Database\Eloquent\Model;
use DB;

class Produto extends Model
{
    /**
	 * Retorna todos os dados relevantes para a entrega do produto
	 */
	public static function medidas(int $idLoja, int $idProduto, int $quantidade, bool $itensAdicionais)
	{
		return DB::table('produtos AS p')
		->select(
			'p.id',
			'p.peso',
			'p.largura',
			'p.profundidade',
			'p.altura',
			'p.valor_venda'
		)
		->where([
			['p.id', $idProduto],
			['p.reve_cod', $idLoja],
		])
		->first();
	}

	public static function freteGratis(int $idLoja, int $idProduto, int $idFormaEntrega)
	{
		return DB::table('produtos AS p')
		->join('produtos_fretegratis AS pf', 'pf.id_produto', 'p.id')
		->where([
			['p.id', $idProduto],
			['p.reve_cod', $idLoja],
			['p.frete_gratis', 'true'],
			['pf.reve_cod', $idLoja],
			['pf.id_formaentrega', $idFormaEntrega]
		])
		->first();

	}

}
