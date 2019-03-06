<?php

namespace App\Http\Controllers\Ecompleto;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

//Importação de Models
use App\Models\Ecompleto\Endereco;

class EnderecoController extends Controller
{
	public static function buscarFaixaCep($cep)
	{
		return Endereco::faixaCep($cep);
	}
}
