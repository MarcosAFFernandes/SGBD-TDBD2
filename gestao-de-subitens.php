<?php
require_once("custom/php/common.php");

if (is_user_logged_in() && current_user_can('manage_subitems')) {
    if (!isset($_REQUEST['estado'])) {

    $itemQuery = "SELECT * FROM item ORDER BY item.name ASC";
    $resultadoItemQuery = mysqli_query($link, $itemQuery);

    if (!$resultadoItemQuery) {
        die ('Query falhou: ' . mysqli_error($link));
    }

    if (mysqli_num_rows($resultadoItemQuery) > 0) {

        echo "<table>
        <tr>
            <th>item</th>
            <th>id</th>
            <th>subitem</th>
            <th>tipo de valor</th>
            <th>nome do campo no formulário</th>
            <th>tipo do campo no formulário</th>
            <th>tipo de unidade</th>
            <th>ordem do campo no formulário</th>
            <th>obrigatório</th>
            <th>estado</th>
            <th>ação</th>
        </tr>";

        while ($item = mysqli_fetch_assoc($resultadoItemQuery)) {

            $subItensQuery = "SELECT * FROM subitem WHERE subitem.item_id = {$item['id']} ORDER BY subitem.name ASC";
            $resultadoSubItensQuery = mysqli_query($link, $subItensQuery);

            if (!$resultadoSubItensQuery) {
                die ('Query falhou: ' . mysqli_error($link));
            }

            $numSubItens = mysqli_num_rows($resultadoSubItensQuery);
            if ($numSubItens > 0) {
                $primeiroCampo = true;

                while ($subItem = mysqli_fetch_assoc($resultadoSubItensQuery)) {
                    echo "<tr>";

                    if ($primeiroCampo) {
                        echo "<td rowspan='$numSubItens'> {$item['name']}</td>";
                        $primeiroCampo = false;
                    }

                    $nomeUnidade = "-";

                    if (!empty($subItem['unit_type_id']) != NULL) {
                        $tipoDeUnidadeQuery = "SELECT * FROM subitem_unit_type WHERE subitem_unit_type.id = {$subItem['unit_type_id']}";
                        $resultadoTipoDeUnidadeQuery = mysqli_query($link, $tipoDeUnidadeQuery);

                        if (!$resultadoTipoDeUnidadeQuery) {
                            die ('Query falhou: ' . mysqli_error($link));
                        }

                        if (mysqli_num_rows($resultadoTipoDeUnidadeQuery) > 0) {
                            $tipoDeUnidade = mysqli_fetch_assoc($resultadoTipoDeUnidadeQuery);

                            $nomeUnidade = $tipoDeUnidade['name'];
                        }
                    }

                    $obrigatorio = ($subItem['mandatory'] == 1 ? "Sim" : "Não");

                    $acao = ($subItem['state'] === 'active')
                    ? "<a href='#'>[editar]</a>
                    <a href='#'>[desativar]</a>
                    <a href='#'>[apagar]</a>" 
                    : "<a href='#'>[editar]</a>
                    <a href='#'>[ativar]</a>
                    <a href='#'>[apagar]</a>";

                    echo "
                    <td>{$subItem['id']}</td>
                    <td>{$subItem['name']}</td>
                    <td>{$subItem['value_type']}</td>
                    <td>{$subItem['form_field_name']}</td>
                    <td>{$subItem['form_field_type']}</td>
                    <td>$nomeUnidade</td>
                    <td>{$subItem['form_field_order']}</td>
                    <td>$obrigatorio</td>
                    <td>{$subItem['state']}</td>
                    <td>$acao</td>
                    </tr>";
                }

            } else {
                echo "<tr>
                <td>{$item['name']}</td>
                <td colspan='10'>Este item não tem subitens</td>
                </tr>";
            }
        }

    } else {
        echo "<tr><td colspan='11'> Não há itens </td></tr>";
    }

    echo "</table>";

    echo "<h3>Gestão de subitems - introdução</h3>";
    echo "<span class='obrigatorio'><strong> * Obrigatório </strong></span>";

    echo "<form action='' method='POST'>
    <label><strong> Nome do subitem: <span class='obrigatorio'>* </span></strong></label>
    <input type='text' class='form-control' name='nome_subitem'>
    <label><strong> Tipo de valor<span class='obrigatorio'>* </span></strong></label>";

    $tipoDeValores = get_enum_values($link, 'subitem', 'value_type');
    if (!empty($tipoDeValores)) {
        foreach ($tipoDeValores as $tipoDeValor) {
            echo "<input type='radio' name='tipo_valor' value={$tipoDeValor}> {$tipoDeValor}";
        }
    }

    echo "<br>";
    echo "<label><strong> Item<span class='obrigatorio'>* </span></strong></label>";

    echo "<select name='item'>";
    echo "<option>Selecione uma das opções</option>";

    $resultadoItemQuery = mysqli_query($link, $itemQuery);
    while ($item = mysqli_fetch_assoc($resultadoItemQuery)) {
        echo "<option value={$item['id']}> {$item['name']} </option>";
    }

    echo "</select><br>";

    echo "<label><strong> Tipo do campo do formulário<span class='obrigatorio'>* </span></strong></label>";

    $tiposDeFormulario = get_enum_values($link, 'subitem', 'form_field_type');
    if (!empty($tiposDeFormulario)) {
        foreach ($tiposDeFormulario as $tipoDeFormulario) {
            echo "<input type='radio' name='tipo_formulario' value={$tipoDeFormulario}> {$tipoDeFormulario}";
        }
    }

    echo "<br>";

    echo "<label><strong> Tipo de Unidade </strong></label>";
    echo "<select name='tipo_unidade'>";
    echo "<option></option>";

    $tipoDeUnidadesQuery = "SELECT * FROM subitem_unit_type ORDER BY subitem_unit_type.name ASC";
    $resultadoTipoDeUnidadesQuery = mysqli_query($link, $tipoDeUnidadesQuery);
    while ($unidade = mysqli_fetch_assoc($resultadoTipoDeUnidadesQuery)) {
        echo "<option value={$unidade['id']}> {$unidade['name']} </option>";
    }

    echo "</select><br>";

    echo "<label><strong> Ordem do campo no formulário: <span class='obrigatorio'> * </span></strong></label>";
    echo "<input type='number' class='form-control' name='ordem_campo' min='1'>";

    echo "<label><strong> Obrigatório<span class='obrigatorio'>* </span></strong></label>";
    echo "<input type='radio' name='obrigatorio' value='1'> Sim";
    echo "<input type='radio' name='obrigatorio' value='0'> Não";

    echo "<br>";

    echo "<input type='hidden' name='estado' value='inserir'>";
    echo "<button type='submit' class='buttons button-color'> Submeter";
    echo "</form>";

    } else if ($_REQUEST['estado'] == 'inserir') {
        $erros = "";

        $nome_subitem = isset($_REQUEST['nome_subitem']) ? trim($_REQUEST['nome_subitem']) : '';
        $tipo_valor = isset($_REQUEST['tipo_valor']) ? $_REQUEST['tipo_valor'] : '';
        $item = isset($_REQUEST['item']) ? $_REQUEST['item'] : '';
        $tipo_formulario = isset($_REQUEST['tipo_formulario']) ? $_REQUEST['tipo_formulario'] : '';
        $tipo_unidade = isset($_REQUEST['tipo_unidade']) && $_REQUEST['tipo_unidade'] !== '' ? $_REQUEST['tipo_unidade'] : NULL;
        $ordem_campo = isset($_REQUEST['ordem_campo']) ? intval($_REQUEST['ordem_campo']) : '';
        $obrigatorio = isset($_REQUEST['obrigatorio']) ? intval($_REQUEST['obrigatorio']) : NULL;

        if (empty($nome_subitem)) {
            $erros .= "O campo Nome é obrigatório.<br>";
        } else if (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚâêîôûÂÊÎÔÛãõÃÕçÇ ]{2,}$/u", $nome_subitem)) {
            $erros .= "O campo Nome deve de conter apenas números, letras, espaços e um mínimo de 2 caracteres.<br>";
        }

        if (empty($tipo_valor)) {
            $erros .= "O campo Tipo de Valor é obrigatório.<br>";
        }

        if (empty($item)) {
            $erros .= "O campo Item é obrigatório.<br>";
        }

        if (empty($tipo_formulario)) {
            $erros .= "Tipo do Campo do Formulário é obrigatório.<br>";
        }

        if (empty($ordem_campo)) {
            $erros .= "Ordem do Campo no Formulário é obrigatório.<br>";
        }

        if ($obrigatorio === NULL) {
            $erros .= "O campo Obrigatório é obrigatório.<br>";
        }

        if ($erros != "") {
            echo "<span class='error'> Ocorreram os seguintes erros: </span><br>";
            echo "$erros<br>";
            voltar_atras();
        } else {
            $valor_tipo_unidade = ($tipo_unidade === NULL) ? "NULL" : "'$tipo_unidade'";

            $insercao = "INSERT INTO subitem (id, name, item_id, value_type, form_field_name, form_field_type, unit_type_id, form_field_order, mandatory, state) 
            VALUES (NULL, '$nome_subitem', '$item', '$tipo_valor', '', '$tipo_formulario', $valor_tipo_unidade, '$ordem_campo', '$obrigatorio', 'active')";
            $resultadoInsercao = mysqli_query($link, $insercao);

            if (!$resultadoInsercao) {
                die ('Erro ao inserir subitem: ' . mysqli_error($link));
            }

            $novoSubItemId = mysqli_insert_id($link);

            $novoItemQuery = "SELECT * FROM item WHERE item.id = $item";
            $resultadoNovoItemQuery = mysqli_query($link, $novoItemQuery);

            if (!$resultadoNovoItemQuery) {
                die ('Query falhou: ' . mysqli_error($link));
            }

            $dadosDeItem = mysqli_fetch_assoc($resultadoNovoItemQuery);
            $primeirasLetras = substr($dadosDeItem['name'], 0, 3);

            $nomeLimpo = preg_replace('/[^a-z0-9_ ]/i', '', $nome_subitem);
            $nomeLimpo = str_replace(' ', '_', $nome_subitem);

            $nomeFormulario = $primeirasLetras . '-' . $novoSubItemId . '-' . $nomeLimpo;

            $atualizacao = "UPDATE subitem SET form_field_name = '$nomeFormulario' WHERE subitem.id = $novoSubItemId";
            $resultadoAtualizacao = mysqli_query($link, $atualizacao);

            if (!$resultadoAtualizacao) {
                die ('Erro ao atualizar o nome do campo no formulário: ' . $mysqli_error($link));
            }

            echo "<h3>Gestão de subitens - inserção</h3>";
            echo "<span class='success'> Inseriu os dados de novo subitem com sucesso. </span><br><br>";
            echo "<a href='$current_page'> Continuar </a>";
        }
    }

} else {
    echo "<span class='error'> Não tem autorização para aceder a esta página. </span>";
}

mysqli_close($link);
?>