<?php
require_once("custom/php/common.php");

if (is_user_logged_in() && current_user_can('manage_records')) {
    if (!isset($_REQUEST['estado'])) {

    $criancasQuery = "SELECT * FROM child ORDER BY child.name ASC";
    $resultadoCriancasQuery = mysqli_query ($link, $criancasQuery);

    if (!$resultadoCriancasQuery) {
        die ('Query falhou: ' . mysqli_error($link));
    }

    if (mysqli_num_rows($resultadoCriancasQuery) > 0) {
        echo "<table>
        <tr>
            <th>Nome</th>
            <th>Data de nascimento</th>
            <th>Enc. de educação</th>
            <th>Telefone do Enc.</th>
            <th>e-mail</th>
            <th>registos</th>
        </tr>";

        while ($crianca = mysqli_fetch_assoc($resultadoCriancasQuery)) {
            echo "<tr>";
            echo "<td>{$crianca['name']}</td>";
            echo "<td>{$crianca['birth_date']}</td>";
            echo "<td>{$crianca['tutor_name']}</td>";
            echo "<td>{$crianca['tutor_phone']}</td>";
            echo "<td>{$crianca['tutor_email']}</td>";

            echo "<td>";

            $itemQuery = "SELECT DISTINCT item.id, item.name 
            FROM item 
            INNER JOIN subitem ON item.id = subitem.item_id
            INNER JOIN value ON subitem.id = value.subitem_id
            WHERE value.child_id = {$crianca['id']}
            ORDER BY item.name ASC";
            $resultadoItemQuery = mysqli_query($link, $itemQuery);

            if (!$resultadoItemQuery) {
                die ('Query falhou: ' . mysqli_error($link));
            }

            if (mysqli_num_rows($resultadoItemQuery) > 0) {
                while ($item = mysqli_fetch_assoc($resultadoItemQuery)) {

                    $nomeItem = strtoupper($item['name']);
                    echo "$nomeItem:<br>";

                    $valorQuery = "SELECT DISTINCT value.date, TIME_FORMAT(value.time, '%H:%i') as formatted_time, value.producer
                    FROM value
                    INNER JOIN subitem ON value.subitem_id = subitem.id
                    WHERE subitem.item_id = {$item['id']} AND value.child_id = {$crianca['id']}
                    ORDER BY value.date ASC, value.time ASC";
                    $resultadoValorQuery = mysqli_query($link, $valorQuery);

                    if (!$resultadoValorQuery) {
                        die ('Query falhou: ' . mysqli_error($link));
                    }

                    if (mysqli_num_rows($resultadoValorQuery) > 0) {
                        while ($valor = mysqli_fetch_assoc($resultadoValorQuery)) {

                            $produtor = !empty($valor['producer']) ? $valor['producer'] : "";
                            
                            echo "<a href='#'>[editar] [apagar]</a>";
                            echo " - <strong>{$valor['date']} {$valor['formatted_time']}</strong> ({$valor['producer']}) - ";

                            $condicaoProdutor = !empty($valor['producer']) ? "AND value.producer = '{$valor['producer']}'" : "";

                            $subItemQuery = "SELECT DISTINCT subitem.name, value.value
                            FROM value
                            INNER JOIN subitem ON value.subitem_id = subitem.id
                            WHERE value.child_id = {$crianca['id']}
                            AND subitem.item_id = {$item['id']} 
                            AND value.date = '{$valor['date']}'
                            AND TIME_FORMAT(value.time, '%H:%i') = '{$valor['formatted_time']}'
                            $condicaoProdutor
                            ORDER BY subitem.name ASC, value.value ASC";
                            $resultadoSubItemQuery = mysqli_query($link, $subItemQuery);

                            if (!$resultadoSubItemQuery) {
                                die ('Query falhou: ' . mysqli_error($link));
                            }

                            $subItemValores = [];
                            
                                while ($subItem = mysqli_fetch_assoc($resultadoSubItemQuery)) {
                                    $subItemValores[] = "<strong>{$subItem['name']}</strong> ({$subItem['value']})";
                                }
                            
                            echo implode("; ", $subItemValores) . "<br>";
                        }
                    }
                } 

                echo "</td>";
                echo "</tr>";
            }
        }

    } else {
        echo "<tr><td colspan='6'>Não há crianças</td></tr>";
    }

    echo "</table>";

    echo "<h3>Dados de registo - introdução</h3>";
    echo "<span class='obrigatorio'><strong> * Obrigatório </strong></span>";

    echo "<form action='' method='POST'>
    <label><strong> Nome completo: <span class='obrigatorio'>* </span></strong></label>
    <input type='text' class='form-control' name='nome_completo'>
    <label><strong> Data de nascimento (AAAA-MM-DD): <span class='obrigatorio'>* </span></strong></label>
    <input type='text' class='form-control' name='data_de_nascimento'>
    <label><strong> Nome completo do encarregado de educação: <span class='obrigatorio'>* </span></strong></label>
    <input type='text' class='form-control' name='nome_completo_ee'>
    <label><strong> Telefone do encarregado de educação (9 digitos): <span class='obrigatorio'>* </span></strong></label>
    <input type='text' class='form-control' name='telefone_ee'>
    <label><strong> Endereço de e-mail do tutor: </strong></label>
    <input type='text' class='form-control' name='email_tutor'>";
    
    echo "<br>";

    echo "<input type='hidden' name='estado' value='validar'>";
    echo "<button type='submit' class='buttons button-color'> Submeter";
    echo "</form>";

    } else if ($_REQUEST['estado'] == 'validar') {
        echo "<h3>Dados de registo - validação</h3>";

        $erros = "";

        $nome_completo = isset($_REQUEST['nome_completo']) ? $_REQUEST['nome_completo'] : '';
        $data_de_nascimento = isset($_REQUEST['data_de_nascimento']) ? $_REQUEST['data_de_nascimento'] : '';
        $nome_completo_ee = isset($_REQUEST['nome_completo_ee']) ? $_REQUEST['nome_completo_ee'] : '';
        $telefone_ee = isset($_REQUEST['telefone_ee']) ? $_REQUEST['telefone_ee'] : '';
        $email_tutor = isset($_REQUEST['email_tutor']) ? $_REQUEST['email_tutor'] : '';

        if (empty($nome_completo)) {
            $erros .= "O campo Nome Completo é obrigatório.<br>";
        } else if (!preg_match('/^[a-zA-ZÀ-ÿ\s]+$/', $nome_completo)) {
            $erros .= "O campo Nome Completo contém caracteres inválidos.<br>";
        }

        if (empty($data_de_nascimento)) {
            $erros .= "O campo Data de nascimento é obrigatório.<br>";
        } else if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_de_nascimento)) {
            $erros .= "O campo Data de nascimento deve estar no formato AAAA-MM-DD.<br>";
        } 

        if (empty($nome_completo_ee)) {
            $erros .= "O campo Nome completo do encarregado de educação é obrigatório.<br>";
        } else if (!preg_match('/^[a-zA-ZÀ-ÿ\s]+$/', $nome_completo_ee)) {
            $erros .= "O campo Nome completo do encarregado de educação contém caracteres inválidos.<br>";
        }

        if (empty($telefone_ee)) {
            $erros .= "O campo Telefone do encarregado de educação é obrigatório.<br>";
        } else if (!preg_match('/^\d{9}$/', $telefone_ee)) {
            $erros .= "O campo Telefone do encarregado de educação deve conter exatamente 9 dígitos.<br>";
        }

        if (!empty($email_tutor) && !filter_var($email_tutor, FILTER_VALIDATE_EMAIL)) {
            $erros .= "O campo Endereço de e-mail do tutor deve conter um e-mail válido.<br>";
        } 

        if ($erros != "") {
            echo "<span class='error'> Ocorreram os seguintes erros: </span><br>";
            echo "$erros<br>";
            voltar_atras();
        }else {
            echo "<p>Estamos prestes a inserir os dados abaixo na base de dados.<br<br> 
            Confirma que os dados estão correctos e pretende submeter os mesmos?</p>";

            echo "<ul>
            <li><strong>Nome completo: </strong>$nome_completo</li>
            <li><strong>Data de nascimento: </strong>$data_de_nascimento</li>
            <li><strong>Nome completo do encarregado de educação: </strong>$nome_completo_ee</li>
            <li><strong>Telefone do encarregado de educação: </strong>$telefone_ee</li>
            <li><strong>Email do tutor: </strong>$email_tutor</li>
            </ul>";

            echo "<form action='' method='POST'>
            <input type='hidden' name='nome_completo' value='$nome_completo'>
            <input type='hidden' name='data_de_nascimento' value='$data_de_nascimento'>
            <input type='hidden' name='nome_completo_ee' value='$nome_completo_ee'>
            <input type='hidden' name='telefone_ee' value='$telefone_ee'>
            <input type='hidden' name='email_tutor' value='$email_tutor'>";

            echo "<input type='hidden' name='estado' value='inserir'>";
            echo "<button type='submit' class='buttons button-color'> Submeter</button>";
            echo "<br><br>";
            voltar_atras();
            echo "</form>";
        }
    } else if ($_REQUEST['estado'] == 'inserir') {
        echo "<h3>Dados de registo - inserção</h3>";

        $nome_completo = isset($_REQUEST['nome_completo']) ? $_REQUEST['nome_completo'] : '';
        $data_de_nascimento = isset($_REQUEST['data_de_nascimento']) ? $_REQUEST['data_de_nascimento'] : '';
        $nome_completo_ee = isset($_REQUEST['nome_completo_ee']) ? $_REQUEST['nome_completo_ee'] : '';
        $telefone_ee = isset($_REQUEST['telefone_ee']) ? $_REQUEST['telefone_ee'] : '';
        $email_tutor = isset($_REQUEST['email_tutor']) ? $_REQUEST['email_tutor'] : '';

        echo "<strong> Inseriu os seguintes dados: </strong><br><br>";
        
        $insercao = "INSERT INTO child (id, name, birth_date, tutor_name, tutor_phone, tutor_email)
        VALUES (NULL, '$nome_completo', '$data_de_nascimento', '$nome_completo_ee', '$telefone_ee', '$email_tutor')";
        $resultadoInsercao = mysqli_query($link, $insercao);

        if ($resultadoInsercao) {
            echo "Nome: $nome_completo <br>
            Data de nascimento: $data_de_nascimento <br>
            Enc. de educação: $nome_completo_ee <br>
            Telefone do Enc.: $telefone_ee <br>
            e-mail: $email_tutor<br><br>";

            echo "<span class='success'> Inseriu os dados de registo com sucesso. </span><br>";
            echo "<strong> Clique em Continuar para avançar</strong><br>";
            echo "<a href='$current_page'> Continuar </a>";
        } else {
            echo "<span class='error'> Ocorreu um erro ao inserir os novos dados: </span>" . mysqli_error($link) . "<br><br>";
        }


    }

} else {
    echo "<span class='error'> Não tem autorização para aceder a esta página. </span>";
}

mysqli_close($link);
?>