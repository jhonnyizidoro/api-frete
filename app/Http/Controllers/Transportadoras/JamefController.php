<?php

namespace App\Http\Controllers\Transportadoras;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class JamefController extends Controller
{
	public static function calcularFrete(int $idLoja, string $cep, object $enderecoLoja, object $medidasDoProduto, object $informacoesPrivadasLoja)
	{
		dd('teste');
	}
}
