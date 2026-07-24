<div class="space-y-6">
    <div>
        <h2 class="text-xl font-semibold text-white mb-1">Importar Tabelas de Preços via PDF</h2>
        <p class="text-white/70 text-sm">Envie um ou mais arquivos PDF de tabelas de preços da Hapvida. O sistema identificará automaticamente as cidades, planos e valores.</p>
    </div>

    {{-- Upload Area --}}
    <div id="import-upload-area"
         class="relative border-2 border-dashed border-white/40 rounded-xl p-10 text-center cursor-pointer hover:border-white/70 hover:bg-white/5 transition-all"
         onclick="document.getElementById('import-pdf-input').click()">
        <svg class="mx-auto h-12 w-12 text-white/50 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <p class="text-white font-medium">Clique ou arraste os PDFs aqui</p>
        <p class="text-white/50 text-sm mt-1">Máximo 10 arquivos · PDF até 20 MB cada</p>
        <input id="import-pdf-input" type="file" multiple accept=".pdf" class="hidden">
    </div>

    {{-- Selected Files Preview --}}
    <div id="import-files-list" class="hidden space-y-2"></div>

    {{-- Import Button --}}
    <div class="flex items-center gap-4">
        <button id="import-btn"
                class="hidden bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-semibold px-6 py-2.5 rounded-lg transition-all disabled:opacity-50"
                onclick="executarImportacao()">
            Importar PDFs
        </button>
        <button id="import-clear-btn"
                class="hidden text-white/60 hover:text-white text-sm underline"
                onclick="limparImportacao()">
            Limpar seleção
        </button>
        <div id="import-spinner" class="hidden flex items-center gap-2 text-white/70 text-sm">
            <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            Processando, aguarde…
        </div>
    </div>

    {{-- Result Area --}}
    <div id="import-result" class="hidden"></div>
</div>

