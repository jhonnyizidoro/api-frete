<?php

Route::prefix('frete')->namespace('Ecompleto')->group(function() {
	Route::get('/produto/{idLoja}/{idProduto}/{cep}/{quantidade?}', 'FreteController@calcularFreteProduto');
});