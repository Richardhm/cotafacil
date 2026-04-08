function openPaymentModal() {
    document.getElementById('paymentModal').classList.remove('hidden');
}

function closePaymentModal() {
    document.getElementById('paymentModal').classList.add('hidden');
}

// Toast com link
function showUpgradeToast() {
    Toastify({
        text: 'Limite trial atingido! <a href="#" onclick="openPaymentModal()" class="font-semibold underline ml-2">Fazer upgrade</a>',
        duration: -1, // Toast permanente
        gravity: 'top',
        position: 'right',
        backgroundColor: '#eab308',
        escapeMarkup: false,
        className: '!bg-yellow-600 !text-white',
        stopOnFocus: true
    }).showToast();
}


document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('user-modal');
    const toggleModalButton = document.getElementById('toggle-modal');
    const closeModalButton = document.getElementById('close-modal');
    const cancelModalButton = document.getElementById('cancel-modal');
    const userForm = document.getElementById('user-form');

    const phoneInput = document.getElementById('phone');
    const im = new Inputmask('(99) 9 9999-9999');
    im.mask(phoneInput);

    const openModal = () => {
        modal.classList.remove('hidden', 'translate-x-full');
        modal.classList.add('translate-x-0');
        document.body.classList.add('overflow-hidden');
    };

    const closeModal = () => {
        modal.classList.add('translate-x-full');
        modal.classList.remove('translate-x-0');
        setTimeout(() => {
            modal.classList.add('hidden');
            userForm.reset();
            document.body.classList.remove('overflow-hidden'); // Restaurar scroll
        }, 500);
    };

    if (toggleModalButton && modal) {
        toggleModalButton.addEventListener('click', function () {
            if (modal.classList.contains('hidden')) {
                openModal();
            } else {
                closeModal();
            }
        });
    }

    if (closeModalButton) {
        closeModalButton.addEventListener('click', closeModal);
    }

    if (cancelModalButton) {
        cancelModalButton.addEventListener('click', closeModal);
    }

    // Fechar modal ao clicar fora
    document.addEventListener('click', function (event) {
        if (!modal.contains(event.target) && !toggleModalButton.contains(event.target)) {
            closeModal();
        }
    });

    if (userForm) {
        userForm.addEventListener('submit', function (event) {
            event.preventDefault();

            const phoneInput = document.getElementById('phone');
            const phonePattern = /^\(\d{2}\) \d \d{4}-\d{4}$/; // Regex para o formato (99) 9 9999-9999

            if (!phonePattern.test(phoneInput.value)) {
                toastr.error("Por favor, insira um telefone válido.", "Erro");
                event.preventDefault();
                return;
            }


            let load = document.querySelector(".ajax_load");
            load.style.display = "flex";
            const formData = new FormData(userForm);
            fetch(routes.storeUser, {
                method: 'POST',
                body: formData,
            })
                .then(response => response.json())
                .then(data => {

                    load.style.display = "none";
                    if (data.success === 'true' || data.success === true) {
                        document.getElementById('user-table').innerHTML = data.html;
                        if(data.emails_cobrados >= 1) {
                            document.querySelector('.preco_total').textContent  = data.preco_total;

                        }
                        closeModal();

                    } else if(data.limite) {
                        const toast = toastr.warning(
                            `Limite de 3 usuários atingido no trial. <a href='${assinaturaEdit}' id="upgrade-link" class="text-blue-200 hover:text-blue-400 underline">Clique aqui para assinar</a>`,
                            'Limite Excedido',
                            {
                                timeOut: 30000, // 30 segundos
                                progressBar: true,
                                closeButton: true,
                                allowHtml: true, // Permite HTML no conteúdo
                                onclick: function() {
                                    //toastr.clear(this);
                                    //openPaymentModal();
                                }
                            }
                    );
                    } else {
                        toastr.error("Erro ao tentar cadastrar usuario.", "Erro");
                        //console.log(data);
                        //alert("Erro ao cadastrar o usuário");
                    }
                })
                .catch(error => {
                    load.style.display = "none";
                    if (error.status === 422) {
                        error.json().then(data => {
                            Object.values(data.errors).forEach(msgs => {
                                msgs.forEach(msg => toastr.error(msg, 'Erro'));
                            });
                        });
                    } else {
                        toastr.error("Erro ao tentar cadastrar usuário.", "Erro");
                    }
                });
        });
    }
});

