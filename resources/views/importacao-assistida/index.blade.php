<x-app-layout>
<div class="max-w-full mx-auto sm:px-6 lg:px-8 px-4 py-4">

    {{-- Cabeçalho --}}
    <div class="mb-4 p-2 rounded bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] border border-white text-white text-center font-bold text-sm uppercase">
        Importação Assistida &mdash; Hapvida
        <span class="block text-xs font-normal opacity-75 mt-0.5">Cidade por cidade, PDF por PDF</span>
    </div>

    <div class="flex flex-col lg:flex-row gap-4">

        {{-- ── Painel esquerdo: formulário ── --}}
        <div class="lg:w-80 flex-shrink-0">
            <div class="rounded-xl bg-[rgba(255,255,255,0.12)] backdrop-blur-[15px] border border-white/20 p-4">

                <h2 class="text-white font-semibold text-sm uppercase tracking-wider mb-4">Parâmetros</h2>

                {{-- Tipo de PDF --}}
                <div class="mb-3">
                    <label class="block text-white/70 text-xs font-medium mb-1">Tipo de PDF</label>
                    <select id="pdf_type" class="w-full rounded-lg bg-white/10 border border-white/20 text-white text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-white/40">
                        <option value="individual">Individual</option>
                        <option value="super_simples">Super Simples</option>
                        <option value="ambulatorial">Ambulatorial</option>
                    </select>
                </div>

                {{-- Página --}}
                <div class="mb-3">
                    <label class="block text-white/70 text-xs font-medium mb-1">Página no PDF</label>
                    <input id="page" type="number" min="1" value="1"
                        class="w-full rounded-lg bg-white/10 border border-white/20 text-white text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-white/40 placeholder-white/40"
                        placeholder="Ex: 3">
                </div>

                {{-- Cidade --}}
                <div class="mb-3">
                    <label class="block text-white/70 text-xs font-medium mb-1">Cidade</label>
                    <input id="cidade" type="text"
                        class="w-full rounded-lg bg-white/10 border border-white/20 text-white text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-white/40 placeholder-white/40"
                        placeholder="Ex: Goiânia">
                </div>

                {{-- UF --}}
                <div class="mb-4">
                    <label class="block text-white/70 text-xs font-medium mb-1">UF</label>
                    <input id="uf" type="text" maxlength="2"
                        class="w-full rounded-lg bg-white/10 border border-white/20 text-white text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-white/40 placeholder-white/40 uppercase"
                        placeholder="Ex: GO">
                </div>

                {{-- Botões --}}
                <div class="flex flex-col gap-2">
                    <button id="btn-preview"
                        class="w-full py-2 px-4 rounded-lg bg-blue-500/80 hover:bg-blue-500 text-white text-sm font-semibold transition-colors">
                        Pré-visualizar
                    </button>
                    <button id="btn-importar" disabled
                        class="w-full py-2 px-4 rounded-lg bg-green-500/50 text-white/50 text-sm font-semibold cursor-not-allowed transition-all"
                        title="Clique em Pré-visualizar primeiro">
                        Confirmar Importação
                    </button>
                    <button id="btn-analisar"
                        class="w-full py-2 px-4 rounded-lg bg-white/10 hover:bg-white/20 text-white/70 text-xs font-medium transition-colors">
                        Ver texto bruto da página
                    </button>
                </div>

                {{-- Spinner --}}
                <div id="spinner" class="hidden mt-4 text-center text-white/60 text-xs">
                    Processando…
                </div>
            </div>

            {{-- Legenda de status --}}
            <div class="mt-3 rounded-xl bg-white/5 border border-white/10 p-3 text-xs">
                <div class="flex items-center gap-2 mb-1">
                    <span class="w-2 h-2 rounded-full bg-green-400 flex-shrink-0"></span>
                    <span class="text-white/70">INSERIR — registro novo</span>
                </div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="w-2 h-2 rounded-full bg-yellow-400 flex-shrink-0"></span>
                    <span class="text-white/70">ATUALIZAR — valor diferente do DB</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-white/30 flex-shrink-0"></span>
                    <span class="text-white/70">IGUAL — nenhuma alteração</span>
                </div>
            </div>
        </div>

        {{-- ── Painel direito: resultado ── --}}
        <div class="flex-1 min-w-0">

            {{-- Placeholder vazio --}}
            <div id="resultado-placeholder" class="flex items-center justify-center h-48 rounded-xl bg-white/5 border border-white/10 text-white/30 text-sm">
                Preencha os parâmetros e clique em Pré-visualizar
            </div>

            {{-- Resultado real (oculto até ter dados) --}}
            <div id="resultado" class="hidden space-y-4">

                {{-- Cabeçalho do resultado --}}
                <div id="resultado-header" class="rounded-xl bg-white/10 border border-white/20 p-4">
                    <div class="flex flex-wrap items-center gap-3">
                        <div>
                            <p class="text-white font-bold text-base" id="res-cidade"></p>
                            <p class="text-white/50 text-xs" id="res-status"></p>
                        </div>
                        <div class="ml-auto flex gap-3 text-center">
                            <div class="bg-green-500/20 rounded-lg px-3 py-2">
                                <p class="text-green-300 font-bold text-lg" id="res-inserir">0</p>
                                <p class="text-green-300/70 text-xs">Inserir</p>
                            </div>
                            <div class="bg-yellow-500/20 rounded-lg px-3 py-2">
                                <p class="text-yellow-300 font-bold text-lg" id="res-atualizar">0</p>
                                <p class="text-yellow-300/70 text-xs">Atualizar</p>
                            </div>
                            <div class="bg-white/10 rounded-lg px-3 py-2">
                                <p class="text-white/60 font-bold text-lg" id="res-igual">0</p>
                                <p class="text-white/40 text-xs">Igual</p>
                            </div>
                        </div>
                    </div>
                    {{-- Avisos --}}
                    <div id="res-avisos" class="hidden mt-3 space-y-1"></div>
                </div>

                {{-- Tabelas por produto --}}
                <div id="res-tabelas" class="space-y-3"></div>

                {{-- Bloco SQL --}}
                <div id="res-sql" class="hidden space-y-3">
                    <div id="sql-insert-block" class="hidden">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-white/60 text-xs font-semibold uppercase">SQL INSERT</span>
                            <button onclick="copiarSql('sql-insert')" class="text-xs text-blue-300 hover:text-blue-100">Copiar</button>
                        </div>
                        <textarea id="sql-insert" readonly rows="6"
                            class="w-full rounded-lg bg-black/30 border border-white/10 text-green-300 text-xs font-mono p-3 resize-y"></textarea>
                    </div>
                    <div id="sql-update-block" class="hidden">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-white/60 text-xs font-semibold uppercase">SQL UPDATE</span>
                            <button onclick="copiarSql('sql-update')" class="text-xs text-blue-300 hover:text-blue-100">Copiar</button>
                        </div>
                        <textarea id="sql-update" readonly rows="6"
                            class="w-full rounded-lg bg-black/30 border border-white/10 text-yellow-300 text-xs font-mono p-3 resize-y"></textarea>
                    </div>
                </div>

            </div>

            {{-- Erro --}}
            <div id="resultado-erro" class="hidden rounded-xl bg-red-500/20 border border-red-500/30 p-4 text-red-300 text-sm"></div>

            {{-- Análise de texto bruto --}}
            <div id="resultado-analise" class="hidden mt-4 rounded-xl bg-black/30 border border-white/10 p-4">
                <p class="text-white/60 text-xs font-semibold uppercase mb-2">Texto bruto da página</p>
                <pre id="analise-texto" class="text-white/70 text-xs font-mono overflow-x-auto whitespace-pre-wrap max-h-96 overflow-y-auto"></pre>
            </div>

        </div>
    </div>
