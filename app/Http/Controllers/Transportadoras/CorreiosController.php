<?php

namespace App\Http\Controllers\Transportadoras;

use App\Http\Controllers\Controller;
use Ixudra\Curl\Facades\Curl;
use App\Helpers\SoapClient;
use App\Helpers\XML;

//Importação dos controllers
use App\Http\Controllers\Ecompleto\LojaController;

class CorreiosController extends Controller
{
	private static $curlUrl = 'http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo';
	private static $errosPermitidos = ['0', '009', '010', '011']; //Erros que retornam dos Correios mas não afetam o funcionamento da entrega
	private static $medidasLimites = [
		'comprimentoMaximo' => 105,
		'larguraMaxima' => 105,
		'alturaMaxima' => 105,
		'comprimentoMinimo' => 16,
		'larguraMinima' => 11,
		'alturaMinima' => 2,
		'pesoMaximo' => 30,
		'somaMaximaDimensoes' => 200
	];

	public static function calcularFrete(int $idLoja, string $cepDestino, string $cepOrigem, int $idServico, object $medidas, $informacoesLoja)
	{
		$medidas = Self::formatarMedidasCorreios($medidas, $idLoja);
		$response = Curl::to(Self::$curlUrl)
		->withTimeout(555)
		->withData([
			'nCdEmpresa' => $informacoesLoja->correios_cdempresa,
			'sDsSenha' => $informacoesLoja->correios_dssenha,
			'nCdServico' => sprintf("%05d", $idServico),
			'sCepOrigem' => $cepOrigem,
			'sCepDestino' => $cepDestino,
			'nCdFormato' => 1,
			'sCdMaoPropria' => 'N',
			'sCdAvisoRecebimento' => 'N',
			'nVlPeso' => $medidas->peso,
			'nVlLargura' => $medidas->largura,
			'nVlComprimento' => $medidas->profundidade,
			'nVlAltura' => $medidas->altura,
			'nVlDiametro' => 0,
			'nVlValorDeclarado' => $medidas->valor_venda,
		])
		->post();

		$response = XML::parse($response)->toObject();
		
		if (is_object($response) && in_array($response->Servicos->cServico->Erro, Self::$errosPermitidos)) {
			$response = $response->Servicos->cServico;
			return [
				'valor_frete' => standardizeFloat($response->Valor),
				'prazo_entrega' => intval($response->PrazoEntrega),
			];
		}
		return false;
	}

	private static function formatarMedidasCorreios(object $medidas, int $idLoja)
	{
		//Formatando medidas mínimas
		if ($medidas->profundidade < Self::$medidasLimites['comprimentoMinimo']) {
			$medidas->profundidade = Self::$medidasLimites['comprimentoMinimo'];
		}
		if ($medidas->largura < Self::$medidasLimites['larguraMinima']) {
			$medidas->largura = Self::$medidasLimites['larguraMinima'];
		}
		if ($medidas->altura < Self::$medidasLimites['alturaMinima']) {
			$medidas->altura = Self::$medidasLimites['alturaMinima'];
		}

		//Formatando medidas máximas
		if (LojaController::buscarParametro($idLoja, 'correios_limite_excedido')) {
			if ($medidas->peso > Self::$medidasLimites['pesoMaximo']) {
				$medidas->peso = Self::$medidasLimites['pesoMaximo'];
			}
			if ($medidas->profundidade > Self::$medidasLimites['comprimentoMaximo']) {
				$medidas->profundidade = Self::$medidasLimites['comprimentoMaximo'];
			}
			if ($medidas->largura > Self::$medidasLimites['larguraMaxima']) {
				$medidas->largura = Self::$medidasLimites['larguraMaxima'];
			}
			if ($medidas->altura > Self::$medidasLimites['alturaMaxima']) {
				$medidas->altura = Self::$medidasLimites['alturaMaxima'];
			}
			if (($medidas->profundidade + $medidas->largura + $medidas->altura) > Self::$medidasLimites['somaMaximaDimensoes']) {
				$medidas->profundidade = floor(Self::$medidasLimites['somaMaximaDimensoes'] / 3);
				$medidas->largura = floor(Self::$medidasLimites['somaMaximaDimensoes'] / 3);
				$medidas->altura = floor(Self::$medidasLimites['somaMaximaDimensoes'] / 3);
			}
		}

		return $medidas;
	}

}
