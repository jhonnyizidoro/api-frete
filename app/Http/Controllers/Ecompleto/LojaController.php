<?php

namespace App\Http\Controllers\Ecompleto;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

//Importação de Models
use App\Models\Ecompleto\Loja;

class LojaController extends Controller
{
	public static function buscarFormasEntrega(int $idLoja)
	{
		return Loja::formasDeEntrega($idLoja, true);
	}

	public static function buscarEnderecos(int $idLoja)
	{
		return Loja::enderecos($idLoja);
	}

	public static function buscarInformacoesPrivadas(int $idLoja)
	{
		return Loja::informacoesPrivadas($idLoja);
	}

}
