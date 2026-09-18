<?php
require 'conexao.php';
require 'auth.php';

exigir_login();

$valor_ph_min = trim($_GET['valor_ph_min'] ?? '');
$valor_ph_max = trim($_GET['valor_ph_max'] ?? '');
$temperatura_min = trim($_GET['temperatura_min'] ?? '');
$temperatura_max = trim($_GET['temperatura_max'] ?? '');
$amostra = trim($_GET['amostra'] ?? '');
$observacao = trim($_GET['observacao'] ?? '');
$sem_observacao = isset($_GET['sem_observacao']) && $_GET['sem_observacao'] == '1';
$responsavel = $_GET['responsavel'] ?? 'todas';
$responsavel_nome = trim($_GET['responsavel_nome'] ?? '');
$data_medicao_inicio = trim($_GET['data_medicao_inicio'] ?? '');
$data_medicao_fim = trim($_GET['data_medicao_fim'] ?? '');
$data_modificacao_inicio = trim($_GET['data_modificacao_inicio'] ?? '');
$data_modificacao_fim = trim($_GET['data_modificacao_fim'] ?? '');

$usuario_id = $_SESSION['usuario_id'];
$mensagem = get_flash('success');
$erro = get_flash('error');
$csrf_token = gerar_csrf_token();

function normalize_search_string(string $value): string {
    $value = mb_strtolower($value, 'UTF-8');
    return strtr($value, [
        'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'ä' => 'a',
        'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
        'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
        'ó' => 'o', 'ò' => 'o', 'õ' => 'o', 'ô' => 'o', 'ö' => 'o',
        'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
        'ç' => 'c',
    ]);
}

$sql = '
SELECT 
    m.id,
    m.valor_ph,
    m.temperatura,
    m.amostra,
    m.observacao,
    m.data_medicao,
    m.criado_em,
    m.atualizado_em,
    m.atualizado_por,
    m.usuario_id,
    u.nome AS criado_por_nome,
    u.ativo AS responsavel_ativo,
    u2.nome AS atualizado_por_nome
FROM medicoes_ph m
LEFT JOIN usuarios u ON u.id = m.usuario_id
LEFT JOIN usuarios u2 ON u2.id = m.atualizado_por
WHERE 1=1
';

$params = [];
$types = '';

$amostra_search_expr = 'LOWER(m.amostra)';
$responsavel_search_expr = 'LOWER(COALESCE(u.nome, ""))';
foreach ([
    'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'ä' => 'a',
    'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
    'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
    'ó' => 'o', 'ò' => 'o', 'õ' => 'o', 'ô' => 'o', 'ö' => 'o',
    'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
    'ç' => 'c',
] as $from => $to) {
    $amostra_search_expr = "REPLACE($amostra_search_expr, '$from', '$to')";
    $responsavel_search_expr = "REPLACE($responsavel_search_expr, '$from', '$to')";
}

if ($valor_ph_min !== '' && is_numeric($valor_ph_min)) {
    $sql .= ' AND m.valor_ph >= ?';
    $params[] = (float)$valor_ph_min;
    $types .= 'd';
}

if ($valor_ph_max !== '' && is_numeric($valor_ph_max)) {
    $sql .= ' AND m.valor_ph <= ?';
    $params[] = (float)$valor_ph_max;
    $types .= 'd';
}

if ($temperatura_min !== '' && is_numeric($temperatura_min)) {
    $sql .= ' AND COALESCE(m.temperatura, 25) >= ?';
    $params[] = (float)$temperatura_min;
    $types .= 'd';
}

if ($temperatura_max !== '' && is_numeric($temperatura_max)) {
    $sql .= ' AND COALESCE(m.temperatura, 25) <= ?';
    $params[] = (float)$temperatura_max;
    $types .= 'd';
}

if ($amostra !== '') {
    $sql .= " AND $amostra_search_expr LIKE ?";
    $params[] = '%' . normalize_search_string($amostra) . '%';
    $types .= 's';
}

if ($sem_observacao) {
    $sql .= " AND TRIM(COALESCE(m.observacao, '')) = ''";
} elseif ($observacao !== '') {
    $sql .= ' AND m.observacao LIKE ?';
    $params[] = '%' . $observacao . '%';
    $types .= 's';
}

