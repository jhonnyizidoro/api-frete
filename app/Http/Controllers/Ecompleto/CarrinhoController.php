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
	 * TODO: Busca a quantidade de intens distintos no carrinho (não considera a quantidade de cada item)
	 * @return: retorna um inteiro com a quantidade de itens
	 */
	public static function buscarQuantidadeItens(int $idLoja, int $idCarrinho)
	{
		return Carrinho::quantidadeItens($idLoja, $idCarrinho);
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
}
