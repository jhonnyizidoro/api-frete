<?php

namespace App\Models\Ecompleto;

use Illuminate\Database\Eloquent\Model;
use DB;

class Loja extends Model
{
	public static function formasDeEntrega(int $idLoja)
	{
		$cdnServer = env('ECOMPLETO_SERVER_CDN');
		return DB::table('forma_entrega AS fe')
		->select(
			'fe.id',
			'fe.nome',
			'fe.texto',
			'fel.nome AS frete_gratis',
			't.nome AS transportadora',
			'fe.pagto_entrega',
			'fe.id_servicorastreamento',
			'fe.calculo_online',
			'fe.bloquear_formapagto',
			'fe.valor_adicional',
			'fe.tipo_calc_peso',
			'fe.formula_cubado',
			'fe.cobrar_imposto',
			'fe.label_fretegratis',
			'fe.calculo_itens_adicionais',
			'fe.prazo_entrega',
			'fe.somente_emergencia',
			'fe.exibe_prazoentrega',
			'fe.tipo_adicional',
			'fe.info_adicional',
			'fe.id_cliente',
			'fe.cache_busca',
			'fe.valor_declarado',
			'fe.verifica_embalagem',
			'fe.volume_cubado_unitario',
			'fe.apelido_marketplace',
			'fe.codigo_interno',
			'fe.codigo_integrador',
			'fe.disponibilidade',
			't.id as id_transportadora'
		)
		->selectRaw("'{$cdnServer}'||t.image_icon AS image_icon")
		->selectRaw("'{$cdnServer}'||t.image_ficha_entrega AS image_ficha_entrega")
		->leftJoin('transportadoras AS t', 't.id', 'fe.id_transportadora')
		->leftJoin('forma_entrega_label AS fel', 'fel.id', 'fe.label_fretegratis')
		->where([
			['fe.reve_cod', $idLoja],
			['fe.status', true],
			['fe.somente_emergencia', false],
		])
		->orderBy('fe.id_servicorastreamento', 'DESC')
		->get();
	}

	public static function informacoesPrivadas(int $idLoja)
	{
		return DB::table('revendas AS r')
		->select(
			'r.proprietario1',
			'r.proprietario2',
			'r.dtaniver_proprietario1',
			'r.dtaniver_proprietario2',
			'r.status',
			'r.id_usuario_cadastro',
			'r.dia_vencimento',
			'r.id_pacote',
			'r.empresario_nacionalidade',
			'r.empresario_rg',
			'r.empresario_orgaoemissor',
			'r.empresario_cpf',
			'r.id_centrocusto',
			'r.id_faturamento_frequencia',
			'r.faturamento_periodo_ultimo',
			'r.faturamento_periodo_proximo',
			'r.id_vendedor',
			'r.id_designer',
			'r.id_implantador',
			'r.id_tabelaprecos',
			'r.cnpj',
			'r.inscricao_estadual',
			'r.email',
			'rcfg.id_redecard',
			'rcfg.tx_redecard',
			'rcfg.avs_redecard',
			'rcfg.id_visanet',
			'rcfg.tx_visanet',
			'rcfg.id_cobrebemcsid',
			'rcfg.id_cobrebem',
			'rcfg.homologa_visanet',
			'rcfg.shopline_codemp',
			'rcfg.shopline_chave',
			'rcfg.amex_estabelecimento',
			'rcfg.amex_accesscode',
			'rcfg.amex_user',
			'rcfg.amex_userpass',
			'rcfg.email_pagseguro',
			'rcfg.fcontrol_email',
			'rcfg.fcontrol_senha',
			'rcfg.email_faturamento',
			'rcfg.email_pagamentodigital',
			'rcfg.deposito_banco',
			'rcfg.deposito_agencia',
			'rcfg.deposito_conta',
			'rcfg.deposito_titular',
			'rcfg.deposito_cpfcnpj',
			'rcfg.deposito_cedente',
			'rcfg.bb_cedente',
			'rcfg.sobretaxa_frete',
			'rcfg.frete_minimo',
			'rcfg.prazo_logistica',
			'rcfg.datassl_ini',
			'rcfg.datassl_fim',
			'rcfg.email_paypal',
			'rcfg.sis_baixaestoque',
			'rcfg.sis_smspedido',
			'rcfg.sis_currentdollar',
			'rcfg.correios_cdempresa',
			'rcfg.correios_dssenha',
			'rcfg.correios_taxatransporte',
			'p.pess_cod'
		)
		->join('revendas_cfg AS rcfg', 'rcfg.reve_cod', 'r.reve_cod')
		->join('pessoas AS p', 'r.pess_cod', 'p.pess_cod')
		->where('r.reve_cod', $idLoja)
		->orderBy('r.reve_cod')
		->first();
	}

	public static function enderecos(int $idLoja)
	{
		return DB::table('revendas AS r')
		->select(
			'e.endereco',
			'e.numero',
			'e.complemento',
			'e.cep',
			'e.bairro',
			'c.cida_nome',
			'u.esta_nome',
			'e.esta_cod',
			'p.nome',
			'p.sigla',
			'e.apelido_endereco',
			'e.destinatario_endereco'
		)
		->join('enderecos AS e', 'e.pess_cod', 'r.pess_cod')
		->join('paises AS p', 'p.id', 'e.id_pais')
		->join('cidades AS c', 'c.cida_cod', 'e.cida_cod')
		->join('estados AS u', 'u.esta_cod', 'e.esta_cod')
		->where([
			['r.reve_cod', $idLoja],
			['e.status', 'true'],
			['e.tipo_endereco', 'S'],
		])
		->orderBy('e.padrao', 'DESC')
		->get();
	}

	public static function parametro(int $idLoja, $nomeParametro)
	{
		return DB::table('lojas_camposadicionais_valor AS lcv')
		->select(
			'lcv.valor_camposadicionais AS valor_campo',
			'lcc.apelido AS nome_campo',
			'lcc.descricao AS descricao_campo'
		)
		->join('lojas_camposadicionais_chave AS lcc', 'lcv.id_camposadicionais_chave', 'lcc.id')
		->where([
			['lcv.id_loja', $idLoja],
			['lcc.apelido', $nomeParametro]
		])
		->first();
	}

}
