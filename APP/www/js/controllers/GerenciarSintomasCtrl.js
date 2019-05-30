app.controller('GerenciarSintomasCtrl', function ($scope, ionicMaterialInk, $http, $ionicSideMenuDelegate, $ionicPopup, $ionicModal, $state, $ionicScrollDelegate) {
	 // Disparando ação de load
	 //$scope.carregando();
	 // Lista de exames
/* 	 $scope.listaSintomas = [{ id: 10,
		  				descricao: "Nauseas"},
							{  id: 11,
		  				descricao: "Diarreia"},
						{		id: 12,
						descricao: "Constipação"}]; */
	 //exemplo em minhas notificacoes
	 //criar outra lista com $scope.opcoes => sintomas, sentimentos etc.
/* 	 $scope.classificacaoSintomas = [{	id: 10,
						 		descricao: "Sintoma"},
							 		{  id: 20,
						 		descricao: "Estado"}]; */
						 
	 
	 $scope.scrollMainToTop = function() {
		 console.log("aqui");
		    $ionicScrollDelegate.$getByHandle('mainScroll').scrollTop();
	 };	  
	 $scope.doRefresh = function() {
		  // $scope.carregarNotificacoes();
	      // TODO
	 };

	 $scope.validar = function(){
		// Validando os campos 
		$scope.confirmarCadastro();
	}

	$scope.confirmarCadastro = function() {
		$scope.modal.show();
		$scope.configurarConfirmacao();
	};

	/** FIM MÉTODO PARA VALIDAR AS INFORMAÇOES **/


/// PESQUISAR SINTOMAS
			// tiago
	/* 		$scope.sintomasPacientes = [{
				nome_paciente: "Maria Eulália", 
				data_cadastro: "2018-10-28", 
				sintoma: "Constipação", 
				intensidade: 3
			}, 
			{
				nome_paciente: "José Ricardo", 
				data_cadastro: "2018-09-13", 
				sintoma: "Nausea", 
				intensidade: 2
			}, 
 			{
				nome_paciente: "José Ricardo", 
				data_cadastro: "2018-09-13", 
				sintoma: "Nausea", 
				intensidade: 2
			},
			{
				nome_paciente: "José Ricardo", 
				data_cadastro: "2018-09-13", 
				sintoma: "Nausea", 
				intensidade: 2
			}, 
			{
				nome_paciente: "Maria Eulália", 
				data_cadastro: "2018-10-03", 
				sintoma: "Diarréia", 
				intensidade: 3 
			}];
/* */
		/* 	$scope.sintomasPacientes = [{
				"nome_paciente": "Maria Eulália", 
				"data_cadastro": "2018-10-28", 
				"sintoma": "Constipação", 
				"intensidade": 3
			}, 
			{
				"nome_paciente": "José Ricardo", 
				"data_cadastro": "2018-09-13", 
				"sintoma": "Nausea", 
				"intensidade": 2
			}, 
 			{
				"nome_paciente": "José Ricardo", 
				"data_cadastro": "2018-09-13", 
				"sintoma": "Nausea", 
				"intensidade": 2
			},
			{
				"nome_paciente": "José Ricardo", 
				"data_cadastro": "2018-09-13", 
				"sintoma": "Nausea", 
				"intensidade": 2
			}, 
			{
				"nome_paciente": "Maria Eulália", 
				"data_cadastro": "2018-10-03", 
				"sintoma": "Diarréia", 
				"intensidade": 3 
			}]; */

  $scope.pesquisaSintomas = function(){
	// ´Mostrando o carregando
	$scope.carregando();
	$scope.mostrarLista = true;
	// Realizando os filtros

	// COMENTADO EM 25/04 PARA O MOC
		$http({
		method: "POST",
		   timeout:$scope.timeout,
		   data: 'filtroBusca=' + JSON.stringify($scope.listarSintomas),
		   url: $scope.strUrlServico + Constantes.APP_SERVICE_GERENCIAR_SINTOMAS,
		   headers: Util.headers($scope.token)
	  })
	   .then(function(response) {
			$scope.carregado();
			listaUsuarios = [];
			response.data.bolRetorno = true
			if(response.data.bolRetorno == true){
				listaUsuarios = $scope.sintomasPacientes;
			//	listaUsuarios = response.data.result;
			}
 


			response.data.bolRetorno = true
			// Mostrando a lista de usuários , "width" : '50px'
			$scope.mostrarLista = true;
			// Criando a tabela Tiago.
			//Util.montarTabela('listaSintomas', MOC_sintomas<listaSintomas>,[{<listar os campos que quero mostrar. mesmos nomes de variaveis do MOC>}] )
			Util.montarTabela('listarSintomas', listaUsuarios, [{ "data": "nome_paciente", "width": '50px'},{ "data": "sintoma" },{ "data": "intensidade" }], [[ 1, "desc"]]);
	   },function(response) {
		   // Mensagem de erro
		   $scope.falhaCarregamento(response);
	  });

//	
	// // Totais por tipo de exame
/* 	 $http({
	    method: "GET",
	    timeout:$scope.timeout,
	   url: $scope.strUrlServico + Constantes.APP_SERVICE_EXAMES_LISTAR_TOTAIS_TIPO,
	    headers: Util.headers($scope.token)
	 }).then(function(response) {
	 	if(response.data.bolRetorno == true){
	 		totaisPorTipo = response.data.result;
	 	}
	 }, function(response) {
	    // Mensagem de erro
	    $scope.falhaCarregamento(response);
	 }); */
}
// Default mostra a lista de usuários
$scope.mostarListaTa = true;
// Função para alternar entre lista e gráficos
$scope.mostarListaF =  function(bolMostar){
	$scope.mostarListaTa = bolMostar;
	if(!bolMostar) graficoPizza();
}

// Default mostra a lista de usuários
$scope.mostarGrafico = 1;
// Função para alternar entre lista e gráficos
$scope.mostarGraficoTipo =  function(intTipo){
	$scope.mostarGrafico = intTipo;
	if(intTipo == 1) graficoPizza();
	else if(intTipo == 2) graficoBarra();
}
// Detalhamento do exame
$scope.objExame = {};
$scope.carregarDetalhe = function(idExame){
   // ´Mostrando o carregando
	$scope.carregando();
	// Realizando os filtros
	$http({
			method: "GET",
		   timeout:$scope.timeout,
		  url: $scope.strUrlServico + Constantes.APP_SERVICE_EXAMES_RECUPERAR_EXAME_ID + "?intIdExame="+idExame,
		   headers: Util.headers($scope.token)
	   })
	   .then(function(response) {
			$scope.carregado();
			if(response.data.bolRetorno == true){
				$scope.objExame = response.data.result;
			}else{
				$scope.closeConfirmar();
				var alertPopup = $ionicPopup.alert({
				   title: "Erro",
				   template: "Exame Não Encontrado!"
				});
				alertPopup.then(function(res) { });
			}
	   }, function(response) {
		   // Mensagem de erro
		   $scope.falhaCarregamento(response);
	   });
}
  
// FIM PESQUISAR SINTOMAS
  
  	});
