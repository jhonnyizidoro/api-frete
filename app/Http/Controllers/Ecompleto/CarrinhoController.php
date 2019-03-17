<?php

namespace App\Http\Controllers\Ecompleto;

use App\Http\Controllers\Controller;

use App\Models\Ecompleto\Carrinho;

class CarrinhoController extends Controller
{
	public static function buscarBloqueioTransportadora(int $idLoja, int $idCarrinho, int $idFormaDeEntrega)
	{
		return Carrinho::bloqueioTransportadora($idLoja, $idCarrinho, $idFormaDeEntrega);
	}

	/**
	 * TODO: Verifica se algum dos itens do carrinho tem 'frete_gratis_todo_carrinho' ou se todos eles tem frete grátis
	 * @return: Booleano indicando se tem frete grátis
	 */
	public static function buscarFreteGratis (int $idLoja, int $idCarrinho, int $idFormaDeEntrega)
	{
		$produtosFreteGratisNoCarrinho = Carrinho::freteGratis($idLoja, $idCarrinho, $idFormaDeEntrega);
		$quantidadeItensCarrinho = Self::buscarQuantidadeItens($idLoja, $idCarrinho);
		$freteGratisTodoCarrinho = $produtosFreteGratisNoCarrinho->contains('frete_gratis_todo_carrinho', true);
		return $freteGratisTodoCarrinho || $produtosFreteGratisNoCarrinho->count() === $quantidadeItensCarrinho;
	}

	public static function buscarPromocaoFrete(int $idLoja, int $idCarrinho, int $idFormaDeEntrega, object $faixaCep)
	{
		return Carrinho::promocaoFrete($idLoja, $idCarrinho, $idFormaDeEntrega, $faixaCep);
	}

	public static function buscarMedidas(int $idLoja, int $idCarrinho, object $formaDeEntrega)
	{
		$medidas = Carrinho::medidas($idLoja, $idCarrinho);

		//TODO: Soma do preco_vendatotal é o valor do carrinho
		$medidasFormatadas = (object) [
			'valor_venda' => $medidas->sum('preco_vendatotal'),
			'valor_venda_nota' => $medidas->sum('preco_vendatotal'),
			'peso' => 0,
			'largura' => 0,
			'profundidade' => 0,
			'altura' => 0,
			'peso_cubico' => 0,
			'volume' => 0
		];

		//TODO: Multiplica as medidas de cada produto  pela quantidade caso tenha 'calculo_itens_adicionais'
		foreach ($medidas as $key => $medidasProduto) {
			if ($formaDeEntrega->calculo_itens_adicionais) {
				$medidas[$key]->peso = round($medidasProduto->peso * $medidasProduto->quantidade, 4);
				$medidas[$key]->altura = round($medidasProduto->altura * $medidasProduto->quantidade, 4);
				$medidas[$key]->peso_cubico = round($medidasProduto->largura * $medidasProduto->altura * $medidasProduto->profundidade * $medidasProduto->quantidade * $formaDeEntrega->formula_cubado / 1000000, 4);
				$medidas[$key]->volume = round($medidasProduto->largura * $medidasProduto->altura * $medidasProduto->profundidade * $medidasProduto->quantidade, 4);
			} else {
				$medidas[$key]->peso_cubico = round($medidasProduto->largura * $medidasProduto->altura * $medidasProduto->profundidade * $formaDeEntrega->formula_cubado / 1000000, 4);
				$medidas[$key]->volume = round($medidasProduto->largura * $medidasProduto->altura * $medidasProduto->profundidade, 4);
			}
		}
		//TODO: Ao cadastrar uma forma de entrega é possível marcar um checkbox que envia o valor do produto para os calculos dos correios
		if (!$formaDeEntrega->valor_declarado) {
			$medidasFormatadas->valor_venda = 0;
		}
		//TODO: Busca a maior largura, maior profundidade e soma as alturas
		foreach ($medidas as $key => $medidasProduto) {
			$medidasFormatadas->altura += $medidasProduto->altura;
			$medidasFormatadas->peso += $medidasProduto->peso;
			$medidasFormatadas->peso_cubico += $medidasProduto->peso_cubico;
			$medidasFormatadas->volume += $medidasProduto->volume;
			if ($medidasFormatadas->largura < $medidasProduto->largura) {
				$medidasFormatadas->largura = $medidasProduto->largura;
			}
			if ($medidasFormatadas->profundidade < $medidasProduto->profundidade) {
				$medidasFormatadas->profundidade = $medidasProduto->profundidade;
			}
		}
		return $medidasFormatadas;
	}

	/**
	 * TODO: Busca a quantidade de intens distintos no carrinho (não considera a quantidade de cada item)
	 * @return: retorna um inteiro com a quantidade de itens
	 */
	public static function buscarQuantidadeItens(int $idLoja, int $idCarrinho)
	{
		return Carrinho::quantidadeItens($idLoja, $idCarrinho);
	}

	/**
	 * TODO: Busca o valor de venda do carrinho
	 * @return: retorna um float com o valor de venda
	 */
	public static function buscarValor(int $idLoja, int $idCarrinho)
	{
		return floatval(Carrinho::valor($idLoja, $idCarrinho));
	}
}