if ($responsavel_nome !== '') {
    $sql .= " AND $responsavel_search_expr LIKE ?";
    $params[] = '%' . normalize_search_string($responsavel_nome) . '%';
    $types .= 's';
}

if ($responsavel === 'minhas') {
    $sql .= ' AND m.usuario_id = ?';
    $params[] = $usuario_id;
    $types .= 'i';
} elseif ($responsavel === 'outras') {
    $sql .= ' AND m.usuario_id <> ?';
    $params[] = $usuario_id;
    $types .= 'i';
}

if ($data_medicao_inicio !== '' && strtotime($data_medicao_inicio) !== false) {
    $sql .= ' AND DATE(m.data_medicao) >= ?';
    $params[] = date('Y-m-d', strtotime($data_medicao_inicio));
    $types .= 's';
}

if ($data_medicao_fim !== '' && strtotime($data_medicao_fim) !== false) {
    $sql .= ' AND DATE(m.data_medicao) <= ?';
    $params[] = date('Y-m-d', strtotime($data_medicao_fim));
    $types .= 's';
}

if ($data_modificacao_inicio !== '' && strtotime($data_modificacao_inicio) !== false) {
    $sql .= ' AND DATE(m.atualizado_em) >= ?';
    $params[] = date('Y-m-d', strtotime($data_modificacao_inicio));
    $types .= 's';
}

if ($data_modificacao_fim !== '' && strtotime($data_modificacao_fim) !== false) {
    $sql .= ' AND DATE(m.atualizado_em) <= ?';
    $params[] = date('Y-m-d', strtotime($data_modificacao_fim));
    $types .= 's';
}

$sql .= ' ORDER BY m.id DESC';

$stmt = mysqli_prepare($conexao, $sql);

if ($types !== '') {
    $bind_params = [];
    foreach ($params as $index => $value) {
        $bind_params[] = &$params[$index];
    }
    mysqli_stmt_bind_param($stmt, $types, ...$bind_params);
}

mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Lista de medições - GrapH</title>

    <link rel="stylesheet" href="estilo.css?v=<?php echo time(); ?>">

</head>

<body>

<div class="container">

<div class="logo-area logo-list">
    <img src="imagens/logo-sistema.png" alt="GrapH" class="logo-system">