$(document).ready(function(){
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    const openModalButtonEdit = document.getElementById('openModal');
    const closeModalButtonEdit = document.getElementById('closeModal');
    const modalEdit = document.getElementById('editUserModal');

    $("body").on('change','#regiao',function(){
        let regiao = $(this).val();
        $.ajax({
            url:routes.gerenciamentoRegiao,
            method:"POST",
            data: {regiao},
            success:function(res) {

                if(res == true) {
                    toastr.success("Região alterada com sucesso", 'Sucesso');
                } else {
                    toastr.error("Erro ao alterar região", 'Error');
                }
            }
        });
    });

    $("input[name='layout_id']").on('change',function(){

        let valor = $(this).val();
        let load = $(".ajax_load");
        $.ajax({
            url:routes.layoutsSelect,
            method:"POST",
            data: {valor},
            beforeSend: function () {
                load.fadeIn(100).css("display", "flex");
            },
            success:function(res) {
                load.fadeOut(100).css("display", "none");
                if(res == "sucesso") {
                    toastr.success("Layout trocado com sucesso.", "Sucesso",{
                        toastClass: "toast-custom-width"
                    });
                } else {
                    toastr.error("Erro ao mudar de Layout. Verifique e tente novamente.", "Error",{
                        toastClass: "toast-custom-width"
                    });
                }
            }
        });

    });




    $('#email').on('input', function() {
        const emailInput = $(this).val();
        const emailError = $('#email-error');
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+\.com$/; // Verifica se termina com .com

        if (!regex.test(emailInput)) {
            emailError.show(); // Exibe a mensagem de erro
        } else {
            emailError.hide(); // Esconde a mensagem de erro
        }
    });





    // Abrir a modal
    $("body").on('click','#openModal',function(){
        let id = $(this).attr('data-id');
        $.ajax({
            url: routes.usersGet, // Rota definida no Laravel
            method: 'POST',
            data: {
                id
            },
            success: function (data) {

                // Preenche o formulário com os dados do usuário
                $('#name_edit').val(data.name);
                $('#email_edit').val(data.email);
                $('#phone_edit').val(data.phone);
                $('#id_edit').val(data.id);

                // Se tiver imagem, exibe na visualização
                if (data.imagem) {

                    $('#imagem_edit').attr('src',data.imagem);
                } else {
                    $('#imagem_edit').attr('src','avatar-padrao.png');
                }
            },
            error: function (xhr) {
                console.log('Erro ao buscar os dados do usuário:', xhr.responseJSON);
                alert('Erro ao buscar os dados do usuário.');
            }
        });
        modalEdit.classList.remove('hidden');
        modalEdit.classList.add('flex');
    });

    $("body").on('click','#closeModal',function(){
        modalEdit.classList.add('hidden');
        modalEdit.classList.remove('flex');
    });

    // Fechar a modal ao clicar fora dela
    modalEdit.addEventListener('click', (e) => {
        if (e.target === modalEdit) {
            modalEdit.classList.add('hidden');
            modalEdit.classList.remove('flex');
        }
    });

    $(document).on('change', '.toggle-switch', function () {
        let load = $(".ajax_load");
        load.fadeIn(100).css("display", "flex");
        let userId = $(this).data('id');
        let status = $(this).is(':checked');
        let $toggle = $(this);

        $.ajax({
            url: routes.usersAlterar,
            method: "POST",
            data: { userId, status },
            success: function (res) {
                load.fadeOut(100).css("display", "none");
                document.querySelector('.preco_total').textContent = res.preco_total;
            },
            error: function (xhr) {
                load.fadeOut(100).css("display", "none");
                // Reverter o toggle para o estado anterior
                $toggle.prop('checked', !status);
                let msg = xhr.responseJSON && xhr.responseJSON.error
                    ? xhr.responseJSON.error
                    : 'Erro ao alterar status do usuário.';
                toastr.error(msg, 'Erro');
            }
        });
    });


    $("body").on("submit", "#editUserForm", function (e) {
        e.preventDefault();
        let load = $(".ajax_load");
        load.fadeIn(100).css("display", "flex");
        let formData = new FormData(this);

        $.ajax({
            url: routes.usersUpdate, // Rota definida no Laravel
            method: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                load.fadeOut(100).css("display", "none");
                if (response.success) {
                    //alert(response.message);
                    // Atualiza os dados na interface ou fecha a modal
                    location.reload(); // Recarrega a página para refletir as alterações
                }
            },
            error: function (xhr) {
                load.fadeOut(100).css("display", "none");
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    Object.values(xhr.responseJSON.errors).forEach(function (msgs) {
                        msgs.forEach(function (msg) { toastr.error(msg, 'Erro'); });
                    });
                } else {
                    toastr.error("Erro ao atualizar os dados.", "Erro");
                }
            },
        });
    });


    $("body").on('click', '.delete', function(e) {
        e.preventDefault();
        let id = $(this).attr('data-id');

        // Exibe a confirmação com SweetAlert2
        Swal.fire({
            title: "Tem certeza?",
            text: "Você não poderá reverter esta ação!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Sim, excluir!",
            cancelButtonText: "Cancelar"
        }).then((result) => {
            if (result.isConfirmed) {
                // Se confirmado, faz a requisição AJAX para deletar o usuário
                $.ajax({
                    url: deletarUser,
                    method: "POST",
                    data: { id: id },
                    success: function(res) {
                        if (res.success) {
                            // Atualiza a tabela de usuários
                            $("#user-table").html(res.html);

                            // Exibe um Toastr de sucesso
                            toastr.success("Usuário excluído com sucesso!", 'Sucesso',{

                                toastClass: "toast-custom-width" // Aplica a classe personalizada

                            });

                        } else {
                            toastr.error("Ocorreu um erro ao excluir o usuário.", "Erro");
                        }
                    },
                    error: function() {
                        toastr.error("Erro ao tentar excluir o usuário.", "Erro");
                    }
                });
            }
        });
        return false;
    });
});

document.getElementById('imagem_edit').addEventListener('click', function() {
    document.getElementById('avatar-input').click();
});

document.getElementById('avatar-input').addEventListener('change', function(event) {
    if (event.target.files.length > 0) {

        let id = $("#id_edit").val();

        let load = document.querySelector(".ajax_load");
        load.style.display = "flex";




        const form = new FormData();
        form.append('imagem',event.target.files[0]);
        form.append('id',id);


        fetch('/profile/user/imagem/atualizar', {
            method: 'POST',
            body: form,
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        }).then(response => response.json())
            .then(data => {
                if (data.message) {
                    load.style.display = "none";
                    // Atualiza a imagem de perfil no DOM
                    const userAvatar = document.getElementById('imagem_edit');
                    const newSrc = URL.createObjectURL(event.target.files[0]);
                    userAvatar.src = newSrc;
                    document.getElementById('user-table').innerHTML = data.html;
                } else {
                    console.error(data.error);
                }
            }).catch(error => {
            console.error('Erro:', error);
        });
    }
});
