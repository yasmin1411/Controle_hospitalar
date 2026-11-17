});





/*

|------------------------------------------------------------------

| MODAL DE RECUPERAÇÃO DE ACESSO

|------------------------------------------------------------------

|

| Abre e fecha a orientação visual sem modificar o backend.

|

*/

const modal = document.getElementById('modalRecuperacao');

const btnRecuperar = document.getElementById('btnRecuperarSenha');

const btnFechar = document.getElementById('btnFecharModal');



const fecharModal = function () {

    modal.classList.remove('ativo');

    modal.setAttribute('aria-hidden', 'true');

};



btnRecuperar.addEventListener('click', function () {

    modal.classList.add('ativo');

    modal.setAttribute('aria-hidden', 'false');

});



btnFechar.addEventListener('click', fecharModal);



modal.addEventListener('click', function (event) {

    if (event.target === modal) fecharModal();

});



document.addEventListener('keydown', function (event) {

    if (event.key === 'Escape' && modal.classList.contains('ativo')) {

        fecharModal();

    }

});





/*

|------------------------------------------------------------------

| ESTADO DE PROCESSAMENTO DO LOGIN

|------------------------------------------------------------------

|

| Muda o visual do botão enquanto o formulário está sendo enviado.

| Não altera a lógica PHP do login.

|

*/

const formLogin = document.getElementById('formLogin');

const btnLogin = document.getElementById('btnLogin');

const textoLogin = document.getElementById('textoLogin');

const iconeLogin = document.getElementById('iconeLogin');



formLogin.addEventListener('submit', function () {

    btnLogin.classList.add('carregando');

    btnLogin.disabled = true;

    iconeLogin.className = 'bi bi-arrow-repeat me-1';

    textoLogin.textContent = 'Entrando...';

});

</script>


</body>

</html>
