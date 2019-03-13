<?php

//CLASSE COM FUNÇÕES GLOBAIS DA API

/**
* @param retorno: objeto ou array com dados de retorno
* @param mensagemDeRetorno: mensagem que será retornada
* @param status: booleano que indica se a requisição teve ou não sucesso
* @param httpCode: código HTTP da requisição
* @return: JSON com os dados informados
*/
if (!function_exists('json'))
{
	function json($retorno, $mensagemDeRetorno, bool $status = true, int $httpCode = 200)
	{
		return response()->json([
			'status' => $status,
			'http_code' => $httpCode,
			'message' => $mensagemDeRetorno,
			'return' => $retorno
		], $httpCode);
	}
}

/**
* TODO: recebe uma string e remove os acentos
* @return: string recebida sem acento
*/
if (!function_exists('removerAcentos'))
{
	function removerAcentos(string $string)
	{
		$string = preg_replace("/(á|à|ã|â|ä)/", 'a', $string);
		$string = preg_replace("/(Á|À|Ã|Â|Ä)/", 'A', $string);
		$string = preg_replace("/(é|è|ê|ë)/", 'e', $string);
		$string = preg_replace("/(É|È|Ê|Ë)/", 'E', $string);
		$string = preg_replace("/(í|ì|î|ï)/", 'i', $string);
		$string = preg_replace("/(Í|Ì|Î|Ï)/", 'I', $string);
		$string = preg_replace("/(ó|ò|õ|ô|ö)/", 'o', $string);
		$string = preg_replace("/(Ó|Ò|Õ|Ô|Ö)/", 'O', $string);
		$string = preg_replace("/(ú|ù|û|ü)/", 'u', $string);
		$string = preg_replace("/(Ú|Ù|Û|Ü)/", 'U', $string);
		$string = preg_replace("/(ñ)/", 'n', $string);
		$string = preg_replace("/(Ñ)/", 'N', $string);
		$string = preg_replace("/(ç)/", 'c', $string);
		$string = preg_replace("/(Ç)/", 'C', $string);
		return $string;
	}
}

/**
* @param value: valor em string ou float que será convertido
* TODO: recebe um valor no formato local e retorna no formato padrão. Exemplo: '1.000,00' e retorna 1000.00
*/
if (!function_exists('standardizeFloat'))
{
	function standardizeFloat($value)
	{
		$value = strval($value);
		$value = str_replace('.', '', $value);
		$value = str_replace(',', '.', $value);
		return floatval($value);
	}
}