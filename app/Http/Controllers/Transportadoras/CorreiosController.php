<?php

namespace App\Http\Controllers\Transportadoras;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Ixudra\Curl\Facades\Curl;

class CorreiosController extends Controller
{

	private static $errosPermitidos = ['0', '009', '010', '011'];

	public static function calcularFrete(int $idLoja, string $cepDestino, string $cepOrigem, int $idServico, object $medidas, $informacoesLoja)
	{
		$response = Curl::to('http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo')
		->withData([
			'nCdEmpresa' => $informacoesLoja->correios_cdempresa,
			'sDsSenha' => $informacoesLoja->correios_dssenha,
			'nCdServico' => $idServico,
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

		$response = simplexml_load_string($response);
		$retorno = $response->Servicos->cServico;

		if (is_object($retorno) && in_array($retorno->Erro, Self::$errosPermitidos)) {
			dd($retorno);
		}

	}
}
