<?php

namespace App\Http\Controllers\Ecompleto;

use App\Http\Controllers\Controller;
use Carbon\Carbon;

//Importação de controllers
use App\Http\Controllers\Ecompleto\LojaController;
use App\Http\Controllers\Ecompleto\EnderecoController;
use App\Http\Controllers\Transportadoras\CorreiosController;
use App\Http\Controllers\Transportadoras\JamefController;
use App\Http\Controllers\Transportadoras\JadlogController;
use App\Http\Controllers\Transportadoras\TNTController;

//Importação de Models
use App\Models\Ecompleto\Frete;

class FreteController extends Controller
{
	public function calcularFrete(string $tipoDeCalculo, int $idLoja, int $idObjeto, string $cep, int $quantidade = 1)
	{
		//TODO: Define qual controller será utilizado para os calculos
		if ($tipoDeCalculo === 'produto') {
			$__CONTROLLER = 'App\Http\Controllers\Ecompleto\ProdutoController';
		} else {
			$__CONTROLLER = 'App\Http\Controllers\Ecompleto\CarrinhoController';
		}

		//TODO: Verifica se o CEP está bloqueado para a entrega!
		if (Self::buscarCepBloqueado($idLoja, $cep)) {
			return json([], 'Erro ao calcular o frete, CEP bloqueado!', false, 403);
		}
		
		//TODO: Informações da loja que serão necessárias para os calculos
		$fretes = [];
		$formasDeEntregaLoja = LojaController::buscarFormasEntrega($idLoja);
		$informacoesPrivadasLoja = LojaController::buscarInformacoesPrivadas($idLoja);
		$enderecoLoja = LojaController::buscarEnderecos($idLoja)[0];

		//TODO: Busca o endereço destino e formata ele
		$faixaCep = EnderecoController::buscarFaixaCep($cep);
		$destinoDeEntrega = "{$faixaCep->esta_cod} - {$faixaCep->esta_nome} - " . ($faixaCep->id_capital === 'S' ? 'Capital' : 'Interior e Região Metropolitana');
		
		foreach ($formasDeEntregaLoja as $formaDeEntrega) {
			
			//TODO: Verifica se o produto está bloqueado para essa forma de entrega ou se algum item do carrinho está bloqueado
			if ($__CONTROLLER::buscarBloqueioTransportadora($idLoja, $idObjeto, $formaDeEntrega->id)) {
				continue;
			}

			//TODO: Busca informações do produto ou do carrinho
			$freteGratis = $__CONTROLLER::buscarFreteGratis($idLoja, $idObjeto, $formaDeEntrega->id);
			$promocaoFrete = $__CONTROLLER::buscarPromocaoFrete($idLoja, $idObjeto, $formaDeEntrega->id, $faixaCep, $quantidade);
			$medidas = $__CONTROLLER::buscarMedidas($idLoja, $idObjeto, $formaDeEntrega, $quantidade);

			//TODO: Criando o objeto de retorno
			$frete = [
				'id' => $formaDeEntrega->id,
				'nome' => $formaDeEntrega->nome,
				'texto' => $formaDeEntrega->texto,
				'destino' => $destinoDeEntrega,
				'image_icon' => $formaDeEntrega->image_icon,
				'frete_gratis' => $formaDeEntrega->frete_gratis,
				'promocao_frete' => $freteGratis,
				'transportadora' => $formaDeEntrega->transportadora,
				'exibe_prazoentrega' => $formaDeEntrega->exibe_prazoentrega
			];

			//TODO: Calcula o valor do frete cotação de frete e retirada na loja
			if ($formaDeEntrega->id_servicorastreamento === 1) {
				$valoresFrete = Self::buscarOrcamentoFrete($formaDeEntrega->id);
				$frete = array_merge($frete, $valoresFrete);
				$fretes[] = $frete;
				continue;
			}

			//TODO: Calcula o frete para as formas de entrega da loja
			if ($formaDeEntrega->id_transportadora === 9) { //RETIRADA NA LOJA
				$valoresFrete = Self::buscarRegraFretePorCep($idLoja, $cep, $formaDeEntrega);
			} elseif (!$formaDeEntrega->calculo_online) { //TABELA DE FRETE
				$valoresFrete = Self::buscarRegraFrete($idLoja, $faixaCep, $formaDeEntrega, $medidas);
			} else { //INTEGRAÇÃO

				//TODO: Busca as tabelas de frete para fazer o calculo da integração
				$regraDeFrete = Self::buscarRegraFrete($idLoja, $faixaCep, $formaDeEntrega, $medidas);
				if (!$regraDeFrete) {
					continue;
				}

				//TODO: Faz a consulta nas APIs das transportadoras
				if ($formaDeEntrega->id_transportadora === 1) {
					$valoresFrete = CorreiosController::calcularFrete($idLoja, $cep, $enderecoLoja->cep, $formaDeEntrega->id_servicorastreamento, $medidas, $informacoesPrivadasLoja);
				} elseif ($formaDeEntrega->codigo_integrador === 413) {
					$valoresFrete = JamefController::calcularFrete($cep, $enderecoLoja, $formaDeEntrega, $medidas, $informacoesPrivadasLoja);
				} elseif ($formaDeEntrega->id_transportadora === 27) {
					$valoresFrete = JadlogController::calcularFrete($idLoja, $cep, $medidas, $formaDeEntrega, $informacoesPrivadasLoja);
				} elseif ($formaDeEntrega->id_transportadora === 176) {
					$valoresFrete = TNTController::calcularFrete($idLoja, $cep, $enderecoLoja, $medidas, $formaDeEntrega, $informacoesPrivadasLoja);
				} else {
					$valoresFrete = ['valor_frete' => 0, 'prazo_entrega' => 0];
				}

				//TODO: Verifica se existe alguma valor ou prazo adicional na regra de frete
				$valoresFrete['valor_frete'] += Self::calcularValorAdicional($formaDeEntrega, $regraDeFrete['valor_frete']);
				$valoresFrete['valor_frete'] += $regraDeFrete['valor_frete'];
			}

			//TODO: Adiciona o novo valor calculado no array de retorno
			if (is_array($valoresFrete)) {
				$frete = array_merge($frete, $valoresFrete);
			} else {
				continue;
			}

			//TODO: Somando o prazo adicional caso exista
			$frete['prazo_entrega'] += $informacoesPrivadasLoja->prazo_logistica;
			$frete['prazo_entrega'] += Self::calcularDiasAdicionaisFeriado($idLoja, $frete['prazo_entrega']);
		
			//TODO: Somando valores adicionais e verificando se o valor de frete repeita o frete mínimo da loja
			$valoresFrete['valor_frete'] += Self::calcularValorAdicional($formaDeEntrega, $valoresFrete['valor_frete']);
			if ($informacoesPrivadasLoja->sobretaxa_frete > 0) {
				$frete['valor_frete'] *= $informacoesPrivadasLoja->sobretaxa_frete;
			}
			if ($informacoesPrivadasLoja->frete_minimo > $frete['valor_frete'] && $frete['valor_frete'] > 0) {
				$frete['valor_frete'] = $informacoesPrivadasLoja->frete_minimo;
			}

			//TODO: alterando o valor do frete com bas nas promoções
			//PRECISA ARRUMAR O DESCONTO PARA CARRINHO
			if ($promocaoFrete) {
				$frete['valor_frete'] -= $frete['valor_frete'] * $promocaoFrete / 100;
			}
	
			$fretes[] = $frete;
		}

		return json($fretes, 'Sucesso ao calcular o frete', true, 200);
	}

