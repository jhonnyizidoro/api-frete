<?php

namespace App\Models\Ecompleto;

use Illuminate\Database\Eloquent\Model;
use DB;

class Endereco extends Model
{
	public static function faixaCep(string $cep)
	{
		return DB::table('faixa_cep AS fc')
		->join('estados AS e', 'fc.esta_cod', 'e.esta_cod')
		->join('paises AS p', 'e.pais_cod', 'p.sigla')
		->where([
			['fc.faixa_ini', '<=', $cep],
			['fc.faixa_fim', '>=', $cep],
		])
		->first();
	}
}
