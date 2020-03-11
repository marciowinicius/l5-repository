<?php

namespace Prettus\Repository\Exceptions;

use App\Core\Exceptions\AbstractException;

class CondicaoCampoNaoSuportadaException extends AbstractException
{
    public function __construct($campoNumero)
    {
        parent::__construct(['error' => "A condição do campo $campoNumero não é suportada."]);
    }
}
