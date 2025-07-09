<?php
require_once("custom/php/common.php");

if (is_user_logged_in() && current_user_can('manage_items')) {
    if (!isset($_REQUEST['estado'])) {

    $tipoDeItensQuery = "SELECT * FROM item_type ORDER BY item_type.name";
    $resultadoTipoDeItensQuery = mysqli_query($link, $tipoDeItensQuery);

    if (!$resultadoTipoDeItensQuery) {
        die ('Query falhou: ' . mysqli_error($link));
    }

    if (mysqli_num_rows($resultadoTipoDeItensQuery) > 0) {

        echo "<table>
        <tr>
            <th>tipo de item</th>
            <th>id</th>
            <th>nome do item</th>
            <th>estado</th>
            <th>ação</th>
        </tr>";

        while ($tipoDeItens = mysqli_fetch_assoc($resultadoTipoDeItensQuery)) {

            $itemQuery = "SELECT * FROM item WHERE item.item_type_id = {$tipoDeItens['id']}";
            $resultadoItemQuery = mysqli_query($link, $itemQuery);

            if (!$resultadoItemQuery) {
                die ('Query falhou: ' . mysqli_error($link));
            }

            $numItens = mysqli_num_rows($resultadoItemQuery);
            if ($numItens > 0) {
                $primeiroCampo = true;

                while ($item = mysqli_fetch_assoc($resultadoItemQuery)) {
                    echo "<tr>";

                    if ($primeiroCampo) {
                    echo "<td rowspan='$numItens'> {$tipoDeItens['name']} </td>";
                    $primeiroCampo = false;
                    }

                    $acao = ($item['state'] === 'active')
                    ? "<a href='#'>[editar]</a> 
                    <a href='#'>[desativar]</a>
                    <a href='#'>[apagar]</a>"
                    : "<a href='#'>[editar]</a> 
                    <a href='#'>[ativar]</a> 
                    <a href='#'>[apagar]</a>";

                    echo "<td>{$item['id']}</td>
                    <td>{$item['name']}</td>
                    <td>{$item['state']}</td>
                    <td>$acao</td>
                    </tr>";
                }

            } else {
                echo "<tr>
                <td>{$tipoDeItens['name']}</td>
                <td colspan='4'><strong> Não existem itens para este tipo de item </strong></td>
                </tr>";
            }
        } 

    } else {
        echo "<tr><td colspan='5'> Não há tipos de itens </td></tr>";
    }
        
    echo "</table>";

    echo "<h3>Gestão de itens - introdução</h3>";
    echo "<span class='obrigatorio'><strong> * Obrigatório </strong></span>";

    echo "<form action='' method='POST'> 
    <label> Nome: <span class='obrigatorio'> * </span></label>
    <input type='text' class='form-control' name='nome' placeholder='Exemplo'>
    <label><strong> Tipo<span class='obrigatorio''>* </span></strong></label>";

    $resultadoTipoDeItensQuery = mysqli_query($link, $tipoDeItensQuery);
    while ($tipoDeItem = mysqli_fetch_assoc($resultadoTipoDeItensQuery)) {
        echo "<input type='radio' name='tipo' value={$tipoDeItem['id']}> {$tipoDeItem['name']}";
    }

    echo "<label><strong> Estado<span class='obrigatorio'>* </span></strong></label>";
    echo "<input type='radio' name='estado_radio' value='active'> Active
        <input type='radio' name='estado_radio' value='inactive'> Inactive&nbsp";
    echo "<input type='hidden' name='estado' value='inserir'>";
    echo "<button type='submit' class='buttons button-color'> Submeter";
    echo "</form>";

    } else if ($_REQUEST['estado'] == 'inserir') {
        echo "<h3>Gestão de itens - inserção</h3>";

        $erros = "";

        $nome = isset($_REQUEST['nome']) ? trim($_REQUEST['nome']) : '';
        $tipo = isset($_REQUEST['tipo']) ? $_REQUEST['tipo'] : '';
        $estado_radio = isset($_REQUEST['estado_radio']) ? $_REQUEST['estado_radio'] : '';

        if (empty($nome)) {
            $erros .= "O campo Nome é obrigatório.<br>";
        } else if (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚâêîôûÂÊÎÔÛãõÃÕçÇ ]{2,}$/u", $nome)) {
            $erros .= "O campo Nome deve de conter apenas números, letras, espaços e um mínimo de 2 caracteres.<br>";
        }

        if (empty($tipo)) {
            $erros .= "O campo Tipo é obrigatório.<br>";
        }

        if (empty($estado_radio)) {
            $erros .= "O campo Estado é obrigatório.<br>";
        }

        if ($erros != "") {
            echo "<span class='error'> Ocorreram os seguintes erros: </span><br>";
            echo "$erros<br>";
            voltar_atras();

        } else {
            $insercao = "INSERT INTO item (id, name, item_type_id, state) VALUES (NULL, '$nome', '$tipo', '$estado_radio')";
            $resultadoInsercao = mysqli_query($link, $insercao);

            if ($resultadoInsercao) {
                echo "<span class='success'> Inseriu os dados de novo item com sucesso. </span><br><br>";
                echo "<a href='$current_page'> Continuar </a>";
            } else {
                echo "<span class='error'> Ocorreu um erro ao inserir o novo item: </span>" . mysqli_error($link) . "<br><br>";
                voltar_atras();
            }
        }
    }

} else {
    echo "<span class='error'> Não tem autorização para aceder a esta página. </span>";
}

mysqli_close($link);
?>