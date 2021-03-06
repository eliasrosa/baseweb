<?php
#!/usr/bin/php -dmemory_limit=128M

unset($argv[0]);
$com_total = count($argv);
$com_ok = 0;
$com_erro = 0;

if ($com_total) {
    echo sprintf("\nInstalando %s componente(s) novo(s)\n", $com_total);
    echo "================================================================================\n";

    foreach ($argv as $com) {

        $pasta = sprintf('components/%s', $com);
        $url = sprintf('https://github.com/eliasrosa/bwcom_%s', $com);
        
        echo "\n\n";
        echo sprintf("Instalando componente '%s':\n", $com);
        echo sprintf("Verificando URL em %s ... ", $url);

        $verify_url = (bool) @file_get_contents($url);
        echo $verify_url ? 'OK' : 'ERRO';
        echo "\n";

        if (!$verify_url) {
            $com_erro++;
            continue;
        }

        if (is_dir('../' . $pasta)) {
            $com_erro++;
            echo sprintf("Já existe o componente %s em ../%s\n", $com, $pasta);
            continue;
        }

        $rep = sprintf('https://github.com/eliasrosa/bwcom_%s.git', $com);
        $command = sprintf('cd ..;git submodule add %s %s', $rep, $pasta);

        $com_ok++;
        system($command, $retorno);
    }

    echo "\n\n";
    echo "================================================================================\n";
    echo sprintf("%s componente(s) instalado(s)!\n", $com_ok);
    echo sprintf("%s erro(s) encontrado(s)!\n\n", $com_erro);
} else {
    echo "Nenhum componente foi encontrado!\n";
}
