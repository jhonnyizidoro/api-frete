<?php

namespace App\Http\Controllers\Ecompleto;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

//Importação de controllers
use App\Http\Controllers\Ecompleto\LojaController;
use App\Http\Controllers\Ecompleto\ProdutoController;
use App\Http\Controllers\Ecompleto\EnderecoController;
use App\Http\Controllers\Transportadoras\CorreiosController;
use App\Http\Controllers\Transportadoras\JamefController;

//Importação de Models
use App\Models\Ecompleto\Frete;

class FreteController extends Controller
{
	public function calcularFreteProduto(int $idLoja, int $idProduto, string $cep, int $quantidade = 1)
	{
		//TODO: Verifica se o CEP está bloqueado para a entrega!
		if (Self::buscarCepBloqueado($idLoja, $cep)) {
			return json([], 'Erro ao calcular o frete!', false, 403);
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
			
			//TODO: Verifica se o produto está bloqueado para essa forma de entrega
			if (ProdutoController::buscarBloqueioTransportadora($idLoja, $idProduto, $formaDeEntrega->id)) {
				continue;
			}

			//TODO: Busca informações do produto
			$freteGratisProduto = ProdutoController::buscarFreteGratis($idLoja, $idProduto, $formaDeEntrega->id) ? true : false;
			$medidasDoProduto = ProdutoController::buscarMedidasProduto($idLoja, $idProduto, $quantidade, $formaDeEntrega);

			//TODO: Criando o objeto de retorno		
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

			//TODO: Calcula o valor do frete
			$valoresFrete = NULL;
			if ($formaDeEntrega->id_servicorastreamento === 1) {
				$valoresFrete = Self::buscarOrcamentoFrete($formaDeEntrega->id);
			} elseif ($formaDeEntrega->calculo_online) {
				if ($formaDeEntrega->id_transportadora === 1) {
					$valoresFrete = CorreiosController::calcularFrete($idLoja, $cep, $enderecoLoja->cep, $formaDeEntrega->id_servicorastreamento, $medidasDoProduto, $informacoesPrivadasLoja);
				} elseif ($formaDeEntrega->id_transportadora === 9) {
					$valoresFrete = Self::buscarRegraFretePorCep($idLoja, $cep, $formaDeEntrega);
				} elseif ($formaDeEntrega->codigo_integrador === 413) {
					$valoresFrete = JamefController::calcularFrete($idLoja, $cep, $enderecoLoja, $formaDeEntrega, $medidasDoProduto, $informacoesPrivadasLoja);
				} elseif ($formaDeEntrega->id_transportadora === 27) {
					//jadlog
				} elseif ($formaDeEntrega->id_transportadora === 176) {
					//tnt
				} else {
					$valoresFrete = Self::buscarRegraFrete($idLoja, $faixaCep, $formaDeEntrega, $medidasDoProduto);
				}
			} else {
				if ($formaDeEntrega->id_transportadora === 9) {

				} elseif ($formaDeEntrega->id_transportadora === 249) {

				} elseif ($formaDeEntrega->id_transportadora === 256) {
					
				} else {

				}
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

			//TODO: somando valor adicional ao frete. Tando o valor adicional da forma de entrega quando o da configuração da loja
			if ($formaDeEntrega->tipo_adicional === 'P') {
				$frete['valor_frete'] += $frete['valor_frete'] * $formaDeEntrega->valor_adicional / 100;
			} else {
				$frete['valor_frete'] += $formaDeEntrega->valor_adicional;
			}
			if ($informacoesPrivadasLoja->sobretaxa_frete > 0) {
				$frete['valor_frete'] *= $informacoesPrivadasLoja->sobretaxa_frete;
			}
			if ($informacoesPrivadasLoja->frete_minimo > $frete['valor_frete']) {
				$frete['valor_frete'] = $informacoesPrivadasLoja->frete_minimo;
			}
			$frete['valor_frete_original'] = $frete['valor_frete'];

			array_push($fretes, $frete);

		}
		
		return json($fretes, 'Sucesso ao calcular o frete', true, 200);
		
	}

	/**
	 * TODO: Verifica se existe algum orçamento com os dados passados. Também verifica as formas de entrega com Transportadora = COTAÇÃO DE FRETE
	 * @return: caso encontre o orçamento retorna o valor do frete, caso não encontre retorna 0 (cotação de frete)
	 */
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

	/**
	* TODO: Verifica se existe alguma regra específica para o CEP informado. Essa informação pode ser acessada em 'Frete' → 'Frete por Região de CEP'
	* @return: retorna o prazo e o valor da entrega caso exista, caso não exista retorna FALSE
	*/
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
	
	/**
	* TODO: Verifica se existe alguma regra cadastrada para a faixa de CEP informada. Essa informação pode ser acessada em 'Frete' → 'Tabela de Frete'
	* @return: na tabela de frete é possível configurar alguns valores adicionais. isso é tratado antes de retornar os valores de frete
	* @return: retorna o prazo e o valor da entrega caso exista, caso não exista retorna FALSE
	*/
	public static function buscarRegraFrete(int $idLoja, object $faixaCep, object $formaDeEntrega, object $medidasDoProduto)
	{
		$regraDeFrete = Frete::regra($idLoja, $faixaCep, $formaDeEntrega->id, $medidasDoProduto->peso);
		if ($regraDeFrete) {
			//TODO: calculando valores adicionais
			$regraDeFrete->valor_frete += $regraDeFrete->valor_adicional_despacho;
			$regraDeFrete->valor_frete += $regraDeFrete->valor_adicional_percnota * $medidasDoProduto->valor_venda / 100;
			$regraDeFrete->valor_frete += ($medidasDoProduto->peso - $regraDeFrete->peso_ini) * $regraDeFrete->valor_adicional_kg;
			return [
				'valor_frete' => $regraDeFrete->valor_frete,
				'prazo_entrega' => intval($regraDeFrete->prazo_entrega_dias),
			];
		}
		return false;
	}

	/**
	* TODO: Verifica se existe algum feriado onde a loja não irá funcionar para acrescentar esses dias ao prazo de entrega.
	* @return: retorna o prazo de entrega somado com a quantidade de feriados existentes entre hoje e a data prevista
	*/
	public static function calcularDiasAdicionaisFeriado(int $idLoja, int $prazoDeEntrega)
	{
		$dataDeEntrega = Carbon::now()->addDay($prazoDeEntrega)->format('Y-m-d');
		return Frete::diasAdicionaisFeriado($idLoja, $dataDeEntrega);
	}

	/**
	* TODO: Verifica se o CEP informado está na lista de bloqueio que pode ser acessada em 'Frete' → 'Bloqueio de CEP'
	* @return: retorna um registro do banco de dados caso encontre ou NULL caso não encontre
	*/
	public static function buscarCepBloqueado(int $idLoja, string $cep)
	{
		return Frete::cepBloqueado($idLoja, $cep);
	}
}