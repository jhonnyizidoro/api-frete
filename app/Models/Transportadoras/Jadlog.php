<?php

namespace App\Models\Transportadoras;

use Illuminate\Database\Eloquent\Model;
use DB;

class Jadlog extends Model
{
	public static function ws(int $idLoja, int $modalidade)
	{
		return DB::table('ws_jadlog')
		->where([
			['id_loja', $idLoja],
			['modalidade', $modalidade]
		])
		->first();
	}
}
