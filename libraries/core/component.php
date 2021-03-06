<?php

defined('BW') or die("Acesso negado!");

class bwComponent extends bwObject
{
    var $id = '';
    var $nome = '';
    var $adm_visivel = true;

    //
    public function __construct()
    {
        parent::__construct();
    }

    function openById($table, $id)
    {
        $tb = Doctrine::getTable($table);

        $db = $tb->find($id);

        if (!$db)
            $db = $tb->create();

        return $db;
    }

    function retorno($db, $retorno = array())
    {
        $retorno = array_merge(array(
            'retorno' => true,
            'redirect' => false,
            'labels' => array(),
            'camposErros' => array(),
            ), $retorno);


        if (is_bool($db) && !$db && count($retorno['camposErros'])) {
            $dados = array();
            $erros = array();
            $isErros = (!$retorno['retorno'] || count($retorno['camposErros'])) ? true : false;
        } else {
            $dados = count($db->toArray()) ? $db->toArray() : array();
            $erros = $db->getErrorStack();
            $retorno['labels'] = $db->labels;
            $isErros = (!$retorno['retorno'] || $erros->count() || count($retorno['camposErros'])) ? true : false;
        }

        $camposErros = array();
        $errosMsg = array(
            'unique' => 'O valor informado já existe, este valor deve ser único!',
            'notnull' => 'Campo não encontrado!',
            'notblank' => 'Este campo não pode estar em branco!',
            'email' => 'O e-mail informado não é válido ou não existe!',
            'type' => 'O tipo do valor está incorreto!',
            'integer' => 'O valor informado deve ser um número inteiro!',
            'upload' => 'Houve um erro ao tenta enviar o arquivo!',
            'nospace' => 'O valor não de conter espaços em branco!',
            'alias' => 'O valor informado não deve conter caracteres especiais!',
            'notbezeroorsmaller' => 'O valor informado deve ser maior que zero!',
        );

        if ($isErros) {
            $msg = "Os seguintes campos devem ser preenchidos corretamente:\n";
            foreach ($erros as $campo => $errorCodes) {
                foreach ($errorCodes as $code) {
                    $codeMsg = isset($errosMsg[$code]) ? $errosMsg[$code] : $code;
                    $camposErros[$campo][] = $codeMsg;
                }
            }

            foreach ($retorno['camposErros'] as $campo => $errorCodes) {
                foreach ($errorCodes as $code) {
                    $codeMsg = isset($errosMsg[$code]) ? $errosMsg[$code] : $code;
                    $camposErros[$campo][] = $codeMsg;
                }
            }

            //  print_r($retorno);
            foreach ($camposErros as $k => $v) {
                $msg .= "\n- " . $retorno['labels'][$k];
            }
        } else {
            $msg = 'Solicitação concluída com sucesso!';
        }

        $retorno = array(
            'retorno' => !$isErros,
            'dados' => bwUtil::array2query($dados),
            'msg' => $msg,
            'camposErros' => $camposErros,
            'redirect' => $retorno['redirect'],
        );

        return $retorno;
    }

    function save($table, $dados = array(), $primary = 'id', $rel = array())
    {
        $tb = Doctrine::getTable($table);

        if (isset($dados[$primary]) && $dados[$primary]) {
            $pc = $tb->find($dados[$primary]);
            $edit = $dados[$primary];
        } else {
            $pc = $tb->create();
            $edit = false;
        }

        try {
            unset($dados[$primary]);
            $pc->fromArray($dados);

            // relacionamentos
            foreach ($rel as $alias => $ids)
                $pc->unlink($alias)->link($alias, $ids);

            $pc->save();

            $pc = $edit ? $pc : $tb->find($pc->$primary);
        } catch (Doctrine_Validator_Exception $e) {
            
        }

        return $pc;
    }

    function remover($table, $dados = array(), $primary = 'id', $rel = array())
    {
        $tb = Doctrine::getTable($table);
        $db = $tb->find($dados[$primary]);

        // relacionamentos
        foreach ($rel as $alias)
            $db->unlink($alias, array(), true);

        $db->delete();

        return $db;
    }

    function getAll()
    {
        $r = array();
        $components = bwFolder::listarConteudo(BW_PATH_COMPONENTS, false, true, false, false);
        sort($components);

        foreach ($components as $com) {
            $file = BW_PATH_COMPONENTS . DS . $com . DS . 'api.php';
            if (bwFile::exists($file)) {
                $class = 'bw' . ucfirst(strtolower($com));
                $api = call_user_func(array($class, 'getInstance'));

                $r[] = $api->getPublicProperties();
            }
        }

        return $r;
    }

    public function getConfig($prefix)
    {
        if (is_null($this->get('_config'))) {
            $c = new bwConfigDB();
            $c->setPrefix('component.' . $this->id);

            $this->set('_config', $c);
        }

        return $this->get('_config');
    }

}

?>
