<?php

namespace App\Http\Controllers\Ecompleto;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

//Importação de controllers
use App\Http\Controllers\Ecompleto\LojaController;
use App\Http\Controllers\Ecompleto\ProdutoController;
use App\Http\Controllers\Transportadoras\CorreiosController;

class FreteController extends Controller
{

	/**
	 * Calcula as formas de entrega disponíveis para um produto
	 */
	public function calcularFreteProduto(int $idLoja, int $idProduto, string $cep, int $quantidade = 1)
	{
		$formasDeEntregaLoja = LojaController::buscarFormasEntrega($idLoja);
		$informacoesPrivadasLoja = LojaController::buscarInformacoesPrivadas($idLoja);
		$enderecoLoja = LojaController::buscarEnderecos($idLoja)[0];
		
		if ($formasDeEntregaLoja->isNotEmpty()) {

			foreach ($formasDeEntregaLoja as $formaDeEntrega) {
				$medidasDoProduto = ProdutoController::buscarMedidasProduto($idLoja, $idProduto, $quantidade, $formaDeEntrega->calculo_itens_adicionais);
				if ($formaDeEntrega->id_transportadora === 1) {
					$frete = CorreiosController::calcularFrete($idLoja, $cep, $enderecoLoja->cep, $formaDeEntrega->id_servicorastreamento, $medidasDoProduto, $informacoesPrivadasLoja);
					dd($frete);
				}
			}

		}

	}
	
}
