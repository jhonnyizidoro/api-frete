<?php

Route::prefix('frete')->namespace('Ecompleto')->group(function() {
	Route::get('/carrinho/{idLoja}/{idBasket}/{cep}', 'FreteController@calcularFreteCarrinho');
	Route::get('/produto/{idLoja}/{idProduto}/{cep}/{quantidade?}', 'FreteController@calcularFreteProduto');
});