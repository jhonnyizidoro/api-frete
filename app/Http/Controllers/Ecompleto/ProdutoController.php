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
	public static function buscarMedidasProduto(int $idLoja, int $idProduto, int $quantidade, bool $itensAdicionais)
	{
		return Produto::medidas($idLoja, $idProduto, $quantidade, $itensAdicionais);
	}

	public static function buscarFreteGratis(int $idLoja, int $idProduto, int $idFormaEntrega)
	{
		return Produto::freteGratis($idLoja, $idProduto, $idFormaEntrega);
	}
}