	public static function calcularValorAdicional($formaDeEntrega, $valorFrete)
	{
		if ($formaDeEntrega->tipo_adicional === 'P') {
			return $valorFrete * $formaDeEntrega->valor_adicional / 100;
		} else {
			return $formaDeEntrega->valor_adicional;
		}
	}

	public static function buscarOrcamentoFrete(int $idFormaDeEntrega, int $idOrcamento = 0)
	{
		$orcamento = Frete::orcamento($idFormaDeEntrega, $idOrcamento);
		if ($orcamento) {
			return [
				'valor_frete' => $orcamento->valor_frete,
				'prazo_entrega' => 0
			]; 
		}
		return [
			'valor_frete' => 0,
			'prazo_entrega' => 0
		]; 
	}

	public static function buscarRegraFretePorCep(int $idLoja, string $cep, object $formaDeEntrega)
	{
		$regraDeFrete = Frete::regraPorCep($idLoja, $cep, $formaDeEntrega->id);
		if ($regraDeFrete) {
			return [
				'valor_frete' => $regraDeFrete->valor_frete,
				'prazo_entrega' => $formaDeEntrega->prazo_entrega
			]; 
		}
		return false;
	}
	
	public static function buscarRegraFrete(int $idLoja, object $faixaCep, object $formaDeEntrega, object $medidas)
	{
		$regraDeFrete = Frete::regra($idLoja, $faixaCep, $formaDeEntrega->id, $medidas->peso);
		if ($regraDeFrete) {
			//TODO: calculando valores adicionais
			$regraDeFrete->valor_frete += $regraDeFrete->valor_adicional_despacho;
			$regraDeFrete->valor_frete += $regraDeFrete->valor_adicional_percnota * $medidas->valor_venda / 100;
			$regraDeFrete->valor_frete += ($medidas->peso - $regraDeFrete->peso_ini) * $regraDeFrete->valor_adicional_kg;
			return [
				'valor_frete' => $regraDeFrete->valor_frete,
				'prazo_entrega' => intval($regraDeFrete->prazo_entrega_dias),
			];
		}
		return false;
	}

	public static function calcularDiasAdicionaisFeriado(int $idLoja, int $prazoDeEntrega)
	{
		$dataDeEntrega = Carbon::now()->addDay($prazoDeEntrega)->format('Y-m-d');
		return Frete::diasAdicionaisFeriado($idLoja, $dataDeEntrega);
	}

	public static function buscarCepBloqueado(int $idLoja, string $cep)
	{
		return Frete::cepBloqueado($idLoja, $cep);
	}
}