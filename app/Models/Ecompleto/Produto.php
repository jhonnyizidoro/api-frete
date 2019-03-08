<?php

namespace App\Models\Ecompleto;

use Illuminate\Database\Eloquent\Model;
use DB;

class Produto extends Model
{
    /**
	 * Retorna todos os dados relevantes para a entrega do produto
	 */
	public static function medidas(int $idLoja, int $idProduto, int $quantidade, object $formaDeEntrega)
	{
		$medidas = DB::table('produtos AS p')
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

		//Formatando medidas
		$medidas->peso = round($medidas->peso * $quantidade, 4);
		//TODO: Ao cadastrar uma forma de entrega é possível marcar um checkbox que leva em conta a $quantidade de produtos ao calcular o frete
		if ($formaDeEntrega->calculo_itens_adicionais) {
			$medidas->altura = round($medidas->altura * $quantidade, 4);
			$medidas->peso_cubico = round($medidas->largura * $medidas->altura * $medidas->profundidade * $quantidade * $formaDeEntrega->formula_cubado / 1000000, 4);
			$medidas->volume = round($medidas->largura * $medidas->altura * $medidas->profundidade * $quantidade, 4);
		} else {
			$medidas->peso_cubico = round($medidas->largura * $medidas->altura * $medidas->profundidade * $formaDeEntrega->formula_cubado / 1000000, 4);
			$medidas->volume = round($medidas->largura * $medidas->altura * $medidas->profundidade, 4);
		}
		//TODO: Ao cadastrar uma forma de entrega é possível marcar um checkbox que envia o valor do produto para os calculos dos correios
		if (!$formaDeEntrega->valor_declarado) {
			$medidas->valor_venda = 0;
		}

		return $medidas;
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
		->first();
	}

	public static function bloqueioTransportadora(int $idLoja, int $idProduto, int $idFormaDeEntrega)
	{
		return DB::table('produtos_bloqueartransportadora')
		->where([
			['id_produto', $idProduto],
			['id_formaentrega', $idFormaDeEntrega],
			['id_loja', $idLoja]
		])
		->first();
	}

}
