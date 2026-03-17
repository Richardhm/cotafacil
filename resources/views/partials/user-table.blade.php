@if($users->isEmpty())
    <div class="text-center bg-gray-100 text-black w-[90%] mx-auto rounded p-3">
        <p class="font-bold text-sm">Sem usuários cadastrados para a sua assinatura.</p>
    </div>
@else
    <div class="relative overflow-x-auto rounded-lg shadow-md w-full">
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <!-- Cabeçalhos -->
            <thead class="text-xs md:text-lg bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px]">
            <!-- Cabeçalho para telas maiores -->
            <tr class="hidden md:table-row">
                <th class="px-2 py-1 text-white text-sm">Foto</th>
                <th class="py-1 text-white text-sm">Nome</th>
                <th class="py-1 text-white text-sm">Email</th>
                <th class="py-1 text-white text-sm">Telefone</th>
                <th class="py-1 text-white text-sm">Editar</th>
                <th class="py-1 text-white text-sm">Liga/Des</th>
            </tr>
            <!-- Cabeçalho para dispositivos móveis -->
            <tr class="md:hidden">
                <th colspan="6" class="px-2 py-1 text-white text-center">
                    Lista de Usuários
                </th>
            </tr>
            </thead>

            <!-- Corpo -->
            <tbody>
            @foreach($users as $user)
                <tr class="bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] border-b md:table-row block mb-4 md:mb-0 rounded-lg">
                    <!-- Coluna Foto -->
                    <td class="px-2 py-1 flex justify-center md:table-cell dark:text-white data-label:Foto">
                        @if(!empty($user['imagem']))
                            <img class="w-12 h-12 rounded-full" src="{{ asset('storage/'.$user['imagem']) }}" alt="{{ $user['name'] }}">
                        @else
                            <div class="w-12 h-12 flex items-center justify-center bg-gray-300 rounded-full">
                                <svg class="w-6 h-6 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A12.083 12.083 0 0112 15c2.5 0 4.847.735 6.879 2.002M15 11a3 3 0 11-6 0 3 3 0 016 0zm7 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        @endif
                    </td>

                    <!-- Coluna Nome -->
                    <td class="py-1 pl-2 text-white md:table-cell block before:content-['Nome:'] before:float-left before:font-bold before:md:content-none">
                        {{ $user['name'] }}
                    </td>

                    <!-- Coluna Email -->
                    <td class="py-1 pl-2 text-white md:table-cell block before:content-['Email:'] before:float-left before:font-bold before:md:content-none">
                        {{ $user['email'] }}
                    </td>

                    <!-- Coluna Telefone -->
                    <td class="py-1 pl-2 text-white md:table-cell block before:content-['Telefone:'] before:float-left before:font-bold before:md:content-none">
                        {{ $user['phone'] }}
                    </td>

                    <!-- Coluna Editar -->
                    <td class="py-1 pl-2 text-white md:table-cell block before:content-['Editar:'] before:float-left before:font-bold before:md:content-none">
                        <button type="button" id="openModal" data-id="{{ $user['id'] }}"
                                class="edit focus:outline-none text-white bg-[rgba(254,254,254,0.18)] backdrop-blur-[15px] focus:ring-2 focus:ring-purple-300 font-medium rounded-md p-1 md:p-2 text-xs md:text-sm">
                            ✍️
                        </button>
                    </td>

                    <!-- Coluna Liga/Des -->
                    <td class="py-1 pl-2 text-white md:table-cell block before:content-['Status:'] before:float-left before:font-bold before:md:content-none">
                        <label class="switch">
                            <input type="checkbox" class="toggle-switch" data-id="{{ $user['id'] }}" {{ $user['status'] == 1 ? "checked" : '' }}>
                            <span class="slider"></span>
                        </label>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endif

<!-- Estilo CSS para responsividade -->
<style>
    /* Mobile: Ajusta os elementos da tabela como blocos */
    @media (max-width: 768px) {
        table {
            border-spacing: 0;
        }

        tbody tr {
            display: block;
            margin-bottom: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 0.5rem;
            padding: 0.5rem;
        }

        td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
        }

        td:before {
            content: attr(data-label);
            font-weight: bold;
            color: #ffffff;
        }

        thead {
            display: none;
        }
    }

    /* Adiciona espaço para botões em telas menores */
    button {
        max-width: 90%;
    }

    /* Ajusta slider de liga/desliga */
    label.switch {
        display: flex;
        align-items: center;
    }
</style>
