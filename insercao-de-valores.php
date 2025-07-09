<?php
require_once("custom/php/common.php");

if (is_user_logged_in() && current_user_can('insert_values')) {
    if (!isset($_REQUEST['estado'])) {

        echo "<h3>Inserção de valores - criança - procurar</h3>";
        echo "<p> Introduza um dos nomes da criança a encontrar e/ou a data de nascimento dela </p>";

        echo "<form action='' method='get'>
        <label><strong> Nome </strong></label>
        <input type='text' class='form-control' name='nome'>
        <label><strong> Data de nascimento - (no formato AAAA-MM-DD) </strong></label>
        <input type='text' class='form-control' name='data_nascimento'>";

        echo "<input type='hidden' name='estado' value='escolher_crianca'>";
        echo "<button type='submit' class='buttons button-color'> Submeter";
        echo "</form>";

    } else if ($_REQUEST['estado'] == 'escolher_crianca') {
        echo "<h3>Inserção de valores - criança - escolher</h3>";

        $nome = isset($_REQUEST['nome']) ? $_REQUEST['nome'] : '';
        $data_nascimento = isset($_REQUEST['data_nascimento']) ? $_REQUEST['data_nascimento'] : '';

        $procuraQuery = "SELECT child.id, child.name, child.birth_date FROM child";
        if (!empty($nome) || !empty($data_nascimento)) {
            $procuraQuery .= " WHERE ";
            $condicoes = [];

            if (!empty($nome)) {
                $condicoes[] .= "name LIKE '%$nome%'";
            }

            if (!empty($data_nascimento)) {
                $condicoes[] .= "birth_date = '$data_nascimento'";
            }

            $procuraQuery .= implode(" AND ", $condicoes);
        }
        $resultadoProcuraQuery = mysqli_query($link, $procuraQuery);

        if (!$resultadoProcuraQuery) {
            die ('Query falhou: ' . mysqli_error($link));
        }

        if (mysqli_num_rows($resultadoProcuraQuery) > 0) {  
            while ($procura = mysqli_fetch_assoc($resultadoProcuraQuery)) {
                echo "<a href='insercao-de-valores?estado=escolher_item&crianca=" . $procura['id'] . "'> [" . $procura['name'] . "]</a>  (" . $procura['birth_date'] . ")<br>";
            }
        }
        echo "<br>";
        voltar_atras();

    } else if ($_REQUEST['estado'] == 'escolher_item') {
        echo "<h3>Inserção de valores - escolher item</h3>";

        $_SESSION['child_id'] = $_REQUEST['crianca'];

        $tipoDeItemQuery = "SELECT item_type.id, item_type.name FROM item_type";
        $resultadoTipoDeItemQuery = mysqli_query($link, $tipoDeItemQuery);

        if (!$resultadoTipoDeItemQuery) {
            die ('Query falhou: ' . mysqli_error($link));
        }

        while ($tipoDeItem = mysqli_fetch_assoc($resultadoTipoDeItemQuery)) {
            echo "<strong>" . $tipoDeItem['name'] . "</strong><br>";

            $itemQuery = "SELECT DISTINCT item.id, item.name
            FROM Item
            INNER JOIN subitem ON subitem.item_id = item.id
            WHERE item.item_type_id = {$tipoDeItem['id']}
            AND item.state = 'active'";
            $resultadoItemQuery = mysqli_query($link, $itemQuery);

            if (!$resultadoItemQuery) {
                die ('Query falhou: ' . mysqli_error($link));
            }

            if (mysqli_num_rows($resultadoItemQuery) > 0) {
                echo "<ul>";
                while ($item = mysqli_fetch_assoc($resultadoItemQuery)) {
                    echo "<li>
                    <a href='insercao-de-valores?estado=introducao&item=" . $item['id'] . "'> [" . $item['name'] . "]</a>
                    </li>";
                }
                echo "</ul>";
            } else {
                echo "<p>Sem subitens ativos</p>";
            }
        }
        
        voltar_atras();
        
    }

} else {
    echo "<span class='error'> Não tem autorização para aceder a esta página. </span>";
}

mysqli_close($link);
?>