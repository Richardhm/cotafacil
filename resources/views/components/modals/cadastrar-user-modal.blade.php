<div id="user-modal" class="fixed inset-0 hidden flex items-center justify-center z-50">
    <div class="bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] shadow-lg border-white border-4 rounded-lg p-6 w-96 relative">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold text-white">Adicionar Usuário</h2>
            <svg id="close-modal" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                 stroke-width="2.5" stroke="currentColor"
                 class="size-6 border-white border-4 rounded hover:cursor-pointer text-white">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </div>

        <div id="aviso-trial" class="hidden mb-3 p-3 bg-yellow-500/20 border border-yellow-400/50 rounded-lg text-yellow-200 text-sm">
            <strong>Você está em trial.</strong> O pagamento inclui sua conta + o novo usuário.<br>
            Total: <span id="aviso-trial-valor" class="font-bold"></span>
        </div>

        <div class="mb-3">
            <label class="block text-sm font-medium text-white mb-1">Nome</label>
            <input type="text" id="u-name" placeholder="Nome completo"
                   class="w-full rounded-md border border-white/30 bg-white/10 text-white placeholder-white/40 px-3 py-2 text-sm focus:outline-none focus:border-blue-400">
        </div>
        <div class="mb-3">
            <label class="block text-sm font-medium text-white mb-1">E-mail</label>
            <input type="email" id="u-email" placeholder="email@exemplo.com"
                   class="w-full rounded-md border border-white/30 bg-white/10 text-white placeholder-white/40 px-3 py-2 text-sm focus:outline-none focus:border-blue-400">
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-white mb-1">Telefone</label>
            <input type="text" id="u-phone" placeholder="(00) 0 0000-0000"
                   class="w-full rounded-md border border-white/30 bg-white/10 text-white placeholder-white/40 px-3 py-2 text-sm focus:outline-none focus:border-blue-400">
        </div>

        <div id="form-erro" class="hidden text-red-300 text-sm mb-3"></div>

        <div class="flex justify-end gap-2">
            <button type="button" id="cancel-modal"
                    class="bg-red-600 text-white px-4 py-2 rounded-lg shadow hover:bg-red-700 transition text-sm">
                Cancelar
            </button>
            <button type="button" id="btn-salvar"
                    class="bg-blue-500 text-white px-4 py-2 rounded-lg shadow hover:bg-blue-600 transition text-sm font-semibold">
                Salvar
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal   = document.getElementById('user-modal');
    const csrf    = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    if (typeof Inputmask !== 'undefined') {
        new Inputmask('(99) 9 9999-9999').mask(document.getElementById('u-phone'));
    }

    function openModal() {
        document.getElementById('u-name').value  = '';
        document.getElementById('u-email').value = '';
        document.getElementById('u-phone').value = '';
        document.getElementById('form-erro').classList.add('hidden');
        document.getElementById('aviso-trial').classList.add('hidden');
        modal.classList.remove('hidden');

        fetch('{{ route("equipe.verificar") }}?info=1')
            .then(r => r.json())
            .then(d => {
                if (d.is_trial) {
                    document.getElementById('aviso-trial-valor').textContent =
                        d.valor_fmt + ' (' + d.quantidade + ' × R$29,90)';
                    document.getElementById('aviso-trial').classList.remove('hidden');
                }
            }).catch(() => {});
    }

    function closeModal() { modal.classList.add('hidden'); }

    document.getElementById('toggle-modal')?.addEventListener('click', openModal);
    document.getElementById('close-modal').addEventListener('click', closeModal);
    document.getElementById('cancel-modal').addEventListener('click', closeModal);
    modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });

    document.getElementById('btn-salvar').addEventListener('click', async () => {
        const name  = document.getElementById('u-name').value.trim();
        const email = document.getElementById('u-email').value.trim();
        const phone = document.getElementById('u-phone').value.trim();
        const erro  = document.getElementById('form-erro');

        if (!name)  { erro.textContent = 'Informe o nome.';    erro.classList.remove('hidden'); return; }
        if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            erro.textContent = 'E-mail inválido.'; erro.classList.remove('hidden'); return;
        }
        if (!phone) { erro.textContent = 'Informe o telefone.'; erro.classList.remove('hidden'); return; }
        erro.classList.add('hidden');

        const btn = document.getElementById('btn-salvar');
        btn.disabled    = true;
        btn.textContent = 'Salvando…';

        const body = new FormData();
        body.append('_token', csrf());
        body.append('name',  name);
        body.append('email', email);
        body.append('phone', phone);

        try {
            const r = await fetch('{{ route("equipe.salvar") }}', { method: 'POST', body });
            const d = await r.json();
            if (d.success) {
                closeModal();
                if (typeof window.adicionarLinhaInativa === 'function') window.adicionarLinhaInativa(d);
            } else {
                erro.textContent = d.error ?? 'Erro ao salvar.';
                erro.classList.remove('hidden');
            }
        } catch (_) {
            erro.textContent = 'Erro de conexão.';
            erro.classList.remove('hidden');
        } finally {
            btn.disabled    = false;
            btn.textContent = 'Salvar';
        }
    });
});
</script>
