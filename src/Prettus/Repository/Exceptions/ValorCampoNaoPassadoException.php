<?php

namespace Prettus\Repository\Exceptions;

use App\Core\Exceptions\AbstractException;

class ValorCampoNaoPassadoException extends AbstractException
{
    public function __construct($campoNumero)
    {
        parent::__construct(['error' => "O valor do campo $campoNumero não foi passado."]);
    }
}
