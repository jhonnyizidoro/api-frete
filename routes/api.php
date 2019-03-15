<?php

Route::prefix('frete')->namespace('Ecompleto')->group(function() {
	Route::get('/{tipo}/{idLoja}/{idProduto}/{cep}/{quantidade?}', 'FreteController@calcularFrete')->where(['tipo' => 'produto|carrinho']);
});