</div>

<script>
const PLANOS = { 1: 'Individual', 5: 'Super Simples', 23: 'Nosso Médico', 24: 'SS - Nosso Médico' };
const ACOM   = { 1: 'Apartamento', 2: 'Enfermaria', 3: 'Ambulatorial' };
const FAIXAS = { 1: '00-18', 2: '19-23', 3: '24-28', 4: '29-33', 5: '34-38', 6: '39-43', 7: '44-48', 8: '49-53', 9: '54-58', 10: '59+' };
const COPAT  = { 0: 'Com Cop. Parcial', 1: 'Com Cop. Total' };
const ODONTO = { 0: 'Sem Odonto', 1: 'Com Odonto' };

const STATUS_STYLE = {
    inserir:   { badge: 'bg-green-500/30 text-green-300',   label: 'INSERIR' },
    atualizar: { badge: 'bg-yellow-500/30 text-yellow-300', label: 'ATUALIZAR' },
    igual:     { badge: 'bg-white/10 text-white/40',        label: 'IGUAL' },
};

let lastParams = null;

function getParams() {
    return {
        pdf_type: document.getElementById('pdf_type').value,
        page:     document.getElementById('page').value,
        cidade:   document.getElementById('cidade').value.trim(),
        uf:       document.getElementById('uf').value.trim().toUpperCase(),
    };
}

function setLoading(on) {
    document.getElementById('spinner').classList.toggle('hidden', !on);
    document.getElementById('btn-preview').disabled  = on;
    document.getElementById('btn-analisar').disabled = on;
}

