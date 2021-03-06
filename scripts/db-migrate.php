<?php
require './inc/init.php';

if ($argc == 1 || in_array('--help', $argv)) {
    console_log("Instala e atualiza o banco de dados");
    console_log("Modo de usar: ./db-migrate [opções]");
    console_log("\nOPÇÕES");
    console_log("    -i, --install");
    console_log("    -u, --update");
    console_log("\n");
    
    die();
}

function isInstaled()
{
    $conn = bwCore::getConexao();
    $sql = sprintf("SHOW TABLES FROM %s WHERE Tables_in_%s = 'bw_versao'"
        , bwConfig::$db_name, bwConfig::$db_name);

    $pdo = $conn->execute($sql);
    $pdo->setFetchMode(Doctrine_Core::FETCH_ASSOC);
    $result = $pdo->fetchObject();

    return (bool) $result;
}

function testVersion($com, $version)
{
    $conn = bwCore::getConexao();
    $sql = sprintf("SHOW columns FROM bw_versao WHERE Field LIKE '%s_%%'", $com);

    $pdo = $conn->execute($sql);
    $pdo->setFetchMode(Doctrine_Core::FETCH_ASSOC);
    $result = $pdo->fetchObject();

    $vesion_current = 0;

    if ($result) {

        $arr = explode('_', $result->Field);

        if (count($arr) == 2) {
            list ($com, $vesion_current) = $arr;
        }

        if (count($arr) == 3) {
            list ($tipo, $com, $vesion_current) = $arr;
        }

        if (($vesion_current + 1) == $version) {
            $r = true;
        } else {
            $r = false;
        }
    } elseif ($version == 1 && !$result) {
        $r = true;
    } else {
        $r = false;
    }

    //echo "$com $version > $vesion_current = " . (int) $r . "\n";
    return $r;
}

function install()
{
    //
    if (isInstaled()) {
        console_log("Banco de dados já está instalado");
        return;
    }

    //
    console_log('Instalando banco de dados');

    $file = BW_PATH . DS . 'install' . DS . 'sql' . DS . '1.sql.php';
    $sql = bwFile::getConteudo($file);

    //
    bwCore::getConexao()->execute($sql);

    //
    console_log("Banco de dados instalado com sucesso!");
}

function update($com, $prefix = '')
{
    $path = BW_PATH_COMPONENTS . DS . $com . DS . 'sql' . DS . '%s.sql.php';

    if ($com == 'bw') {
        $path = BW_PATH . DS . 'install' . DS . 'sql' . DS . '%s.sql.php';
    }

    for ($version = 1; true; $version++) {
        $file = sprintf($path, $version);

        if (!bwFile::exists($file)) {
            break;
        }

        if (testVersion($prefix . $com, $version)) {

            $version_old = ($version - 1 < 1) ? 'NEW' : $version - 1;

            $sql = bwFile::getConteudo($file);
            bwCore::getConexao()->execute($sql);
            console_log(sprintf("%s - (%d => %d)"
                    , $prefix . $com
                    , $version_old
                    , $version
                ));
        }
    }
}

function updateComponentes()
{
    $componentes = bwFolder::listarConteudo(
            BW_PATH_COMPONENTS, false, true, false, false);

    foreach ($componentes as $com) {
        update($com, 'com_');
    }
}

if (in_array('-i', $argv) || in_array('--install', $argv)) {
    install();
}

if (in_array('-u', $argv) || in_array('--update', $argv)) {

    if (!isInstaled()) {
        console_log("Banco de dados não está instalado");
        die();
    }

    //
    console_log("Atualizado componentes e plugins");

    //
    update('bw');

    //
    updateComponentes();

    //
    console_log("Banco de dados atualizado com sucesso");
}
