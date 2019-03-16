<?php

namespace App\Http\Controllers\Transportadoras;

use App\Http\Controllers\Controller;
use App\Helpers\SoapClient;
use App\Helpers\XML;
use Ixudra\Curl\Facades\Curl;
use Spatie\ArrayToXml\ArrayToXml;

//Importação dos controllers
use App\Http\Controllers\Ecompleto\LojaController;

class TNTController extends Controller
{
	private static $curlUrl = 'http://ws.tntbrasil.com.br/servicos/CalculoFrete';

	public static function calcularFrete(int $idLoja, string $cep, object $enderecoLoja, object $medidas, object $formaDeEntrega, object $informacoesPrivadasLoja)
	{
		$loginTNT = LojaController::buscarParametro($idLoja, 'ws_tnt_login');

		if (!$loginTNT) {
			return false;
		}

		//TODO: Buscando parâmetros necessários para o CURL
		$situacaoTributariaTNT = LojaController::buscarParametro($idLoja, 'ws_tnt_situacaotributaria');
		$inscricaoEstadualTNT = LojaController::buscarParametro($idLoja, 'ws_tnt_insc_estadual');
		$tipoFreteTNT = LojaController::buscarParametro($idLoja, 'ws_tnt_tipofrete');
		$servicoTNT = LojaController::buscarParametro($idLoja, 'ws_tnt_servico');
		$divisaoTNT = LojaController::buscarParametro($idLoja, 'ws_tnt_divisao');
		$cnpjTNT = LojaController::buscarParametro($idLoja, 'ws_tnt_cnpj');

		//TODO: Formatando os dados buscados
		if (!$inscricaoEstadualTNT) {
			$inscricaoEstadualTNT = $informacoesPrivadasLoja->inscricao_estadual;
		}
		if (!$cnpjTNT) {
			$cnpjTNT = $informacoesPrivadasLoja->cnpj;
		}
		if ($formaDeEntrega->id_servicorastreamento === 2) {
			$servicoTNT = 'ANC';
		} elseif ($formaDeEntrega->id_servicorastreamento === 3) {
			$servicoTNT = 'RNC';
		}
		$inscricaoEstadualTNT = str_replace('-', '', $inscricaoEstadualTNT);
		$cep = str_replace("-", "", $cep);
		$enderecoLoja->cep = str_replace("-", "", $enderecoLoja->cep);

		//TODO: gerando o XML de envio
		$xmlArray = [
			'soapenv:Header' => '',
			'soapenv:Body' => [
				'ser:calculaFrete' => [
					'ser:in0' => [
						'mod:cdDivisaoCliente' => $divisaoTNT,
						'mod:cepDestino' => $cep,
						'mod:cepOrigem' => $enderecoLoja->cep,
						'mod:login' => $loginTNT,
						'mod:nrIdentifClienteDest' => 00000000,
						'mod:nrIdentifClienteRem' => $cnpjTNT,
						'mod:nrInscricaoEstadualDestinatario' => '',
						'mod:nrInscricaoEstadualRemetente' => $inscricaoEstadualTNT,
						'mod:psReal' => $medidas->peso,
						'mod:senha' => '',
						'mod:tpFrete' => $tipoFreteTNT,
						'mod:tpPessoaDestinatario' => 'F',
						'mod:tpPessoaRemetente' => 'J',
						'mod:tpServico' => $servicoTNT,
						'mod:tpSituacaoTributariaDestinatario' => 'CO',
						'mod:tpSituacaoTributariaRemetente' => $situacaoTributariaTNT,
						'mod:vlMercadoria' => $medidas->valor_venda_nota,
					]
				]
			]
		];
		$xml = ArrayToXml::convert($xmlArray, [
			'rootElementName' => 'soapenv:Envelope',
			'_attributes' => [
				'xmlns:soapenv' => 'http://schemas.xmlsoap.org/soap/envelope/',
				'xmlns:ser' => 'http://service.calculoFrete.mercurio.com',
				'xmlns:mod' => 'http://model.vendas.lms.mercurio.com'
			]
		]);

		//TODO: Enviando o XML
		$response = Curl::to(Self::$curlUrl)
		->withTimeout(5)
		->withHeaders([
			"Content-type: text/xml;charset='UTF-8'",
			'Accept: text/xml',
			'Cache-Control: no-cache',
			'Pragma: no-cache',
			'SOAPAction: ' . Self::$curlUrl,
			'Content-length: ' . strlen($xml),
		])
		->withData($xml)
		->post();

		//TODO: retornando a resposta
		$response = XML::parse($response)->toObject();
		$response = $response->Body->calculaFreteResponse->out;
		if (!$response->errorList) {
			return [
				'valor_frete' => $response->vlTotalFrete,
				'prazo_entrega' => $response->prazoEntrega
			];
		}
		return false;
	}
}