async function fetchJson(url, body) {
    const res = await fetch(url, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body:    JSON.stringify(body),
    });
    return res.json();
}

function showErro(msg) {
    document.getElementById('resultado-placeholder').classList.add('hidden');
    document.getElementById('resultado').classList.add('hidden');
    document.getElementById('resultado-analise').classList.add('hidden');
    const el = document.getElementById('resultado-erro');
    el.textContent = msg;
    el.classList.remove('hidden');
}

function renderResultado(data, imported) {
    document.getElementById('resultado-placeholder').classList.add('hidden');
    document.getElementById('resultado-erro').classList.add('hidden');
    document.getElementById('resultado-analise').classList.add('hidden');
    document.getElementById('resultado').classList.remove('hidden');

    const statusTxt = imported
        ? '✓ Importado com sucesso'
        : 'Pré-visualização — nada foi gravado ainda';

    document.getElementById('res-cidade').textContent = `${data.cidade} - ${data.uf}`;
    document.getElementById('res-status').textContent  = statusTxt + (data.cidade_criada ? ' (cidade criada)' : '');
    document.getElementById('res-inserir').textContent   = data.total_inserir;
    document.getElementById('res-atualizar').textContent = data.total_atualizar;
    document.getElementById('res-igual').textContent     = data.total_igual;

    // Avisos
    const avisosEl = document.getElementById('res-avisos');
    if (data.avisos && data.avisos.length) {
        avisosEl.innerHTML = data.avisos.map(a =>
            `<p class="text-yellow-300 text-xs">⚠ ${a}</p>`
        ).join('');
        avisosEl.classList.remove('hidden');
    } else {
        avisosEl.classList.add('hidden');
    }

    // Tabelas agrupadas por (plano_id, copat, odonto)
    renderTabelas(data.registros);

    // SQL
    const sqlBlock = document.getElementById('res-sql');
    const insertBlock = document.getElementById('sql-insert-block');
    const updateBlock = document.getElementById('sql-update-block');

    if (data.sql_insert || data.sql_update) {
        sqlBlock.classList.remove('hidden');
        if (data.sql_insert) {
            document.getElementById('sql-insert').value = data.sql_insert;
            insertBlock.classList.remove('hidden');
        } else {
            insertBlock.classList.add('hidden');
        }
        if (data.sql_update) {
            document.getElementById('sql-update').value = data.sql_update;
            updateBlock.classList.remove('hidden');
        } else {
            updateBlock.classList.add('hidden');
        }
    } else {
        sqlBlock.classList.add('hidden');
    }
}

function renderTabelas(registros) {
    const container = document.getElementById('res-tabelas');
    container.innerHTML = '';

    // Agrupar por (plano_id, copat, odonto)
    const groups = {};
    for (const r of registros) {
        const key = `${r.plano_id}_${r.copat}_${r.odonto}`;
        if (!groups[key]) groups[key] = { plano_id: r.plano_id, copat: r.copat, odonto: r.odonto, rows: {} };
        if (!groups[key].rows[r.acomodacao_id]) groups[key].rows[r.acomodacao_id] = {};
        groups[key].rows[r.acomodacao_id][r.faixa_id] = r;
    }

    for (const group of Object.values(groups)) {
        const titulo = `${PLANOS[group.plano_id] ?? group.plano_id} / ${COPAT[group.copat]} / ${ODONTO[group.odonto]}`;
        const acomIds = Object.keys(group.rows).map(Number).sort();

        // Verificar se o grupo tem alguma alteração
        let hasChange = false;
        for (const acomRows of Object.values(group.rows)) {
            for (const r of Object.values(acomRows)) {
                if (r.status !== 'igual') { hasChange = true; break; }
            }
            if (hasChange) break;
        }

        const headerClass = hasChange
            ? 'text-white font-semibold'
            : 'text-white/40 font-semibold';

        let html = `
            <details class="rounded-xl bg-white/5 border border-white/10 overflow-hidden" ${hasChange ? 'open' : ''}>
                <summary class="cursor-pointer px-4 py-3 text-sm ${headerClass} select-none flex items-center gap-2">
                    ${titulo}
                    ${hasChange ? '' : '<span class="text-xs text-white/30 font-normal">— todos iguais</span>'}
                </summary>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="border-t border-white/10">
                                <th class="px-3 py-2 text-left text-white/40 font-medium">Faixa</th>
        `;

        for (const acomId of acomIds) {
            html += `<th class="px-3 py-2 text-right text-white/40 font-medium">${ACOM[acomId] ?? acomId} PDF</th>`;
            html += `<th class="px-3 py-2 text-right text-white/40 font-medium">${ACOM[acomId] ?? acomId} DB</th>`;
            html += `<th class="px-3 py-2 text-center text-white/40 font-medium">Status</th>`;
        }

        html += `</tr></thead><tbody>`;

        for (let faixaId = 1; faixaId <= 10; faixaId++) {
            html += `<tr class="border-t border-white/5 hover:bg-white/5">`;
            html += `<td class="px-3 py-1.5 text-white/60">${FAIXAS[faixaId] ?? faixaId}</td>`;

            for (const acomId of acomIds) {
                const r = group.rows[acomId]?.[faixaId];
                if (!r) {
                    html += `<td colspan="3" class="px-3 py-1.5 text-white/20 text-center">—</td>`;
                    continue;
                }
                const st = STATUS_STYLE[r.status] ?? STATUS_STYLE.igual;
                const valorDb = r.valor_db !== null ? `R$ ${Number(r.valor_db).toFixed(2).replace('.', ',')}` : '—';

                html += `<td class="px-3 py-1.5 text-right text-white/80">R$ ${Number(r.valor_pdf).toFixed(2).replace('.', ',')}</td>`;
                html += `<td class="px-3 py-1.5 text-right text-white/40">${valorDb}</td>`;
                html += `<td class="px-3 py-1.5 text-center"><span class="inline-block px-1.5 py-0.5 rounded text-xs ${st.badge}">${st.label}</span></td>`;
            }

            html += `</tr>`;
        }

        html += `</tbody></table></div></details>`;
        container.insertAdjacentHTML('beforeend', html);
    }
}

