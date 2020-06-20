<?php

namespace Prettus\Repository\Criteria;

use Prettus\Repository\Exceptions\CondicaoCampoNaoPassadaException;
use Prettus\Repository\Exceptions\CondicaoCampoNaoSuportadaException;
use Prettus\Repository\Exceptions\ValorCampoNaoPassadoException;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class FiltroRequestCriteria implements CriteriaInterface
{
    private $campoValor;
    private $campoPosicao;
    private $condicaoValor;
    private $condicaoPosicao;
    private $valorValor;
    private $valorPosicao;

    public function apply($model, RepositoryInterface $repository)
    {
        $request = Request::capture()->all();

        for ($i = 0; $i <= 100; $i++) {
            $this->campoPosicao = 'campo' . $i;
            if (array_key_exists($this->campoPosicao, $request) and isset($request[$this->campoPosicao])) {
                $this->verificarCondicaoCampo($i, $request);
                $this->verificarValorCampo($i, $request);

                $this->setarValores($request);

                $relation = null;
                if (stripos($this->campoValor, '.')) {
                    $explode = explode('.', $this->campoValor);
                    $this->campoValor = array_pop($explode);
                    $relation = implode('.', $explode);
                }

                if ($relation) {
                    $model = $model->whereHas($relation, function ($query) {
                        $query->where($this->campoValor, $this->condicaoValor, $this->valorValor);
                    });
                } else {
                    $model = $model->where($this->campoValor, $this->condicaoValor, $this->valorValor);
                }
            } else {
                break;
            }
        }

        return $model;
    }

    private function setarValores(array $request)
    {
        $this->campoValor = $request[$this->campoPosicao];
        $this->campoValor = $this->campoValor == 'null' ? null : $this->campoValor;
        $this->condicaoValor = $this->retornarCondicao($request[$this->condicaoPosicao], $this->campoPosicao);
        if ($this->condicaoValor == 'like' or $this->condicaoValor == 'ilike'){
            $this->valorValor = "%" . $request[$this->valorPosicao] . '%';
        } else {
            $this->valorValor = $request[$this->valorPosicao];
        }
    }

    /**
     * @param int $i
     * @param array $request
     * @throws CondicaoCampoNaoPassadaException
     */
    private function verificarCondicaoCampo(int $i, array $request)
    {
        $this->condicaoPosicao = 'condicao' . $i;
        if (!array_key_exists($this->condicaoPosicao, $request) OR !isset($request[$this->condicaoPosicao])) {
            throw new CondicaoCampoNaoPassadaException($this->campoPosicao);
        }
    }

    /**
     * @param int $i
     * @param array $request
     * @throws ValorCampoNaoPassadoException
     */
    private function verificarValorCampo(int $i, array $request)
    {
        $this->valorPosicao = 'valor' . $i;
        if (!array_key_exists($this->valorPosicao, $request) OR !isset($request[$this->valorPosicao])) {
            throw new ValorCampoNaoPassadoException($this->campoPosicao);
        }
    }

    /**
     * @param $condicaoUrl
     * @param $campoNumero
     * @return string
     * @throws CondicaoCampoNaoSuportadaException
     */
    private function retornarCondicao($condicaoUrl, $campoNumero)
    {
        switch ($condicaoUrl) {
            case "contendo":
                $condicao = "like";
                break;
            case "não contendo":
                $condicao = "not like";
                break;
            case "igual":
                $condicao = "=";
                break;
            case "maior":
                $condicao = ">";
                break;
            case "maior ou igual":
                $condicao = ">=";
                break;
            case "menor":
                $condicao = "<";
                break;
            case "menor ou igual":
                $condicao = "<=";
                break;
            case "diferente":
                $condicao = "<>";
                break;
            case "é":
                $condicao = "=";
                break;
            case "não é":
                $condicao = "<>";
                break;
            default:
                throw new CondicaoCampoNaoSuportadaException($campoNumero);
                break;
        }

        return $condicao;
    }

}
