<?php

namespace App\Http\Controllers\Ecompleto;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

//Importação de Models
use App\Models\Ecompleto\Produto;

class ProdutoController extends Controller
{
    /**
	 * Retorna todos os dados relevantes para a entrega do produto
	 */
	public static function buscarMedidasProduto(int $idLoja, int $idProduto, int $quantidade, object $formaDeEntrega)
	{
		return Produto::medidas($idLoja, $idProduto, $quantidade, $formaDeEntrega);
	}

	public static function buscarFreteGratis(int $idLoja, int $idProduto, int $idFormaDeEntrega)
	{
		return Produto::freteGratis($idLoja, $idProduto, $idFormaDeEntrega);
	}

	public static function buscarBloqueioTransportadora(int $idLoja, int $idProduto, int $idFormaDeEntrega)
	{
		return Produto::bloqueioTransportadora($idLoja, $idProduto, $idFormaDeEntrega);
	}
}