function habilitarImportar(params) {
    const btn = document.getElementById('btn-importar');
    btn.disabled = false;
    btn.classList.remove('bg-green-500/50', 'text-white/50', 'cursor-not-allowed');
    btn.classList.add('bg-green-500/80', 'hover:bg-green-500', 'text-white', 'cursor-pointer');
    btn.title = '';
    lastParams = params;
}

function copiarSql(id) {
    const el = document.getElementById(id);
    navigator.clipboard.writeText(el.value).then(() => {
        alert('SQL copiado!');
    });
}

// ── Event listeners ──────────────────────────────────────────────────────

document.getElementById('btn-preview').addEventListener('click', async () => {
    const params = getParams();
    if (!params.cidade || !params.uf) { alert('Informe a cidade e o UF.'); return; }

    setLoading(true);
    try {
        const data = await fetchJson('{{ route("importacao-assistida.preview") }}', params);
        if (!data.sucesso) { showErro(data.erro ?? 'Erro desconhecido.'); return; }
        renderResultado(data, false);
        habilitarImportar(params);
    } catch (e) {
        showErro('Erro de comunicação: ' + e.message);
    } finally {
        setLoading(false);
    }
});

document.getElementById('btn-importar').addEventListener('click', async () => {
    if (!lastParams) return;
    if (!confirm(`Confirma a importação de "${lastParams.cidade} - ${lastParams.uf}" (${lastParams.pdf_type}, página ${lastParams.page})?`)) return;

    setLoading(true);
    try {
        const data = await fetchJson('{{ route("importacao-assistida.importar") }}', lastParams);
        if (!data.sucesso) { showErro(data.erro ?? 'Erro ao importar.'); return; }
        renderResultado(data, true);

        // Desabilitar o botão após import bem-sucedido
        const btn = document.getElementById('btn-importar');
        btn.disabled = true;
        btn.classList.add('bg-green-500/50', 'text-white/50', 'cursor-not-allowed');
        btn.classList.remove('bg-green-500/80', 'hover:bg-green-500', 'text-white', 'cursor-pointer');
        btn.title = 'Já importado';
        lastParams = null;
    } catch (e) {
        showErro('Erro de comunicação: ' + e.message);
    } finally {
        setLoading(false);
    }
});

document.getElementById('btn-analisar').addEventListener('click', async () => {
    const params = getParams();
    setLoading(true);
    try {
        const data = await fetchJson('{{ route("importacao-assistida.analisar") }}', { pdf_type: params.pdf_type, page: params.page });
        if (!data.sucesso) { showErro(data.erro ?? 'Erro.'); return; }

        document.getElementById('resultado-placeholder').classList.add('hidden');
        document.getElementById('resultado-erro').classList.add('hidden');
        document.getElementById('analise-texto').textContent = data.dados.texto_bruto ?? JSON.stringify(data.dados, null, 2);
        document.getElementById('resultado-analise').classList.remove('hidden');
    } catch (e) {
        showErro('Erro de comunicação: ' + e.message);
    } finally {
        setLoading(false);
    }
});

document.getElementById('uf').addEventListener('input', function() {
    this.value = this.value.toUpperCase();
});
</script>
</x-app-layout>
