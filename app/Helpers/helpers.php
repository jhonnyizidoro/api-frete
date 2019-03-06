<?php

function json($retorno, $mensagemDeRetorno, bool $status = true, int $httpCode = 200)
{
	return response()->json([
		'status' => $status,
		'http_code' => $httpCode,
		'message' => $mensagemDeRetorno,
		'return' => $retorno
	], $httpCode);
}

function toFloat($string)
{
	$string = str_replace('.', '', $string);
	$string = str_replace(",", ".", $string);
	return floatval($string);
}