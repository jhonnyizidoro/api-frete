<?php

namespace App\Http\Controllers\Ecompleto;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

//Importação de controllers
use App\Http\Controllers\Ecompleto\LojaController;
use App\Http\Controllers\Ecompleto\ProdutoController;
use App\Http\Controllers\Transportadoras\CorreiosController;
use App\Http\Controllers\Ecompleto\EnderecoController;

//Importação de Models
use App\Models\Ecompleto\Frete;

class FreteController extends Controller
{

	/**
	 * Calcula as formas de entrega disponíveis para um produto
	 */
	public function calcularFreteProduto(int $idLoja, int $idProduto, string $cep, int $quantidade = 1)
	{

		//Verifica se o CEP está bloqueado para a entrega!
		if ($bloqueio = Self::buscarCepBloqueado($idLoja, $cep)) {
			return json([], 'Erro ao calcular o frete!', false, 403);
		}

		//Informações da loja que serão necessárias para os calculos
		$fretes = [];
		$formasDeEntregaLoja = LojaController::buscarFormasEntrega($idLoja);
		$informacoesPrivadasLoja = LojaController::buscarInformacoesPrivadas($idLoja);
		$enderecoLoja = LojaController::buscarEnderecos($idLoja)[0];

		//Busca o endereço destino e formata ele
		$faixaCep = EnderecoController::buscarFaixaCep($cep);
		$destinoDeEntrega = "{$faixaCep->esta_cod} - {$faixaCep->esta_nome} - " . ($faixaCep->id_capital === 'S' ? 'Capital' : 'Interior e Região Metropolitana');

		if ($formasDeEntregaLoja->isNotEmpty()) {
			foreach ($formasDeEntregaLoja as $formaDeEntrega) {

				//Verifica se o produto tem alguma promoção de frete
				$freteGratisProduto = ProdutoController::buscarFreteGratis($idLoja, $idProduto, $formaDeEntrega->id) ? true : false;

				//Busca as medidas do produto para usar nos calculos
				$medidasDoProduto = ProdutoController::buscarMedidasProduto($idLoja, $idProduto, $quantidade, $formaDeEntrega->calculo_itens_adicionais);
				
				//Criando o objeto de retorno		
				$frete = [
					'id' => $formaDeEntrega->id,
					'nome' => $formaDeEntrega->nome,
					'texto' => $formaDeEntrega->texto,
					'destino' => $destinoDeEntrega,
					'image_icon' => $formaDeEntrega->image_icon,
					'frete_gratis' => $formaDeEntrega->frete_gratis,
					'promocao_frete' => $freteGratisProduto,
					'transportadora' => $formaDeEntrega->transportadora,
					'exibe_prazoentrega' => $formaDeEntrega->exibe_prazoentrega
				];


				if ($formaDeEntrega->id_transportadora === 1) { //Correios
					$valoresFrete = CorreiosController::calcularFrete($idLoja, $cep, $enderecoLoja->cep, $formaDeEntrega->id_servicorastreamento, $medidasDoProduto, $informacoesPrivadasLoja);
				} elseif ($formaDeEntrega->id_transportadora === 9) {
					dd('TESTE');
				} else {
					$valoresFrete = Self::buscarRegraFrete($idLoja, $faixaCep, $formaDeEntrega, $medidasDoProduto);
				}
				$frete = array_merge($frete, $valoresFrete);
				array_push($fretes, $frete);
			}
		}

		return json($fretes, 'Sucesso ao calcular o frete', true, 200);

	}

	public static function buscarCepBloqueado(int $idLoja, string $cep)
	{
		return Frete::cepBloqueado($idLoja, $cep);
	}

	public static function buscarRegraFrete(int $idLoja, object $faixaCep, object $formaDeEntrega, object $medidasDoProduto)
	{
		$regraDeFrete = Frete::regra($idLoja, $faixaCep, $formaDeEntrega->id, $medidasDoProduto->peso);
		return [
			'valor_frete' => floatval($regraDeFrete->valor_frete),
			'prazo_entrega_dias' => intval($regraDeFrete->prazo_entrega_dias),
		];
	}
	
}
