<?php

namespace App\Http\Controllers\Transportadoras;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\SoapClient;
use SoapClient as PHPSoap;
use Carbon\Carbon;

class JamefController extends Controller
{

	private static $soapUrl = 'http://www.jamef.com.br/webservice/JAMW0520.apw?WSDL';

	public static function calcularFrete(int $idLoja, string $cep, object $enderecoLoja, object $formaDeEntrega, object $medidasDoProduto, object $informacoesPrivadasLoja)
	{
		$response = SoapClient::wsdl(Self::$soapUrl)
		->parameters([
			'JAMW0520_05' => [
				'TIPTRA' => '1',
				'CNPJCPF' => $informacoesPrivadasLoja->cnpj,
				'MUNORI' => removerAcentos($enderecoLoja->cida_nome),
				'ESTORI' => $enderecoLoja->esta_cod,
				'SEGPROD' => '000004',
				'QTDVOL' => 1,
				'PESO' => $medidasDoProduto->peso,
				'VALMER' => $medidasDoProduto->valor_venda,
				'METRO3' => $medidasDoProduto->peso_cubico,
				'CNPJDES' => $informacoesPrivadasLoja->cnpj,
				'CEPDES' => str_replace('-', '', $cep),
			]
		])
		->call('JAMW0520_05');

		if (strpos($response->JAMW0520_05RESULT->MSGERRO, 'Ok') !== false) {
			$prazoDeEntrega = Self::calcularPrazoDeEntrega($cep, $enderecoLoja, $informacoesPrivadasLoja->cnpj);
			if ($prazoDeEntrega) {
				return [
					'valor_frete' => end($response->JAMW0520_05RESULT->VALFRE->AVALFRE)->TOTAL,
					'prazo_entrega' => $prazoDeEntrega
				];
			}
		}
		return false;
	}

	public static function calcularPrazoDeEntrega(string $cep, object $enderecoLoja, string $cnpj)
	{
		$response = SoapClient::wsdl(Self::$soapUrl)
		->parameters([
			'JAMW0520_04' => [
				"TIPTRA" => "1",
				"MUNORI" => removerAcentos($enderecoLoja->cida_nome),
				"ESTORI" => $enderecoLoja->esta_cod,
				"CNPJCPF" => $cnpj,
				"CDATINI" => date("d/m/Y"),
				"CHORINI" => date("h:i"),
				'CEPDES' => str_replace('-', '', $cep),
			]
		])
		->call('JAMW0520_04');
		
		if ($response->JAMW0520_04RESULT->MSGERRO === 'OK') {
			$dataDeHoje = Carbon::now();
			$dataDeEntrega = Carbon::createFromFormat('d/m/y', $response->JAMW0520_04RESULT->CDTMAX);
			return $dataDeHoje->diffInDays($dataDeEntrega);
		}
		return false;
	}
}