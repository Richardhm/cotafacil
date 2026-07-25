#!/usr/bin/env bash
#
# Deploy da padronização de vínculos administradora/plano/cidade.
#
# Uso:
#   bash deploy-vinculos.sh
#
# Para em qualquer erro e pede confirmação antes de cada passo que grava.
# Rodar na raiz do projeto, no servidor.

set -euo pipefail

MIGRATION_CASCADE="database/migrations/2026_07_24_103000_change_tabela_origens_fk_to_cascade_on_administradora_planos.php"
MIGRATION_INDICE="database/migrations/2026_07_24_111500_add_unique_index_to_administradora_planos.php"

confirmar() {
    read -r -p ">>> $1 [s/N] " resposta
    case "$resposta" in
        [sS]) return 0 ;;
        *) echo "Abortado."; exit 1 ;;
    esac
}

titulo() {
    echo
    echo "==================================================================="
    echo " $1"
    echo "==================================================================="
}

# --------------------------------------------------------------------------
titulo "PASSO 0 — Backup completo do banco"
# --------------------------------------------------------------------------
# Lê as credenciais do .env do próprio projeto.
DB_DATABASE=$(grep -E '^DB_DATABASE=' .env | cut -d= -f2- | tr -d '"'"'"' ')
DB_USERNAME=$(grep -E '^DB_USERNAME=' .env | cut -d= -f2- | tr -d '"'"'"' ')
ARQUIVO_DUMP="backup-antes-padronizacao-$(date +%Y%m%d-%H%M%S).sql"

echo "Banco: $DB_DATABASE"
echo "Dump:  $ARQUIVO_DUMP"
confirmar "Gerar o dump agora? (vai pedir a senha do MySQL)"

mysqldump -u "$DB_USERNAME" -p "$DB_DATABASE" > "$ARQUIVO_DUMP"
echo "Dump gerado: $(du -h "$ARQUIVO_DUMP" | cut -f1)"
echo "GUARDE ESTE ARQUIVO FORA DO SERVIDOR antes de continuar."
confirmar "Dump salvo em local seguro?"

# --------------------------------------------------------------------------
titulo "PASSO 1 — Colocar o site em manutenção"
# --------------------------------------------------------------------------
php artisan down --render="errors::503" || true

# --------------------------------------------------------------------------
titulo "PASSO 2 — Limpar caches antigos"
# --------------------------------------------------------------------------
php artisan config:clear
php artisan route:clear
php artisan view:clear

# --------------------------------------------------------------------------
titulo "PASSO 3 — FK de cidade para ON DELETE CASCADE"
# --------------------------------------------------------------------------
# Apaga os vínculos órfãos (tabela_origens_id NULL) e troca a FK.
# Tem que rodar ANTES da padronização.
php artisan migrate --path="$MIGRATION_CASCADE" --force

# --------------------------------------------------------------------------
titulo "PASSO 4 — Padronização (SIMULAÇÃO)"
# --------------------------------------------------------------------------
php artisan vinculos:padronizar

echo
echo "Confira os números acima:"
echo "  - a assinatura modelo é a do Richard e tem 213 vínculos?"
echo "  - os nomes de Soluction e das 8 cidades bateram (senão o comando já teria abortado)?"
echo "  - a quantidade de linhas a inserir/remover faz sentido?"
confirmar "Números conferem?"

# --------------------------------------------------------------------------
titulo "PASSO 5 — Padronização (VALENDO)"
# --------------------------------------------------------------------------
php artisan vinculos:padronizar --executar

echo
echo "Backup das linhas removidas ficou em storage/app/backup-padronizar-vinculos-*.sql"
confirmar "Conferência acima mostrou 0 assinaturas divergentes?"

# --------------------------------------------------------------------------
titulo "PASSO 6 — Índice único"
# --------------------------------------------------------------------------
# Só roda depois da padronização: se houver duplicata, a criação do índice falha.
php artisan migrate --path="$MIGRATION_INDICE" --force

# --------------------------------------------------------------------------
titulo "PASSO 7 — Recriar caches"
# --------------------------------------------------------------------------
php artisan config:cache
php artisan route:cache
php artisan view:cache

# --------------------------------------------------------------------------
titulo "PASSO 8 — Tirar da manutenção"
# --------------------------------------------------------------------------
php artisan up

titulo "PRONTO"
echo "Teste agora:"
echo "  1. /configuracoes -> aba Administradora/Plano/Cidade -> Ver Vínculos"
echo "     (modal deve abrir com as cidades; clicar numa cidade mostra os planos)"
echo "  2. Excluir um vínculo pelo modal (some sem recarregar a página)"
echo "  3. Cadastrar um cliente novo e conferir se nasceu com 213 vínculos:"
echo "     php artisan tinker --execute=\"echo DB::table('administradora_planos')->where('assinatura_id', <ID>)->count();\""