</div>

    <div class="topbar">
        <div>
            <h2>Lista de medições</h2>
            <div class="small">Registros do laboratório para usuários aprovados e ativos.</div>
        </div>
        <div class="topbar-actions">
            <a class="btn btn-danger" href="logout.php">Sair</a>
        </div>
    </div>

    <div class="page-grid">
        <main class="results-panel">
            <?php if ($mensagem): ?>
                <div class="msg success"><?php echo htmlspecialchars($mensagem); ?></div>
            <?php endif; ?>
            <?php if ($erro): ?>
                <div class="msg error"><?php echo htmlspecialchars($erro); ?></div>
            <?php endif; ?>

            <div class="search-section">
                <label for="amostra-search">Pesquisar amostra</label>
                <input id="amostra-search" type="search" placeholder="Digite a amostra" value="<?php echo htmlspecialchars($amostra); ?>">
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Amostra</th>
                            <th>pH</th>
                            <th>Responsável</th>
                            <th>Observação</th>
                            <th>Temperatura</th>
                            <th>Data da medição</th>
                            <th>Data de modificação</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($resultado) === 0): ?>
                            <tr>
                                <td colspan="8">Nenhuma medição encontrada.</td>
                            </tr>
                        <?php else: ?>
                            <?php while ($linha = mysqli_fetch_assoc($resultado)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($linha['amostra'] ?? ''); ?></td>
                                    <td><?php echo number_format((float)$linha['valor_ph'], 2, ',', '.'); ?></td>
                                    <td>
                                        <div class="responsavel-cell">
                                            <strong><?php echo htmlspecialchars($linha['criado_por_nome'] ?? ''); ?></strong>
                                            <span class="status-dot <?php echo ((int)($linha['responsavel_ativo'] ?? 0) === 1) ? 'active' : 'inactive'; ?>">●</span>
                                            <span><?php echo ((int)($linha['responsavel_ativo'] ?? 0) === 1) ? 'Ativo' : 'Inativo'; ?></span>
                                        </div>
                                    </td>
                                    <td class="cell-observation">
                                        <?php
                                        $obs = trim((string)($linha['observacao'] ?? ''));
                                        echo htmlspecialchars($obs !== '' ? $obs : 'Nenhuma observação escrita');
                                        ?>
                                    </td>
                                    <td><?php echo ($linha['temperatura'] === null || $linha['temperatura'] === '') ? '' : number_format((float)$linha['temperatura'], 2, ',', '.') . ' °C'; ?></td>
                                    <td>
                                        <div class="small"><?php echo !empty($linha['data_medicao']) ? format_datetime_brasilia($linha['data_medicao']) : '—'; ?></div>
                                    </td>
                                    <td>
                                        <div class="small"><?php echo !empty($linha['atualizado_em']) ? format_datetime_brasilia($linha['atualizado_em']) : 'Nunca'; ?></div>
                                        <div class="small">Por: <?php echo !empty($linha['atualizado_por_nome']) ? htmlspecialchars($linha['atualizado_por_nome']) : '—'; ?></div>
                                    </td>
                                    <td class="actions">
                                        <?php if (is_admin() || (int)$linha['usuario_id'] === $usuario_id): ?>
                                            <a href="medicoes_editar.php?id=<?php echo (int)$linha['id']; ?>">Editar</a>
                                        <?php endif; ?>
                                        <?php if (is_admin() || (int)$linha['usuario_id'] === $usuario_id): ?>
                                            <form method="POST" action="medicoes_excluir.php" style="display:inline-block; margin:0;">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                                <input type="hidden" name="id" value="<?php echo (int)$linha['id']; ?>">
                                                <button class="link-button" type="submit" onclick="return confirm('Excluir esta medição?');">Excluir</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>

        <aside class="sidebar">
            <div class="sidebar-actions">
                <a class="btn btn-highlight" href="medicoes_cadastrar.php">Nova medição</a>
            </div>

            <section class="filter-panel">
                <h3>Filtros</h3>
                <form method="GET">
                    <input type="hidden" id="amostra-filter" name="amostra" value="<?php echo htmlspecialchars($amostra); ?>">
                    <div class="filters-grid">
                        <div class="filter-group">
                            <label>Valor pH</label>
                            <div class="split-inputs">
                                <input type="number" step="0.01" min="0" max="14" name="valor_ph_min" placeholder="Mínimo" value="<?php echo htmlspecialchars($valor_ph_min); ?>">
                                <input type="number" step="0.01" min="0" max="14" name="valor_ph_max" placeholder="Máximo" value="<?php echo htmlspecialchars($valor_ph_max); ?>">
                            </div>
                        </div>

                        <div class="filter-group">
                            <label>Temperatura (°C)</label>
                            <div class="split-inputs">
                                <input type="number" step="0.1" name="temperatura_min" placeholder="Mínimo" value="<?php echo htmlspecialchars($temperatura_min); ?>">
                                <input type="number" step="0.1" name="temperatura_max" placeholder="Máximo" value="<?php echo htmlspecialchars($temperatura_max); ?>">
                            </div>
                        </div>

                        <div class="filter-group full-width">
                            <label>Observação</label>
                            <input id="observacao-filter" type="text" name="observacao" placeholder="Digite uma observação" value="<?php echo htmlspecialchars($observacao); ?>" <?php echo $sem_observacao ? 'disabled' : ''; ?>>
                            <label style="display: inline-flex; align-items: center; gap: 2px; margin-top: 2px; font-weight: 500; font-size: 12px; line-height: 1.1;">
                                <input type="checkbox" name="sem_observacao" value="1" style="margin: 0; width: 12px; height: 12px;" <?php echo $sem_observacao ? 'checked' : ''; ?>>
                                Buscar itens sem observação
                            </label>
                        </div>

                        <div class="dependent-filters-box">
                            <div class="filter-group">
                                <label>Responsável</label>
                                <select name="responsavel">
                                    <option value="todas" <?php echo $responsavel === 'todas' ? 'selected' : ''; ?>>Todas</option>
                                    <option value="minhas" <?php echo $responsavel === 'minhas' ? 'selected' : ''; ?>>Minhas medições</option>
                                    <option value="outras" <?php echo $responsavel === 'outras' ? 'selected' : ''; ?>>Medições de outros</option>
                                </select>
                            </div>

                            <div class="filter-group full-width" id="grupo-pesquisa-responsavel" style="margin-top: 12px;">
                                <label>Pesquisar responsável</label>
                                <input type="text" name="responsavel_nome" placeholder="Digite o nome do responsável" value="<?php echo htmlspecialchars($responsavel_nome); ?>" style="margin-bottom: 0;">
                            </div>
                        </div>

                        <div class="filter-group">
                            <label>Data da medição</label>
                            <div class="date-range">
                                <span>de</span>
                                <input type="date" name="data_medicao_inicio" value="<?php echo htmlspecialchars($data_medicao_inicio); ?>">
                                <span>até</span>
                                <input type="date" name="data_medicao_fim" value="<?php echo htmlspecialchars($data_medicao_fim); ?>">
                            </div>
                        </div>

                        <div class="filter-group">
                            <label>Data de modificação</label>
                            <div class="date-range">
                                <span>de</span>
                                <input type="date" name="data_modificacao_inicio" value="<?php echo htmlspecialchars($data_modificacao_inicio); ?>">
                                <span>até</span>
                                <input type="date" name="data_modificacao_fim" value="<?php echo htmlspecialchars($data_modificacao_fim); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="buttons">
                        <button class="btn" type="submit">Filtrar</button>
                        <a class="btn-secondary" href="medicoes_listar.php">Limpar filtros</a>
                    </div>
                </form>
            </section>
        </aside>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const topSearch = document.getElementById('amostra-search');
    const filterForm = document.querySelector('.filter-panel form');
    const observacaoInput = filterForm ? filterForm.querySelector('#observacao-filter') : null;
    const semObservacaoCheckbox = filterForm ? filterForm.querySelector('[name="sem_observacao"]') : null;
    let debounceTimer;

    if (!topSearch || !filterForm || !observacaoInput || !semObservacaoCheckbox) {
        return;
    }

    let amostraField = filterForm.querySelector('[name="amostra"]');
    if (!amostraField) {
        amostraField = document.createElement('input');
        amostraField.type = 'hidden';
        amostraField.name = 'amostra';
        amostraField.id = 'amostra-filter';
        filterForm.appendChild(amostraField);
    }

    const syncObservacaoFilter = () => {
        observacaoInput.disabled = semObservacaoCheckbox.checked;
    };

    syncObservacaoFilter();
    semObservacaoCheckbox.addEventListener('change', syncObservacaoFilter);

    const responsavelSelect = filterForm.querySelector('[name="responsavel"]');
    const grupoPesquisaResponsavel = document.getElementById('grupo-pesquisa-responsavel');
    if (responsavelSelect && grupoPesquisaResponsavel) {
        const syncResponsavelFilter = () => {
            if (responsavelSelect.value === 'minhas') {
                grupoPesquisaResponsavel.style.display = 'none';
            } else {
                grupoPesquisaResponsavel.style.display = '';
            }
        };
        syncResponsavelFilter();
        responsavelSelect.addEventListener('change', syncResponsavelFilter);
    }

    const updateResults = async () => {
        const params = new URLSearchParams(new FormData(filterForm));
        params.set('amostra', topSearch.value);
        amostraField.value = topSearch.value;

        try {
            const response = await fetch(window.location.pathname + '?' + params.toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                cache: 'no-store'
            });

            if (!response.ok) {
                throw new Error('Falha ao buscar resultados.');
            }

            const text = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(text, 'text/html');
            const newBody = doc.querySelector('.table-wrap table tbody');
            const resultsBody = document.querySelector('.table-wrap table tbody');

            if (newBody && resultsBody) {
                resultsBody.innerHTML = newBody.innerHTML;
            }
        } catch (error) {
            console.error(error);
            filterForm.submit();
        }
    };

    topSearch.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(updateResults, 350);
    });
});
</script>
<?php include 'footer.php'; ?>
</body>
</html>