<?php

namespace App\Http\Controllers\Ecompleto;

use App\Http\Controllers\Controller;

//Importação de Models
use App\Models\Ecompleto\Produto;

class ProdutoController extends Controller
{
	public static function buscarMedidasProduto(int $idLoja, int $idProduto, int $quantidade, object $formaDeEntrega)
	{
		$medidas = Produto::medidas($idLoja, $idProduto, $quantidade, $formaDeEntrega);
		$medidas->valor_venda_nota = $medidas->valor_venda;
		//TODO: Ao cadastrar uma forma de entrega é possível marcar um checkbox que leva em conta a $quantidade de produtos ao calcular o frete
		if ($formaDeEntrega->calculo_itens_adicionais) {
			$medidas->peso = round($medidas->peso * $quantidade, 4);
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

	public static function buscarFreteGratis(int $idLoja, int $idProduto, int $idFormaDeEntrega)
	{
		return Produto::freteGratis($idLoja, $idProduto, $idFormaDeEntrega);
	}

	public static function buscarBloqueioTransportadora(int $idLoja, int $idProduto, int $idFormaDeEntrega)
	{
		return Produto::bloqueioTransportadora($idLoja, $idProduto, $idFormaDeEntrega);
	}

	public static function buscarPromocaoFrete(int $idLoja, int $idProduto, int $quantidade, int $idFormaDeEntrega, object $faixaCep)
	{
		return Produto::promocaoFrete($idLoja, $idProduto, $quantidade, $idFormaDeEntrega, $faixaCep);
	}
}
