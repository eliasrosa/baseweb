<?
defined('BW') or die("Acesso negado!");

bwAdm::init('/login');

//
bwHtml::setTitle("Administração");

$template = bwTemplate::getInstance();
$login = bwLogin::getInstance();
?>

<!DOCTYPE html>
<html>
    <head>              

        <!-- jQuery e jQuery UI -->
        <link type="text/css" href="<?= BW_URL_JAVASCRIPTS ?>/jquery/themes-1.8rc2/redmond/style.css" rel="Stylesheet" />
        <script type="text/javascript" src="<?= BW_URL_JAVASCRIPTS ?>/jquery/jquery-1.7.1.min.js"></script> 
        <script type="text/javascript" src="<?= BW_URL_JAVASCRIPTS ?>/jquery/ui-1.8rc2.js"></script> 

        {BW HEAD}

        <!-- Head da página -->
        <link type="text/css" href="<?= $template->getUrl() ?>/css/styles.css" rel="Stylesheet" />
        <script type="text/javascript" src="<?= $template->getUrl() ?>/js/comum.js"></script>       
        
        <!--[if lte IE 8]>
            <link type="text/css" href="<?= $template->getUrl() ?>/css/style-ie.css" rel="Stylesheet" />
        <![endif]-->

    </head>
    <body>

        <div id="page">

            <div id="top">
                <h1><?= bwCore::getConfig()->getValue('site.titulo'); ?></h1>
                <div><a href="<?= bwRouter::_('/sair'); ?>">Sair</a></div>
                <div><a href="<?= bwRouter::_('/config'); ?>">Configurações</a></div>
                <div><a href="<?= BW_URL_BASE2 ?>" target="_blank">Visualizar site <?= bwCore::getConfig()->getValue('site.titulo'); ?></a></div>
            </div>

            <div id="menu">
                <?
                $com = bwRequest::getVar('com', '');
                foreach (bwComponent::getAll() as $c) {
                    if ($c['adm_visivel']) {
                        $active = ($com == $c['id']) ? ' active' : '';
                        $class = "{$c['id']}{$active}";
                        echo sprintf('<div class="programas %s"><a href="%s">%s</a></div>', $class, bwRouter::_('/'.$c['id']), $c['nome']
                        );
                    }
                }
                ?>
            </div>

            <div id="main">
                {BW VIEW}
            </div>

            <div id="rodape">
                <p>BaseWeb 2.0 - Desenvolvido por Elias da Rosa - <a href="http://www.eliasdarosa.com.br" target="_blank">http://www.eliasdarosa.com.br</a></p>
            </div>

        </div>      
    </body>
</html>
