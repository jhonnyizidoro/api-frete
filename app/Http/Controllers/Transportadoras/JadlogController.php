<?php

namespace App\Http\Controllers\Transportadoras;

use App\Http\Controllers\Controller;
use App\Helpers\SoapClient;

use App\Models\Transportadoras\Jadlog;

class JadlogController extends Controller
{
	public static function calcularFrete(int $idLoja, string $cep, object $medidasDoProduto, object $formaDeEntrega, object $informacoesPrivadasLoja)
	{
		$ws = Self::buscarWs($idLoja, $formaDeEntrega->id_servicorastreamento);
		if ($ws) {
			$response = SoapClient::wsdl('http://www.jadlog.com.br:8080/JadlogEdiWs/services/ValorFreteBean?wsdl')
			->parameters([
				[
					'vModalidade' => $ws->modalidade,
					'Password' => $ws->senha,
					'vSeguro' => $ws->tipo_seguro,
					'vVlDec' => $medidasDoProduto->valor_venda_nota,
					'vVlColeta' => $ws->valor_coleta,
					'vCepOrig' => str_replace("-", "", $ws->cep_origem),
					'vCepDest' => str_replace("-", "", $cep),
					'vPeso' => $medidasDoProduto->peso,
					'vFrap' => 'N',
					'vEntrega' => 'D',
					'vCnpj' => $informacoesPrivadasLoja->cnpj
				]
			])
			->call('valorar');
			
			if ($response) {
				$response = SoapClient::parseXML($response->valorarReturn);
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