<script>
(function () {
    const input       = document.getElementById('import-pdf-input');
    const area        = document.getElementById('import-upload-area');
    const filesList   = document.getElementById('import-files-list');
    const importBtn   = document.getElementById('import-btn');
    const clearBtn    = document.getElementById('import-clear-btn');
    const spinner     = document.getElementById('import-spinner');
    const resultArea  = document.getElementById('import-result');

    let selectedFiles = [];

    area.addEventListener('dragover', (e) => { e.preventDefault(); area.classList.add('border-white/70'); });
    area.addEventListener('dragleave', () => area.classList.remove('border-white/70'));
    area.addEventListener('drop', (e) => {
        e.preventDefault();
        area.classList.remove('border-white/70');
        handleFiles([...e.dataTransfer.files].filter(f => f.type === 'application/pdf'));
    });

    input.addEventListener('change', () => handleFiles([...input.files]));

    function handleFiles(newFiles) {
        selectedFiles = [...selectedFiles, ...newFiles].slice(0, 10);
        renderFileList();
    }

    function renderFileList() {
        if (selectedFiles.length === 0) {
            filesList.classList.add('hidden');
            importBtn.classList.add('hidden');
            clearBtn.classList.add('hidden');
            return;
        }
        filesList.classList.remove('hidden');
        importBtn.classList.remove('hidden');
        clearBtn.classList.remove('hidden');
        filesList.innerHTML = selectedFiles.map((f, i) => `
            <div class="flex items-center justify-between bg-white/10 rounded-lg px-4 py-2 text-sm text-white">
                <span>📄 ${f.name} <span class="text-white/50">(${(f.size/1024/1024).toFixed(1)} MB)</span></span>
                <button onclick="removerArquivo(${i})" class="text-white/50 hover:text-red-400 ml-4">✕</button>
            </div>
        `).join('');
    }

    window.removerArquivo = function(index) {
        selectedFiles.splice(index, 1);
        renderFileList();
        input.value = '';
    };

    window.limparImportacao = function() {
        selectedFiles = [];
        input.value   = '';
        resultArea.classList.add('hidden');
        resultArea.innerHTML = '';
        renderFileList();
    };

    window.executarImportacao = async function() {
        if (selectedFiles.length === 0) return;

        importBtn.disabled = true;
        spinner.classList.remove('hidden');
        resultArea.classList.add('hidden');

        const formData = new FormData();
        selectedFiles.forEach(f => formData.append('pdfs[]', f));
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

        try {
            const resp = await fetch('{{ route("tabelas.importar.pdf") }}', {
                method: 'POST',
                body: formData,
            });
            const data = await resp.json();
            renderResult(data);
        } catch (err) {
            renderResult({ sucesso: false, erros: ['Erro de comunicação: ' + err.message], avisos: [], detalhes: [] });
        } finally {
            importBtn.disabled = false;
            spinner.classList.add('hidden');
        }
    };

    function renderResult(data) {
        resultArea.classList.remove('hidden');

        const cor    = data.sucesso ? 'green' : 'red';
        const icone  = data.sucesso ? '✅' : '❌';
        const titulo = data.sucesso ? 'Importação concluída!' : 'Importação com erros';

        let html = `<div class="space-y-5">
            <div class="rounded-xl border border-${cor}-500/40 bg-${cor}-500/10 p-5 space-y-4">
                <h3 class="text-${cor}-300 font-semibold text-lg">${icone} ${titulo}</h3>`;

        if (data.sucesso) {
            html += `<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                ${stat('Cidades', data.cidades_encontradas, 'blue')}
                ${stat('Cadastradas', data.tabelas_cadastradas, 'green')}
                ${stat('Atualizadas', data.tabelas_atualizadas, 'yellow')}
                ${stat('Registros', data.registros_gravados, 'purple')}
            </div>`;
        }

        if (data.erros && data.erros.length > 0) {
            html += `<div class="space-y-1">
                <p class="text-red-300 font-medium text-sm">Erros (${data.erros.length})</p>
                ${data.erros.map(e => `<p class="text-red-200 text-sm bg-red-900/30 rounded px-3 py-1">⛔ ${e}</p>`).join('')}
            </div>`;
        }

        if (data.avisos && data.avisos.length > 0) {
            html += `<div class="space-y-1">
                <p class="text-yellow-300 font-medium text-sm">Avisos</p>
                ${data.avisos.map(a => `<p class="text-yellow-200 text-sm bg-yellow-900/30 rounded px-3 py-1">⚠️ ${a}</p>`).join('')}
            </div>`;
        }

        if (data.detalhes && data.detalhes.length > 0) {
            html += `<details class="cursor-pointer">
                <summary class="text-white/60 text-sm hover:text-white">Ver detalhes (${data.detalhes.length} tabelas)</summary>
                <div class="mt-2 space-y-0.5 max-h-40 overflow-y-auto">
                    ${data.detalhes.map(d => `<p class="text-white/50 text-xs font-mono px-2">${d}</p>`).join('')}
                </div>
            </details>`;
        }

        html += `</div>`; // fecha card principal

        // ── Blocos SQL para produção ──────────────────────────────────────
        if (data.sql_insert || data.sql_update) {
            html += `<div class="rounded-xl border border-blue-500/30 bg-blue-500/5 p-5 space-y-4">
                <h3 class="text-blue-300 font-semibold text-base">🗄️ SQL para o Servidor de Produção</h3>
                <p class="text-white/50 text-xs">
                    Copie o bloco adequado e execute no banco de produção via phpMyAdmin ou cliente MySQL.<br>
                    <span class="text-green-300 font-medium">INSERT</span> — use se os registros <b>não existem</b> ainda em produção. &nbsp;
                    <span class="text-yellow-300 font-medium">UPDATE</span> — use se os registros <b>já existem</b> e precisam ser atualizados.
                </p>`;

            if (data.sql_insert) {
                html += sqlBlock('INSERT — Inserir novos registros', data.sql_insert, 'green', 'sql-insert-block');
            }

            if (data.sql_update) {
                html += sqlBlock('UPDATE — Atualizar registros existentes', data.sql_update, 'yellow', 'sql-update-block');
            }

            html += `</div>`;
        }

        html += `</div>`; // fecha space-y-5
        resultArea.innerHTML = html;
    }

    function sqlBlock(titulo, sql, cor, id) {
        return `
        <div class="space-y-2">
            <div class="flex items-center justify-between">
                <p class="text-${cor}-300 font-medium text-sm">${titulo}</p>
                <button onclick="copiarSQL('${id}')"
                        class="text-xs bg-${cor}-500/20 hover:bg-${cor}-500/40 text-${cor}-300 px-3 py-1 rounded-lg transition-all">
                    📋 Copiar SQL
                </button>
            </div>
            <textarea id="${id}" readonly rows="8"
                      class="w-full bg-black/40 text-green-200 text-xs font-mono border border-white/10 rounded-lg p-3 resize-y focus:outline-none"
                      style="min-height:120px">${escHtml(sql)}</textarea>
        </div>`;
    }

    window.copiarSQL = function(id) {
        const el = document.getElementById(id);
        navigator.clipboard.writeText(el.value).then(() => {
            const btn = el.previousElementSibling.querySelector('button');
            const orig = btn.textContent;
            btn.textContent = '✅ Copiado!';
            setTimeout(() => btn.textContent = orig, 2000);
        });
    };

    function escHtml(str) {
        return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    function stat(label, value, color) {
        return `<div class="bg-${color}-500/20 rounded-lg p-3">
            <p class="text-${color}-300 text-2xl font-bold">${value ?? 0}</p>
            <p class="text-white/60 text-xs mt-1">${label}</p>
        </div>`;
    }
})();
</script>
