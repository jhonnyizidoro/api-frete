<?php

namespace App\Http\Controllers\Ecompleto;

use App\Http\Controllers\Controller;

//Importação de Models
use App\Models\Ecompleto\Carrinho;

class CarrinhoController extends Controller
{
    public static function buscarBloqueioTransportadora(int $idLoja, int $idCarrinho, int $idFormaDeEntrega)
	{
		return Carrinho::bloqueioTransportadora($idLoja, $idCarrinho, $idFormaDeEntrega);
	}

	public static function buscarFreteGratis(int $idLoja, int $idCarrinho, int $idFormaDeEntrega)
	{
		return Carrinho::freteGratis($idLoja, $idCarrinho, $idFormaDeEntrega);
	}

	public static function buscarPromocaoFrete(int $idLoja, int $idCarrinho, int $quantidade, int $idFormaDeEntrega, object $faixaCep)
	{
		return Carrinho::promocaoFrete($idLoja, $idCarrinho, $quantidade, $idFormaDeEntrega, $faixaCep);
	}

	public static function buscarQuantidadeProdutos(int $idLoja, int $idCarrinho)
	{
		return Carrinho::quantidadeProdutos($idLoja, $idCarrinho);
	}
}
