<?php

namespace App\Http\Controllers\Transportadoras;

use App\Http\Controllers\Controller;
use App\Helpers\SoapClient;
use App\Helpers\XML;

//Importação dos models
use App\Models\Transportadoras\Jadlog;

class JadlogController extends Controller
{
	private static $soapUrl = 'http://www.jadlog.com.br:8080/JadlogEdiWs/services/ValorFreteBean?wsdl';
	
	public static function calcularFrete(int $idLoja, string $cep, object $medidas, object $formaDeEntrega, object $informacoesPrivadasLoja)
	{
		$ws = Self::buscarWs($idLoja, $formaDeEntrega->id_servicorastreamento);
		if ($ws) {
			$response = SoapClient::wsdl(Self::$soapUrl)
			->timeout(5)
			->parameters([[
				'vModalidade' => $ws->modalidade,
				'Password' => $ws->senha,
				'vSeguro' => $ws->tipo_seguro,
				'vVlDec' => $medidas->valor_venda_nota,
				'vVlColeta' => $ws->valor_coleta,
				'vCepOrig' => str_replace("-", "", $ws->cep_origem),
				'vCepDest' => str_replace("-", "", $cep),
				'vPeso' => $medidas->peso,
				'vFrap' => 'N',
				'vEntrega' => 'D',
				'vCnpj' => $informacoesPrivadasLoja->cnpj
			]])
			->call('valorar');
			
			if ($response) {
				$response = XML::parse($response->valorarReturn)->toObject();
				$valorFrete = standardizeFloat($response->Jadlog_Valor_Frete->Retorno);
				if ($valorFrete >= -2) {
					return [
						'valor_frete' => $valorFrete,
						'prazo_entrega' => 1
					]; 
				}
			}
			return false;
		}
	}

	public static function buscarWs(int $idLoja, int $idServicoDeRastreamento)
	{
		$modalidade = $idServicoDeRastreamento === 1 ? 0 : $idServicoDeRastreamento;
		return Jadlog::ws($idLoja, $modalidade);
	}
}